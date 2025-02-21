<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de BI</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Tooltipster CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tooltipster/4.2.8/css/tooltipster.bundle.min.css">
    <link rel="stylesheet" href="/styles.css">
    <style>
        .draggable-item {
            padding: 10px;
            margin: 5px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            cursor: grab;
        }
        .draggable-item:active {
            cursor: grabbing;
        }
        .sidebar, .main-content {
            padding: 15px;
        }
        .sidebar {
            background-color: #f1f1f1;
        }
        .modal-window {
            position: fixed;
            z-index: 1000;
            background: var(--color-bg);
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .modal-window-phone {
            width: 350px;
            height: calc(100% - 50px);
            top: 45px;
            right: 20px;
        }
        .bi-painel-lateral-indicador {
            cursor: grab;
        }
        .bi-painel-lateral-indicador:active {
            cursor: grabbing;
        }
        .bi-analise {
            border: 1px solid #ccc;
            border-radius: 3px;
            margin: 10px;
            padding: 10px;
            background-color: #fff;
        }
        .bi-analise-conteudo {
            height: 553px;
            overflow-y: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .bi-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .bi-grid-header, .bi-grid-body {
            display: flex;
            gap: 10px;
        }
        .bi-grid-header-cell, .bi-grid-body-cell {
            flex: 1;
            min-width: 100px;
            padding: 5px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .bi-grid-header-cell {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .context-menu {
            display: none;
            position: absolute;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .context-menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .context-menu li {
            padding: 8px 16px;
            cursor: pointer;
        }

        .context-menu li:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Painel Lateral (Itens Arrastáveis) -->
            <div class="modal-window modal-window-phone" id="painel-lateral">
                <div class="modal-window-content-box">
                    <div class="modal-window-top-bar">
                        <span class="modal-window-title">Painel Lateral</span>
                        <div class="modal-window-buttons">
                            <a class="btn modal-top-bar-btn-max modal-top-bar-btn fas fa-ellipsis-v title" title="Opções"></a>
                            <a class="btn modal-top-bar-btn-max modal-top-bar-btn far fa-square title" title="Maximizar"></a>
                            <a class="btn modal-top-bar-btn-close modal-top-bar-btn fas fa-times title" title="Fechar"></a>
                        </div>
                    </div>
                    <div class="modal-window-holder-content scroll-vertical">
                        <div class="bi-painel-lateral">
                            <div class="bi-painel-lateral-header">
                                <a class="btn btn-button control btn-primary" tabindex="0">
                                    <div class="btn-button-caption">Indicadores</div>
                                </a>
                                <!--<a class="btn btn-button control btn-primary" tabindex="0">
                                    <div class="btn-button-caption">Visualização</div>
                                </a>
                                <a class="btn btn-button control btn-primary" tabindex="0">
                                    <div class="btn-button-caption">Layout</div>
                                </a>-->
                            </div>
                            <div class="bi-painel-lateral-conteudo">
                                <!-- Exemplo de itens arrastáveis -->
                                <!--<div class="bi-painel-lateral-indicador bi-indicador-drop-filtros draggable holder-btn-flutuante">
                                    <i class="bi-painel-lateral-indicador-icone fas fa-calculator"></i>
                                    <span class="bi-painel-lateral-indicador-descricao" style="border: 1px solid #d2d2d2;border-radius: 3px;cursor: pointer;display: block;margin: 2px;padding: 2px 6px;width: 100%;">Base Bonificacao</span>
                                    <i class="btn-flutuante indicador-btn-opcoes fas fa-bars"></i>
                                </div>
                                <div class="bi-painel-lateral-indicador bi-indicador-drop-filtros draggable holder-btn-flutuante">
                                    <i class="bi-painel-lateral-indicador-icone fas fa-calculator"></i>
                                    <span class="bi-painel-lateral-indicador-descricao" style="border: 1px solid #d2d2d2;border-radius: 3px;cursor: pointer;display: block;margin: 2px;padding: 2px 6px;width: 100%;">Custo Franquia</span>
                                    <i class="btn-flutuante indicador-btn-opcoes fas fa-bars"></i>
                                </div>-->
                                <!-- Adicione mais itens aqui -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Área de Análise (Onde os indicadores serão arrastados) -->
            <div class="col-md-9 main-content">
                <h1>Dashboard de BI</h1>
                <div class="bi-analise holder-btn-flutuante bi-analise-dockpanel">
                    <div class="tarja-busy hide-busy-tarja"></div>
                    <div class="bi-analise-header dashpanel-drag-handle" style="display: none;"></div>
                    <div class="bi-analise-filtros ui-droppable" style="display: block; height: inherit;">
                        <a class="btn btn-button control btn-light btn btn-bi-analise-sem-filtros" tabindex="0">
                            <div class="btn-button-caption">Adicione Indicador como Filtro</div>
                        </a>
                    </div>
                    <div class="bi-analise-conteudo scroll-vertical-compacto ui-droppable">
                        <!-- Grid de análise será preenchido dinamicamente -->
                    </div>
                    <button class="btn btn-flutuante bi-analise-btn-opcoes fas fa-bars"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/amplify.js/1.1.2/amplify.store.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tooltipster/4.2.8/js/tooltipster.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.simplePagination.js/1.6/jquery.simplePagination.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/circliful/1.0.0/js/jquery.circliful.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/image-compressor.js/1.0.0/image-compressor.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lozad.js/1.16.0/lozad.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.4/raphael-min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    <script src="script.js"></script>
</body>
</html>