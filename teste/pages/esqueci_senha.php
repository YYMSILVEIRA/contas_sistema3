<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/logs.php';

// Exemplo de uso: registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI']; // Captura a URL acessada
registrarLog($pagina_acessada);

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = trim($_POST['token']);

    // Verifica se o token existe no banco de dados e se a flag redefinir_senha está como 'SIM'
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE token_recuperacao_senha = ? AND redefininir_senha = ?');
    $stmt->execute([$token, 'SIM']);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Redireciona para a página de redefinição de senha
        $_SESSION['usuario_temp'] = $usuario['id'];
        header('Location: redefinir_senha.php');
        exit();
    } else {
        $error = "Token inválido ou usuário não precisa redefinir a senha.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci a Senha</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .esqueci-senha-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .esqueci-senha-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .esqueci-senha-container .form-control {
            margin-bottom: 15px;
        }
        .esqueci-senha-container .btn {
            width: 100%;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="esqueci-senha-container">
            <h2>Esqueci a Senha</h2>
            <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="token" class="form-label">Token de Recuperação:</label>
                    <input type="text" id="token" name="token" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Validar Token</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>