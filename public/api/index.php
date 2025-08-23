<?php
require_once __DIR__ . '/../../vendor/autoload.php';
header("Content-Type: application/json; charset=UTF-8");
$router = new App\Core\Router();
// Rotte Utente
$router->post('/register', [App\Controllers\UserController::class, 'register']);
$router->post('/login', [App\Controllers\UserController::class, 'login']);
// Rotte Cene (NUOVE)
$router->get('/dinners', [App\Controllers\DinnerController::class, 'getAll']);
$router->post('/dinners', [App\Controllers\DinnerController::class, 'create']);
// Esecuzione del router
$url = $_GET['url'] ?? '';
$router->dispatch($url);