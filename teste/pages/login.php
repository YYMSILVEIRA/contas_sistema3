<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/logs.php';

// Exemplo de uso: registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI']; // Captura a URL acessada
registrarLog($pagina_acessada);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verifica as credenciais do usuário
    $resultado_login = login($username, $password, $pdo);

    if ($resultado_login === true) {
        // Login bem-sucedido, redireciona para o dashboard
        header('Location: ../pages/dashboard.php');
        exit();
    } elseif ($resultado_login === 'redefinir_senha') {
        // Redireciona para a página de redefinição de senha
        header('Location: redefinir_senha.php');
        exit();
    } else {
        $error = "Usuário ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.8rem;
        }
        .login-container .form-control {
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 16px;
        }
        .login-container .form-control:focus {
            border-color: #007AFF;
            box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.2);
        }
        .login-container .btn {
            width: 100%;
            background-color: #007AFF;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            font-weight: 500;
            color: #fff;
            transition: background-color 0.3s ease;
        }
        .login-container .btn:hover {
            background-color: #005bb5;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .login-container .btn-link {
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .login-container{color: #007AFF;}
        .btn-link {color: #005bb5;}
        
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Usuário:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
        <div class="mt-3 text-center">
            <a href="esqueci_senha.php" class="btn btn-link">Esqueci a senha</a>
        </div>
    </div>

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>