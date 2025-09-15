<?php

namespace App\Controllers;

use App\Models\ParticipationRequest;
use App\Models\Participation;
use App\Models\Dinner;
use App\Core\AuthMiddleware;

class ParticipationController
{

    public function richiediPartecipazione()
    {
        $userData = AuthMiddleware::proteggi();
        $data = json_decode(file_get_contents("php://input"));
        $id_cena = $data->id_cena ?? null;

        if (!$id_cena) {
            http_response_code(400);
            echo json_encode(['message' => 'ID della cena mancante.']);
            return;
        }

        $dinnerModel = new Dinner();
        $dinner = $dinnerModel->trovaTramiteId($id_cena);

        if (!$dinner || $dinner['stato'] !== 'APERTA') {
            http_response_code(409);
            echo json_encode(['message' => 'Cena non disponibile o non più aperta.']);
            return;
        }

        $requestModel = new ParticipationRequest();
        if ($requestModel->crea($id_cena, $userData->id)) {
            http_response_code(201);
            echo json_encode(['message' => 'Richiesta di partecipazione inviata.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore: richiesta già inviata o problema del server.']);
        }
    }

    public function leggiRichiestePerCena($id)
    {
        $userData = AuthMiddleware::proteggi();
        $dinnerModel = new Dinner();
        $dinner = $dinnerModel->trovaTramiteId($id);

        if ($dinner['id_oste'] != $userData->id) {
            http_response_code(403);
            echo json_encode(['message' => 'Accesso non autorizzato.']);
            return;
        }

        $requestModel = new ParticipationRequest();
        $requests = $requestModel->trovaTramiteCena($id);

        http_response_code(200);
        echo json_encode($requests);
    }

    public function gestisciRichiesta($id)
    {
        $userData = AuthMiddleware::proteggi();
        $data = json_decode(file_get_contents("php://input"));
        $stato = $data->stato ?? null;

        if (!in_array($stato, ['ACCETTATA', 'RIFIUTATA'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Stato non valido.']);
            return;
        }

        $requestModel = new ParticipationRequest();
        $request = $requestModel->trovaTramiteId($id);
        if (!$request) {
            http_response_code(404);
            echo json_encode(['message' => 'Richiesta non trovata.']);
            return;
        }

        $dinnerModel = new Dinner();
        $dinner = $dinnerModel->trovaTramiteId($request['id_cena']);

        if ($dinner['id_oste'] != $userData->id) {
            http_response_code(403);
            echo json_encode(['message' => 'Azione non consentita. Non sei l\'oste di questa cena.']);
            return;
        }

        if ($stato === 'ACCETTATA') {
            if ($dinner['numPostiDisponibili'] <= 0) {
                http_response_code(409);
                echo json_encode(['message' => 'Impossibile accettare: posti esauriti.']);
                return;
            }

            $participationModel = new Participation();
            if ($participationModel->creaDaRichiesta($request)) {
                $dinnerModel->aggiornaPosti($dinner['id'], -1);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Errore nella creazione della partecipazione.']);
                return;
            }
        }

        if ($requestModel->aggiornaStato($id, $stato)) {
            http_response_code(200);
            echo json_encode(['message' => 'Stato della richiesta aggiornato con successo.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore nell\'aggiornamento dello stato della richiesta.']);
        }
    }

    public function annullaPartecipazione($id)
    {
        $userData = AuthMiddleware::proteggi();
        $participationModel = new Participation();
        $participation = $participationModel->trovaTramiteId($id);

        if (!$participation) {
            http_response_code(404);
            echo json_encode(['message' => 'Partecipazione non trovata.']);
            return;
        }

        if ($participation['id_commensale'] != $userData->id) {
            http_response_code(403);
            echo json_encode(['message' => 'Azione non consentita.']);
            return;
        }

        if ($participationModel->annulla($id)) {
            $dinnerModel = new Dinner();
            $dinnerModel->aggiornaPosti($participation['id_cena'], 1);
            http_response_code(200);
            echo json_encode(['message' => 'Partecipazione annullata con successo.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore durante l\'annullamento.']);
        }
    }

    public function leggiPartecipazioniUtente()
    {
        $userData = AuthMiddleware::proteggi();
        $participationModel = new Participation();
        $participations = $participationModel->trovaTramiteUtente($userData->id);
        http_response_code(200);
        echo json_encode($participations);
    }

    public function leggiPartecipantiCena($id)
    {
        $userData = AuthMiddleware::proteggi();
        $dinnerModel = new Dinner();
        $dinner = $dinnerModel->trovaTramiteId($id);

        if (!$dinner || $dinner['id_oste'] != $userData->id) {
            http_response_code(403);
            echo json_encode(['message' => 'Non autorizzato.']);
            return;
        }

        $participationModel = new Participation();
        $participants = $participationModel->trovaPartecipantiPerCena($id, $userData->id);
        http_response_code(200);
        echo json_encode($participants);
    }
}
