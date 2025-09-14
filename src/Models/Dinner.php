<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Dinner {
    private $conn;
    private $table = 'cene';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function leggiTutteAperte() {
        $query = "SELECT c.*, u.nome as nome_oste, u.cognome as cognome_oste FROM " . $this->table . " c JOIN utenti u ON c.id_oste = u.id WHERE c.stato = 'APERTA' ORDER BY c.dataOra ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trovaTramiteId($id) {
        $query = "SELECT c.*, u.nome as nome_oste, u.cognome as cognome_oste FROM " . $this->table . " c JOIN utenti u ON c.id_oste = u.id WHERE c.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function trovaTramiteOste($id_oste) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_oste = :id_oste ORDER BY dataOra DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_oste', $id_oste);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crea($data) {
        $query = "INSERT INTO " . $this->table . " (titolo, descrizione, dataOra, localita, numPostiDisponibili, menu, id_oste) 
                  VALUES (:titolo, :descrizione, :dataOra, :localita, :numPostiDisponibili, :menu, :id_oste)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titolo', $data->titolo);
        $stmt->bindParam(':descrizione', $data->descrizione);
        $stmt->bindParam(':dataOra', $data->dataOra);
        $stmt->bindParam(':localita', $data->localita);
        $stmt->bindParam(':numPostiDisponibili', $data->numPostiDisponibili, PDO::PARAM_INT);
        $stmt->bindParam(':menu', $data->menu);
        $stmt->bindParam(':id_oste', $data->id_oste, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function aggiornaPosti($id, $variazione) {
        $query = "UPDATE " . $this->table . " SET numPostiDisponibili = numPostiDisponibili + :variazione WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':variazione', $variazione, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}