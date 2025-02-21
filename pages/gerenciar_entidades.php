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
$entidade = $_GET['entidade'] ?? 'plataformas'; // Define a entidade padrão

// Lógica para cadastrar/editar entidades
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $descricao = trim($_POST['descricao']);
    $acao = trim($_POST['acao'] ?? 'NEGAR');
    $situacao = trim($_POST['situacao'] ?? 'ATIVO');

    switch ($entidade) {
        case 'plataformas':
            $tabela = 'plataformas';
            $campos = ['descricao', 'acao', 'situacao'];
            $valores = [$descricao, $acao, $situacao];
            break;
        case 'perfil':
            $tabela = 'perfil';
            $campos = ['nome_perfil', 'situacao'];
            $valores = [$descricao, $situacao];
            break;
        case 'paginas':
            $tabela = 'paginas';
            $campos = ['descricao', 'acao_padrao', 'situacao', 'id_plataforma'];
            $valores = [$descricao, $acao, $situacao, intval($_POST['id_plataforma'])];
            break;
        case 'campos':
            $tabela = 'campos';
            $campos = ['id_pagina', 'descricao', 'acao', 'situacao', 'id_plataforma'];
            $valores = [intval($_POST['id_pagina']), $descricao, $acao, $situacao, intval($_POST['id_plataforma'])];
            break;
        default:
            die("Entidade inválida.");
    }

    if ($id) {
        // Editar entidade
        $sql = "UPDATE $tabela SET " . implode('=?, ', $campos) . "=? WHERE id=?";
        $valores[] = $id;
    } else {
        // Cadastrar nova entidade
        $sql = "INSERT INTO $tabela (" . implode(', ', $campos) . ") VALUES (" . str_repeat('?,', count($campos) - 1) . "?)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    $MensagemSucesso = '<div id="sucesso" class="alert alert-success" role="alert">Entidade salva com sucesso!</div>';
}

// Processar edição in-place via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_campo'])) {
    $id = intval($_POST['id']);
    $campo = trim($_POST['campo']);
    $valor = trim($_POST['valor']);

    // Atualiza o campo no banco de dados
    $stmt = $pdo->prepare("UPDATE $entidade SET $campo = ? WHERE id = ?");
    $stmt->execute([$valor, $id]);

    echo json_encode(['status' => 'success']);
    exit();
}

// Buscar entidades existentes
$sql = "SELECT * FROM $entidade";
$stmt = $pdo->query($sql);
$entidades = $stmt->fetchAll();

// Buscar plataformas e páginas para selects
$plataformas = $pdo->query("SELECT * FROM plataformas")->fetchAll();
$paginas = $pdo->query("SELECT * FROM paginas")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Entidades</title>
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
        <?= Cabecalho("Gerenciar Entidades", "Dashboard", "Contas a Pagar", "Relatórios", "dashboard.php", "contas_pagar.php", "relatorios.php"); ?>
        <?= $MensagemSucesso; ?>

        <!-- Menu de Seleção de Entidade -->
        <div class="form-container">
            <label for="entidade" class="form-label">Selecione o tipo de entidade:</label>
            <select id="entidade" name="entidade" class="form-control" onchange="location = this.value;">
                <option value="gerenciar_entidades.php?entidade=plataformas" <?= $entidade === 'plataformas' ? 'selected' : '' ?>>Plataformas</option>
                <option value="gerenciar_entidades.php?entidade=perfil" <?= $entidade === 'perfil' ? 'selected' : '' ?>>Perfis</option>
                <option value="gerenciar_entidades.php?entidade=paginas" <?= $entidade === 'paginas' ? 'selected' : '' ?>>Páginas</option>
                <option value="gerenciar_entidades.php?entidade=campos" <?= $entidade === 'campos' ? 'selected' : '' ?>>Campos</option>
            </select>
        </div>

        <!-- Formulário de Cadastro/Edição -->
        <div class="form-container">
            <form method="post">
                <input type="hidden" name="id" value="">
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição:</label>
                    <input type="text" id="descricao" name="descricao" class="form-control" required>
                </div>
                <?php if ($entidade === 'paginas' || $entidade === 'campos'): ?>
                    <div class="mb-3">
                        <label for="id_plataforma" class="form-label">Plataforma:</label>
                        <select id="id_plataforma" name="id_plataforma" class="form-control" required>
                            <?php foreach ($plataformas as $plataforma): ?>
                                <option value="<?= $plataforma['id'] ?>"><?= htmlspecialchars($plataforma['descricao']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <?php if ($entidade === 'campos'): ?>
                    <div class="mb-3">
                        <label for="id_pagina" class="form-label">Página:</label>
                        <select id="id_pagina" name="id_pagina" class="form-control" required>
                            <?php foreach ($paginas as $pagina): ?>
                                <option value="<?= $pagina['id'] ?>"><?= htmlspecialchars($pagina['descricao']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <?php if ($entidade !== 'perfil'): ?>
                    <div class="mb-3">
                        <label for="acao" class="form-label">Ação:</label>
                        <select id="acao" name="acao" class="form-control" required>
                            <option value="NEGAR">NEGAR</option>
                            <option value="PERMITIR">PERMITIR</option>
                        </select>
                    </div>
                <?php endif; ?>
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

        <!-- Tabela de Entidades Existentes -->
        <div class="form-container">
            <h3><?= ucfirst($entidade) ?> Cadastradas</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <?php if ($entidade !== 'perfil'): ?>
                            <th>Ação</th>
                        <?php endif; ?>
                        <th>Situação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entidades as $ent): ?>
                        <tr>
                            <td><?= htmlspecialchars($ent['id']) ?></td>
                            <td class="editable" data-id="<?= $ent['id'] ?>" data-campo="descricao"><?= htmlspecialchars($ent['descricao'] ?? $ent['nome_perfil']) ?></td>
                            <?php if ($entidade !== 'perfil'): ?>
                                <td class="editable" data-id="<?= $ent['id'] ?>" data-campo="acao"><?= htmlspecialchars($ent['acao'] ?? $ent['acao_padrao']) ?></td>
                            <?php endif; ?>
                            <td class="editable" data-id="<?= $ent['id'] ?>" data-campo="situacao"><?= htmlspecialchars($ent['situacao']) ?></td>
                            <td>
                                
                                <button type="button" class="btn btn-danger btn-sm" onclick="excluirEntidade(<?= $ent['id'] ?>)">Excluir</button>
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
                        url: 'gerenciar_entidades.php',
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

        // Função para editar uma entidade
        function editarEntidade(id) {
            // Redireciona para a página de edição
            window.location.href = `editar_entidade.php?entidade=<?= $entidade ?>&id=${id}`;
        }

        // Função para excluir uma entidade
        function excluirEntidade(id) {
            if (confirm('Tem certeza que deseja excluir esta entidade?')) {
                $.ajax({
                    url: 'excluir_entidade.php',
                    method: 'POST',
                    data: {
                        entidade: '<?= $entidade ?>',
                        id: id
                    },
                    success: function(response) {
                        alert('Entidade excluída com sucesso!');
                        location.reload();
                    },
                    error: function() {
                        alert('Erro ao excluir entidade.');
                    }
                });
            }
        }
    </script>
</body>
</html>