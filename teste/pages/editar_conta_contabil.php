<?php
require_once '../includes/db.php';
require_once '../padroes/arquivos_de_padrao.php';
require_once '../includes/auth.php';
require_once '../includes/logs.php';

// Registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI'];
registrarLog($pagina_acessada);

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Busca os dados da conta contábil
    $sql = "SELECT id, codigo, nome, classificacao, tipo_saldo, status 
            FROM contas_contabeis 
            WHERE id = ? AND id_empresa = (SELECT id_empresa FROM usuarios WHERE id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $user_id]);
    $contaContabil = $stmt->fetch();

    if (!$contaContabil) {
        echo "Conta Contábil não encontrada.";
        exit();
    }
} else {
    echo "ID não fornecido.";
    exit();
}

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo']);
    $nome = trim($_POST['nome']);
    $classificacao = trim($_POST['classificacao']);
    $tipo_saldo = trim($_POST['tipo_saldo']);
    $status = trim($_POST['status']);

    // Atualiza os dados no banco de dados
    $sql = "UPDATE contas_contabeis 
            SET codigo = ?, nome = ?, classificacao = ?, tipo_saldo = ?, status = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$codigo, $nome, $classificacao, $tipo_saldo, $status, $id]);

    // Redireciona de volta para a página principal
    header('Location: listar_contas_contabeis.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Conta Contábil</title>
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
        <?= Cabecalho("Editar Conta Contábil", "Dashboard", "Contas Contábeis", "Relatórios", "dashboard.php", "listar_contas_contabeis.php", "relatorios.php"); ?>

        <!-- Formulário de Edição -->
        <div class="form-container">
            <form method="post">
                <div class="mb-3">
                    <label for="codigo" class="form-label">Código:</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" value="<?= htmlspecialchars($contaContabil['codigo']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($contaContabil['nome']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="classificacao" class="form-label">Classificação:</label>
                    <select id="classificacao" name="classificacao" class="form-control" required>
                        <option value="Ativo" <?= ($contaContabil['classificacao'] == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                        <option value="Passivo" <?= ($contaContabil['classificacao'] == 'Passivo') ? 'selected' : '' ?>>Passivo</option>
                        <option value="Patrimonio Liquido" <?= ($contaContabil['classificacao'] == 'Patrimonio Liquido') ? 'selected' : '' ?>>Patrimônio Líquido</option>
                        <option value="Receita" <?= ($contaContabil['classificacao'] == 'Receita') ? 'selected' : '' ?>>Receita</option>
                        <option value="Despesa" <?= ($contaContabil['classificacao'] == 'Despesa') ? 'selected' : '' ?>>Despesa</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tipo_saldo" class="form-label">Tipo de Saldo:</label>
                    <select id="tipo_saldo" name="tipo_saldo" class="form-control" required>
                        <option value="Devedor" <?= ($contaContabil['tipo_saldo'] == 'Devedor') ? 'selected' : '' ?>>Devedor</option>
                        <option value="Credor" <?= ($contaContabil['tipo_saldo'] == 'Credor') ? 'selected' : '' ?>>Credor</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status:</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Ativo" <?= ($contaContabil['status'] == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                        <option value="Inativo" <?= ($contaContabil['status'] == 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                        <option value="Bloqueado" <?= ($contaContabil['status'] == 'Bloqueado') ? 'selected' : '' ?>>Bloqueado</option>
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