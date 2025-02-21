<?php
require_once '../includes/db.php'; // Inclui a conexão com o banco de dados
require_once '../includes/auth.php';
require_once '../includes/logs.php';
// Exemplo de uso: registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI']; // Captura a URL acessada
registrarLog($pagina_acessada);

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepara e executa a query de exclusão
    $sql = "DELETE FROM contas_pagar WHERE id = ? and id_empresa=(select id_empresa from usuarios where id=".strval($_SESSION['user_id']).")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // Redireciona de volta para a página principal
    header('Location: contas_pagar.php');
    exit();
} else {
    echo "ID não fornecido.";
}
?>