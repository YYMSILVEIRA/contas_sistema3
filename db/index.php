<?php
session_start(); // Inicia a sessão
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente de Banco de Dados</title>
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Cliente de Banco de Dados</h1>

        <!-- Formulário de Conexão -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Conectar ao Banco de Dados
            </div>
            <div class="card-body">
                <form action="process.php" method="POST">
                    <div class="mb-3">
                        <label for="db_type" class="form-label">Tipo de Banco de Dados</label>
                        <select class="form-select" id="db_type" name="db_type" required>
                            <option value="mysql">MySQL</option>
                            <option value="pgsql">PostgreSQL</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="host" class="form-label">Host</label>
                        <input type="text" class="form-control" id="host" name="host" placeholder="Ex: localhost" required>
                    </div>
                    <div class="mb-3">
                        <label for="dbname" class="form-label">Nome do Banco</label>
                        <input type="text" class="form-control" id="dbname" name="dbname" placeholder="Ex: meu_banco" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Ex: root" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Senha">
                    </div>
                    <button type="submit" class="btn btn-primary">Conectar</button>
                </form>
            </div>
        </div>

        <!-- Área para Executar Consultas -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                Executar Consulta SQL
            </div>
            <div class="card-body">
                <form action="process.php" method="POST">
                    <div class="mb-3">
                        <label for="sql_query" class="form-label">Consulta SQL</label>
                        <textarea class="form-control" id="sql_query" name="sql_query" rows="3" placeholder="Ex: SELECT * FROM usuarios" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Executar</button>
                </form>
            </div>
        </div>

        <!-- Exibição dos Resultados -->
        <?php if (isset($_SESSION['database']['result'])): ?>
            <div class="card">
                <div class="card-header bg-info text-white">
                    Resultados
                </div>
                <div class="card-body">
                    <?php if (is_array($_SESSION['database']['result'])): ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($_SESSION['database']['result'][0]) as $column): ?>
                                        <th><?php echo htmlspecialchars($column); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['database']['result'] as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?php echo htmlspecialchars($value); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <pre><?php echo htmlspecialchars($_SESSION['database']['result']); ?></pre>
                    <?php endif; ?>
                </div>
            </div>
            <?php unset($_SESSION['database']['result']); ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>