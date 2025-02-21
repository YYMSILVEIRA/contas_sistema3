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
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h1 class="h4">Navegando em: <?php echo htmlspecialchars($diretorioAtual); ?></h1>
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
                            <li class="list-group-item">
                                <strong>Pasta:</strong>
                                <a href="editor.php?dir=<?php echo urlencode($caminhoItem); ?>" class="text-decoration-none"><?php echo $item; ?></a>
                                <a href="editor.php?dir=<?php echo urlencode($diretorioAtual); ?>&excluir=<?php echo urlencode($item); ?>" class="btn btn-danger btn-sm float-end" onclick="return confirm('Tem certeza que deseja excluir esta pasta?')">Excluir</a>
                            </li>
                        <?php else: ?>
                            <li class="list-group-item">
                                <strong>Arquivo:</strong>
                                <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#editorModal" data-bs-arquivo="<?php echo urlencode($item); ?>" data-bs-caminho="<?php echo urlencode($caminhoItem); ?>"><?php echo $item; ?></a>
                                <a href="<?php echo urlencode($caminhoItem); ?>" download class="btn btn-success btn-sm float-end">Baixar</a>
                                <a href="editor.php?dir=<?php echo urlencode($diretorioAtual); ?>&excluir=<?php echo urlencode($item); ?>" class="btn btn-danger btn-sm float-end me-2" onclick="return confirm('Tem certeza que deseja excluir este arquivo?')">Excluir</a>
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