<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novoEmail = trim($_POST['novoEmail']);
    $userId = $_SESSION['user_id'];

    // Validação básica do e-mail
    if (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'E-mail inválido.']);
        exit();
    }

    // Atualiza o e-mail no banco de dados
    $stmt = $pdo->prepare('UPDATE usuarios SET email = ? WHERE id = ?');
    $stmt->execute([$novoEmail, $userId]);

    echo json_encode(['status' => 'success']);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
?>