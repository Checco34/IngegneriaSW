<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Participation
{
    private $conn;
    private $table = 'partecipazioni';

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function creaDaRichiesta($request)
    {
        $query = "INSERT INTO " . $this->table . " (id_richiesta, id_cena, id_commensale) VALUES (:id_richiesta, :id_cena, :id_commensale)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_richiesta', $request['id']);
        $stmt->bindParam(':id_cena', $request['id_cena']);
        $stmt->bindParam(':id_commensale', $request['id_commensale']);
        return $stmt->execute();
    }

    public function trovaTramiteId($id_partecipazione)
    {
       $query = "SELECT p.*, c.stato AS stato_cena 
              FROM " . $this->table . " p 
              JOIN cene c ON p.id_cena = c.id 
              WHERE p.id = :id_partecipazione LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_partecipazione', $id_partecipazione);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function trovaTramiteUtenteECena($id_commensale, $id_cena)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id_commensale = :id_commensale AND id_cena = :id_cena AND statoPartecipante = 'CONFERMATO' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_commensale', $id_commensale);
        $stmt->bindParam(':id_cena', $id_cena);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function annulla($id_partecipazione)
    {
        $query = "UPDATE " . $this->table . " SET statoPartecipante = 'ANNULLATO_DA_UTENTE' WHERE id = :id_partecipazione AND statoPartecipante = 'CONFERMATO'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_partecipazione', $id_partecipazione);
        return $stmt->execute();
    }

    public function trovaPartecipazioniPassateTramiteUtente($id_commensale)
    {
        $query = "SELECT 
                p.*, 
                c.titolo, c.dataOra, c.id_oste,
                u_oste.nome AS nome_oste, u_oste.cognome AS cognome_oste,
                (SELECT COUNT(*) FROM recensioni r WHERE r.id_cena = c.id AND r.id_valutatore = :id_commensale AND r.id_valutato = c.id_oste) > 0 AS ha_recensito_oste
              FROM partecipazioni p
              JOIN cene c ON p.id_cena = c.id
              JOIN utenti u_oste ON c.id_oste = u_oste.id
              WHERE p.id_commensale = :id_commensale AND p.statoPartecipante = 'CONFERMATO' AND c.dataOra < :current_time
              ORDER BY c.dataOra DESC";

        $current_time = (new \DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_commensale', $id_commensale);
        $stmt->bindParam(':current_time', $current_time);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trovaPartecipazioniFutureTramiteUtente($id_commensale)
    {
        $query = "SELECT 
                p.id, p.id_cena,
                c.titolo, c.dataOra,
                u_oste.nome AS nome_oste
              FROM partecipazioni p
              JOIN cene c ON p.id_cena = c.id
              JOIN utenti u_oste ON c.id_oste = u_oste.id
              WHERE p.id_commensale = :id_commensale AND p.statoPartecipante = 'CONFERMATO' AND c.dataOra >= :current_time
              ORDER BY c.dataOra ASC";
        
        $current_time = (new \DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_commensale', $id_commensale);
        $stmt->bindParam(':current_time', $current_time);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trovaPartecipantiPerCena($id_cena, $id_oste)
    {
        $query = "SELECT
                    p.id, p.id_commensale,
                    u.nome, u.cognome,
                    (SELECT COUNT(*) FROM recensioni r WHERE r.id_cena = :id_cena AND r.id_valutatore = :id_oste AND r.id_valutato = p.id_commensale) > 0 AS oste_ha_recensito
                  FROM partecipazioni p
                  JOIN utenti u ON p.id_commensale = u.id
                  JOIN cene c ON p.id_cena = c.id
                  WHERE p.id_cena = :id_cena 
                    AND p.statoPartecipante = 'CONFERMATO' 
                    AND c.dataOra < :current_time";

        $current_time = (new \DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ':id_cena' => $id_cena,
            ':id_oste' => $id_oste,
            ':current_time' => $current_time
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function trovaPartecipantiConfermatiPerCena($id_cena)
    {
        $query = "SELECT id_commensale FROM " . $this->table . " 
                  WHERE id_cena = :id_cena AND statoPartecipante = 'CONFERMATO'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_cena' => $id_cena]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
