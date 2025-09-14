<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Participation {
    private $conn;
    private $table = 'partecipazioni';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Crea un nuovo record di partecipazione a seguito di una richiesta accettata.
     */
    public function create($id_richiesta, $id_cena, $id_commensale) {
        $query = "INSERT INTO " . $this->table . " (id_richiesta, id_cena, id_commensale) VALUES (:id_richiesta, :id_cena, :id_commensale)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_richiesta', $id_richiesta);
        $stmt->bindParam(':id_cena', $id_cena);
        $stmt->bindParam(':id_commensale', $id_commensale);
        return $stmt->execute();
    }

    /**
     * Trova una partecipazione specifica tramite il suo ID.
     */
    public function findById($id_partecipazione) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id_partecipazione LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_partecipazione', $id_partecipazione);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Trova una partecipazione confermata di un utente per una specifica cena.
     * Utile per verificare se un utente può lasciare una recensione.
     */
    public function findByUserAndDinner($id_commensale, $id_cena) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_commensale = :id_commensale AND id_cena = :id_cena AND statoPartecipante = 'CONFERMATO' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_commensale', $id_commensale);
        $stmt->bindParam(':id_cena', $id_cena);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Annulla una partecipazione impostando il suo stato su ANNULLATO_DA_UTENTE.
     */
    public function cancel($id_partecipazione) {
        $query = "UPDATE " . $this->table . " SET statoPartecipante = 'ANNULLATO_DA_UTENTE' WHERE id = :id_partecipazione";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_partecipazione', $id_partecipazione);
        return $stmt->execute();
    }
}