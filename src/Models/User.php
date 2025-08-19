<?php
namespace Models;

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Crea un nuovo utente nel database.
     * Usa prepared statements per la sicurezza. [cite: 108]
     */
    public function create($nome, $email, $password_hash, $tipo) {
        $stmt = $this->pdo->prepare(
            'INSERT INTO utenti (nome, email, password, tipo) VALUES (:nome, :email, :password, :tipo)'
        );
        return $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':password' => $password_hash,
            ':tipo' => $tipo // 'oste' o 'commensale'
        ]);
    }

    /**
     * Trova un utente tramite il suo ID. [cite: 103]
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM utenti WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Trova un utente tramite la sua email.
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare('SELECT * FROM utenti WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
}
