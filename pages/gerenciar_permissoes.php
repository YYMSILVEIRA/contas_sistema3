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
$tipo_permissao = $_GET['tipo'] ?? 'plataformas'; // Define o tipo de permissão padrão

// Lógica para edição in-place via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_campo'])) {
    $id = intval($_POST['id']);
    $campo = trim($_POST['campo']);
    $valor = trim($_POST['valor']);

    // Define a tabela correta com base no tipo de permissão
    switch ($tipo_permissao) {
        case 'plataformas':
            $tabela = 'perfil_permissoes_plataformas';
            break;
        case 'paginas':
            $tabela = 'perfil_permissoes_pagina';
            break;
        case 'campos':
            $tabela = 'perfil_permissoes_campos';
            break;
        default:
            die(json_encode(['status' => 'error', 'message' => 'Tipo de permissão inválido.']));
    }

    // Verifica se o campo existe na tabela
    $stmt = $pdo->prepare("SHOW COLUMNS FROM $tabela LIKE ?");
    $stmt->execute([$campo]);
    if (!$stmt->fetch()) {
        die(json_encode(['status' => 'error', 'message' => 'Campo inválido.']));
    }

    // Atualiza o campo no banco de dados
    $stmt = $pdo->prepare("UPDATE $tabela SET $campo = ? WHERE id = ?");
    if ($stmt->execute([$valor, $id])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar alteração.']);
    }
    exit();
}

// Lógica para cadastrar/editar permissões
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['editar_campo'])) {
    $id = $_POST['id'] ?? null;
    $id_perfil = intval($_POST['id_perfil']);
    $id_entidade = intval($_POST['id_entidade']);
    $acao = trim($_POST['acao']);
    $situacao = trim($_POST['situacao']);

    switch ($tipo_permissao) {
        case 'plataformas':
            $tabela = 'perfil_permissoes_plataformas';
            $campos = ['id_perfil', 'id_plataforma', 'acao', 'situacao'];
            $valores = [$id_perfil, $id_entidade, $acao, $situacao];
            break;
        case 'paginas':
            $tabela = 'perfil_permissoes_pagina';
            $campos = ['id_perfil', 'id_pagina', 'acao', 'situacao', 'id_plataforma'];
            $valores = [$id_perfil, $id_entidade, $acao, $situacao, intval($_POST['id_plataforma'])];
            break;
        case 'campos':
            $tabela = 'perfil_permissoes_campos';
            $campos = ['id_perfil', 'id_campo', 'acao', 'situacao', 'id_plataforma'];
            $valores = [$id_perfil, $id_entidade, $acao, $situacao, intval($_POST['id_plataforma'])];
            break;
        default:
            die("Tipo de permissão inválido.");
    }

    if ($id) {
        // Editar permissão
        $sql = "UPDATE $tabela SET " . implode('=?, ', $campos) . "=? WHERE id=?";
        $valores[] = $id;
    } else {
        // Cadastrar nova permissão
        $sql = "INSERT INTO $tabela (" . implode(', ', $campos) . ") VALUES (" . str_repeat('?,', count($campos) - 1) . "?)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    $MensagemSucesso = '<div id="sucesso" class="alert alert-success" role="alert">Permissão salva com sucesso!</div>';
}

// Definir a tabela correta com base no tipo de permissão
switch ($tipo_permissao) {
    case 'plataformas':
        $tabela = 'perfil_permissoes_plataformas';
        break;
    case 'paginas':
        $tabela = 'perfil_permissoes_pagina';
        break;
    case 'campos':
        $tabela = 'perfil_permissoes_campos';
        break;
    default:
        die("Tipo de permissão inválido.");
}

// Buscar permissões existentes
$sql = "SELECT pp.*, p.nome_perfil FROM $tabela pp JOIN perfil p ON pp.id_perfil = p.id";
$stmt = $pdo->query($sql);
$permissoes = $stmt->fetchAll();

// Buscar perfis, plataformas, páginas e campos para selects
$perfis = $pdo->query("SELECT * FROM perfil")->fetchAll();
$plataformas = $pdo->query("SELECT * FROM plataformas")->fetchAll();
$paginas = $pdo->query("SELECT * FROM paginas")->fetchAll();
$campos = $pdo->query("SELECT * FROM campos")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Permissões</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f8f9fa; }
        .form-container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .form-control { border-radius: 8px; border: 1px solid #ced4da; padding: 10px; font-size: 16px; }
        .form-control:focus { border-color: #007AFF; box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.2); }
        .btn-primary { border: none; border-radius: 8px; padding: 12px; font-size: 16px; font-weight: 500; }
        .btn-primary:hover { background-color: #0063cc; }
        .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 8px; padding: 10px; margin-bottom: 20px; }
        .fade { transition: opacity 0.5s ease-in-out; opacity: 0; }
        .editable { cursor: pointer; }
        .editable:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <?= Cabecalho("Gerenciar Permissões", "Dashboard", "Contas a Pagar", "Relatórios", "dashboard.php", "contas_pagar.php", "relatorios.php"); ?>
        <?= $MensagemSucesso; ?>

        <!-- Menu de Seleção de Tipo de Permissão -->
        <div class="form-container">
            <label for="tipo_permissao" class="form-label">Selecione o tipo de permissão:</label>
            <select id="tipo_permissao" name="tipo_permissao" class="form-control" onchange="location = this.value;">
                <option value="gerenciar_permissoes.php?tipo=plataformas" <?= $tipo_permissao === 'plataformas' ? 'selected' : '' ?>>Permissões de Plataformas</option>
                <option value="gerenciar_permissoes.php?tipo=paginas" <?= $tipo_permissao === 'paginas' ? 'selected' : '' ?>>Permissões de Páginas</option>
                <option value="gerenciar_permissoes.php?tipo=campos" <?= $tipo_permissao === 'campos' ? 'selected' : '' ?>>Permissões de Campos</option>
            </select>
        </div>

        <!-- Formulário de Cadastro/Edição de Permissões -->
        <div class="form-container">
            <form method="post">
                <input type="hidden" name="id" value="">
                <div class="mb-3">
                    <label for="id_perfil" class="form-label">Perfil:</label>
                    <select id="id_perfil" name="id_perfil" class="form-control" required>
                        <?php foreach ($perfis as $perfil): ?>
                            <option value="<?= $perfil['id'] ?>"><?= htmlspecialchars($perfil['nome_perfil']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="id_entidade" class="form-label">Entidade:</label>
                    <select id="id_entidade" name="id_entidade" class="form-control" required>
                        <?php if ($tipo_permissao === 'plataformas'): ?>
                            <?php foreach ($plataformas as $plataforma): ?>
                                <option value="<?= $plataforma['id'] ?>"><?= htmlspecialchars($plataforma['descricao']) ?></option>
                            <?php endforeach; ?>
                        <?php elseif ($tipo_permissao === 'paginas'): ?>
                            <?php foreach ($paginas as $pagina): ?>
                                <option value="<?= $pagina['id'] ?>"><?= htmlspecialchars($pagina['descricao']) ?></option>
                            <?php endforeach; ?>
                        <?php elseif ($tipo_permissao === 'campos'): ?>
                            <?php foreach ($campos as $campo): ?>
                                <option value="<?= $campo['id'] ?>"><?= htmlspecialchars($campo['descricao']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="acao" class="form-label">Ação:</label>
                    <select id="acao" name="acao" class="form-control" required>
                        <option value="NEGAR">NEGAR</option>
                        <option value="PERMITIR">PERMITIR</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="situacao" class="form-label">Situação:</label>
                    <select id="situacao" name="situacao" class="form-control" required>
                        <option value="ATIVO">ATIVO</option>
                        <option value="INATIVO">INATIVO</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </form>
        </div>

        <!-- Tabela de Permissões Existentes -->
        <div class="form-container">
            <h3>Permissões Cadastradas</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Perfil</th>
                        <th>Entidade</th>
                        <th>Ação</th>
                        <th>Situação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissoes as $permissao): ?>
                        <tr>
                            <td><?= htmlspecialchars($permissao['id']) ?></td>
                            <td class="editable" data-id="<?= $permissao['id'] ?>" data-campo="id_perfil"><?= htmlspecialchars($permissao['nome_perfil']) ?></td>
                            <td class="editable" data-id="<?= $permissao['id'] ?>" data-campo="id_entidade"><?= htmlspecialchars($permissao['id_plataforma'] ?? $permissao['id_pagina'] ?? $permissao['id_campo']) ?></td>
                            <td class="editable" data-id="<?= $permissao['id'] ?>" data-campo="acao"><?= htmlspecialchars($permissao['acao']) ?></td>
                            <td class="editable" data-id="<?= $permissao['id'] ?>" data-campo="situacao"><?= htmlspecialchars($permissao['situacao']) ?></td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" onclick="editarPermissao(<?= $permissao['id'] ?>)">Editar</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="excluirPermissao(<?= $permissao['id'] ?>)">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= $Rodape; ?>
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
                        url: 'gerenciar_permissoes.php',
                        method: 'POST',
                        data: {
                            editar_campo: true,
                            id: id,
                            campo: campo,
                            valor: novoValor
                        },
                        success: function(response) {
                            console.log(response); // Debugging: Exibe a resposta do servidor
                            try {
                                var resposta = JSON.parse(response);
                                if (resposta.status === 'success') {
                                    $this.text(novoValor); // Atualiza o texto na tabela
                                } else {
                                    alert(resposta.message || 'Erro ao salvar alteração.');
                                    $this.text(valorAtual); // Reverte para o valor original
                                }
                            } catch (e) {
                                console.error('Erro ao processar resposta:', e);
                                alert('Erro ao processar resposta do servidor.');
                                $this.text(valorAtual); // Reverte para o valor original
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erro na requisição AJAX:', status, error); // Debugging
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

        // Função para editar uma permissão
        function editarPermissao(id) {
            // Redireciona para a página de edição
            window.location.href = `editar_permissao.php?tipo=<?= $tipo_permissao ?>&id=${id}`;
        }

        // Função para excluir uma permissão
        function excluirPermissao(id) {
            if (confirm('Tem certeza que deseja excluir esta permissão?')) {
                $.ajax({
                    url: 'gerenciar_permissoes.php',
                    method: 'POST',
                    data: {
                        tipo: '<?= $tipo_permissao ?>',
                        id: id
                    },
                    success: function(response) {
                        alert('Permissão excluída com sucesso!');
                        location.reload();
                    },
                    error: function() {
                        alert('Erro ao excluir permissão.');
                    }
                });
            }
        }
    </script>
</body>
</html>