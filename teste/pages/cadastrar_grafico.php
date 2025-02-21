<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../padroes/arquivos_de_padrao.php';

if (!isLoggedIn() || $_SESSION['id_perfil'] != 1) {
    header('Location: login.php');
    exit();
}

$BaseDados = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'];
    $tipo = $_POST['tipo'];
    $dados = json_encode($_POST['dados']);
    $id_empresa = $_SESSION['id_empresa'];

    $sql = "INSERT INTO graficos (titulo, tipo, dados, id_empresa) VALUES (:titulo, :tipo, :dados, (SELECT id_empresa FROM usuarios WHERE id = " . strval($_SESSION['user_id']) . "))";
    $params = [
        ':titulo' => $titulo,
        ':tipo' => $tipo,
        ':dados' => $dados
    ];

    $BaseDados->execute($sql, $params);

    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Gráfico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Cadastrar Gráfico</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo de Gráfico</label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <option value="linha">Linha</option>
                    <option value="barra">Barra</option>
                    <option value="pizza">Pizza</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="dados" class="form-label">Dados (JSON)</label>
                <textarea class="form-control" id="dados" name="dados" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
    </div>
</body>
</html>