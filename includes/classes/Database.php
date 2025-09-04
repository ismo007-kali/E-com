<?php
class Database {
    private static $instance = null;
    private $pdo;
    private $stmt;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql, $params = []) {
        $this->stmt = $this->pdo->prepare($sql);
        return $this->stmt->execute($params);
    }

    public function fetchAll() {
        return $this->stmt->fetchAll();
    }

    public function fetch() {
        return $this->stmt->fetch();
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }
}
?>
