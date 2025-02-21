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

    // Busca os dados da categoria
    $sql = "SELECT id, nome_categoria, descricao, id_situacao 
            FROM categorias 
            WHERE id = ? AND id_empresa = (SELECT id_empresa FROM usuarios WHERE id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $user_id]);
    $categoria = $stmt->fetch();

    if (!$categoria) {
        echo "Categoria não encontrada.";
        exit();
    }
} else {
    echo "ID não fornecido.";
    exit();
}

// Busca as situações disponíveis (ativo/inativo)
$sqlSituacoes = "SELECT id, descricao FROM situacoes WHERE descricao IN ('ATIVO', 'INATIVO')";
$stmtSituacoes = $pdo->query($sqlSituacoes);
$situacoes = $stmtSituacoes->fetchAll();

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_categoria = trim($_POST['nome_categoria']);
    $descricao = trim($_POST['descricao']);
    $id_situacao = trim($_POST['situacao']); // Novo campo: status da categoria

    // Atualiza os dados no banco de dados
    $sql = "UPDATE categorias 
            SET nome_categoria = ?, descricao = ?, id_situacao = ? 
            WHERE id = ? AND id_empresa = (SELECT id_empresa FROM usuarios WHERE id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome_categoria, $descricao, $id_situacao, $id, $user_id]);

    // Redireciona de volta para a página principal
    header('Location: categorias.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoria</title>
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
        <?= Cabecalho("Editar Categoria", "Dashboard", "Categorias", "Relatórios", "dashboard.php", "categorias.php", "relatorios.php"); ?>

        <!-- Formulário de Edição -->
        <div class="form-container">
            <form method="post">
                <div class="mb-3">
                    <label for="nome_categoria" class="form-label">Nome da Categoria:</label>
                    <input type="text" id="nome_categoria" name="nome_categoria" class="form-control" value="<?= htmlspecialchars($categoria['nome_categoria']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição:</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3"><?= htmlspecialchars($categoria['descricao']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="situacao" class="form-label">Status:</label>
                    <select id="situacao" name="situacao" class="form-control" required>
                        <option value="">Selecione um status</option>
                        <?php foreach ($situacoes as $situacao): ?>
                            <option value="<?= htmlspecialchars($situacao['id']) ?>" 
                                <?= ($situacao['id'] == $categoria['id_situacao']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($situacao['descricao']) ?>
                            </option>
                        <?php endforeach; ?>
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