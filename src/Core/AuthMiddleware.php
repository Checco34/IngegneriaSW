<?php
namespace App\Core;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
class AuthMiddleware {
    public static function protect() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['message' => 'Accesso negato. Token non fornito.']);
            exit();
        }
        list($jwt) = sscanf($authHeader, 'Bearer %s');
        if (!$jwt) {
            http_response_code(401);
            echo json_encode(['message' => 'Accesso negato. Formato token non valido.']);
            exit();
        }
        try {
            $secret_key = getenv('JWT_SECRET_KEY');
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
            // Restituisce i dati dell'utente (id, email, ruolo) per un uso successivo
            return $decoded->data;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['message' => 'Accesso negato. Token non valido o scaduto.']);
            exit();
        }
    }
}