<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/logs.php';
require_once '../padroes/arquivos_de_padrao.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Caminho para o autoload do Composer



// Registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI'];
registrarLog($pagina_acessada);

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Função para enviar e-mail
function enviarEmail($destinatario, $assunto, $mensagem, $descricao_personalizada = '', $cc_email = '') {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP do Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'yuriyamandumadridsilveira2@gmail.com'; // Seu e-mail
        $mail->Password = 'afae uely bend qfko'; // Sua senha ou senha de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        $mail->Port = 587; // Porta do Gmail

        // Codificação UTF-8
        $mail->CharSet = 'UTF-8';

        // Remetente e destinatário
        $mail->setFrom('yurimadridsilveira2002@outlook.com', 'Resumo Financeiro | Contas Sistema');
        $mail->addAddress($destinatario);

        // Adicionar cópia (CC) se fornecido
        if (!empty($cc_email)) {
            $mail->addCC($cc_email);
        }

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = $assunto;

        // Adicionar a descrição personalizada ao corpo do e-mail
        if (!empty($descricao_personalizada)) {
            $mensagem = "{$descricao_personalizada}" . $mensagem;
        }

        $mail->Body = $mensagem;

        // Envia o e-mail
        $mail->send();
        echo "<script>alert('E-mail enviado com sucesso!');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Falha ao enviar e-mail. Erro: {$mail->ErrorInfo}');</script>";
    }
}

// Captura os valores do formulário
$filtro_fornecedor = $_GET['fornecedor'] ?? '';
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_data_inicio = $_GET['data_inicio'] ?? '';
$filtro_data_fim = $_GET['data_fim'] ?? '';
$filtro_situacao = $_GET['situacao'] ?? '';
$filtro_descricao = $_GET['descricao_pesquisa'] ?? '';
$descricao_personalizada = $_GET['descricao'] ?? '';
$cc_email = $_GET['cc_email'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';

// Parâmetros de ordenação
$ordenar_por = $_GET['ordenar_por'] ?? 'data';
$ordenacao = $_GET['ordenacao'] ?? 'ASC';

// Montar a cláusula WHERE para os filtros
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
if (!empty($filtro_categoria)) {
    $where_pagar .= " AND id_categoria = " . intval($filtro_categoria);
    $where_receber .= " AND id_categoria = " . intval($filtro_categoria);
}

// Total de contas a pagar
$stmt = $pdo->query("SELECT SUM(valor) AS total_pagar FROM contas_pagar cp WHERE ".$where_pagar."");
$resultado_pagar = $stmt->fetch();
$total_pagar = $resultado_pagar['total_pagar'] ?? 0;

// Total de contas a receber
$stmt = $pdo->query("SELECT SUM(valor) AS total_receber FROM contas_receber cr WHERE $where_receber");
$resultado_receber = $stmt->fetch();
$total_receber = $resultado_receber['total_receber'] ?? 0;

// Saldo (Receber - Pagar)
$saldo = $total_receber - $total_pagar;

// Contas a pagar com ordenação
$sql_pagar = "SELECT cp.descricao, cp.valor, DATE_FORMAT(cp.data, '%d/%m/%Y') as data, cp.fornecedor, s.descricao as situacao 
              FROM contas_pagar cp 
              LEFT JOIN situacoes s ON cp.id_situacao = s.id 
              WHERE $where_pagar 
              ORDER BY $ordenar_por $ordenacao";
$stmt = $pdo->query($sql_pagar);
$contas_pagar = $stmt->fetchAll() ?? [];

// Contas a receber com ordenação
$sql_receber = "SELECT cr.descricao, cr.valor, DATE_FORMAT(cr.data, '%d/%m/%Y') as data, cr.cliente, s.descricao as situacao 
                FROM contas_receber cr 
                LEFT JOIN situacoes s ON cr.id_situacao = s.id 
                WHERE $where_receber 
                ORDER BY $ordenar_por $ordenacao";
$stmt = $pdo->query($sql_receber);
$contas_receber = $stmt->fetchAll() ?? [];

// Enviar e-mail se o botão for clicado
if (isset($_GET['enviar_email'])) {
    // Gerar o conteúdo do e-mail
    $assunto = "Relatório Financeiro";

    $mensagem = "
        <h2>Resumo Financeiro</h2>
        <p><strong>Total a Pagar:</strong> R$ " . number_format($total_pagar, 2, ',', '.') . "</p>
        <p><strong>Total a Receber:</strong> R$ " . number_format($total_receber, 2, ',', '.') . "</p>
        <p><strong>Saldo:</strong> R$ " . number_format($saldo, 2, ',', '.') . "</p>

        <h3>Contas a Pagar</h3>
        <table border='1' cellpadding='5' cellspacing='0'>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Pagar Até</th>
                    <th>Fornecedor</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
    ";

    if (!empty($contas_pagar)) {
        foreach ($contas_pagar as $conta) {
            $mensagem .= "
                <tr>
                    <td>{$conta['descricao']}</td>
                    <td>R$ " . number_format($conta['valor'], 2, ',', '.') . "</td>
                    <td>{$conta['data']}</td>
                    <td>{$conta['fornecedor']}</td>
                    <td>{$conta['situacao']}</td>
                </tr>
            ";
        }
    } else {
        $mensagem .= "<tr><td colspan='5'>Nenhuma conta a pagar encontrada.</td></tr>";
    }

    $mensagem .= "
            </tbody>
        </table>

        <h3>Contas a Receber</h3>
        <table border='1' cellpadding='5' cellspacing='0'>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Receber Até</th>
                    <th>Cliente</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
    ";

    if (!empty($contas_receber)) {
        foreach ($contas_receber as $conta) {
            $mensagem .= "
                <tr>
                    <td>{$conta['descricao']}</td>
                    <td>R$ " . number_format($conta['valor'], 2, ',', '.') . "</td>
                    <td>{$conta['data']}</td>
                    <td>{$conta['cliente']}</td>
                    <td>{$conta['situacao']}</td>
                </tr>
            ";
        }
    } else {
        $mensagem .= "<tr><td colspan='5'>Nenhuma conta a receber encontrada.</td></tr>";
    }

    $mensagem .= "
            </tbody>
        </table>
    ";

    // Busca o e-mail do usuário logado
    $sql = "SELECT email FROM usuarios WHERE id = " . intval($_SESSION['user_id']);
    $stmt = $pdo->query($sql);
    $email_usuario = $stmt->fetch()['email'];

    // Enviar o e-mail com a descrição personalizada e cópia (CC)
    enviarEmail($email_usuario, $assunto, $mensagem, $descricao_personalizada, $cc_email);
}

// Exportar para Excel
if (isset($_GET['exportar_excel'])) {
    header('Location: exportar_excel.php?' . http_build_query($_GET));
    exit();
}

// Exportar para PDF
if (isset($_GET['exportar_pdf'])) {
    header('Location: exportar_pdf.php?' . http_build_query($_GET));
    exit();
}
if (!empty($filtro_categoria)) {
    $where_pagar .= " AND id_categoria = " . intval($filtro_categoria);
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: #ffffff;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }
        .card h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .card p {
            font-size: 1.2rem;
            font-weight: bold;
            color: #007AFF; /* Azul padrão do iOS */
        }
        #saldo {
            font-weight: bold;
        }
        .sortable {
            cursor: pointer;
        }
        .sortable:hover {
            background-color: #f8f9fa;
            color: #007AFF;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background-color: #007AFF;
            color: #fff;
            font-weight: 500;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-primary {
            /*background-color: #007AFF;*/
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #005bb5;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 500;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 500;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 500;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <?= Cabecalho("Relatório", "Dashboard", "Contas a Pagar", "Contas a Receber", "dashboard.php", "contas_pagar.php", "contas_receber.php"); ?>

        <!-- Formulário de Filtros -->
        <form method="GET" action="" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="fornecedor" class="form-label">Fornecedor:</label>
                <input type="text" id="fornecedor" name="fornecedor" class="form-control" value="<?= htmlspecialchars($filtro_fornecedor) ?>">
            </div>
            <div class="col-md-3">
                <label for="cliente" class="form-label">Cliente:</label>
                <input type="text" id="cliente" name="cliente" class="form-control" value="<?= htmlspecialchars($filtro_cliente) ?>">
            </div>
            <div class="col-md-3">
                <label for="data_inicio" class="form-label">Data Início:</label>
                <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="<?= htmlspecialchars($filtro_data_inicio) ?>">
            </div>
            <div class="col-md-3">
                <label for="data_fim" class="form-label">Data Fim:</label>
                <input type="date" id="data_fim" name="data_fim" class="form-control" value="<?= htmlspecialchars($filtro_data_fim) ?>">
            </div>
            <div class="col-md-3">
                <label for="categoria" class="form-label">Categoria:</label>
                <select id="categoria" name="categoria" class="form-control">
                    <option value="">Todas</option>
                    <?php
                    // Busca todas as categorias possíveis
                    $stmt = $pdo->query("SELECT id, nome_categoria FROM categorias where id_situacao=1 and  id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . intval($_SESSION['user_id']) . ")");
                    $categorias = $stmt->fetchAll();
                    foreach ($categorias as $categoria): ?>
                        <option value="<?= htmlspecialchars($categoria['id']) ?>" <?= ($filtro_categoria == $categoria['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria['nome_categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="situacao" class="form-label">Situação:</label>
                <select id="situacao" name="situacao" class="form-control">
                    <option value="">Todas</option>
                    <?php
                    // Busca todas as situações possíveis
                    $stmt = $pdo->query("SELECT descricao FROM situacoes;");
                    $situacoes = $stmt->fetchAll();
                    foreach ($situacoes as $situacao): ?>
                        <option value="<?= htmlspecialchars($situacao['descricao']) ?>" <?= ($filtro_situacao == $situacao['descricao']) ? 'selected' : '' ?> >
                            <?= htmlspecialchars($situacao['descricao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>          <div class="col-md-3">
                <label for="situacao" class="form-label">Situação:</label>
                <select id="situacao" name="situacao" class="form-control">
                    <option value="">Todas</option>
                    <?php
                    // Busca todas as situações possíveis
                    $stmt = $pdo->query("SELECT descricao FROM situacoes;");
                    $situacoes = $stmt->fetchAll();
                    foreach ($situacoes as $situacao): ?>
                        <option value="<?= htmlspecialchars($situacao['descricao']) ?>" <?= ($filtro_situacao == $situacao['descricao']) ? 'selected' : '' ?> >
                            <?= htmlspecialchars($situacao['descricao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="descricao_pesquisa" class="form-label">Pesquisar por Descrição:</label>
                <input type="text" id="descricao_pesquisa" name="descricao_pesquisa" class="form-control" value="<?= htmlspecialchars($filtro_descricao) ?>">
            </div>
            <div class="col-md-3">
                <label for="descricao" class="form-label">Descrição Personalizada:</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3"><?= htmlspecialchars($descricao_personalizada) ?></textarea>
            </div>
            <div class="col-md-3">
                <label for="cc_email" class="form-label">Enviar cópia para (CC):</label>
                <input type="email" id="cc_email" name="cc_email" class="form-control" value="<?= htmlspecialchars($cc_email) ?>">
            </div>
            
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <button type="submit" name="enviar_email" class="btn btn-success">Enviar por E-mail</button>
                <button type="submit" name="exportar_excel" class="btn btn-warning">Exportar para Excel</button>
                <!--<button type="submit" name="exportar_pdf" class="btn btn-danger">Exportar para PDF</button>-->
            </div>
        </form>

        <!-- Resumo Financeiro -->
        <h3>Resumo Financeiro</h3>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center p-4">
                    <h3>Total a Pagar</h3>
                    <p>R$ <?= number_format($total_pagar, 2, ',', '.') ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-4">
                    <h3>Total a Receber</h3>
                    <p>R$ <?= number_format($total_receber, 2, ',', '.') ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-4">
                    <h3>Saldo</h3>
                    <p id="saldo">R$ <?= number_format($saldo, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Detalhes das Contas -->
        <h3>Detalhes das Contas</h3>
        
        <!-- Contas a Pagar -->
        <h4>Contas a Pagar</h4>
        <?php if (!empty($contas_pagar)): ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="sortable" onclick="ordenarPor('descricao')">Descrição <?= setaOrdenacao('descricao', $ordenar_por, $ordenacao) ?></th>
                        <th class="sortable" onclick="ordenarPor('valor')">Valor <?= setaOrdenacao('valor', $ordenar_por, $ordenacao) ?></th>
                        <th class="sortable" onclick="ordenarPor('data')">Pagar Até <?= setaOrdenacao('data', $ordenar_por, $ordenacao) ?></th>
                        <th class="sortable" onclick="ordenarPor('fornecedor')">Fornecedor <?= setaOrdenacao('fornecedor', $ordenar_por, $ordenacao) ?></th>
                        <th class="sortable" onclick="ordenarPor('situacao')">Situação <?= setaOrdenacao('situacao', $ordenar_por, $ordenacao) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contas_pagar as $conta): ?>
                        <tr>
                            <td><?= htmlspecialchars($conta['descricao']) ?></td>
                            <td>R$ <?= number_format($conta['valor'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($conta['data']) ?></td>
                            <td><?= htmlspecialchars($conta['fornecedor']) ?></td>
                            <td><?= htmlspecialchars($conta['situacao']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhuma conta a pagar encontrada.</p>
        <?php endif; ?>

        <!-- Contas a Receber -->
        <h4>Contas a Receber</h4>
        <?php if (!empty($contas_receber)): ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="sortable" onclick="ordenarPor('descricao')">Descrição <?= setaOrdenacao('descricao', $ordenar_por, $ordenacao) ?></th>
                        <th class="sortable" onclick="ordenarPor('valor')">Valor <?= setaOrdenacao('valor', $ordenar_por, $ordenacao) ?></th>
                        <th class="sortable" onclick="ordenarPor('data')">Receber Até <?= setaOrdenacao('data', $ordenar_por, $ordenacao) ?></th>
                        <th class="sortable" onclick="ordenarPor('cliente')">Cliente <?= setaOrdenacao('cliente', $ordenar_por, $ordenacao) ?></th>
                        <th class="sortable" onclick="ordenarPor('situacao')">Situação <?= setaOrdenacao('situacao', $ordenar_por, $ordenacao) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contas_receber as $conta): ?>
                        <tr>
                            <td><?= htmlspecialchars($conta['descricao']) ?></td>
                            <td>R$ <?= number_format($conta['valor'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($conta['data']) ?></td>
                            <td><?= htmlspecialchars($conta['cliente']) ?></td>
                            <td><?= htmlspecialchars($conta['situacao']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhuma conta a receber encontrada.</p>
        <?php endif; ?>

        <!-- Rodapé -->
        <?= $Rodape; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Função para ordenar por uma coluna
        function ordenarPor(coluna) {
            const urlParams = new URLSearchParams(window.location.search);
            let ordenacao = 'ASC';

            if (urlParams.get('ordenar_por') === coluna && urlParams.get('ordenacao') === 'ASC') {
                ordenacao = 'DESC';
            }

            urlParams.set('ordenar_por', coluna);
            urlParams.set('ordenacao', ordenacao);

            window.location.href = `relatorios.php?${urlParams.toString()}`;
        }

        // Seleciona o elemento <p> com o ID "saldo"
        const saldoElement = document.getElementById('saldo');

        // Extrai o valor numérico do conteúdo do elemento
        const saldoTexto = saldoElement.textContent; // Exemplo: "R$ 1500,00"
        const saldoValor = parseFloat(saldoTexto.replace('R$', '').replace('.', '').replace(',', '.')); // Converte para número

        // Altera a cor com base no valor
        if (saldoValor > 0) {
            saldoElement.style.color = 'green'; // Saldo positivo (verde)
        } else if (saldoValor < 0) {
            saldoElement.style.color = 'red'; // Saldo negativo (vermelho)
        } else {
            saldoElement.style.color = 'black'; // Saldo zero (preto)
        }
    </script>
</body>
</html>

<?php
// Função para exibir a seta de ordenação
function setaOrdenacao($coluna, $ordenar_por, $ordenacao) {
    if ($ordenar_por === $coluna) {
        return $ordenacao === 'ASC' ? '▲' : '▼';
    }
    return '';
}
?>