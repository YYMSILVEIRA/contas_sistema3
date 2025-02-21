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

    // Busca os dados do centro de custos
    $sql = "SELECT id, codigo, nome, responsavel, localizacao, tipo, natureza, orcamento_anual, status 
            FROM centro_custos 
            WHERE id = ? AND id_empresa = (SELECT id_empresa FROM usuarios WHERE id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $user_id]);
    $centroCustos = $stmt->fetch();

    if (!$centroCustos) {
        echo "Centro de Custos não encontrado.";
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
    $responsavel = trim($_POST['responsavel']);
    $localizacao = trim($_POST['localizacao']);
    $tipo = trim($_POST['tipo']);
    $natureza = trim($_POST['natureza']);
    $orcamento_anual = trim($_POST['orcamento_anual']);
    $status = trim($_POST['status']);

    // Atualiza os dados no banco de dados
    $sql = "UPDATE centro_custos 
            SET codigo = ?, nome = ?, responsavel = ?, localizacao = ?, tipo = ?, natureza = ?, orcamento_anual = ?, status = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$codigo, $nome, $responsavel, $localizacao, $tipo, $natureza, $orcamento_anual, $status, $id]);

    // Redireciona de volta para a página principal
    header('Location: listar_centros_custos.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Centro de Custos</title>
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
        <?= Cabecalho("Editar Centro de Custos", "Dashboard", "Centros de Custos", "Relatórios", "dashboard.php", "listar_centros_custos.php", "relatorios.php"); ?>

        <!-- Formulário de Edição -->
        <div class="form-container">
            <form method="post">
                <div class="mb-3">
                    <label for="codigo" class="form-label">Código:</label>
                    <input type="text" id="codigo" name="codigo" class="form-control" value="<?= htmlspecialchars($centroCustos['codigo']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($centroCustos['nome']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="responsavel" class="form-label">Responsável:</label>
                    <input type="text" id="responsavel" name="responsavel" class="form-control" value="<?= htmlspecialchars($centroCustos['responsavel']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="localizacao" class="form-label">Localização:</label>
                    <input type="text" id="localizacao" name="localizacao" class="form-control" value="<?= htmlspecialchars($centroCustos['localizacao']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo:</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <option value="Produtivo" <?= ($centroCustos['tipo'] == 'Produtivo') ? 'selected' : '' ?>>Produtivo</option>
                        <option value="Administrativo" <?= ($centroCustos['tipo'] == 'Administrativo') ? 'selected' : '' ?>>Administrativo</option>
                        <option value="Comercial" <?= ($centroCustos['tipo'] == 'Comercial') ? 'selected' : '' ?>>Comercial</option>
                        <option value="Apoio" <?= ($centroCustos['tipo'] == 'Apoio') ? 'selected' : '' ?>>Apoio</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="natureza" class="form-label">Natureza:</label>
                    <select id="natureza" name="natureza" class="form-control" required>
                        <option value="Fixo" <?= ($centroCustos['natureza'] == 'Fixo') ? 'selected' : '' ?>>Fixo</option>
                        <option value="Variavel" <?= ($centroCustos['natureza'] == 'Variavel') ? 'selected' : '' ?>>Variável</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="orcamento_anual" class="form-label">Orçamento Anual:</label>
                    <input type="number" id="orcamento_anual" name="orcamento_anual" step="0.01" class="form-control" value="<?= htmlspecialchars($centroCustos['orcamento_anual']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status:</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Ativo" <?= ($centroCustos['status'] == 'Ativo') ? 'selected' : '' ?>>Ativo</option>
                        <option value="Inativo" <?= ($centroCustos['status'] == 'Inativo') ? 'selected' : '' ?>>Inativo</option>
                        <option value="Em Revisao" <?= ($centroCustos['status'] == 'Em Revisao') ? 'selected' : '' ?>>Em Revisão</option>
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