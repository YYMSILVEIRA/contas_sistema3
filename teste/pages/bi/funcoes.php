<?php

class Database {
    private $host = 'localhost';
    private $dbname = 'teste_contas_sistema';
    private $username = 'root';
    private $password = 'C@n&c@D&C@f&2024@__@';
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Método para executar uma consulta SQL e retornar os dados em JSON.
     *
     * @param string $sql A consulta SQL.
     * @param array $filtros Filtros para a consulta (opcional).
     * @return string Dados em formato JSON.
     */
    public function query($sql, $filtros = []) {
        try {
            $stmt = $this->conn->prepare($sql);

            // Aplica os filtros, se houver
            foreach ($filtros as $coluna => $valor) {
                if (is_array($valor)) {
                    // Se for um array, trata como uma cláusula IN
                    $placeholders = implode(', ', array_fill(0, count($valor), '?'));
                    $sql = str_replace(":$coluna", $placeholders, $sql);
                    foreach ($valor as $item) {
                        $stmt->bindValue($paramIndex++, $item);
                    }
                } else {
                    // Se for um valor único, aplica normalmente
                    $stmt->bindValue(":$coluna", $valor);
                }
            }

            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna os dados como um array associativo

            // Retorna os dados em formato JSON
            header('Content-Type: application/json; charset=utf-8');
            return json_encode($resultados, JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            // Em caso de erro na consulta
            header('Content-Type: application/json; charset=utf-8');
            return json_encode(['error' => 'Erro ao buscar dados: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}

// Exemplo de uso da classe
/*$db = new Database();

// Consulta SQL
$sqlTabIndicadores = "SELECT id, descricao FROM contas_pagar";

// Executa a consulta e retorna os dados em JSON
$ListaResultados = json_decode($db->query($sqlTabIndicadores), true);


// Verifica se a decodificação foi bem-sucedida
if (json_last_error() === JSON_ERROR_NONE) {
    echo "JSON decodificado com sucesso!\n";
} else {
    echo "Erro ao decodificar JSON: " . json_last_error_msg();
}
foreach ($ListaResultados as $item) {
    $id = $item['id'];           // Acessa o valor da coluna 'id'
    $descricao = $item['descricao']; // Acessa o valor da coluna 'descricao'
    echo "ID: $id, Descrição: $descricao\n"; // Exibe os valores
}*/
?>