<?php
namespace App\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;

class UserController
{
    public function registrati()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->email) || !isset($data->password) || !isset($data->nome) || !isset($data->cognome)) {
            http_response_code(400);
            echo json_encode(['message' => 'Dati di registrazione incompleti.']);
            return;
        }

        $userModel = new User();
        if ($userModel->trovaTramiteEmail($data->email)) {
            http_response_code(409);
            echo json_encode(['message' => 'Email già registrata.']);
            return;
        }

        if ($userModel->crea($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Utente registrato con successo.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore durante la registrazione dell\'utente.']);
        }
    }
    
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"));
        $userModel = new User();
        $user = $userModel->trovaTramiteEmail($data->email);

        if (!$user || !password_verify($data->password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Credenziali non valide.']);
            return;
        }

        $secret_key = getenv('JWT_SECRET_KEY');
        $payload = [
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24),
            'data' => [
                'id' => $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email']
            ]
        ];

        $jwt = JWT::encode($payload, $secret_key, 'HS256');
        http_response_code(200);
        echo json_encode(['token' => $jwt]);
    }
}