<?php
session_start();

function login($username, $password, $pdo) {
    $username = trim($username);
    $password = trim($password);

    // Busca o usuário no banco de dados, incluindo o campo redefininir_senha
    $stmt = $pdo->prepare("SELECT * FROM usuario where codigo = '$id'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verifica se o usuário existe e se a senha está correta
    if ($user && hash('sha256', $password) === $user['password']) {
        // Armazena os dados do usuário na sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['id_perfil'] = (int)$user['id_perfil']; // Força o tipo inteiro
        $_SESSION['email'] = $user['email'];

        // Verifica se o usuário precisa redefinir a senha
        if ($user['redefininir_senha'] === 'SIM') {
            // Armazena os dados do usuário temporariamente na sessão
            $_SESSION['usuario_temp'] = $user;
            return 'redefinir_senha'; // Indica que o usuário precisa redefinir a senha
        }

        return true; // Login bem-sucedido
    }

    return false; // Login falhou
}

function isLoggedIn() {
    return isset($_SESSION['user_id']); // Verifica se o usuário está logado
}

function logout() {
    session_unset(); // Limpa todas as variáveis de sessão
    session_destroy(); // Destrói a sessão
}

// Processar logout
if (isset($_GET['logout'])) {
    logout();
    header('Location: ../pages/login.php'); // Redireciona para a página de login
    exit();
}
?>