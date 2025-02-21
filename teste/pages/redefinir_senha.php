<?php
//session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/logs.php';

// Verifica se o usuário está na sessão e se precisa redefinir a senha
if (!isset($_SESSION['usuario_temp'])) {
    header('Location: login.php'); // Redireciona se não houver necessidade de redefinir a senha
    exit();
}

$usuario_id = $_SESSION['usuario_temp'];

// Processar a redefinição de senha
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($nova_senha === $confirmar_senha) {
        // Criptografa a nova senha
        $nova_senha_hash = hash('sha256', $nova_senha);

        // Atualiza a senha no banco de dados
        $stmt = $pdo->prepare('UPDATE usuarios SET password = ?, redefininir_senha = ? WHERE id = ?');
        $stmt->execute([$nova_senha_hash, 'NÃO', $usuario_id]);

        // Remove o usuário temporário da sessão
        unset($_SESSION['usuario_temp']);

        // Redireciona para o dashboard
        header('Location: ../pages/dashboard.php');
        exit();
    } else {
        $error = "As senhas não coincidem.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../icons/icone.ico">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .redefinir-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .redefinir-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .redefinir-container .form-control {
            margin-bottom: 15px;
        }
        .redefinir-container .btn {
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
        <div class="redefinir-container">
            <h2>Redefinir Senha</h2>
            <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="nova_senha" class="form-label">Nova Senha:</label>
                    <input type="password" id="nova_senha" name="nova_senha" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirmar_senha" class="form-label">Confirmar Nova Senha:</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Redefinir Senha</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS (opcional, apenas se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>