<?php
// Configurações do banco de dados
$host = 'localhost'; // Endereço do servidor MySQL
$dbname = 'teste_contas_sistema'; // Nome do banco de dados
$username = 'root'; // Nome de usuário do banco de dados
$password = 'C@n&c@D&C@f&2024@__@'; // Senha do banco de dados

// Conexão com o banco de dados
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Consulta SQL para buscar os nomes dos indicadores
$sql = "SELECT id, descricao FROM contas_pagar";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode($resultados);
} catch (PDOException $e) {
    // Em caso de erro na consulta
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>