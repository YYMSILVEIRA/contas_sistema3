<?php
$host = 'localhost';
$db = 'contas_sistema';
$user = 'root';
$pass = 'Minha senha';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

class Database {
    private $pdo;

    // Construtor: conecta ao banco de dados
    public function __construct($host = null, $dbname = null, $user = null, $pass = null) {
        // Se nenhum valor for passado, usa as credenciais padrão do db.php
        $host = $host ?? 'localhost';
        $dbname = $dbname ?? 'contas_sistema';
        $user = $user ?? 'root';
        $pass = $pass ?? 'C@n&c@D&C@f&2024@__@';

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new Exception("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    // Método para executar consultas SELECT
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erro ao executar a consulta: " . $e->getMessage());
        }
    }

    // Método para executar consultas que retornam uma única linha
    public function querySingle($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erro ao executar a consulta: " . $e->getMessage());
        }
    }

    // Método para executar INSERT, UPDATE ou DELETE
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount(); // Retorna o número de linhas afetadas
        } catch (PDOException $e) {
            throw new Exception("Erro ao executar a operação: " . $e->getMessage());
        }
    }

    // Método para obter o último ID inserido
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}

?>