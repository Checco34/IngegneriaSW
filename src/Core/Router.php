<?php

namespace App\Core;

use App\Controllers\UserController;
use App\Controllers\DinnerController;
use App\Controllers\ParticipationController;
use App\Controllers\ReviewController;

class Router {
    private $routes = [];

    private function addRoute($method, $path, $handler) {
        $this->routes[strtoupper($method)][$path] = $handler;
    }

    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    public function dispatch($url) {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = '/' . trim($url, '/');

        // Special handling for dynamic URLs like /api/cene/{id}
        if ($method === 'GET' && strpos($path, '/api/cene/requests/') === 0) {
            $parts = explode('/', $path);
            $id_cena = end($parts);
            $handler = [ParticipationController::class, 'getRequestsByDinner'];
            // Dispatch with the dynamic parameter
            $controller = new $handler[0]();
            $action = $handler[1];
            $controller->$action($id_cena);
            return;
        }

        // Gestione per i dettagli di una singola cena
        if ($method === 'GET' && preg_match('/^\/api\/cene\/(\d+)$/', $path, $matches)) {
            $id_cena = $matches[1];
            $handler = [DinnerController::class, 'getSingle'];
            $controller = new $handler[0]();
            $action = $handler[1];
            $controller->$action($id_cena);
            return;
        }

        $handler = $this->routes[$method][$path] ?? null;

        if ($handler) {
            $controller = new $handler[0]();
            $action = $handler[1];
            $controller->$action();
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Endpoint non trovato.']);
        }
    }
}