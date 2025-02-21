<?php
require_once '../includes/db.php'; // Arquivo de conexão com o banco de dados
require_once '../includes/auth.php'; // Arquivo onde a função isLoggedIn() está definida
require_once '../includes/logs.php';

// Verifica se o usuário está logado e se é um administrador (id_perfil = 1)
if (!isLoggedIn() || $_SESSION['id_perfil'] != 1) {
    header('Location: login.php'); // Redireciona para a página de login se não for um administrador
    exit();
}

// Exemplo de uso: registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI']; // Captura a URL acessada
registrarLog($pagina_acessada);

// Verifica se há um termo de pesquisa
$termoPesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';

// Verifica a ordenação
$ordenarPor = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'id'; // Coluna padrão para ordenação
$ordenacao = isset($_GET['ordenacao']) ? $_GET['ordenacao'] : 'ASC'; // Direção padrão da ordenação

// Configurações de paginação
$itensPorPagina = 15; // Número de itens por página
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página atual, padrão é 1
$offset = ($paginaAtual - 1) * $itensPorPagina; // Calcula o offset para a consulta SQL

// Função para buscar usuários com paginação, pesquisa e ordenação
function getUsuarios($pdo, $itensPorPagina, $offset, $termoPesquisa = '', $ordenarPor = 'id', $ordenacao = 'ASC') {
    $sql = 'SELECT * FROM usuarios WHERE 1=1';
    $params = [];

    // Adiciona condições de pesquisa se houver um termo
    if (!empty($termoPesquisa)) {
        $sql .= ' AND (username LIKE :termo1 OR id_empresa LIKE :termo2 OR email LIKE :termo3 OR id_perfil LIKE :termo4 OR redefininir_senha LIKE :termo5 OR token_recuperacao_senha LIKE :termo6)';
        $params[':termo1'] = '%' . $termoPesquisa . '%';
        $params[':termo2'] = '%' . $termoPesquisa . '%';
        $params[':termo3'] = '%' . $termoPesquisa . '%';
        $params[':termo4'] = '%' . $termoPesquisa . '%';
        $params[':termo5'] = '%' . $termoPesquisa . '%';
        $params[':termo6'] = '%' . $termoPesquisa . '%';
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

// Função para contar o total de usuários com pesquisa
function getTotalUsuarios($pdo, $termoPesquisa = '') {
    $sql = 'SELECT COUNT(*) as total FROM usuarios WHERE 1=1';
    $params = [];

    // Adiciona condições de pesquisa se houver um termo
    if (!empty($termoPesquisa)) {
        $sql .= ' AND (username LIKE :termo1 OR id_empresa LIKE :termo2 OR email LIKE :termo3 OR id_perfil LIKE :termo4 OR redefininir_senha LIKE :termo5 OR token_recuperacao_senha LIKE :termo6)';
        $params[':termo1'] = '%' . $termoPesquisa . '%';
        $params[':termo2'] = '%' . $termoPesquisa . '%';
        $params[':termo3'] = '%' . $termoPesquisa . '%';
        $params[':termo4'] = '%' . $termoPesquisa . '%';
        $params[':termo5'] = '%' . $termoPesquisa . '%';
        $params[':termo6'] = '%' . $termoPesquisa . '%';
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

// Processar adição de novo usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar_usuario'])) {
    $username = trim($_POST['username']);
    $password = hash('sha256', trim($_POST['password'])); // Criptografa a senha
    $id_empresa = intval($_POST['id_empresa']);
    $email = trim($_POST['email']);
    $id_perfil = intval($_POST['id_perfil']);
    $redefinir_senha = trim($_POST['redefinir_senha']);
    $token_recuperacao_senha = ''; // Inicializa o token como vazio

    // Insere o novo usuário no banco de dados
    $stmt = $pdo->prepare('INSERT INTO usuarios (username, password, id_empresa, email, id_perfil, redefininir_senha, token_recuperacao_senha) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$username, $password, $id_empresa, $email, $id_perfil, $redefinir_senha, $token_recuperacao_senha]);

    header('Location: gerenciar_usuarios.php'); // Recarrega a página para exibir o novo usuário
    exit();
}

// Processar edição de usuário existente via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_campo'])) {
    $id = intval($_POST['id']);
    $campo = trim($_POST['campo']);
    $valor = trim($_POST['valor']);

    // Verifica se o campo é a senha
    if ($campo === 'password') {
        $valor = hash('sha256', $valor);
    }

    // Atualiza o campo no banco de dados
    $stmt = $pdo->prepare("UPDATE usuarios SET $campo = ? WHERE id = ?");
    $stmt->execute([$valor, $id]);

    echo json_encode(['status' => 'success']);
    exit();
}

// Busca os usuários para a página atual
$usuarios = getUsuarios($pdo, $itensPorPagina, $offset, $termoPesquisa, $ordenarPor, $ordenacao);

// Busca o total de usuários para calcular o número de páginas
$totalUsuarios = getTotalUsuarios($pdo, $termoPesquisa);
$totalPaginas = ceil($totalUsuarios / $itensPorPagina); // Calcula o total de páginas
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
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
        <!-- Botão para voltar ao dashboard principal -->
        <a href="../pages/dashboard.php" class="btn btn-secondary mt-4">Voltar ao Dashboard</a>

        <h1 class="mt-4">Gerenciar Usuários</h1>

        <!-- Campo de pesquisa -->
        <form method="get" action="" class="mb-4">
            <div class="input-group">
                <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar em todos os campos..." value="<?= htmlspecialchars($termoPesquisa) ?>">
                <button type="submit" class="btn btn-primary">Pesquisar</button>
                <?php if (!empty($termoPesquisa)): ?>
                    <a href="gerenciar_usuarios.php" class="btn btn-secondary">Limpar Pesquisa</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Formulário para adicionar novo usuário -->
        <h3 class="mt-4">Adicionar Novo Usuário</h3>
        <form method="post" class="mb-4">
            <div class="mb-3">
                <label for="username" class="form-label">Nome de Usuário:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="id_empresa" class="form-label">ID da Empresa:</label>
                <input type="number" id="id_empresa" name="id_empresa" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>
            <div class="mb-3">
                <label for="id_perfil" class="form-label">ID do Perfil:</label>
                <input type="number" id="id_perfil" name="id_perfil" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="redefinir_senha" class="form-label">Redefinir Senha:</label>
                <select id="redefinir_senha" name="redefinir_senha" class="form-control" required>
                    <option value="NÃO">NÃO</option>
                    <option value="SIM">SIM</option>
                </select>
            </div>
            <button type="submit" name="adicionar_usuario" class="btn btn-primary">Adicionar Usuário</button>
        </form>

        <!-- Tabela de usuários existentes -->
        <h3 class="mt-4">Usuários Existentes</h3>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="sortable" onclick="ordenarPor('id')">ID <?= setaOrdenacao('id', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('username')">Nome de Usuário <?= setaOrdenacao('username', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('id_empresa')">ID da Empresa <?= setaOrdenacao('id_empresa', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('email')">Email <?= setaOrdenacao('email', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('id_perfil')">ID do Perfil <?= setaOrdenacao('id_perfil', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('redefininir_senha')">Redefinir Senha <?= setaOrdenacao('redefininir_senha', $ordenarPor, $ordenacao) ?></th>
                    <th class="sortable" onclick="ordenarPor('token_recuperacao_senha')">Token de Recuperação <?= setaOrdenacao('token_recuperacao_senha', $ordenarPor, $ordenacao) ?></th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                    <td class="editable" data-id="<?= $usuario['id'] ?>" data-campo="username"><?= htmlspecialchars($usuario['username']) ?></td>
                    <td class="editable" data-id="<?= $usuario['id'] ?>" data-campo="id_empresa"><?= htmlspecialchars($usuario['id_empresa']) ?></td>
                    <td class="editable" data-id="<?= $usuario['id'] ?>" data-campo="email"><?= htmlspecialchars($usuario['email']) ?></td>
                    <td class="editable" data-id="<?= $usuario['id'] ?>" data-campo="id_perfil"><?= htmlspecialchars($usuario['id_perfil']) ?></td>
                    <td class="editable" data-id="<?= $usuario['id'] ?>" data-campo="redefininir_senha">
                        <select class="form-control" onchange="atualizarCampo(this, 'redefininir_senha', <?= $usuario['id'] ?>)">
                            <option value="NÃO" <?= $usuario['redefininir_senha'] === 'NÃO' ? 'selected' : '' ?>>NÃO</option>
                            <option value="SIM" <?= $usuario['redefininir_senha'] === 'SIM' ? 'selected' : '' ?>>SIM</option>
                        </select>
                    </td>
                    <td class="editable" data-id="<?= $usuario['id'] ?>" data-campo="token_recuperacao_senha">
                        <input type="text" class="form-control token-field" value="<?= htmlspecialchars($usuario['token_recuperacao_senha']) ?>" readonly onclick="copiarToken(this)">
                    </td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" onclick="gerarToken(<?= $usuario['id'] ?>)">
                            Gerar Token
                        </button>
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

    <!-- Script para edição in-place -->
    <script>
        $(document).ready(function() {
            $('.editable').on('dblclick', function() {
                var $this = $(this);
                var valorAtual = $this.text().trim();
                var campo = $this.data('campo');
                var id = $this.data('id');

                // Cria um input para edição
                var $input = $('<input>', {
                    type: 'text',
                    value: valorAtual,
                    class: 'form-control'
                });

                // Cria um botão de salvar
                var $botaoSalvar = $('<button>', {
                    text: 'Salvar',
                    class: 'btn btn-success btn-sm ms-2'
                });

                // Substitui o texto pelo input e botão
                $this.html($input);
                $this.append($botaoSalvar);
                $input.focus();

                // Salva a alteração ao clicar no botão de salvar
                $botaoSalvar.on('click', function() {
                    var novoValor = $input.val().trim();

                    // Envia a alteração via AJAX
                    $.ajax({
                        url: 'gerenciar_usuarios.php',
                        method: 'POST',
                        data: {
                            editar_campo: true,
                            id: id,
                            campo: campo,
                            valor: novoValor
                        },
                        success: function(response) {
                            $this.text(novoValor); // Atualiza o texto na tabela
                        },
                        error: function() {
                            alert('Erro ao salvar alteração.');
                            $this.text(valorAtual); // Reverte para o valor original
                        }
                    });
                });

                // Cancela a edição ao pressionar Esc
                $input.on('keydown', function(e) {
                    if (e.key === 'Escape') {
                        $this.text(valorAtual); // Reverte para o valor original
                    }
                });
            });
        });

        // Função para atualizar o campo redefinir_senha via AJAX
        function atualizarCampo(select, campo, id) {
            var valor = select.value;

            $.ajax({
                url: 'gerenciar_usuarios.php',
                method: 'POST',
                data: {
                    editar_campo: true,
                    id: id,
                    campo: campo,
                    valor: valor
                },
                success: function(response) {
                    alert('Campo atualizado com sucesso!');
                },
                error: function() {
                    alert('Erro ao atualizar campo.');
                }
            });
        }

        // Função para gerar um token aleatório
        function gerarToken(id) {
            const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let token = '';
            for (let i = 0; i < 32; i++) {
                token += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
            }

            // Atualiza o campo na tabela
            $(`input[data-id="${id}"].token-field`).val(token);

            // Envia o token para o servidor via AJAX
            $.ajax({
                url: 'gerenciar_usuarios.php',
                method: 'POST',
                data: {
                    editar_campo: true,
                    id: id,
                    campo: 'token_recuperacao_senha',
                    valor: token
                },
                success: function(response) {
                    alert('Token gerado e salvo com sucesso!');
                    location.reload();
                },
                error: function() {
                    alert('Erro ao salvar token.');
                }
            });
        }

        // Função para copiar o token para a área de transferência
        function copiarToken(input) {
            input.select(); // Seleciona o conteúdo do campo
            document.execCommand('copy'); // Copia o conteúdo selecionado
            alert('Token copiado para a área de transferência!');
        }

        // Função para ordenar por uma coluna
        function ordenarPor(coluna) {
            const urlParams = new URLSearchParams(window.location.search);
            let ordenacao = 'ASC';

            if (urlParams.get('ordenar_por') === coluna && urlParams.get('ordenacao') === 'ASC') {
                ordenacao = 'DESC';
            }

            urlParams.set('ordenar_por', coluna);
            urlParams.set('ordenacao', ordenacao);

            window.location.href = `gerenciar_usuarios.php?${urlParams.toString()}`;
        }
    </script>

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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