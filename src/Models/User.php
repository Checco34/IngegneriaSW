<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $conn;
    private $table = 'utenti';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function trovaTramiteEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crea($data) {
        $query = "INSERT INTO " . $this->table . " (nome, cognome, email, password) VALUES (:nome, :cognome, :email, :password)";
        $stmt = $this->conn->prepare($query);

        $password_hash = password_hash($data->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':nome', $data->nome);
        $stmt->bindParam(':cognome', $data->cognome);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':password', $password_hash);

        return $stmt->execute();
    }
}