<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../padroes/arquivos_de_padrao.php';
require_once '../includes/logs.php';

// Registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI'];
registrarLog($pagina_acessada);

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}



$NM_PLATAFORMA = 'CONTAS_SISTEMA';
$NM_PAGINA="DASHBOARD";
require_once '../includes/valida_permissoes.php';


// Verifica se o usuário é administrador (id_perfil = 1)
$isAdmin = ($_SESSION['id_perfil'] == 1);


// Total a Pagar
$sql = "SELECT SUM(valor) AS total_a_pagar FROM contas_pagar WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ") and id_situacao in (8);";
$resultado = $BaseDados->query($sql);
$total_pagar = ($resultado && count($resultado) > 0) ? (float)$resultado[0]['total_a_pagar'] : 0.0;

// Total a Receber
$sql = "SELECT IFNULL(SUM(valor), 0) AS total_a_receber FROM contas_receber WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ") and id_situacao in (8);";
$resultado = $BaseDados->query($sql);
$total_receber = ($resultado && count($resultado) > 0) ? (float)$resultado[0]['total_a_receber'] : 0.0;

// Saldo
$saldo = $total_receber - $total_pagar;

// Valores por situação (contas a pagar)
$sql = "
    SELECT s.descricao AS situacao, SUM(cp.valor) AS total 
    FROM contas_pagar cp
    LEFT JOIN situacoes s ON cp.id_situacao = s.id
    WHERE cp.id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ")
    GROUP BY s.descricao
    HAVING SUM(cp.valor) > 0
";
$valores_pagar_por_situacao = $BaseDados->query($sql);

// Valores por situação (contas a receber)
$sql = "
    SELECT s.descricao AS situacao, SUM(cr.valor) AS total 
    FROM contas_receber cr
    LEFT JOIN situacoes s ON cr.id_situacao = s.id
    WHERE cr.id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ")
    GROUP BY s.descricao
    HAVING SUM(cr.valor) > 0
";
$valores_receber_por_situacao = $BaseDados->query($sql);

// Gastos por mês/ano (contas a pagar)
$sql = "
    SELECT YEAR(data) AS ano, MONTH(data) AS mes, SUM(valor) AS total 
    FROM contas_pagar 
    WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ")
    GROUP BY YEAR(data), MONTH(data)
    ORDER BY ano, mes
";
$gastos_por_mes_ano = $BaseDados->query($sql);

// Ganhos por mês/ano (contas a receber)
$sql = "
    SELECT YEAR(data) AS ano, MONTH(data) AS mes, SUM(valor) AS total 
    FROM contas_receber 
    WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ")
    GROUP BY YEAR(data), MONTH(data)
    ORDER BY ano, mes
";
$ganhos_por_mes_ano = $BaseDados->query($sql);

// Gastos por semana (contas a pagar)
$sql = "
    SELECT YEAR(data) AS ano, WEEK(data) AS semana, SUM(valor) AS total 
    FROM contas_pagar 
    WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ")
    GROUP BY YEAR(data), WEEK(data)
    ORDER BY ano, semana
";
$gastos_por_semana = $BaseDados->query($sql);

// Ganhos por semana (contas a receber)
$sql = "
    SELECT YEAR(data) AS ano, WEEK(data) AS semana, SUM(valor) AS total 
    FROM contas_receber 
    WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ")
    GROUP BY YEAR(data), WEEK(data)
    ORDER BY ano, semana
";
$ganhos_por_semana = $BaseDados->query($sql);

// Gastos por dia (contas a pagar)
$sql = "
    SELECT YEAR(data) AS ano, MONTH(data) AS mes, DAY(data) AS dia, SUM(valor) AS total 
    FROM contas_pagar 
    WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ")
    GROUP BY YEAR(data), MONTH(data), DAY(data)
    ORDER BY ano, mes, dia
";
$gastos_por_dia = $BaseDados->query($sql);

// Ganhos por dia (contas a receber)
$sql = "
    SELECT YEAR(data) AS ano, MONTH(data) AS mes, DAY(data) AS dia, SUM(valor) AS total 
    FROM contas_receber 
    WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . ")
    GROUP BY YEAR(data), MONTH(data), DAY(data)
    ORDER BY ano, mes, dia
";
$ganhos_por_dia = $BaseDados->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .quick-links {
            margin-top: 30px;
        }
        .quick-links h2 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .quick-links ul {
            list-style: none;
            padding: 0;
        }
        .quick-links li {
            margin-bottom: 10px;
        }
        .quick-links a {
            text-decoration: none;
            color: #007AFF; /* Azul padrão do iOS */
            font-size: 1rem;
            transition: color 0.3s ease;
        }
        .quick-links a:hover {
            color: #005bb5; /* Azul mais escuro ao passar o mouse */
        }
        #saldo {
            font-weight: bold;
        }
        .chart-container {
            margin-top: 30px;
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-size: 1.8rem;
            /*color: #2c3e50 !important;*/
        }
        .nav-link {
            /*color: #2c3e50 !important;*/
            font-weight: 500;
        }
        .nav-link:hover {
            color: #ffffff !important; /* Azul padrão do iOS */

        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <?= Cabecalho("Dashboard", "Contas a Pagar", "Contas a Receber", "Relatórios", "contas_pagar.php", "contas_receber.php", "relatorios.php", 1); ?>

        <!-- Título -->
        <center><h3 class="mt-4">Resumo Geral</h3></center>

        <!-- Cards de Resumo -->
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card text-center p-4">
                    <h3>Total a Pagar</h3>
                    <p>R$ <?= number_format($total_pagar, 2, ',', '.') ?></p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center p-4">
                    <h3>Total a Receber</h3>
                    <p>R$ <?= number_format($total_receber, 2, ',', '.') ?></p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center p-4">
                    <h3>Saldo</h3>
                    <p id="saldo">R$ <?= number_format($saldo, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Valores por Situação (Contas a Pagar e Receber) -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card p-4">
                    <h3>Valores por Situação (Contas a Pagar)</h3>
                    <ul>
                        <?php if (!empty($valores_pagar_por_situacao)): ?>
                            <?php foreach ($valores_pagar_por_situacao as $situacao): ?>
                                <li>
                                    <strong><?= htmlspecialchars($situacao['situacao']) ?>:</strong>
                                    R$ <?= number_format($situacao['total'], 2, ',', '.') ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Nenhum valor encontrado.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4">
                    <h3>Valores por Situação (Contas a Receber)</h3>
                    <ul>
                        <?php if (!empty($valores_receber_por_situacao)): ?>
                            <?php foreach ($valores_receber_por_situacao as $situacao): ?>
                                <li>
                                    <strong><?= htmlspecialchars($situacao['situacao']) ?>:</strong>
                                    R$ <?= number_format($situacao['total'], 2, ',', '.') ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Nenhum valor encontrado.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Gráficos de Gastos e Ganhos por Mês/Ano -->
        <div class="row mt-4 chart-container">
            <div class="col-md-6">
                <div class="card p-4">
                    <h3>Gastos por Mês/Ano</h3>
                    <canvas id="gastosChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4">
                    <h3>Ganhos por Mês/Ano</h3>
                    <canvas id="ganhosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráficos de Gastos e Ganhos por Semana -->
        <div class="row mt-4 chart-container">
            <div class="col-md-6">
                <div class="card p-4">
                    <h3>Gastos por Semana</h3>
                    <canvas id="gastosSemanaChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4">
                    <h3>Ganhos por Semana</h3>
                    <canvas id="ganhosSemanaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráficos de Gastos e Ganhos por Dia -->
        <div class="row mt-4 chart-container">
            <div class="col-md-6">
                <div class="card p-4">
                    <h3>Gastos por Dia</h3>
                    <canvas id="gastosDiaChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4">
                    <h3>Ganhos por Dia</h3>
                    <canvas id="ganhosDiaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Links Rápidos -->
        <div class="quick-links">
            <h2>Links Rápidos</h2>
            <ul>
                
                <?php permissaoCampo("GERENCIADOR_USUARIOS",'<li><a href="gerenciar_usuarios.php"><b>Gerenciador de Usuários</b></a></li>')?>
                
                <?php permissaoCampo("DISPARAR_EMAIL_VENCIMENTOS",'<li><a href="enviar_emails.php"><b>Disparar E-mail contas vencendo</b></a></li>')?>
                
                <?php permissaoCampo("GERENCIAMENTO_LOGS",'<li><a href="visualizar_logs.php"><b>Logs da plataforma</b></a></li>')?>
                
                <?php permissaoCampo("EDITOR_INTEGRADO",'<li><a href="../editor.php"><b>Editor de código integrado</b></a></li>')?>

                <?php permissaoCampo("GERENCIAR_ENTIDADES",'<li><a href="../gerenciar_entidades.php"><b>Gerenciador de Entidades</b></a></li>')?>

                
                <?php permissaoCampo("ADD_CONTA_PAGAR",'<li><a href="adicionar_contas_pagar.php">Adicionar Conta a Pagar</a></li>')?>
                
                <?php permissaoCampo("ADD_CONTAS_RECEBER",'<li><a href="adicionar_contas_receber.php">Adicionar Conta a Receber</a></li>')?>
                
                <?php permissaoCampo("RELATORIOS",'<li><a href="relatorios.php">Ver Relatórios</a></li>')?>
                
                <?php permissaoCampo("CAD_CLI_FOR",'<li><a href="cadastrar_cliente_fornecedor.php">Cadastrar Cliente/Fornecedor</a></li>')?>
                
                <?php permissaoCampo("LST_CLI_FOR",'<li><a href="listar_clientes_fornecedores.php">Listar Clientes/Fornecedores</a></li>')?>
                
                <?php permissaoCampo("EDT_REG_CONTA_PAGAR",'<li><a href="editar_registro_pagar_manual.php">Editar Conta a Pagar</a></li>')?>
                
                <?php permissaoCampo("EDT_CONTA_RECEBER",'<li><a href="editar_registro_receber_manual.php">Editar Conta a Receber</a></li>')?>
               
                <?php permissaoCampo("CATEGORIAS",'<li><a href="categorias.php">Categorias</a></li>')?>
                
                <?php permissaoCampo("ADD_CATEGORIA",'<li><a href="adicionar_categoria.php">Adicionar Nova Categoria</a></li>')?>
                                
            </ul>
        </div>

        <!-- Rodapé -->
        <?= $Rodape; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
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

        // Dados para os gráficos
        const gastosData = {
            labels: <?= json_encode(array_map(function ($item) {
                return date('m/Y', mktime(0, 0, 0, $item['mes'], 1, $item['ano']));
            }, $gastos_por_mes_ano)) ?>,
            datasets: [{
                label: 'Gastos',
                data: <?= json_encode(array_column($gastos_por_mes_ano, 'total')) ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        };

        const ganhosData = {
            labels: <?= json_encode(array_map(function ($item) {
                return date('m/Y', mktime(0, 0, 0, $item['mes'], 1, $item['ano']));
            }, $ganhos_por_mes_ano)) ?>,
            datasets: [{
                label: 'Ganhos',
                data: <?= json_encode(array_column($ganhos_por_mes_ano, 'total')) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };

        // Configuração dos gráficos
        const configGastos = {
            type: 'line',
            data: gastosData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const configGanhos = {
            type: 'line',
            data: ganhosData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        // Renderizar os gráficos
        const gastosChart = new Chart(document.getElementById('gastosChart'), configGastos);
        const ganhosChart = new Chart(document.getElementById('ganhosChart'), configGanhos);

        // Dados para os gráficos de semana e dia (mantidos conforme o código original)
        // Dados para os gráficos de semana
   const gastosSemanaData = {
       labels: <?= json_encode(array_map(function ($item) {
           return 'Semana ' . $item['semana'] . ' de ' . $item['ano'];
       }, $gastos_por_semana)) ?>,
       datasets: [{
           label: 'Gastos',
           data: <?= json_encode(array_column($gastos_por_semana, 'total')) ?>,
           backgroundColor: 'rgba(255, 99, 132, 0.2)',
           borderColor: 'rgba(255, 99, 132, 1)',
           borderWidth: 1
       }]
   };

   const ganhosSemanaData = {
       labels: <?= json_encode(array_map(function ($item) {
           return 'Semana ' . $item['semana'] . ' de ' . $item['ano'];
       }, $ganhos_por_semana)) ?>,
       datasets: [{
           label: 'Ganhos',
           data: <?= json_encode(array_column($ganhos_por_semana, 'total')) ?>,
           backgroundColor: 'rgba(75, 192, 192, 0.2)',
           borderColor: 'rgba(75, 192, 192, 1)',
           borderWidth: 1
       }]
   };

   // Dados para os gráficos de dia
   const gastosDiaData = {
       labels: <?= json_encode(array_map(function ($item) {
           return $item['dia'] . '/' . $item['mes'] . '/' . $item['ano'];
       }, $gastos_por_dia)) ?>,
       datasets: [{
           label: 'Gastos',
           data: <?= json_encode(array_column($gastos_por_dia, 'total')) ?>,
           backgroundColor: 'rgba(255, 99, 132, 0.2)',
           borderColor: 'rgba(255, 99, 132, 1)',
           borderWidth: 1
       }]
   };

   const ganhosDiaData = {
       labels: <?= json_encode(array_map(function ($item) {
           return $item['dia'] . '/' . $item['mes'] . '/' . $item['ano'];
       }, $ganhos_por_dia)) ?>,
       datasets: [{
           label: 'Ganhos',
           data: <?= json_encode(array_column($ganhos_por_dia, 'total')) ?>,
           backgroundColor: 'rgba(75, 192, 192, 0.2)',
           borderColor: 'rgba(75, 192, 192, 1)',
           borderWidth: 1
       }]
   };

   // Configuração dos gráficos de semana
   const configGastosSemana = {
       type: 'line',
       data: gastosSemanaData,
       options: {
           scales: {
               y: {
                   beginAtZero: true
               }
           }
       }
   };

   const configGanhosSemana = {
       type: 'line',
       data: ganhosSemanaData,
       options: {
           scales: {
               y: {
                   beginAtZero: true
               }
           }
       }
   };

   // Configuração dos gráficos de dia
   const configGastosDia = {
       type: 'line',
       data: gastosDiaData,
       options: {
           scales: {
               y: {
                   beginAtZero: true
               }
           }
       }
   };

   const configGanhosDia = {
       type: 'line',
       data: ganhosDiaData,
       options: {
           scales: {
               y: {
                   beginAtZero: true
               }
           }
       }
   };

   // Renderizar os gráficos de semana e dia
   const gastosSemanaChart = new Chart(document.getElementById('gastosSemanaChart'), configGastosSemana);
   const ganhosSemanaChart = new Chart(document.getElementById('ganhosSemanaChart'), configGanhosSemana);
   const gastosDiaChart = new Chart(document.getElementById('gastosDiaChart'), configGastosDia);
   const ganhosDiaChart = new Chart(document.getElementById('ganhosDiaChart'), configGanhosDia);
    </script>
</body>
</html>