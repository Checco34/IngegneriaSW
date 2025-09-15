<?php
namespace App\Controllers;

use App\Models\Review;
use App\Models\Participation;
use App\Models\Dinner;
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
       //Recupera la cena per ottenere l'ID dell'oste
       
        $dinnerModel = new Dinner();
        $dinner = $dinnerModel->trovaTramiteId($data->id_cena);

        if (!$dinner) {
            http_response_code(404);
            echo json_encode(['message' => 'Cena non trovata.']);
            return;
        }

        $id_oste = $dinner['id_oste'];
        $id_valutator = $data->id_valutatore;
        $id_valutated = $data->id_valutato;

        //Controlla se il valutatore era presente alla cena (come oste o commensale)
        $participationModel = new Participation();
        $valutatorIsOste = ($id_valutator == $id_oste);
        $valutatorHasPartecipated = $participationModel->trovaTramiteUtenteECena($id_valutator, $data->id_cena);
        $valutatorIsPresent = $valutatorIsOste || $valutatorHasPartecipated;

        //Controlla se il valutato era presente alla cena (come oste o commensale)
        $valutatoIsOste = ($id_valutated == $id_oste);
        $valutatedHasPartecipated = $participationModel->trovaTramiteUtenteECena($id_valutated, $data->id_cena);
        $valutatedIsPresent = $valutatoIsOste || $valutatedHasPartecipated;

        //Se uno dei due non era presente, blocca l'operazione
        if (!$valutatorIsPresent || !$valutatedIsPresent) {
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