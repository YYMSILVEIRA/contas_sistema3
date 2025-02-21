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

// Inicializa a variável $MensagemSucesso
$MensagemSucesso = "";

// Buscar clientes da tabela cliente_fornecedor
$sql = "SELECT nome FROM cliente_fornecedor WHERE cliente_ou_fornecedor IN ('cliente', 'ambos') AND id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . intval($_SESSION['user_id']) . ")";
$stmt = $pdo->query($sql);
$clientes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Buscar categorias
$sqlCategorias = "SELECT id, nome_categoria FROM categorias WHERE id_empresa = (SELECT id_empresa FROM usuarios WHERE id = " . intval($_SESSION['user_id']) . ")";
$stmtCategorias = $pdo->query($sqlCategorias);
$categorias = $stmtCategorias->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = trim($_POST['descricao']);
    $valor = trim($_POST['valor']);
    $data = trim($_POST['data']);
    $cliente = trim($_POST['cliente']);
    $id_categoria = trim($_POST['categoria']); // Novo campo: categoria

    // Validar se o valor é positivo
    if ($valor <= 0) {
        echo "<script>alert('O valor não pode ser negativo ou zero.');</script>";
    } else {
        // Inserir a conta a receber com a categoria
        $stmt = $pdo->prepare('INSERT INTO contas_receber (descricao, valor, data, cliente, id_empresa, id_categoria) VALUES (?, ?, ?, ?, (SELECT id_empresa FROM usuarios WHERE id = ?), ?)');
        $stmt->execute([$descricao, $valor, $data, $cliente, $_SESSION['user_id'], $id_categoria]);

        $MensagemSucesso = '<div id="sucesso" class="alert alert-success" role="alert">Conta a receber cadastrada com sucesso!</div>';
        $MensagemSucesso .= "<script>
            const alerta = document.getElementById('sucesso');
            setTimeout(() => {
                alerta.classList.add('fade');
                setTimeout(() => {
                    alerta.remove();
                }, 500);
            }, 5000);
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Nova Conta a Receber</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #007AFF;
            box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.2);
        }
        .btn-primary {
            /*background-color: #007AFF;*/
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #0063cc;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .fade {
            transition: opacity 0.5s ease-in-out;
            opacity: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <?= Cabecalho("Adicionar Conta a Receber", "Dashboard", "Contas a Receber", "Relatórios", "dashboard.php", "contas_receber.php", "relatorios.php"); ?>
        <?= $MensagemSucesso; ?>
        <!-- Formulário -->
        <div class="form-container">
            <form method="post">
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição:</label>
                    <input type="text" id="descricao" name="descricao" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="valor" class="form-label">Valor:</label>
                    <input type="number" id="valor" name="valor" step="0.01" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="data" class="form-label">Data:</label>
                    <input type="date" id="data" name="data" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="cliente" class="form-label">Cliente:</label>
                    <select id="cliente" name="cliente" class="form-control" required>
                        <option value="">Selecione um cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= htmlspecialchars($cliente) ?>"><?= htmlspecialchars($cliente) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="categoria" class="form-label">Categoria:</label>
                    <select id="categoria" name="categoria" class="form-control" required>
                        <option value="">Selecione uma categoria</option>
                        <?php if (!empty($categorias)): ?>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= strval($categoria['id']); ?>"><?= htmlspecialchars($categoria['nome_categoria']); ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Nenhuma categoria disponível</option>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn_adicionar">Adicionar</button>
            </form>
        </div>

        <!-- Rodapé -->
        <?= $Rodape; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>