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

$conta = null; // Inicializa a variável $conta

// Verifica se o ID foi enviado via POST (formulário) ou GET (URL)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
} elseif (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = null;
}

// Busca os dados da conta se o ID foi fornecido
if ($id) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT cp.id, cp.descricao, cp.valor, cp.data, cp.fornecedor, cp.id_empresa, cp.id_situacao, 
                   (SELECT s.descricao FROM situacoes s WHERE s.id = cp.id_situacao) AS descricao_situacao 
            FROM contas_pagar cp 
            WHERE cp.id = ? AND cp.id_empresa = (SELECT u.id_empresa FROM usuarios u WHERE id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $user_id]);
    $conta = $stmt->fetch();

    if (!$conta) {
        $mensagem = "Conta não encontrada.";
    }
}

// Busca todas as situações possíveis
$stmt = $pdo->query("SELECT id, descricao FROM situacoes WHERE id IN (5, 6, 7, 8)");
$situacoes = $stmt->fetchAll();

// Busca os fornecedores da tabela cliente_fornecedor
$stmt = $pdo->query("SELECT nome FROM cliente_fornecedor WHERE cliente_ou_fornecedor IN ('fornecedor', 'ambos')");
$fornecedores = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['descricao'])) {
    $id = $_POST['id'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $fornecedor = $_POST['fornecedor'];
    $situacao = $_POST['situacao'];

    // Atualiza os dados no banco de dados
    $sql = "UPDATE contas_pagar 
            SET descricao = ?, valor = ?, data = ?, fornecedor = ?, id_situacao = ? 
            WHERE id = ? AND id_empresa = (SELECT id_empresa FROM usuarios WHERE id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$descricao, $valor, $data, $fornecedor, $situacao, $id, $user_id]);

    // Redireciona de volta para a página principal
    header('Location: contas_pagar.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Conta</title>
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
        <?= Cabecalho("Editar Conta a Pagar", "Dashboard", "Contas a Pagar", "Relatórios", "dashboard.php", "contas_pagar.php", "relatorios.php"); ?>

        <!-- Mensagem de erro (se houver) -->
        <?php if (isset($mensagem)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <!-- Formulário para inserir o ID -->
        <div class="form-container">
            <form method="post">
                <div class="mb-3">
                    <label for="id" class="form-label">ID do Registro:</label>
                    <input type="number" id="id" name="id" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn_adicionar">Carregar Registro</button>
            </form>
        </div>

        <!-- Formulário de Edição (exibido apenas se o registro for carregado) -->
        <?php if ($conta): ?>
            <div class="form-container mt-4">
                <form method="post">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($conta['id']) ?>">
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição:</label>
                        <input type="text" id="descricao" name="descricao" class="form-control" value="<?= htmlspecialchars($conta['descricao']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="valor" class="form-label">Valor:</label>
                        <input type="number" id="valor" name="valor" step="0.01" class="form-control" value="<?= htmlspecialchars($conta['valor']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="data" class="form-label">Data:</label>
                        <input type="date" id="data" name="data" class="form-control" value="<?= htmlspecialchars($conta['data']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="fornecedor" class="form-label">Fornecedor:</label>
                        <select id="fornecedor" name="fornecedor" class="form-control" required>
                            <option value="">Selecione um fornecedor</option>
                            <?php foreach ($fornecedores as $fornecedor): ?>
                                <option value="<?= htmlspecialchars($fornecedor) ?>" 
                                    <?= ($fornecedor == $conta['fornecedor']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($fornecedor) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="situacao" class="form-label">Situação:</label>
                        <select id="situacao" name="situacao" class="form-control" required>
                            <option value="">Selecione uma situação</option>
                            <?php foreach ($situacoes as $situacao): ?>
                                <option value="<?= htmlspecialchars($situacao['id']) ?>" 
                                    <?= ($situacao['id'] == $conta['id_situacao']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($situacao['descricao']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn_adicionar">Salvar Alterações</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Rodapé -->
        <?= $Rodape; ?>
    </div>

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>