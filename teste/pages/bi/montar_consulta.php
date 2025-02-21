<?php
// Inclui a classe Database
require_once './funcoes.php';

// Cria uma instância da classe Database
$db = new Database();

try {
    // Consulta para obter as tabelas
    $sqlTabelas = "SELECT id, nome_tabela AS nome, descricao_tabela AS campos FROM tabelas_indicadores";
    $tabelas = json_decode($db->query($sqlTabelas), true);

    // Consulta para obter os indicadores
    $sqlIndicadores = "SELECT id, nome_indicador AS nome, descricao_indicador AS formula FROM indicadores";
    $indicadores = json_decode($db->query($sqlIndicadores), true);

    // Consulta para obter os filtros
    $sqlFiltros = "SELECT id, nome_filtro AS nome, condicao FROM filtros";
    $filtros = json_decode($db->query($sqlFiltros), true);

    // Formata os dados para o formato desejado
    $response = [
        'tabelas' => array_map(function ($tabela) {
            return [
                'id' => (int)$tabela['id'],
                'nome' => $tabela['nome'],
                'campos' => explode(',', $tabela['campos']) // Converte a string de campos em array
            ];
        }, $tabelas),
        'indicadores' => array_map(function ($indicador) {
            return [
                'id' => (int)$indicador['id'],
                'nome' => $indicador['nome'],
                'formula' => $indicador['formula']
            ];
        }, $indicadores),
        'filtros' => array_map(function ($filtro) {
            return [
                'id' => (int)$filtro['id'],
                'nome' => $filtro['nome'],
                'condicao' => $filtro['condicao']
            ];
        }, $filtros)
    ];

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Em caso de erro, retorna uma mensagem de erro em JSON
    http_response_code(500);
    header('Content-Type: application/json');
    error_log($e->getMessage().__DIR__);
    echo json_encode(['error' => $e->getMessage()]);
}
?>