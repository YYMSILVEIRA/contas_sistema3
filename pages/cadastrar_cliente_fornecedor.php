<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../padroes/arquivos_de_padrao.php';
require_once '../includes/logs.php';
// Exemplo de uso: registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI']; // Captura a URL acessada
registrarLog($pagina_acessada);

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$mensagem = ''; // Variável para armazenar mensagens de sucesso ou erro

// Processar o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $apelido = trim($_POST['apelido']);
    $email = trim($_POST['email']);
    $cnpj_cpf = trim($_POST['cnpj_cpf']);
    $cliente_ou_fornecedor = trim($_POST['cliente_ou_fornecedor']);
    $id_situacao = 1; // Valor padrão para a situação

    // Validação básica dos campos
    if (empty($nome) || empty($apelido) || empty($cliente_ou_fornecedor)) {
        $mensagem = '<div class="alert alert-danger">Preencha todos os campos obrigatórios.</div>';
    } else {
        // Inserir os dados no banco de dados
        $sql = "INSERT INTO cliente_fornecedor (nome, apelido, email, cnpj_cpf, id_situacao, cliente_ou_fornecedor, id_empresa) 
                VALUES (?, ?, ?, ?, ?, ?,(SELECT id_empresa FROM usuarios WHERE id = " . intval($_SESSION['user_id']) . "))";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$nome, $apelido, $email, $cnpj_cpf, $id_situacao, $cliente_ou_fornecedor]);
            $mensagem = '<div class="alert alert-success">Cadastro realizado com sucesso!</div>';
        } catch (PDOException $e) {
            $mensagem = '<div class="alert alert-danger">Erro ao cadastrar: ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Cliente/Fornecedor</title>
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
        <?= Cabecalho("Listar Clientes/Fornecedores", "Dashboard", "Gerenciador de Usuários", "Cadastrar Cliente/Fornecedor", "dashboard.php", "gerenciar_usuarios.php", "cadastrar_cliente_fornecedor.php"); ?>

        <!-- Mensagens de sucesso ou erro -->
        <?= $mensagem ?>

        <!-- Formulário de Cadastro -->
        <div class="form-container">
            <form method="post">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" id="nome" name="nome" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="apelido" class="form-label">Apelido:</label>
                    <input type="text" id="apelido" name="apelido" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail:</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="cnpj_cpf" class="form-label">CNPJ/CPF:</label>
                    <input type="text" id="cnpj_cpf" name="cnpj_cpf" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="cliente_ou_fornecedor" class="form-label">Tipo:</label>
                    <select id="cliente_ou_fornecedor" name="cliente_ou_fornecedor" class="form-control" required>
                        <option value="">Selecione...</option>
                        <option value="cliente">Cliente</option>
                        <option value="fornecedor">Fornecedor</option>
                        <option value="ambos">Ambos</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn_adicionar">Cadastrar</button>
            </form>
        </div>

        <!-- Rodapé -->
        <?= $Rodape; ?>
    </div>

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>