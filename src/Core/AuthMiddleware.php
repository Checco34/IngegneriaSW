<?php
namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    public static function proteggi() {
        $headers = getallheaders();
        $jwt = null;
        
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                $jwt = $matches[1];
            }
        }

        if (!$jwt) {
            http_response_code(401);
            echo json_encode(['message' => 'Accesso non autorizzato.']);
            exit();
        }

        try {
            $secret_key = getenv('JWT_SECRET_KEY');
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
            return $decoded->data;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['message' => 'Token non valido.']);
            exit();
        }
    }

    public static function recuperaUtente() {
        $headers = getallheaders();
        $jwt = null;
        
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                $jwt = $matches[1];
            }
        }

        if (!$jwt) {
            return null;
        }

        try {
            $secret_key = getenv('JWT_SECRET_KEY');
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
            return $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }
}