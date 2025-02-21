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

$MensagemSucesso = "";

// Buscar situações
$sql = "SELECT id, descricao FROM situacoes WHERE descricao IN ('ATIVO', 'INATIVO') ORDER BY descricao ASC;";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$situacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_categoria = trim($_POST['nome_categoria']);
    $descricao = trim($_POST['descricao']);
    $id_situacao = trim($_POST['id_situacao']);

    // Inserir nova categoria
    $stmt = $pdo->prepare('INSERT INTO categorias (nome_categoria, descricao, id_empresa, id_situacao) VALUES (?, ?, (SELECT id_empresa FROM usuarios WHERE id = ?), ?)');
    $stmt->execute([$nome_categoria, $descricao, $_SESSION['user_id'], $id_situacao]);

    $MensagemSucesso = '<div id="sucesso" class="alert alert-success" role="alert">Categoria cadastrada com sucesso!</div>';
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Nova Categoria</title>
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
        <?= Cabecalho("Cadastrar Nova Categoria", "Dashboard", "Categorias", "Relatórios", "dashboard.php", "categorias.php", "relatorios.php"); ?>
        <?= $MensagemSucesso; ?>

        <!-- Formulário -->
        <div class="form-container">
            <form method="post">
                <div class="mb-3">
                    <label for="nome_categoria" class="form-label">Nome da Categoria:</label>
                    <input type="text" id="nome_categoria" name="nome_categoria" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição:</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="id_situacao" class="form-label">Situação:</label>
                    <select id="id_situacao" name="id_situacao" class="form-control" required>
                        <option value="">Selecione uma situação</option>
                        <?php if (!empty($situacoes)): ?>
                            <?php foreach ($situacoes as $situacao): ?>
                                <option value="<?= strval($situacao['id']); ?>"><?= htmlspecialchars($situacao['descricao']); ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Nenhuma situação disponível</option>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn_adicionar">Cadastrar</button>
            </form>
        </div>

        <!-- Rodapé -->
        <?= $Rodape; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>