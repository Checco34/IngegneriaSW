<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Review {
    private $conn;
    private $table = 'recensioni';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function crea($data) {
        $query = "INSERT INTO " . $this->table . " (id_cena, id_valutatore, id_valutato, voto, commento) 
                  VALUES (:id_cena, :id_valutatore, :id_valutato, :voto, :commento)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id_cena', $data->id_cena);
        $stmt->bindParam(':id_valutatore', $data->id_valutatore);
        $stmt->bindParam(':id_valutato', $data->id_valutato);
        $stmt->bindParam(':voto', $data->voto);
        $stmt->bindParam(':commento', $data->commento);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function trovaTramiteUtente($id_valutato) {
        $query = "SELECT r.*, u.nome as nome_valutatore, u.cognome as cognome_valutatore 
                  FROM " . $this->table . " r
                  JOIN utenti u ON r.id_valutatore = u.id
                  WHERE r.id_valutato = :id_valutato
                  ORDER BY r.data DESC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_valutato', $id_valutato);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}