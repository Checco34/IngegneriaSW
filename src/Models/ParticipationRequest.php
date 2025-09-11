<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class ParticipationRequest {
    private $conn;
    private $table = 'richieste_partecipazione';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Metodo per creare una nuova richiesta di partecipazione
    public function create($id_cena, $id_commensale) {
        $query = "INSERT INTO " . $this->table . " (id_cena, id_commensale, stato) VALUES (:id_cena, :id_commensale, 'IN ATTESA')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cena', $id_cena);
        $stmt->bindParam(':id_commensale', $id_commensale);
        return $stmt->execute();
    }

    // Metodo per ottenere tutte le richieste per una specifica cena
    public function getRequestsByDinner($id_cena) {
        $query = "SELECT r.*, u.nome, u.cognome FROM " . $this->table . " r 
                  JOIN utenti u ON r.id_commensale = u.id 
                  WHERE r.id_cena = :id_cena";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cena', $id_cena);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Metodo per aggiornare lo stato di una richiesta
    public function updateStatus($id_richiesta, $stato) {
        $query = "UPDATE " . $this->table . " SET stato = :stato WHERE id = :id_richiesta";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':stato', $stato);
        $stmt->bindParam(':id_richiesta', $id_richiesta);
        return $stmt->execute();
    }
}