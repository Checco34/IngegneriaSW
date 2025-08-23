<?php
namespace App\Controllers;
use App\Models\Dinner;
use App\Core\AuthMiddleware;
class DinnerController {
    public function getAll() {
        $dinnerModel = new Dinner();
        $dinners = $dinnerModel->readAll();
        echo json_encode($dinners);
    }
    public function create() {
        // 1. Protegge l'endpoint e ottiene i dati dell'utente dal token
        $userData = AuthMiddleware::protect();
        
        // 2. Controlla se l'utente ha il ruolo corretto
        if ($userData->ruolo !== 'OSTE') {
            http_response_code(403); // Forbidden
            echo json_encode(['message' => 'Azione non consentita. Ruolo non sufficiente.']);
            return;
        }
        $data = json_decode(file_get_contents("php://input"));
        // 3. Associa la cena all'ID dell'oste autenticato
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
}