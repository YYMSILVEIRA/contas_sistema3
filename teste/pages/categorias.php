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

// Verifica se há um termo de pesquisa
$termoPesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';

// Verifica a ordenação
$ordenarPor = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'nome_categoria'; // Coluna padrão para ordenação
$ordenacao = isset($_GET['ordenacao']) ? $_GET['ordenacao'] : 'ASC'; // Direção padrão da ordenação

// Configurações de paginação
$itensPorPagina = 15; // Número de itens por página
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página atual, padrão é 1
$offset = ($paginaAtual - 1) * $itensPorPagina; // Calcula o offset para a consulta SQL

// Consulta para buscar as categorias
$sql = "
    SELECT c.id, c.nome_categoria, c.descricao, s.descricao as situacao 
    FROM categorias c
    LEFT JOIN situacoes s ON c.id_situacao = s.id
    WHERE c.id_empresa = (SELECT id_empresa FROM usuarios WHERE id = :user_id)
";

// Adiciona condições de pesquisa se houver um termo
if (!empty($termoPesquisa)) {
    $sql .= " AND (c.nome_categoria LIKE :termo1 OR c.descricao LIKE :termo2 OR s.descricao LIKE :termo3)";
}

// Adiciona a ordenação
$sql .= " ORDER BY $ordenarPor $ordenacao LIMIT :itensPorPagina OFFSET :offset";

$stmt = $pdo->prepare($sql);

// Vincula o parâmetro do ID do usuário
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

// Vincula os parâmetros de pesquisa, se houver
if (!empty($termoPesquisa)) {
    $stmt->bindValue(':termo1', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmt->bindValue(':termo2', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmt->bindValue(':termo3', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
}

// Vincula os parâmetros de paginação
$stmt->bindValue(':itensPorPagina', $itensPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$categorias = $stmt->fetchAll();

// Consulta para contar o total de registros (para paginação)
$sqlTotal = "
    SELECT COUNT(*) as total 
    FROM categorias c
    LEFT JOIN situacoes s ON c.id_situacao = s.id
    WHERE c.id_empresa = (SELECT id_empresa FROM usuarios WHERE id = :user_id)
";

if (!empty($termoPesquisa)) {
    $sqlTotal .= " AND (c.nome_categoria LIKE :termo1 OR c.descricao LIKE :termo2 OR s.descricao LIKE :termo3)";
}

$stmtTotal = $pdo->prepare($sqlTotal);

// Vincula o parâmetro do ID do usuário
$stmtTotal->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

// Vincula os parâmetros de pesquisa, se houver
if (!empty($termoPesquisa)) {
    $stmtTotal->bindValue(':termo1', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmtTotal->bindValue(':termo2', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmtTotal->bindValue(':termo3', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
}

$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetch()['total'];
$totalPaginas = ceil($totalRegistros / $itensPorPagina); // Calcula o total de páginas
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Categorias</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8f9fa;
        }
        .btn-editar {
            background-color: #007AFF;
            color: #fff;
            padding: 5px 10px;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-editar:hover {
            background-color: #005bb5;
        }
        .btn-excluir {
            background-color: #e74c3c;
            color: #fff;
            padding: 5px 10px;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-excluir:hover {
            background-color: #c0392b;
        }
        .sortable {
            cursor: pointer;
        }
        .sortable:hover {
            background-color: #f8f9fa;
            color: #007AFF;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background-color: #007AFF;
            color: #fff;
            font-weight: 500;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .pagination .page-item.active .page-link {
            background-color: #007AFF;
            border-color: #007AFF;
        }
        .pagination .page-link {
            color: #FFFFFF;
        }
        .pagination .page-link:hover {
            color: #000000;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <?= Cabecalho("Lista de Categorias", "Dashboard", "Adicionar Categoria", "Relatórios", "dashboard.php", "adicionar_categoria.php", "relatorios.php", 0); ?>

        <!-- Título -->
        <h3 class="mt-4">Lista de Categorias</h3>

        <!-- Campo de pesquisa -->
        <form method="get" action="" class="mb-4">
            <div class="input-group">
                <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar em todos os campos..." value="<?= htmlspecialchars($termoPesquisa) ?>">
                <button type="submit" class="btn btn-primary">Pesquisar</button>
                <?php if (!empty($termoPesquisa)): ?>
                    <a href="categorias.php" class="btn btn-secondary">Limpar Pesquisa</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Tabela de Categorias -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="sortable" onclick="ordenarPor('nome_categoria')">Nome da Categoria <?= setaOrdenacao('nome_categoria', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('descricao')">Descrição <?= setaOrdenacao('descricao', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('situacao')">Situação <?= setaOrdenacao('situacao', $ordenarPor, $ordenacao) ?></th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td><?= htmlspecialchars($categoria['nome_categoria']) ?></td>
                    <td><?= htmlspecialchars($categoria['descricao']) ?></td>
                    <td><?= htmlspecialchars($categoria['situacao']) ?></td>
                    <td>
                        <a href="<?= 'editar_categoria.php?id='.$categoria['id'] ?>" class="btn-editar">Editar</a>
                        <a href="<?= 'excluir_categoria.php?id='.$categoria['id'] ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Controles de paginação -->
        <nav aria-label="Navegação de páginas">
            <ul class="pagination">
                <!-- Botão "Anterior" -->
                <li class="page-item <?= $paginaAtual <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $paginaAtual - 1 ?>&pesquisa=<?= urlencode($termoPesquisa) ?>&ordenar_por=<?= $ordenarPor ?>&ordenacao=<?= $ordenacao ?>" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <!-- Links das páginas -->
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= $i == $paginaAtual ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $i ?>&pesquisa=<?= urlencode($termoPesquisa) ?>&ordenar_por=<?= $ordenarPor ?>&ordenacao=<?= $ordenacao ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Botão "Próximo" -->
                <li class="page-item <?= $paginaAtual >= $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $paginaAtual + 1 ?>&pesquisa=<?= urlencode($termoPesquisa) ?>&ordenar_por=<?= $ordenarPor ?>&ordenacao=<?= $ordenacao ?>" aria-label="Próximo">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Rodapé -->
        <?= $Rodape; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Função para ordenar por uma coluna
        function ordenarPor(coluna) {
            const urlParams = new URLSearchParams(window.location.search);
            let ordenacao = 'ASC';

            if (urlParams.get('ordenar_por') === coluna && urlParams.get('ordenacao') === 'ASC') {
                ordenacao = 'DESC';
            }

            urlParams.set('ordenar_por', coluna);
            urlParams.set('ordenacao', ordenacao);

            window.location.href = `categorias.php?${urlParams.toString()}`;
        }
    </script>
</body>
</html>

<?php
// Função para exibir a seta de ordenação
function setaOrdenacao($coluna, $ordenarPor, $ordenacao) {
    if ($ordenarPor === $coluna) {
        return $ordenacao === 'ASC' ? '▲' : '▼';
    }
    return '';
}
?>