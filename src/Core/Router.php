<?php
namespace App\Core;
class Router {
    private $routes = [];
    private function addRoute($method, $path, $handler) {
        $this->routes[strtoupper($method)][$path] = $handler;
    }
    public function get($path, $handler) { $this->addRoute('GET', $path, $handler); }
    public function post($path, $handler) { $this->addRoute('POST', $path, $handler); }
    public function dispatch($url) {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = '/' . trim($url, '/');
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