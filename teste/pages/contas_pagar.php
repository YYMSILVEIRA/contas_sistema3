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

// Verifica se há um termo de pesquisa
$termoPesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';

// Verifica a ordenação
$ordenarPor = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'data'; // Coluna padrão para ordenação
$ordenacao = isset($_GET['ordenacao']) ? $_GET['ordenacao'] : 'ASC'; // Direção padrão da ordenação

// Configurações de paginação
$itensPorPagina = 15; // Número de itens por página
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página atual, padrão é 1
$offset = ($paginaAtual - 1) * $itensPorPagina; // Calcula o offset para a consulta SQL

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = trim($_POST['descricao']);
    $valor = trim($_POST['valor']);
    $data = trim($_POST['data']);
    $fornecedor = trim($_POST['fornecedor']);

    $stmt = $pdo->prepare('INSERT INTO contas_pagar (descricao, valor, data, fornecedor, id_empresa) VALUES (?, ?, ?, ?, (SELECT id_empresa FROM usuarios WHERE id = ?))');
    $stmt->execute([$descricao, $valor, $data, $fornecedor, $_SESSION['user_id']]);
}

// Consulta para buscar as contas a pagar com a situação
$sql = "
    SELECT cp.id, cp.descricao, cp.valor, DATE_FORMAT(cp.data, '%d/%m/%Y') as data, cp.fornecedor, s.descricao as situacao 
    FROM contas_pagar cp
    LEFT JOIN situacoes s ON cp.id_situacao = s.id
    WHERE cp.id_empresa = (SELECT id_empresa FROM usuarios WHERE id = :user_id) 
    AND cp.id_situacao IN (8,7,5) 
";

// Adiciona condições de pesquisa se houver um termo
if (!empty($termoPesquisa)) {
    $sql .= " AND (cp.descricao LIKE :termo1 OR cp.valor LIKE :termo2 OR cp.data LIKE :termo3 OR cp.fornecedor LIKE :termo4 OR s.descricao LIKE :termo5)";
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
    $stmt->bindValue(':termo4', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmt->bindValue(':termo5', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
}

// Vincula os parâmetros de paginação
$stmt->bindValue(':itensPorPagina', $itensPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$contas = $stmt->fetchAll();

// Consulta para contar o total de registros (para paginação)
$sqlTotal = "
    SELECT COUNT(*) as total 
    FROM contas_pagar cp
    LEFT JOIN situacoes s ON cp.id_situacao = s.id
    WHERE cp.id_empresa = (SELECT id_empresa FROM usuarios WHERE id = :user_id) 
    AND cp.id_situacao IN (8,7,5)
";

if (!empty($termoPesquisa)) {
    $sqlTotal .= " AND (cp.descricao LIKE :termo1 OR cp.valor LIKE :termo2 OR cp.data LIKE :termo3 OR cp.fornecedor LIKE :termo4 OR s.descricao LIKE :termo5)";
}

$stmtTotal = $pdo->prepare($sqlTotal);

// Vincula o parâmetro do ID do usuário
$stmtTotal->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

// Vincula os parâmetros de pesquisa, se houver
if (!empty($termoPesquisa)) {
    $stmtTotal->bindValue(':termo1', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmtTotal->bindValue(':termo2', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmtTotal->bindValue(':termo3', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmtTotal->bindValue(':termo4', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmtTotal->bindValue(':termo5', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
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
    <title>Contas a Pagar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <style>
        .btn-editar {
            background-color: #3498db;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-editar:hover {
            background-color: #2980b9;
        }
        .btn-excluir {
            background-color: #e74c3c;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <?= Cabecalho("Contas a Pagar", "Dashboard", "Adicionar Conta a Pagar", "Relatórios", "dashboard.php", "adicionar_contas_pagar.php", "relatorios.php",0); ?>

        <!-- Título -->
        <h3 class="mt-4">Lista de Contas a Pagar</h3>

        <!-- Campo de pesquisa -->
        <form method="get" action="" class="mb-4">
            <div class="input-group">
                <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar em todos os campos..." value="<?= htmlspecialchars($termoPesquisa) ?>">
                <button type="submit" class="btn btn-primary">Pesquisar</button>
                <?php if (!empty($termoPesquisa)): ?>
                    <a href="contas_pagar.php" class="btn btn-secondary">Limpar Pesquisa</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Tabela de Contas -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="sortable" onclick="ordenarPor('descricao')">Descrição <?= setaOrdenacao('descricao', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('valor')">Valor <?= setaOrdenacao('valor', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('data')">Pagar Até <?= setaOrdenacao('data', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('fornecedor')">Fornecedor <?= setaOrdenacao('fornecedor', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('situacao')">Situação <?= setaOrdenacao('situacao', $ordenarPor, $ordenacao) ?></th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contas as $conta): ?>
                <tr>
                    <td><?= htmlspecialchars($conta['descricao']) ?></td>
                    <td>R$ <?= number_format($conta['valor'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($conta['data']) ?></td>
                    <td><?= htmlspecialchars($conta['fornecedor']) ?></td>
                    <td><?= htmlspecialchars($conta['situacao']) ?></td>
                    <td>
                        <a href="<?= 'editar_conta_pagar.php?id='.$conta['id'] ?>" class="btn-editar">Editar</a>
                        <a href="<?= 'excluir_conta_pagar.php?id='.$conta['id'] ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta conta?')">Excluir</a>
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

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
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

            window.location.href = `contas_pagar.php?${urlParams.toString()}`;
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