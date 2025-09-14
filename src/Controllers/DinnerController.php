<?php
namespace App\Controllers;

use App\Models\Dinner;
use App\Core\AuthMiddleware;

class DinnerController
{
    public function crea()
    {
        $userData = AuthMiddleware::proteggi();

        $data = json_decode(file_get_contents("php://input"));

        $required_fields = ['titolo', 'descrizione', 'dataOra', 'localita', 'numPostiDisponibili'];
        foreach ($required_fields as $field) {
            if (!isset($data->$field) || empty(trim((string)$data->$field))) {
                http_response_code(400);
                echo json_encode(['message' => 'Campo obbligatorio mancante: ' . $field]);
                return;
            }
        }

        if (!is_numeric($data->numPostiDisponibili) || (int)$data->numPostiDisponibili <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Il numero di posti disponibili deve essere un numero intero positivo.']);
            return;
        }

        $data->id_oste = $userData->id;

        $dinnerModel = new Dinner();
        $dinnerId = $dinnerModel->crea($data);
        if ($dinnerId) {
            http_response_code(201);
            echo json_encode(['message' => 'Cena creata con successo.', 'id' => $dinnerId]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore nella creazione della cena.']);
        }
    }

    public function leggiTutte()
    {
        $dinnerModel = new Dinner();
        $dinners = $dinnerModel->leggiTutteAperte();
        echo json_encode($dinners);
    }

    public function leggiSingola($id) {
        $dinnerModel = new Dinner();
        $dinner = $dinnerModel->trovaTramiteId($id);
        if ($dinner) {
            http_response_code(200);
            echo json_encode($dinner);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Cena non trovata.']);
        }
    }

    public function leggiCeneOrganizzate()
    {
        $userData = AuthMiddleware::proteggi();
        $dinnerModel = new Dinner();
        $dinners = $dinnerModel->trovaTramiteOste($userData->id);
        http_response_code(200);
        echo json_encode($dinners);
    }
}