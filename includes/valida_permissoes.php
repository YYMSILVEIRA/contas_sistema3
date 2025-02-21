<?php
require_once '../includes/db.php'; // Certifique-se de incluir a conexão com o banco de dados
require_once '../includes/gerenciar_permissoes.php';

@session_start(); // Garante que a sessão esteja ativa

$BaseDados = new Database();
$gerenciadorPermissoes = new GerenciadorPermissoes($BaseDados);

$NM_PLATAFORMA = 'CONTAS_SISTEMA';
$NM_PAGINA = "DASHBOARD";

// Valida a permissão para a plataforma
if (!$gerenciadorPermissoes->plataforma($_SESSION['id_perfil'], $NM_PLATAFORMA)) {
    header('Location: login.php');
    exit();
}

// Valida a permissão para a página
if (!$gerenciadorPermissoes->pagina($_SESSION['id_perfil'], $NM_PLATAFORMA, $NM_PAGINA)) {
    header('Location: login.php');
    exit();
}

// Função para validar permissão de campo
function permissaoCampo($nome_campo, $html_campo, $sessao = null, $NM_PLATAFORMA = 'CONTAS_SISTEMA', $NM_PAGINA = 'DASHBOARD') {
    global $gerenciadorPermissoes;

    $sessao = $sessao ?? $_SESSION['id_perfil'];

    if ($gerenciadorPermissoes->campo($sessao, $NM_PLATAFORMA, $NM_PAGINA, $nome_campo)) {
        echo $html_campo;
    } else {
        echo "";
    }    
}
?>
