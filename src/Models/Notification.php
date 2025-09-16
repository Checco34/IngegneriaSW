<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Notification
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function crea($id_utente, $messaggio, $id_recensione = null)
    {
        $query = "INSERT INTO notifiche (id_utente, messaggio, id_recensione) VALUES (:id_utente, :messaggio, :id_recensione)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_utente' => $id_utente, 'messaggio' => $messaggio,  'id_recensione' => $id_recensione]);
    }

    public function trovaPerUtente($id_utente)
    {
        $query = "SELECT * FROM notifiche WHERE id_utente = :id_utente ORDER BY data_creazione DESC LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_utente' => $id_utente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function segnaComeLette($id_utente)
    {
        $query = "UPDATE notifiche SET letta = TRUE WHERE id_utente = :id_utente AND letta = FALSE";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(['id_utente' => $id_utente]);
    }
    
    public function contaNonLette($id_utente) {
        $query = "SELECT COUNT(*) as count FROM notifiche WHERE id_utente = :id_utente AND letta = FALSE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_utente' => $id_utente]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function trovaSingolaConDettagliRecensione($id_notifica)
    {
        $query = "SELECT 
                    n.*, 
                    r.voto, 
                    r.commento,
                    u.nome AS nome_valutatore,
                    c.titolo AS titolo_cena
                FROM notifiche n
                LEFT JOIN recensioni r ON n.id_recensione = r.id
                LEFT JOIN utenti u ON r.id_valutatore = u.id
                LEFT JOIN cene c ON r.id_cena = c.id
                WHERE n.id = :id_notifica";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_notifica' => $id_notifica]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}