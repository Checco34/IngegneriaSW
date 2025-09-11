<?php

namespace App\Controllers;

use App\Models\Dinner;
use App\Core\AuthMiddleware;

class DinnerController
{
    public function create()
    {
        // 1. Protegge l'endpoint e ottiene i dati dell'utente dal token
        $userData = AuthMiddleware::protect();

        // 2. Controlla se l'utente ha il ruolo corretto
        if ($userData->ruolo !== 'OSTE') {
            http_response_code(403); // Forbidden
            echo json_encode(['message' => 'Azione non consentita. Ruolo non sufficiente.']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        // 3. Esegui la validazione dei dati
        $required_fields = ['titolo', 'descrizione', 'dataOra', 'localita', 'numPostiDisponibili', 'menu'];
        foreach ($required_fields as $field) {
            if (!isset($data->$field) || empty(trim($data->$field))) {
                http_response_code(400); // Bad Request
                echo json_encode(['message' => 'Campo obbligatorio mancante: ' . $field]);
                return;
            }
        }

        // 4. Aggiungi la validazione specifica per il numero di posti
        if (!is_numeric($data->numPostiDisponibili) || (int)$data->numPostiDisponibili <= 0) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Il numero di posti disponibili deve essere un numero intero positivo.']);
            return;
        }

        // 5. Associa la cena all'ID dell'oste autenticato
        $data->id_oste = $userData->id;

        $dinnerModel = new Dinner();
        if ($dinnerModel->create($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Cena creata con successo.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore nella creazione della cena.']);
        }
    }

    public function getAll()
    {
        $dinnerModel = new Dinner();
        $dinners = $dinnerModel->readAll();
        echo json_encode($dinners);
    }

    public function getSingle($id) {
        $dinnerModel = new Dinner();
        $dinner = $dinnerModel->readSingle($id);
        if ($dinner) {
            http_response_code(200);
            echo json_encode($dinner);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Cena non trovata.']);
        }
    }
}
