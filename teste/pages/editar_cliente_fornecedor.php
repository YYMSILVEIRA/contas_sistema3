<?php
require_once '../includes/db.php';
require_once '../padroes/arquivos_de_padrao.php';
require_once '../includes/auth.php';
require_once '../includes/logs.php';
// Exemplo de uso: registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI']; // Captura a URL acessada
registrarLog($pagina_acessada);

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Verifica se o ID do cliente/fornecedor foi passado na URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Busca os dados do cliente/fornecedor
    $sql = "SELECT id, nome, apelido, email, cnpj_cpf, cliente_ou_fornecedor 
            FROM cliente_fornecedor 
            WHERE id = ? AND id_empresa = (SELECT id_empresa FROM usuarios WHERE id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $user_id]);
    $registro = $stmt->fetch();

    if (!$registro) {
        echo "Registro não encontrado.";
        exit();
    }
} else {
    echo "ID não fornecido.";
    exit();
}

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $apelido = trim($_POST['apelido']);
    $email = trim($_POST['email']);
    $cnpj_cpf = trim($_POST['cnpj_cpf']);
    $cliente_ou_fornecedor = trim($_POST['cliente_ou_fornecedor']);

    // Atualiza os dados no banco de dados
    $sql = "UPDATE cliente_fornecedor 
            SET nome = ?, apelido = ?, email = ?, cnpj_cpf = ?, cliente_ou_fornecedor = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $apelido, $email, $cnpj_cpf, $cliente_ou_fornecedor, $id]);

    // Redireciona de volta para a página de listagem
    header('Location: listar_clientes_fornecedores.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente/Fornecedor</title>
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
        <?= Cabecalho("Editar Cliente/Fornecedor", "Dashboard", "Listar Cliente/Fornecedor", "Cadastrar Cliente/Fornecedor", "dashboard.php", "listar_clientes_fornecedores.php", "cadastrar_cliente_fornecedor.php"); ?>

        <!-- Formulário de Edição -->
        <div class="form-container">
            <form method="post">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($registro['nome']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="apelido" class="form-label">Apelido:</label>
                    <input type="text" id="apelido" name="apelido" class="form-control" value="<?= htmlspecialchars($registro['apelido']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($registro['email']) ?>">
                </div>
                <div class="mb-3">
                    <label for="cnpj_cpf" class="form-label">CNPJ/CPF:</label>
                    <input type="text" id="cnpj_cpf" name="cnpj_cpf" class="form-control" value="<?= htmlspecialchars($registro['cnpj_cpf']) ?>">
                </div>
                <div class="mb-3">
                    <label for="cliente_ou_fornecedor" class="form-label">Tipo:</label>
                    <select id="cliente_ou_fornecedor" name="cliente_ou_fornecedor" class="form-control" required>
                        <option value="cliente" <?= ($registro['cliente_ou_fornecedor'] == 'cliente') ? 'selected' : '' ?>>Cliente</option>
                        <option value="fornecedor" <?= ($registro['cliente_ou_fornecedor'] == 'fornecedor') ? 'selected' : '' ?>>Fornecedor</option>
                        <option value="ambos" <?= ($registro['cliente_ou_fornecedor'] == 'ambos') ? 'selected' : '' ?>>Ambos</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn_adicionar">Salvar Alterações</button>
            </form>
        </div>

        <!-- Rodapé -->
        <?= $Rodape; ?>
    </div>

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>