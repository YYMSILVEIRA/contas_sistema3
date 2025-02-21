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

// Consulta SQL para buscar tabelas e seus indicadores
$sql = "
    SELECT t.id AS tabela_id, t.nome_tabela, t.descricao_tabela, i.id AS indicador_id, i.nome_indicador
    FROM tabelas_indicadores t
    LEFT JOIN indicadores i ON t.id = i.id_tabela
    ORDER BY t.id, i.id;
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organiza os dados em uma estrutura hierárquica
    $tabelas = [];
    foreach ($resultados as $row) {
        $tabela_id = $row['tabela_id'];
        if (!isset($tabelas[$tabela_id])) {
            $tabelas[$tabela_id] = [
                'nome_tabela' => $row['nome_tabela'],
                'descricao_tabela' => $row['descricao_tabela'],
                'indicadores' => []
            ];
        }
        if ($row['indicador_id']) {
            $tabelas[$tabela_id]['indicadores'][] = [
                'id' => $row['indicador_id'],
                'nome_indicador' => $row['nome_indicador']
            ];
        }
    }

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode(array_values($tabelas)); // Converte para array indexado
} catch (PDOException $e) {
    // Em caso de erro na consulta
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>