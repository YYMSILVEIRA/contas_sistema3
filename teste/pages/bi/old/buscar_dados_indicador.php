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

// Obtém o ID do indicador da query string
$id = $_GET['id'];
echo "<script>console.log(' O id é: ".$id."');</script>";

// Consulta SQL para buscar os dados do indicador
$sql = "SELECT descricao, valor, data, fornecedor FROM contas_pagar WHERE id = :2"; // Ajuste a consulta conforme necessário
//$sql = "SELECT descricao, valor, data, fornecedor FROM contas_pagar WHERE id = :id"; // Ajuste a consulta conforme necessário

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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