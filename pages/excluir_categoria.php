<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/logs.php';

// Verifica se o usuário está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Verifica se o ID da categoria foi passado via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: categorias.php'); // Redireciona se o ID não for fornecido
    exit();
}

// Obtém o ID da categoria a ser excluída
$id_categoria = intval($_GET['id']);

// Verifica se o ID é válido
if ($id_categoria <= 0) {
    header('Location: categorias.php'); // Redireciona se o ID for inválido
    exit();
}

// Prepara a consulta para excluir a categoria
try {
    $sql = "DELETE FROM categorias WHERE id = :id AND id_empresa = (SELECT id_empresa FROM usuarios WHERE id = :user_id)";
    $stmt = $pdo->prepare($sql);

    // Vincula os parâmetros
    $stmt->bindValue(':id', $id_categoria, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

    // Executa a exclusão
    $stmt->execute();

    // Verifica se a categoria foi excluída
    if ($stmt->rowCount() > 0) {
        // Registra o log da exclusão
        registrarLog("Categoria excluída: ID $id_categoria");

        // Redireciona com mensagem de sucesso
        header('Location: categorias.php?sucesso=1');
    } else {
        // Redireciona com mensagem de erro (categoria não encontrada ou não pertence à empresa do usuário)
        header('Location: categorias.php?erro=1');
    }
} catch (PDOException $e) {
    // Registra o log do erro
    registrarLog("Erro ao excluir categoria: " . $e->getMessage());

    // Redireciona com mensagem de erro
    header('Location: categorias.php?erro=2');
}

exit();
?>