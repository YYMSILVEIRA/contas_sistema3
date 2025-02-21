<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Captura os filtros da URL
$filtro_fornecedor = $_GET['fornecedor'] ?? '';
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_data_inicio = $_GET['data_inicio'] ?? '';
$filtro_data_fim = $_GET['data_fim'] ?? '';
$filtro_situacao = $_GET['situacao'] ?? '';
$filtro_descricao = $_GET['descricao_pesquisa'] ?? '';

// Monta a cláusula WHERE para os filtros
$where_pagar = "id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . intval($_SESSION['user_id']) . ")";
$where_receber = $where_pagar;

if (!empty($filtro_fornecedor)) {
    $where_pagar .= " AND UPPER(fornecedor) LIKE UPPER(" . $pdo->quote("%".$filtro_fornecedor."%") . ")";
} elseif ((empty($filtro_fornecedor)) and (!empty($filtro_cliente))) {
    $where_pagar .= " AND 0=1";
}

if (!empty($filtro_cliente)) {
    $where_receber .= " AND UPPER(cliente) LIKE UPPER(" . $pdo->quote("%".$filtro_cliente."%") . ")";
} elseif (empty($filtro_cliente) and (!empty($filtro_fornecedor))) {
    $where_receber .= " AND 0=1";
}

if (!empty($filtro_data_inicio) && !empty($filtro_data_fim)) {
    $where_pagar .= " AND data BETWEEN " . $pdo->quote($filtro_data_inicio) . " AND " . $pdo->quote($filtro_data_fim);
    $where_receber .= " AND data BETWEEN " . $pdo->quote($filtro_data_inicio) . " AND " . $pdo->quote($filtro_data_fim);
}

if (!empty($filtro_situacao)) {
    $where_pagar .= " AND id_situacao = (SELECT id FROM situacoes WHERE descricao = " . $pdo->quote($filtro_situacao) . ")";
    $where_receber .= " AND id_situacao = (SELECT id FROM situacoes WHERE descricao = " . $pdo->quote($filtro_situacao) . ")";
}

if (!empty($filtro_descricao)) {
    $where_pagar .= " AND UPPER(cp.descricao) LIKE UPPER(" . $pdo->quote("%".$filtro_descricao."%") . ")";
    $where_receber .= " AND UPPER(cr.descricao) LIKE UPPER(" . $pdo->quote("%".$filtro_descricao."%") . ")";
}

// Busca as contas a pagar e a receber
$sql_pagar = "SELECT cp.descricao, cp.valor, DATE_FORMAT(cp.data, '%d/%m/%Y') as data, cp.fornecedor, s.descricao as situacao 
              FROM contas_pagar cp 
              LEFT JOIN situacoes s ON cp.id_situacao = s.id 
              WHERE $where_pagar";
$contas_pagar = $pdo->query($sql_pagar)->fetchAll();

$sql_receber = "SELECT cr.descricao, cr.valor, DATE_FORMAT(cr.data, '%d/%m/%Y') as data, cr.cliente, s.descricao as situacao 
                FROM contas_receber cr 
                LEFT JOIN situacoes s ON cr.id_situacao = s.id 
                WHERE $where_receber";
$contas_receber = $pdo->query($sql_receber)->fetchAll();

// Cria uma nova planilha
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Adiciona cabeçalhos
$sheet->setCellValue('A1', 'Descrição');
$sheet->setCellValue('B1', 'Valor');
$sheet->setCellValue('C1', 'Data');
$sheet->setCellValue('D1', 'Fornecedor/Cliente');
$sheet->setCellValue('E1', 'Situação');
$sheet->setCellValue('F1', 'Tipo de Conta');

// Adiciona os dados das contas a pagar
$row = 2;
foreach ($contas_pagar as $conta) {
    $sheet->setCellValue('A' . $row, $conta['descricao']);
    $sheet->setCellValue('B' . $row, $conta['valor']);
    $sheet->setCellValue('C' . $row, $conta['data']);
    $sheet->setCellValue('D' . $row, $conta['fornecedor']);
    $sheet->setCellValue('E' . $row, $conta['situacao']);
    $sheet->setCellValue('F' . $row, "Conta a Pagar");
    $row++;
}

// Adiciona os dados das contas a receber
foreach ($contas_receber as $conta) {
    $sheet->setCellValue('A' . $row, $conta['descricao']);
    $sheet->setCellValue('B' . $row, $conta['valor']);
    $sheet->setCellValue('C' . $row, $conta['data']);
    $sheet->setCellValue('D' . $row, $conta['cliente']);
    $sheet->setCellValue('E' . $row, $conta['situacao']);
    $sheet->setCellValue('F' . $row, "Conta a Receber");
    $row++;
}

// Configura o cabeçalho para download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="relatorio_contas.xlsx"');
header('Cache-Control: max-age=0');

// Salva a planilha no output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;