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

// Processar o formulário de cadastro de centro de custos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo']);
    $nome = trim($_POST['nome']);
    $responsavel = trim($_POST['responsavel']);
    $localizacao = trim($_POST['localizacao']);
    $tipo = trim($_POST['tipo']);
    $natureza = trim($_POST['natureza']);
    $orcamento_anual = trim($_POST['orcamento_anual']);
    $status = trim($_POST['status']);
    
    $custos_diretos = trim($_POST['custos_diretos']);
    $custos_indiretos = trim($_POST['custos_indiretos']);
    $departamentos_relacionados = trim($_POST['departamentos_relacionados']);
    $processos_negocio = trim($_POST['processos_negocio']);
    

    // Inserir no banco de dados
    $stmt = $pdo->prepare('INSERT INTO centro_custos (codigo, nome, responsavel, localizacao, tipo, natureza, orcamento_anual, status, id_empresa, custos_diretos, custos_indiretos,departamentos_relacionados,processos_negocio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, (SELECT id_empresa FROM usuarios WHERE id = ?), ?, ?, ?, ?)');
    $stmt->execute([$codigo, $nome, $responsavel, $localizacao, $tipo, $natureza, $orcamento_anual, $status, $_SESSION['user_id'],$custos_diretos,$custos_indiretos,$departamentos_relacionados,$processos_negocio]);
    echo "<div class='alert alert-success' role='alert'>
  Novo Centro de Custos cadastrado com sucesso!
</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Centro de Custos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn_adicionar {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <?= Cabecalho("Cadastrar Centro de Custos", "Dashboard", "Centros de Custos", "Relatórios", "dashboard.php", "listar_centros_custos.php", "relatorios.php"); ?>

        <!-- Formulário -->
        <div class="form-container">
            <form method="post">
                <div class="mb-3">
                    <label for="codigo" class="form-label">Código:</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" required>
                    <?= Ajuda("codigoHelp","<b>Código do Centro de Custos</b>: Um identificador único para facilitar a referência e a organização."); ?>
                </div>
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" id="nome" name="nome" class="form-control" required>
                    <?= Ajuda("nomeHelp",'<b>Nome do Centro de Custos</b>: Descrição clara do departamento, projeto ou unidade (ex.: "Marketing", "Produção", "TI").'); ?>
                </div>
                <div class="mb-3">
                    <label for="responsavel" class="form-label">Responsável:</label>
                    <input type="text" id="responsavel" name="responsavel" class="form-control" required>
                    <?= Ajuda("responsavelHelp","<b>Responsável</b>: Nome ou cargo da pessoa responsável pelo centro de custos."); ?>
                </div>
                <div class="mb-3">
                    <label for="localizacao" class="form-label">Localização:</label>
                    <input type="text" id="localizacao" name="localizacao" class="form-control" required>
                    <?= Ajuda("localizacaoHelp","<b>Localização</b>: Filial, departamento ou unidade física onde o centro de custos está alocado."); ?>
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo:</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <option value="Produtivo">Produtivo</option>
                        <option value="Administrativo">Administrativo</option>
                        <option value="Comercial">Comercial</option>
                        <option value="Apoio">Apoio</option>
                    </select>
                    <?= Ajuda("tipoHelp","<b>Centro de Custos Produtivos</b>: Relacionados à produção de bens ou serviços (ex.: linha de produção).<br>
<b>Centro de Custos Administrativos</b>: Relacionados à administração da empresa (ex.: RH, Financeiro).<br>
<b>Centro de Custos Comerciais</b>: Relacionados às vendas e marketing.<br>
<b>Centro de Custos de Apoio</b>: Serviços internos que suportam outras áreas (ex.: TI, Manutenção).<br>"); ?>
                </div>
                <div class="mb-3">
                    <label for="natureza" class="form-label">Natureza:</label>
                    <select id="natureza" name="natureza" class="form-control" required>
                        <option value="Fixo">Fixo</option>
                        <option value="Variavel">Variável</option>
                    </select>
                    <?= Ajuda("naturezaHelp","<b>Fixos</b>: Custos que não variam com o volume de produção ou vendas (ex.: aluguel).<br><b>Variáveis</b>: Custos que variam conforme a produção ou vendas (ex.: matéria-prima)."); ?>
                </div>
                

                <div class="mb-3">
                    <label for="orcamento_anual" class="form-label">Orçamento Anual:</label>
                    <input type="number" id="orcamento_anual" name="orcamento_anual" step="0.01" class="form-control" required>
                    <?= Ajuda("orcamento_anualHelp","<b>Orçamento Anual</b>: Valor planejado para os custos do centro."); ?>
                </div>


                <div class="mb-3">
                    <label for="custos_diretos" class="form-label">Custos Diretos:</label>
                    <input type="number" id="custos_diretos" name="custos_diretos" step="0.01" class="form-control">
                    <?= Ajuda("custos_diretosHelp","<b>Custos Diretos</b>: Custos diretamente atribuíveis ao centro de custos (ex.: salários, materiais).
"); ?>
                </div>


                <div class="mb-3">
                    <label for="custos_indiretos" class="form-label">Custos Indiretos:</label>
                    <input type="number" id="custos_indiretos" name="custos_indiretos" step="0.01" class="form-control">
                    <?= Ajuda("custos_indiretosHelp","<b>Custos Indiretos</b>: Custos compartilhados e rateados entre vários centros (ex.: energia, aluguel)."); ?>
                </div>

                <div class="mb-3">
                    <label for="departamentos_relacionados" class="form-label">Departamentos Relacionados:</label>
                    <input type="text" id="departamentos_relacionados" name="departamentos_relacionados" class="form-control">
                    <?= Ajuda("departamentos_relacionadosHelp","<b>Departamentos Relacionados</b>: Outros centros de custos ou áreas que interagem com ele."); ?>
                </div>


                <div class="mb-3">
                    <label for="processos_negocio" class="form-label">Processos de Negócio:</label>
                    <input type="text" id="processos_negocio" name="processos_negocio" class="form-control">
                    <?= Ajuda("processos_negocioHelp","<b>Processos de Negócio</b>: Processos internos que dependem do centro de custos."); ?>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status:</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Ativo">Ativo</option>
                        <option value="Inativo">Inativo</option>
                        <option value="Em Revisao">Em Revisão</option>
                    </select>
                    <?= Ajuda("statusHelp","<b>Status</b>: Se está ativo, inativo ou em revisão."); ?>
                </div>
                <button type="submit" class="btn btn-primary btn_adicionar">Cadastrar</button>
            </form>
        </div>

        <!-- Rodapé -->
        <?= $Rodape; ?>
    </div>

    <script>
        <?= AjudaConf("codigo","codigoHelp");?>
        <?= AjudaConf("nome","nomeHelp");?>
        <?= AjudaConf("responsavel","responsavelHelp");?>
        <?= AjudaConf("localizacao","localizacaoHelp");?>
        <?= AjudaConf("tipo","tipoHelp");?>
        <?= AjudaConf("natureza","naturezaHelp");?>
        <?= AjudaConf("orcamento_anual","orcamento_anualHelp");?>
        <?= AjudaConf("custos_diretos","custos_diretosHelp");?>
        <?= AjudaConf("custos_indiretos","custos_indiretosHelp");?>
        <?= AjudaConf("departamentos_relacionados","departamentos_relacionadosHelp");?>
        <?= AjudaConf("processos_negocio","processos_negocioHelp");?>
        <?= AjudaConf("status","statusHelp");?>
    </script>

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>