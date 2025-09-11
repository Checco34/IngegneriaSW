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

    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE stato = 'APERTA' ORDER BY dataOra ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (titolo, descrizione, dataOra, localita, numPostiDisponibili, menu, id_oste) 
                  VALUES (:titolo, :descrizione, :dataOra, :localita, :numPostiDisponibili, :menu, :id_oste)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titolo', $data->titolo);
        $stmt->bindParam(':descrizione', $data->descrizione);
        $stmt->bindParam(':dataOra', $data->dataOra);
        $stmt->bindParam(':localita', $data->localita);
        $stmt->bindParam(':numPostiDisponibili', $data->numPostiDisponibili);
        $stmt->bindParam(':menu', $data->menu);
        $stmt->bindParam(':id_oste', $data->id_oste);
        return $stmt->execute();
    }

    public function readSingle($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSpots($id, $change) {
        $query = "UPDATE " . $this->table . " SET numPostiDisponibili = numPostiDisponibili + (:change) WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':change', $change);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}