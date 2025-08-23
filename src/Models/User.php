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
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (nome, cognome, email, password, ruolo) VALUES (:nome, :cognome, :email, :password, :ruolo)";
        $stmt = $this->conn->prepare($query);
        $nome = htmlspecialchars(strip_tags($data->nome));
        $cognome = htmlspecialchars(strip_tags($data->cognome));
        $email = htmlspecialchars(strip_tags($data->email));
        $ruolo = htmlspecialchars(strip_tags($data->ruolo));
        $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cognome', $cognome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':ruolo', $ruolo);
        return $stmt->execute();
    }
}