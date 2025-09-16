<?php

namespace App\Models;

use App\Core\Database;
use APP\Core\AuthMiddleware;
use PDO;

class Dinner
{
    private $conn;
    private $table = 'cene';

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    private function verificaEAggiornaStato($dinner) {
        if (!$dinner) {
            return null;
        }

        $now = new \DateTime();
        $dinnerDate = new \DateTime($dinner['dataOra']);

        if ($dinnerDate < $now && ($dinner['stato'] === 'APERTA' || $dinner['stato'] === 'COMPLETA')) {
            $this->aggiornaStato($dinner['id'], 'CONCLUSA');
            $dinner['stato'] = 'CONCLUSA';
        }
        return $dinner;
    }

    public function leggiTutteAperte($id_utente_corrente = null)
    {
        $query = "SELECT 
                    c.*, 
                    u.nome as nome_oste, 
                    u.cognome as cognome_oste,
                    rp.stato AS stato_richiesta_utente
                  FROM " . $this->table . " c 
                  JOIN utenti u ON c.id_oste = u.id
                  LEFT JOIN richieste_partecipazione rp ON c.id = rp.id_cena AND rp.id_commensale = :id_utente_corrente
                  WHERE c.stato = 'APERTA' AND c.dataOra > :current_time
                  ORDER BY c.dataOra ASC";

        $current_time = (new \DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_utente_corrente', $id_utente_corrente);
        $stmt->bindParam( ':current_time', $current_time);
        $stmt->execute();           

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trovaTramiteId($id, $id_utente_corrente = null)
    {
        $query = "SELECT 
                    c.*, 
                    u.nome as nome_oste, 
                    u.cognome as cognome_oste,
                    rp.stato AS stato_richiesta_utente
                  FROM " . $this->table . " c 
                  JOIN utenti u ON c.id_oste = u.id
                  LEFT JOIN richieste_partecipazione rp ON c.id = rp.id_cena AND rp.id_commensale = :id_utente_corrente
                  WHERE c.id = :id 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_utente_corrente', $id_utente_corrente);
        $stmt->execute();
        $dinner = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->verificaEAggiornaStato($dinner);
    }

    public function trovaTramiteOste($id_oste)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id_oste = :id_oste ORDER BY dataOra DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_oste', $id_oste);
        $stmt->execute();
        $dinners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map([$this, 'verificaEAggiornaStato'], $dinners);
    }

    public function crea($data)
    {
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

    public function aggiornaPosti($id, $variazione)
    {
        $query = "UPDATE " . $this->table . " SET numPostiDisponibili = numPostiDisponibili + :variazione WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':variazione', $variazione, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function aggiornaStato($id, $nuovoStato) {
        $query = "UPDATE " . $this->table . " SET stato = :nuovoStato WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':nuovoStato' => $nuovoStato,
            ':id' => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public function annulla($id)
    {
        $query = "UPDATE " . $this->table . " SET stato = 'ANNULLATA' 
                WHERE id = :id AND (stato = 'APERTA' OR stato = 'COMPLETA')";
                
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return $stmt->rowCount() > 0;
        }
        return false;
    }
}
