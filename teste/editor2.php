<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor de Texto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .editor-container {
            display: flex;
            width: 100%;
            font-family: monospace;
        }
        .line-numbers {
            width: 40px;
            padding-right: 10px;
            text-align: right;
            border-right: 1px solid #ccc;
            user-select: none;
            background-color: #f8f9fa;
            color: #6c757d;
            overflow-y: hidden; /* Evita que os números das linhas rolem */
        }
        .code-editor {
            flex-grow: 1;
            padding-left: 10px;
        }
        textarea {
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            resize: none;
            font-family: monospace;
            background-color: transparent;
        }
    </style>
</head>
<body class="bg-light">
    <!-- ... (restante do código HTML) ... -->

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
                        <div class="editor-container">
                            <div class="line-numbers" id="lineNumbers">
                                <!-- Números das linhas serão inseridos aqui -->
                            </div>
                            <div class="code-editor">
                                <textarea name="conteudo" rows="20" class="form-control mb-3" id="conteudoEditor"></textarea>
                            </div>
                        </div>
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
                        const editor = document.getElementById('conteudoEditor');
                        editor.value = data;

                        // Atualiza os números das linhas
                        const lineNumbers = document.getElementById('lineNumbers');
                        const lines = data.split('\n').length;
                        lineNumbers.innerHTML = '';
                        for (let i = 1; i <= lines; i++) {
                            lineNumbers.innerHTML += `<div>${i}</div>`;
                        }
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

            // Atualiza os números das linhas ao digitar no editor
            const conteudoEditor = document.getElementById('conteudoEditor');
            conteudoEditor.addEventListener('input', function () {
                const lineNumbers = document.getElementById('lineNumbers');
                const lines = conteudoEditor.value.split('\n').length;
                lineNumbers.innerHTML = '';
                for (let i = 1; i <= lines; i++) {
                    lineNumbers.innerHTML += `<div>${i}</div>`;
                }
            });
        });
    </script>
</body>
</html>