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

    public function crea($id_cena, $id_commensale) {
        $query = "INSERT INTO " . $this->table . " (id_cena, id_commensale) VALUES (:id_cena, :id_commensale)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cena', $id_cena);
        $stmt->bindParam(':id_commensale', $id_commensale);
        try {
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    public function trovaTramiteId($id_richiesta) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id_richiesta LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_richiesta', $id_richiesta);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function trovaTramiteCena($id_cena) {
        $query = "SELECT r.id, r.id_cena, r.id_commensale, r.dataRichiesta, r.stato, u.nome, u.cognome FROM " . $this->table . " r 
                  JOIN utenti u ON r.id_commensale = u.id 
                  WHERE r.id_cena = :id_cena ORDER BY r.dataRichiesta DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cena', $id_cena);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function aggiornaStato($id_richiesta, $stato) {
        $query = "UPDATE " . $this->table . " SET stato = :stato WHERE id = :id_richiesta AND stato = 'IN ATTESA'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':stato', $stato);
        $stmt->bindParam(':id_richiesta', $id_richiesta);
        return $stmt->execute();
    }
}