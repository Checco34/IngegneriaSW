<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../src/Core/Autoloader.php';

use App\Core\Router;
use App\Controllers\UserController;
use App\Controllers\DinnerController;
use App\Controllers\ParticipationController;
use App\Controllers\ReviewController;

$router = new Router();

// --- Rotte Utente ---
$router->post('/api/register', [UserController::class, 'register']);
$router->post('/api/login', [UserController::class, 'login']);

// --- Rotte Cene ---
$router->post('/api/cene', [DinnerController::class, 'create']);
$router->get('/api/cene', [DinnerController::class, 'getAll']);
$router->get('/api/cene/{id}', [DinnerController::class, 'getSingle']);

// --- Rotte Partecipazione ---
$router->post('/api/requests', [ParticipationController::class, 'requestParticipation']);
$router->get('/api/cene/{id}/requests', [ParticipationController::class, 'getRequestsByDinner']);
$router->post('/api/requests/manage', [ParticipationController::class, 'manageRequest']);
$router->post('/api/participations/cancel', [ParticipationController::class, 'cancelParticipation']);

// --- Rotte Recensioni ---
$router->post('/api/reviews', [ReviewController::class, 'create']);
$router->get('/api/users/{id}/reviews', [ReviewController::class, 'getReviewsForUser']);

$url = $_SERVER['REQUEST_URI'];
$router->dispatch($url);