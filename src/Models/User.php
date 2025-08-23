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
        // La query SQL rimane la stessa
        $query = "INSERT INTO " . $this->table . " (nome, cognome, email, password, ruolo) VALUES (:nome, :cognome, :email, :password, :ruolo)";
        
        try {
            $stmt = $this->conn->prepare($query);

            // Sanificazione e Hashing
            $nome = htmlspecialchars(strip_tags($data->nome));
            $cognome = htmlspecialchars(strip_tags($data->cognome));
            $email = htmlspecialchars(strip_tags($data->email));
            $ruolo = htmlspecialchars(strip_tags($data->ruolo));
            $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
            
            // Binding dei parametri
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':cognome', $cognome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':ruolo', $ruolo);
            
            // Esegui la query
            return $stmt->execute();

        } catch (\PDOException $e) {
            // SE C'È UN ERRORE SQL, LO CATTURIAMO QUI
            // In un ambiente di produzione, scriveresti questo errore in un file di log.
            // Per il debug, lo mostriamo direttamente nella risposta API.
            http_response_code(500); // Errore del server
            
            echo json_encode([
                'message' => 'Errore del database durante la creazione dell\'utente.',
                'error' => $e->getMessage() // <-- QUESTA È LA RIGA PIÙ IMPORTANTE
            ]);

            // Interrompiamo lo script per essere sicuri di vedere solo questo errore.
            exit();
        }
    }
}