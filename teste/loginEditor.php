<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';

// Processar o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = login($username, $password, $pdo);

    if ($result === true) {
        // Redireciona para o editor após o login
        header('Location: /contas_sistema/editor.php');
        exit();
    } elseif ($result === 'redefinir_senha') {
        // Redireciona para a página de redefinição de senha
        header('Location: redefinir_senha.php');
        exit();
    } else {
        $error = 'Usuário ou senha incorretos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h1 class="h4">Login</h1>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuário:</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha:</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>