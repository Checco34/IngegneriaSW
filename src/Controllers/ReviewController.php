<?php
namespace App\Controllers;

use App\Models\Review;
use App\Models\Participation;
use App\Core\AuthMiddleware;

class ReviewController {
    
    public function crea() {
        $userData = AuthMiddleware::proteggi();
        $data = json_decode(file_get_contents("php://input"));

        $required_fields = ['id_cena', 'id_valutato', 'voto', 'commento'];
        foreach ($required_fields as $field) {
            if (!isset($data->$field)) {
                http_response_code(400);
                echo json_encode(['message' => "Dato mancante: $field"]);
                return;
            }
        }

        $data->id_valutatore = $userData->id;

        if ($data->id_valutatore == $data->id_valutato) {
            http_response_code(400);
            echo json_encode(['message' => 'Non puoi recensire te stesso.']);
            return;
        }

        $participationModel = new Participation();
        $evaluatorParticipation = $participationModel->trovaTramiteUtenteECena($data->id_valutatore, $data->id_cena);
        $evaluatedParticipation = $participationModel->trovaTramiteUtenteECena($data->id_valutato, $data->id_cena);

        if (!$evaluatorParticipation || !$evaluatedParticipation) {
            http_response_code(403);
            echo json_encode(['message' => 'Per scrivere una recensione, entrambi gli utenti devono aver partecipato alla stessa cena.']);
            return;
        }
        
        $reviewModel = new Review();
        if ($reviewModel->crea($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Recensione creata con successo.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore nella creazione della recensione.']);
        }
    }

    public function leggiRecensioniPerUtente($id_utente) {
        $reviewModel = new Review();
        $reviews = $reviewModel->trovaTramiteUtente($id_utente);
        http_response_code(200);
        echo json_encode($reviews);
    }
}