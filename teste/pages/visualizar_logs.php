<?php
require_once '../includes/db.php'; // Arquivo de conexão com o banco de dados
require_once '../includes/auth.php'; // Arquivo onde a função isLoggedIn() está definida

// Verifica se o usuário está logado e se é um administrador (id_perfil = 1)
if (!isLoggedIn() || $_SESSION['id_perfil'] != 1) {
    header('Location: login.php'); // Redireciona para a página de login se não for um administrador
    exit();
}

// Lógica de exclusão de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Prepara a query para excluir o registro
    $sql = 'DELETE FROM logs WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    // Executa a query
    if ($stmt->execute()) {
        $status = 'success'; // Registro excluído com sucesso
    } else {
        $status = 'error'; // Erro ao excluir o registro
    }

    // Redireciona para a mesma página para evitar reenvio do formulário
    header('Location: visualizar_logs.php?status=' . $status);
    exit();
}

// Configurações de paginação
$itensPorPagina = 25; // Número de itens por página
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página atual, padrão é 1
$offset = ($paginaAtual - 1) * $itensPorPagina; // Calcula o offset para a consulta SQL

// Verifica se há um termo de pesquisa
$termoPesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';

// Verifica a ordenação
$ordenarPor = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'id'; // Coluna padrão para ordenação
$ordenacao = isset($_GET['ordenacao']) ? $_GET['ordenacao'] : 'DESC'; // Direção padrão da ordenação

// Função para converter datas no formato dd/mm/yyyy H24:mm:ss para yyyy-mm-dd H:i:s
function converterDataParaBanco($data) {
    $dataHora = DateTime::createFromFormat('d/m/Y H:i:s', $data);
    return $dataHora ? $dataHora->format('Y-m-d H:i:s') : null;
}

// Função para buscar logs com paginação, pesquisa e ordenação
function getLogs($pdo, $itensPorPagina, $offset, $termoPesquisa = '', $ordenarPor = 'id', $ordenacao = 'DESC') {
    $sql = 'SELECT * FROM logs WHERE 1=1';
    $params = [];

    // Adiciona condições de pesquisa se houver um termo
    if (!empty($termoPesquisa)) {
        // Verifica se o termo de pesquisa é uma data no formato dd/mm/yyyy H24:mm:ss
        $dataBanco = converterDataParaBanco($termoPesquisa);
        if ($dataBanco) {
            $sql .= ' AND (created_at = :termo_data)';
            $params[':termo_data'] = $dataBanco;
        } else {
            $sql .= ' AND (id LIKE :termo1 OR ip_address LIKE :termo2 OR access_time LIKE :termo3 OR page_accessed LIKE :termo4 OR user_id LIKE :termo5 OR operating_system LIKE :termo6 OR created_at LIKE :termo7)';
            $params[':termo1'] = '%' . $termoPesquisa . '%';
            $params[':termo2'] = '%' . $termoPesquisa . '%';
            $params[':termo3'] = '%' . $termoPesquisa . '%';
            $params[':termo4'] = '%' . $termoPesquisa . '%';
            $params[':termo5'] = '%' . $termoPesquisa . '%';
            $params[':termo6'] = '%' . $termoPesquisa . '%';
            $params[':termo7'] = '%' . $termoPesquisa . '%';
        }
    }

    // Adiciona a ordenação
    $sql .= " ORDER BY $ordenarPor $ordenacao LIMIT :itensPorPagina OFFSET :offset";
    $stmt = $pdo->prepare($sql);

    // Vincula os parâmetros
    $stmt->bindValue(':itensPorPagina', $itensPorPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    if (!empty($termoPesquisa)) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }

    $stmt->execute();
    return $stmt->fetchAll();
}

// Função para contar o total de logs com pesquisa
function getTotalLogs($pdo, $termoPesquisa = '') {
    $sql = 'SELECT COUNT(*) as total FROM logs WHERE 1=1';
    $params = [];

    // Adiciona condições de pesquisa se houver um termo
    if (!empty($termoPesquisa)) {
        // Verifica se o termo de pesquisa é uma data no formato dd/mm/yyyy H24:mm:ss
        $dataBanco = converterDataParaBanco($termoPesquisa);
        if ($dataBanco) {
            $sql .= ' AND (created_at = :termo_data)';
            $params[':termo_data'] = $dataBanco;
        } else {
            $sql .= ' AND (id LIKE :termo1 OR ip_address LIKE :termo2 OR access_time LIKE :termo3 OR page_accessed LIKE :termo4 OR user_id LIKE :termo5 OR operating_system LIKE :termo6 OR created_at LIKE :termo7)';
            $params[':termo1'] = '%' . $termoPesquisa . '%';
            $params[':termo2'] = '%' . $termoPesquisa . '%';
            $params[':termo3'] = '%' . $termoPesquisa . '%';
            $params[':termo4'] = '%' . $termoPesquisa . '%';
            $params[':termo5'] = '%' . $termoPesquisa . '%';
            $params[':termo6'] = '%' . $termoPesquisa . '%';
            $params[':termo7'] = '%' . $termoPesquisa . '%';
        }
    }

    $stmt = $pdo->prepare($sql);
    if (!empty($termoPesquisa)) {
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
    $stmt->execute();
    return $stmt->fetch()['total'];
}

// Busca os logs para a página atual
$logs = getLogs($pdo, $itensPorPagina, $offset, $termoPesquisa, $ordenarPor, $ordenacao);

// Busca o total de logs para calcular o número de páginas
$totalLogs = getTotalLogs($pdo, $termoPesquisa);
$totalPaginas = ceil($totalLogs / $itensPorPagina); // Calcula o total de páginas
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Logs do Sistema</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
</head>
<body>
    <div class="container">
        <!-- Botão para voltar ao dashboard principal -->
        <a href="../pages/dashboard.php" class="btn btn-secondary mt-4">Voltar ao Dashboard</a>

        <h1 class="mt-4">Logs do Sistema</h1>

        <!-- Exibe mensagens de status (sucesso ou erro) -->
        <?php if (isset($_GET['status'])): ?>
            <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> mt-3">
                <?= $_GET['status'] === 'success' ? 'Registro excluído com sucesso!' : 'Erro ao excluir o registro.' ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de pesquisa e ordenação -->
        <form method="get" action="" class="mb-4">
            <div class="row">
                <!-- Campo de pesquisa -->
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar em todos os campos..." value="<?= htmlspecialchars($termoPesquisa) ?>">
                        <button type="submit" class="btn btn-primary">Pesquisar</button>
                        <?php if (!empty($termoPesquisa)): ?>
                            <a href="visualizar_logs.php" class="btn btn-secondary">Limpar Pesquisa</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Campo de ordenação -->
                <div class="col-md-6">
                    <div class="input-group">
                        <select name="ordenar_por" class="form-select">
                            <option value="id" <?= $ordenarPor === 'id' ? 'selected' : '' ?>>ID</option>
                            <option value="ip_address" <?= $ordenarPor === 'ip_address' ? 'selected' : '' ?>>Endereço IP</option>
                            <option value="access_time" <?= $ordenarPor === 'access_time' ? 'selected' : '' ?>>Data e Hora de Acesso</option>
                            <option value="page_accessed" <?= $ordenarPor === 'page_accessed' ? 'selected' : '' ?>>Página Acessada</option>
                            <option value="user_id" <?= $ordenarPor === 'user_id' ? 'selected' : '' ?>>ID do Usuário</option>
                            <option value="operating_system" <?= $ordenarPor === 'operating_system' ? 'selected' : '' ?>>Sistema Operacional</option>
                            <option value="created_at" <?= $ordenarPor === 'created_at' ? 'selected' : '' ?>>Data de Criação</option>
                        </select>
                        <select name="ordenacao" class="form-select">
                            <option value="ASC" <?= $ordenacao === 'ASC' ? 'selected' : '' ?>>Ascendente</option>
                            <option value="DESC" <?= $ordenacao === 'DESC' ? 'selected' : '' ?> >Descendente</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Ordenar</button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Tabela de logs -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Endereço IP</th>
                    <th>Sistema Operacional</th>
                    <th>ID do Usuário</th>
                    <th>Página Acessada</th>
                    <th>Data e Hora de Acesso</th>
                    <th>Ação</th> <!-- Nova coluna -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['id']) ?></td>
                    <td><?= htmlspecialchars($log['ip_address']) ?></td>
                    <td><center><?= htmlspecialchars($log['operating_system']) ?></center></td>
                    <td><center><?= htmlspecialchars($log['user_id']) ?></center></td>
                    <td><?= htmlspecialchars($log['page_accessed']) ?></td>
                    <td><center><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></center></td>
                    <td>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $log['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este registro?');">Excluir</button>
                        </form>
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
    </div>

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>