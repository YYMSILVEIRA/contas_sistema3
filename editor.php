<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Verifica se o usuário está logado
if (!isLoggedIn()) {
    header('Location: loginEditor.php');
    exit();
}

// Verifica se o usuário tem id_perfil = 1
if ($_SESSION['id_perfil'] !== 1) {
    die("Acesso negado: você não tem permissão para acessar esta página.");
}

// Definindo o diretório raiz (onde o editor está instalado)
$diretorioRaiz = realpath(__DIR__); // Pega o diretório atual do script
$diretorioAtual = isset($_GET['dir']) ? $_GET['dir'] : $diretorioRaiz;

// Verificando se o diretório atual está dentro do diretório raiz (segurança)
if (strpos(realpath($diretorioAtual), $diretorioRaiz) !== 0) {
    die("Acesso negado: diretório fora do escopo permitido.");
}

// Processando o upload de arquivos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_upload'])) {
    $arquivo = $_FILES['arquivo_upload'];
    $caminhoArquivo = $diretorioAtual . '/' . basename($arquivo['name']);

    if (move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
        echo "<div class='alert alert-success'>Arquivo enviado com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger'>Erro ao enviar o arquivo.</div>";
    }
}

// Processando a exclusão de arquivos ou pastas
if (isset($_GET['excluir'])) {
    $itemExcluir = $diretorioAtual . '/' . basename($_GET['excluir']);

    if (is_dir($itemExcluir)) {
        // Excluir pasta (recursivamente)
        function excluirPasta($caminho) {
            if (is_dir($caminho)) {
                $itens = scandir($caminho);
                foreach ($itens as $item) {
                    if ($item !== '.' && $item !== '..') {
                        excluirPasta($caminho . '/' . $item);
                    }
                }
                rmdir($caminho);
            } else {
                unlink($caminho);
            }
        }
        excluirPasta($itemExcluir);
        echo "<div class='alert alert-success'>Pasta excluída com sucesso!</div>";
    } elseif (file_exists($itemExcluir)) {
        // Excluir arquivo
        unlink($itemExcluir);
        echo "<div class='alert alert-success'>Arquivo excluído com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger'>Item não encontrado.</div>";
    }
}

// Processando a edição do nome de arquivos/pastas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_nome']) && isset($_POST['item_atual'])) {
    $itemAtual = $diretorioAtual . '/' . basename($_POST['item_atual']);
    $novoNome = $diretorioAtual . '/' . basename($_POST['novo_nome']);

    if (file_exists($itemAtual)) {
        if (rename($itemAtual, $novoNome)) {
            echo "<div class='alert alert-success'>Nome alterado com sucesso!</div>";
        } else {
            echo "<div class='alert alert-danger'>Erro ao alterar o nome.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Item não encontrado.</div>";
    }
}

// Verificando se um arquivo foi solicitado para edição
if (isset($_GET['arquivo'])) {
    $arquivo = $_GET['arquivo'];
    $caminhoArquivo = $diretorioAtual . '/' . $arquivo;

    // Verificando se o formulário foi enviado para salvar o arquivo
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conteudo = $_POST['conteudo'];
        file_put_contents($caminhoArquivo, $conteudo);
        echo "<div class='alert alert-success'>Arquivo salvo com sucesso!</div>";
        exit(); // Encerra a execução para evitar redirecionamento
    }

    // Lendo o conteúdo do arquivo (se existir)
    $conteudo = file_exists($caminhoArquivo) ? file_get_contents($caminhoArquivo) : '';
}

// Listando os arquivos e pastas do diretório atual
$itens = scandir($diretorioAtual);
$itens = array_diff($itens, array('.', '..'));

// Processando a criação de uma nova pasta ou arquivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $nome = $_POST['nome'];
    $caminhoNovoItem = $diretorioAtual . '/' . $nome;

    if (isset($_POST['tipo']) && $_POST['tipo'] === 'pasta') {
        // Criar nova pasta
        if (!file_exists($caminhoNovoItem)) {
            mkdir($caminhoNovoItem, 0755, true);
            echo "<div class='alert alert-success'>Pasta criada com sucesso!</div>";
        } else {
            echo "<div class='alert alert-warning'>A pasta já existe.</div>";
        }
    } elseif (isset($_POST['tipo']) && $_POST['tipo'] === 'arquivo') {
        // Criar novo arquivo
        if (!file_exists($caminhoNovoItem)) {
            file_put_contents($caminhoNovoItem, ''); // Cria um arquivo vazio
            echo "<div class='alert alert-success'>Arquivo criado com sucesso!</div>";
        } else {
            echo "<div class='alert alert-warning'>O arquivo já existe.</div>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor de Texto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .edit-input {
            display: none;
        }
        .hidden-buttons {
            display: none;
        }
        .list-group-item {
            cursor: pointer; /* Mostra o cursor de mão ao passar sobre o item */
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
            <!-- Campo de pesquisa no gerenciador de arquivos -->
                     <div class="mb-4">
                           <input type="text" id="pesquisaInput" class="form-control" placeholder="Pesquisar arquivos ou pastas...">
                     </div>
                <h1 class="h4">Navegando em: <?php echo htmlspecialchars($diretorioAtual); ?></h1>
                <!-- Botão de Atualizar Página -->
                <button onclick="location.reload();" class="btn btn-secondary float-end">Atualizar Página</button>
            </div>
            <div class="card-body">
                <!-- Ações de criação e upload -->
                <div class="mb-4">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#criarPastaModal">
                        Criar Nova Pasta
                    </button>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#criarArquivoModal">
                        Criar Novo Arquivo
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        Subir Arquivo
                    </button>
                </div>

                <!-- Lista de itens e diretórios -->
                <ul class="list-group mb-3">
                    <?php foreach ($itens as $item): ?>
                        <?php
                        $caminhoItem = $diretorioAtual . '/' . $item;
                        if (is_dir($caminhoItem)): ?>
                            <li class="list-group-item" onclick="mostrarBotoes('<?php echo $item; ?>')">
                                <strong>Pasta:</strong>
                                <span class="item-title"><?php echo $item; ?></span>
                                <div class="hidden-buttons" id="buttons-<?php echo $item; ?>">
                                    <a href="editor.php?dir=<?php echo urlencode($caminhoItem); ?>" class="btn btn-primary btn-sm">Abrir</a>
                                    <button onclick="editarNome('<?php echo $item; ?>')" class="btn btn-warning btn-sm">Renomear</button>
                                    <a href="editor.php?dir=<?php echo urlencode($diretorioAtual); ?>&excluir=<?php echo urlencode($item); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta pasta?')">Excluir</a>
                                </div>
                                <form method="POST" style="display: none;" class="edit-input" id="form-<?php echo $item; ?>">
                                    <input type="text" name="novo_nome" value="<?php echo $item; ?>" class="form-control-sm">
                                    <input type="hidden" name="item_atual" value="<?php echo $item; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Salvar</button>
                                </form>
                            </li>
                        <?php else: ?>
                            <li class="list-group-item" onclick="mostrarBotoes('<?php echo $item; ?>')">
                                <strong>Arquivo:</strong>
                                <span class="item-title"><?php echo $item; ?></span>
                                <div class="hidden-buttons" id="buttons-<?php echo $item; ?>">
                                    <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editorModal" data-bs-arquivo="<?php echo urlencode($item); ?>" data-bs-caminho="<?php echo urlencode($caminhoItem); ?>">Editar</a>
                                    <a href="<?php echo urlencode($caminhoItem); ?>" download class="btn btn-success btn-sm">Baixar</a>
                                    <button onclick="editarNome('<?php echo $item; ?>')" class="btn btn-warning btn-sm">Renomear</button>
                                    <a href="editor.php?dir=<?php echo urlencode($diretorioAtual); ?>&excluir=<?php echo urlencode($item); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este arquivo?')">Excluir</a>
                                </div>
                                <form method="POST" style="display: none;" class="edit-input" id="form-<?php echo $item; ?>">
                                    <input type="text" name="novo_nome" value="<?php echo $item; ?>" class="form-control-sm">
                                    <input type="hidden" name="item_atual" value="<?php echo $item; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Salvar</button>
                                </form>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <!-- Botão para voltar à pasta anterior -->
                <?php if ($diretorioAtual !== $diretorioRaiz): ?>
                    <a href="editor.php?dir=<?php echo urlencode(dirname($diretorioAtual)); ?>" class="btn btn-secondary">Voltar para pasta anterior</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para upload de arquivos -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Subir Arquivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="arquivo_upload" class="form-label">Selecione o arquivo:</label>
                            <input type="file" name="arquivo_upload" id="arquivo_upload" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para criar nova pasta -->
    <div class="modal fade" id="criarPastaModal" tabindex="-1" aria-labelledby="criarPastaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="criarPastaModalLabel">Criar Nova Pasta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nomePasta" class="form-label">Nome da Pasta:</label>
                            <input type="text" name="nome" id="nomePasta" class="form-control" required>
                            <input type="hidden" name="tipo" value="pasta">
                        </div>
                        <button type="submit" class="btn btn-primary">Criar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para criar novo arquivo -->
    <div class="modal fade" id="criarArquivoModal" tabindex="-1" aria-labelledby="criarArquivoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="criarArquivoModalLabel">Criar Novo Arquivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nomeArquivo" class="form-label">Nome do Arquivo:</label>
                            <input type="text" name="nome" id="nomeArquivo" class="form-control" required>
                            <input type="hidden" name="tipo" value="arquivo">
                        </div>
                        <button type="submit" class="btn btn-primary">Criar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar arquivo -->
    <div class="modal fade" id="editorModal" tabindex="-1" aria-labelledby="editorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editorModalLabel">Editando: <span id="nomeArquivoEditor"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="formEditor">
                        <textarea name="conteudo" rows="20" class="form-control mb-3" id="conteudoEditor"></textarea>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS e dependências -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
      // Função para filtrar itens no gerenciador de arquivos
        document.getElementById('pesquisaInput').addEventListener('input', function () {
            const termo = this.value.toLowerCase();
            const itens = document.querySelectorAll('.list-group-item');

            itens.forEach(item => {
                const nomeItem = item.querySelector('.item-title').textContent.toLowerCase();
                if (nomeItem.includes(termo)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        // Variável para armazenar o item atualmente aberto
        let itemAberto = null;

        // Função para mostrar os botões ao clicar na célula
        function mostrarBotoes(item) {
            // Fecha o item aberto anteriormente, se houver
            if (itemAberto && itemAberto !== item) {
                document.getElementById(`buttons-${itemAberto}`).style.display = 'none';
            }

            // Abre o item clicado
            const buttons = document.getElementById(`buttons-${item}`);
            buttons.style.display = buttons.style.display === 'none' ? 'block' : 'none';

            // Atualiza o item aberto
            itemAberto = buttons.style.display === 'block' ? item : null;
        }

        // Fecha os botões ao clicar em qualquer lugar da página, exceto nos botões
        document.addEventListener('click', function (event) {
            if (itemAberto && !event.target.closest(`#buttons-${itemAberto}`) && !event.target.closest('.list-group-item')) {
                document.getElementById(`buttons-${itemAberto}`).style.display = 'none';
                itemAberto = null;
            }
        });

        // Função para alternar a visibilidade do campo de edição
        function editarNome(item) {
            const form = document.getElementById(`form-${item}`);
            form.style.display = form.style.display === 'none' ? 'inline' : 'none';
        }

        // Script para carregar o conteúdo do arquivo no modal de edição
        document.addEventListener('DOMContentLoaded', function () {
            const editorModal = document.getElementById('editorModal');
            editorModal.addEventListener('show.bs.modal', function (event) {
                const link = event.relatedTarget; // Link que acionou o modal
                const nomeArquivo = link.getAttribute('data-bs-arquivo'); // Nome do arquivo
                const caminhoArquivo = link.getAttribute('data-bs-caminho'); // Caminho completo do arquivo

                // Atualiza o título do modal
                document.getElementById('nomeArquivoEditor').textContent = decodeURIComponent(nomeArquivo);

                // Carrega o conteúdo do arquivo via PHP
                fetch(`carregar_arquivo.php?caminho=${encodeURIComponent(caminhoArquivo)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro ao carregar o arquivo.');
                        }
                        return response.text();
                    })
                    .then(data => {
                        document.getElementById('conteudoEditor').value = data;
                    })
                    .catch(error => {
                        console.error('Erro ao carregar o arquivo:', error);
                        alert('Erro ao carregar o arquivo. Verifique o console para mais detalhes.');
                    });

                // Atualiza o action do formulário para incluir o nome do arquivo
                document.getElementById('formEditor').action = `editor.php?dir=<?php echo urlencode($diretorioAtual); ?>&arquivo=${nomeArquivo}`;
            });

            // Envio do formulário de edição via AJAX
            const formEditor = document.getElementById('formEditor');
            formEditor.addEventListener('submit', function (event) {
                event.preventDefault(); // Evita o envio tradicional do formulário

                const formData = new FormData(formEditor);

                fetch(formEditor.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert('Arquivo salvo com sucesso!');
                })
                .catch(error => {
                    console.error('Erro ao salvar o arquivo:', error);
                    alert('Erro ao salvar o arquivo. Verifique o console para mais detalhes.');
                });
            });
        });
    </script>
</body>
</html>