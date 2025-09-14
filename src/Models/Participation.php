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

    public function creaDaRichiesta($request) {
        $query = "INSERT INTO " . $this->table . " (id_richiesta, id_cena, id_commensale) VALUES (:id_richiesta, :id_cena, :id_commensale)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_richiesta', $request['id']);
        $stmt->bindParam(':id_cena', $request['id_cena']);
        $stmt->bindParam(':id_commensale', $request['id_commensale']);
        return $stmt->execute();
    }

    public function trovaTramiteId($id_partecipazione) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id_partecipazione LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_partecipazione', $id_partecipazione);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function trovaTramiteUtenteECena($id_commensale, $id_cena) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_commensale = :id_commensale AND id_cena = :id_cena AND statoPartecipante = 'CONFERMATO' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_commensale', $id_commensale);
        $stmt->bindParam(':id_cena', $id_cena);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function annulla($id_partecipazione) {
        $query = "UPDATE " . $this->table . " SET statoPartecipante = 'ANNULLATO_DA_UTENTE' WHERE id = :id_partecipazione AND statoPartecipante = 'CONFERMATO'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_partecipazione', $id_partecipazione);
        return $stmt->execute();
    }
}