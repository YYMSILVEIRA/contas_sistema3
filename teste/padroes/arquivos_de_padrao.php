<?php
require_once '../includes/auth.php';
function Ajuda($ID,$Conteudo){
    return '<small id="'.$ID.'" class="form-text text-muted" style="display: none;">'.$Conteudo.'</small>';
}
function AjudaConf($CampoID,$AjudaID){
    return "document.getElementById('".$CampoID."').addEventListener('focus', function() {
            document.getElementById('".$AjudaID."').style.display = 'block';
        });
        document.getElementById('".$CampoID."').addEventListener('blur', function() {
            document.getElementById('".$AjudaID."').style.display = 'none';
        });";
}
function Cabecalho($NMTela, $TxtBtn1, $TxtBtn2, $TxtBtn3, $lnkBtn1, $lnkBtn2, $lnkBtn3, $EditarEmail = 0) {
    $emailUsuario = $_SESSION['email'];
    $TxtOptionAltEmail = "";

    if ($EditarEmail == 1) {
        $TxtOptionAltEmail = "<li class='nav-item'>
                                <button class='nav-link btn btn-primary me-2' data-bs-toggle='modal' data-bs-target='#editarEmailModal' data-email='".$emailUsuario."'>
                                    <i class='fas fa-edit'></i> Alterar E-mail
                                </button>
                              </li>";
    } else {
        $TxtOptionAltEmail = "";
    }

    return "
        <!-- Barra de Navegação -->
        <nav class='navbar navbar-expand-lg navbar-dark bg-dark mb-4 rounded'>
            <div class='container-fluid'>
                <a class='navbar-brand' href='#' style='font-size: 1.8rem;'>".$NMTela."</a>
                <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>
                    <span class='navbar-toggler-icon'></span>
                </button>
                <div class='collapse navbar-collapse' id='navbarNav'>
                    <ul class='navbar-nav ms-auto'>
                        <li class='nav-item'>
                            <a class='nav-link btn btn-primary me-2' href='".$lnkBtn1."'>".$TxtBtn1."</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link btn btn-primary me-2' href='".$lnkBtn2."'>".$TxtBtn2."</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link btn btn-primary me-2' href='".$lnkBtn3."'>".$TxtBtn3."</a>
                        </li>
                        ".$TxtOptionAltEmail."
                        <li class='nav-item'>
                            <a class='nav-link btn btn-primary me-2' href='../includes/auth.php?logout=true'>Sair</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Modal para editar e-mail -->
        <div class='modal fade' id='editarEmailModal' tabindex='-1' aria-labelledby='editarEmailModalLabel' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='editarEmailModalLabel'>Editar E-mail</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <form id='formEditarEmail'>
                            <div class='mb-3'>
                                <label for='novoEmail' class='form-label'>Novo E-mail:</label>
                                <input type='email' class='form-control' id='novoEmail' name='novoEmail' required>
                            </div>
                            <button type='submit' class='btn btn-primary'>Salvar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script para preencher o modal com o e-mail atual e enviar o formulário via AJAX -->
        <script>
            $(document).ready(function() {
                // Preenche o campo de e-mail no modal com o valor atual
                $('#editarEmailModal').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget); // Botão que acionou o modal
                    var emailAtual = button.data('email'); // Extrai o e-mail do atributo data-email
                    $('#novoEmail').val(emailAtual); // Preenche o campo no modal
                });

                // Envia o formulário via AJAX
                $('#formEditarEmail').on('submit', function(e) {
                    e.preventDefault(); // Impede o envio padrão do formulário

                    var novoEmail = $('#novoEmail').val();

                    $.ajax({
                        url: '../includes/atualizar_email.php', // Arquivo PHP para processar a atualização
                        method: 'POST',
                        data: { novoEmail: novoEmail },
                        success: function(response) {
                            alert('E-mail atualizado com sucesso!');
                            $('#editarEmailModal').modal('hide'); // Fecha o modal
                            location.reload(); // Recarrega a página para refletir a mudança
                        },
                        error: function() {
                            alert('Erro ao atualizar o e-mail.');
                        }
                    });
                });
            });
        </script>
    ";
}

$Rodape = "
        <!-- Rodapé -->
        <footer class='bg-dark text-white text-center py-3 mt-4 rounded'>
            <p class='mb-0'>IB2S Sistema de Contas a Pagar e Receber &copy; " . date('Y') . "</p>
        </footer>
    ";
?>