<?php
namespace App\Core;

class Router {
    private $routes = [];

    private function aggiungiRotta($method, $url, $handler) {
        $urlRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $url);
        $this->routes[$method]['#^' . $urlRegex . '$#'] = $handler;
    }

    public function get($url, $handler) {
        $this->aggiungiRotta('GET', $url, $handler);
    }

    public function post($url, $handler) {
        $this->aggiungiRotta('POST', $url, $handler);
    }

    public function put($url, $handler) {
        $this->aggiungiRotta('PUT', $url, $handler);
    }

    public function delete($url, $handler) {
        $this->aggiungiRotta('DELETE', $url, $handler);
    }

    public function instrada($url) {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = parse_url($url, PHP_URL_PATH);

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $handler) {
                if (preg_match($route, $url, $matches)) {
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    
                    $controller = new $handler[0]();
                    $action = $handler[1];

                    call_user_func_array([$controller, $action], $params);
                    return;
                }
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Endpoint non trovato']);
    }
}