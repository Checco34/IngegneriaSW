<?php
namespace App\Core;
use PDO;
use PDOException;
class Database {
    private static $instance = null;
    private $conn;
    private function __construct() {
        $host = getenv('DB_HOST');
        $db_name = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');
        try {
            $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            exit();
        }
    }
    public static function getInstance() {
        if (!self::$instance) { self::$instance = new Database(); }
        return self::$instance;
    }
    public function getConnection() { return $this->conn; }
}