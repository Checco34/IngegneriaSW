<?php
namespace App\Controllers;

use App\Models\ParticipationRequest;
use App\Models\Dinner;
use App\Core\AuthMiddleware;

class ParticipationController {
    
    // Endpoint per permettere al commensale di iscriversi a una cena
    public function requestParticipation() {
        $userData = AuthMiddleware::protect();
        
        // Controlla se l'utente è un commensale
        if ($userData->ruolo !== 'COMMENSALE') {
            http_response_code(403);
            echo json_encode(['message' => 'Solo un commensale può iscriversi a una cena.']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        $id_cena = $data->id_cena ?? null;

        if (!$id_cena) {
            http_response_code(400);
            echo json_encode(['message' => 'ID della cena mancante.']);
            return;
        }

        $dinnerModel = new Dinner();
        $dinner = $dinnerModel->readSingle($id_cena);

        // Controllo per i posti disponibili (Alternative flow del caso d'uso Iscrizione Cena)
        if (!$dinner || $dinner['numPostiDisponibili'] <= 0) {
            http_response_code(409); // Conflict
            echo json_encode(['message' => 'Posti esauriti o cena non disponibile.']);
            return;
        }
        
        $requestModel = new ParticipationRequest();
        if ($requestModel->create($id_cena, $userData->id)) {
            // Aggiorna il numero di posti disponibili
            $dinnerModel->updateSpots($id_cena, -1);
            http_response_code(201);
            echo json_encode(['message' => 'Richiesta di partecipazione inviata.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore nella richiesta di partecipazione.']);
        }
    }

    // Endpoint per l'oste per visualizzare le richieste di partecipazione
    public function getRequestsByDinner($id_cena) {
        $userData = AuthMiddleware::protect();
        $dinnerModel = new Dinner();
        $dinner = $dinnerModel->readSingle($id_cena);

        // Controlla che l'utente sia l'oste proprietario della cena
        if ($userData->ruolo !== 'OSTE' || $dinner['id_oste'] !== $userData->id) {
            http_response_code(403);
            echo json_encode(['message' => 'Accesso non autorizzato.']);
            return;
        }

        $requestModel = new ParticipationRequest();
        $requests = $requestModel->getRequestsByDinner($id_cena);
        
        if (empty($requests)) {
            // Alternative flow del caso d'uso Gestione Iscrizioni
            http_response_code(200); 
            echo json_encode(['message' => 'Nessuna richiesta di iscrizione presente per questa cena.', 'data' => []]);
        } else {
            http_response_code(200);
            echo json_encode($requests);
        }
    }

    // Endpoint per l'oste per accettare o rifiutare una richiesta
    public function manageRequest() {
        $userData = AuthMiddleware::protect();
        
        if ($userData->ruolo !== 'OSTE') {
            http_response_code(403);
            echo json_encode(['message' => 'Azione non consentita.']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        $id_richiesta = $data->id_richiesta ?? null;
        $stato = $data->stato ?? null;

        if (!$id_richiesta || !in_array($stato, ['ACCETTATA', 'RIFIUTATA'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Dati mancanti o stato non valido.']);
            return;
        }

        $requestModel = new ParticipationRequest();
        if ($requestModel->updateStatus($id_richiesta, $stato)) {
            http_response_code(200);
            echo json_encode(['message' => 'Stato della richiesta aggiornato con successo.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore nell\'aggiornamento dello stato della richiesta.']);
        }
    }
}