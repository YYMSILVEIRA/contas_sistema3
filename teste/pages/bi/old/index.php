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
    <link rel="stylesheet" href="./styles.css">
    
</head>
<body>
    <div class="container-fluid">
        <div class="context-menu">
            <ul>
                <li class="context-menu-item" data-action="excluir">Excluir</li>
            </ul>
        </div>
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
                                <!--
                                <a class="btn btn-button control btn-primary" tabindex="0">
                                    <div class="btn-button-caption">Visualização</div>
                                </a>
                                <a class="btn btn-button control btn-primary" tabindex="0">
                                    <div class="btn-button-caption">Layout</div>
                                </a>
                                -->
                            </div>
                            <div class="bi-painel-lateral-conteudo">
                                <!-- Exemplo de itens arrastáveis -->
                                
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
                        <div class="bi-analise-conteudo scroll-vertical-compacto ui-droppable">
                            <table id="dashboard-table" class="table table-bordered">
                                <thead>
                                    <tr id="table-header">
                                        <!-- Cabeçalho dinâmico será preenchido aqui -->
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    <!-- Linhas dinâmicas serão preenchidas aqui -->
                                </tbody>
                            </table>
                        </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/circliful/1.0.0/js/jquery.circliful.min.js"></script>
    <script src="script.js"></script>
</body>
</html>