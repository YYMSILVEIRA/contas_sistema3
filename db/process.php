<?php
header('Content-Type: application/json'); // Retorna JSON

require 'db_client.php'; // Inclui a classe DatabaseClient

$action = $_POST['action'] ?? null;

if ($action === 'connect') {
    // Define as credenciais na classe DatabaseClient
    DatabaseClient::setCredentials(
        $_POST['db_type'],
        $_POST['host'],
        $_POST['dbname'],
        $_POST['username'],
        $_POST['password']
    );
    echo json_encode(['message' => 'Credenciais configuradas com sucesso!']);
    exit;
}

if ($action === 'query') {
    try {
        // Cria uma instância do DatabaseClient (usa as credenciais já configuradas)
        $dbClient = new DatabaseClient();
        $result = $dbClient->query($_POST['sql_query']);
        echo json_encode(['data' => $result]);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Erro: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['message' => 'Ação inválida.']);