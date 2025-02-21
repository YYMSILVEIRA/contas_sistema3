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

    // Busca os dados da conta
    $sql = "SELECT cr.id, cr.descricao, cr.valor, cr.data, cr.cliente, cr.id_empresa, cr.id_situacao, cr.id_categoria,
                   (SELECT s.descricao FROM situacoes s WHERE s.id = cr.id_situacao) AS descricao_situacao,
                   (SELECT cat.nome_categoria FROM categorias cat WHERE cat.id = cr.id_categoria) AS nome_categoria
            FROM contas_receber cr 
            WHERE cr.id = ? AND cr.id_empresa = (SELECT u.id_empresa FROM usuarios u WHERE id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $user_id]);
    $conta = $stmt->fetch();

    if (!$conta) {
        echo "Conta não encontrada.";
        exit();
    }
} else {
    echo "ID não fornecido.";
    exit();
}

// Busca todas as situações possíveis
$stmt = $pdo->query("SELECT id, descricao FROM situacoes WHERE id IN (5, 6, 7, 8)");
$situacoes = $stmt->fetchAll();

// Busca os clientes da tabela cliente_fornecedor
$stmt = $pdo->query("SELECT nome FROM cliente_fornecedor WHERE cliente_ou_fornecedor IN ('cliente', 'ambos')");
$clientes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Busca as categorias
$stmt = $pdo->query("SELECT id, nome_categoria FROM categorias WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = $user_id)");
$categorias = $stmt->fetchAll();

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $cliente = $_POST['cliente'];
    $situacao = $_POST['situacao'];
    $id_categoria = $_POST['categoria']; // Novo campo: categoria

    // Atualiza os dados no banco de dados
    $sql = "UPDATE contas_receber 
            SET descricao = ?, valor = ?, data = ?, cliente = ?, id_situacao = ?, id_categoria = ?
            WHERE id = ? AND id_empresa = (SELECT id_empresa FROM usuarios WHERE id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$descricao, $valor, $data, $cliente, $situacao, $id_categoria, $id, $user_id]);

    // Redireciona de volta para a página principal
    header('Location: contas_receber.php');
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
        <?= Cabecalho("Editar Conta a Receber", "Dashboard", "Contas a Receber", "Relatórios", "dashboard.php", "contas_receber.php", "relatorios.php"); ?>

        <!-- Formulário de Edição -->
        <div class="form-container">
            <form method="post">
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
                    <label for="cliente" class="form-label">Cliente:</label>
                    <select id="cliente" name="cliente" class="form-control" required>
                        <option value="">Selecione um cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= htmlspecialchars($cliente) ?>" 
                                <?= ($cliente == $conta['cliente']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente) ?>
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
                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoria:</label>
                    <select id="categoria" name="categoria" class="form-control" required>
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= htmlspecialchars($categoria['id']) ?>" 
                                <?= ($categoria['id'] == $conta['id_categoria']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['nome_categoria']) ?>
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