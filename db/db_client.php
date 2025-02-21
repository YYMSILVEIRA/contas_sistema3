<?php

class DatabaseClient {
    // Propriedades privadas para armazenar as credenciais
    private static $dbType;
    private static $host;
    private static $dbname;
    private static $username;
    private static $password;
    
    $dbType="mysql";
    $host="localhost";
    $dbname="contas_sistema";
    $username="root";
    $password="Minha senha";

    // Conexão PDO
    private $connection;

    // Método estático para definir as credenciais
    public static function setCredentials($dbType, $host, $dbname, $username, $password) {
        self::$dbType = $dbType;
        self::$host = $host;
        self::$dbname = $dbname;
        self::$username = $username;
        self::$password = $password;
    }

    // Construtor da classe
    public function __construct() {
        if (empty(self::$dbType) || empty(self::$host) || empty(self::$dbname) || empty(self::$username)) {
            throw new Exception("Credenciais do banco de dados não foram configuradas.");
        }

        try {
            if (self::$dbType === 'mysql') {
                $this->connection = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$dbname,
                    self::$username,
                    self::$password
                );
            } elseif (self::$dbType === 'pgsql') {
                $this->connection = new PDO(
                    "pgsql:host=" . self::$host . ";dbname=" . self::$dbname,
                    self::$username,
                    self::$password
                );
            } else {
                throw new Exception("Tipo de banco de dados não suportado.");
            }
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Erro na conexão: " . $e->getMessage());
        }
    }

    // Método para executar consultas
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro na consulta: " . $e->getMessage());
        }
    }

    // Método para executar comandos DML (INSERT, UPDATE, DELETE)
    public function executeDML($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Erro na execução do DML: " . $e->getMessage());
        }
    }

    // Método para executar comandos DDL (CREATE, ALTER, DROP)
    public function executeDDL($sql) {
        try {
            $this->connection->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Erro na execução do DDL: " . $e->getMessage());
        }
    }

    // Método para fechar a conexão
    public function close() {
        $this->connection = null;
    }
}