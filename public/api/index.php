<?php

require_once __DIR__ . '/autloader.php';

use App\Core\Router;
use App\Controllers\UserController;
use App\Controllers\DinnerController;
use App\Controllers\PartecipationController as ParticipationController;
header("Content-Type: application/json");

$router = new Router();

// Rotte per l'autenticazione
$router->post('/api/register', [App\Controllers\UserController::class, 'register']);
$router->post('/api/login', [App\Controllers\UserController::class, 'login']);
$router->post('/api/logout', [App\Controllers\UserController::class, 'logout']);

// Rotte per le cene (pubbliche)
$router->get('/api/cene', [App\Controllers\DinnerController::class, 'getAll']);

// Rotta per la creazione di una cena (protetta da JWT)
$router->post('/api/cene/create', [App\Controllers\DinnerController::class, 'create']);

// Rotte per le iscrizioni (protette da JWT)
$router->post('/api/partecipazione/richiedi', [App\Controllers\ParticipationController::class, 'requestParticipation']);
$router->get('/api/partecipazione/gestisci/{id_cena}', [App\Controllers\ParticipationController::class, 'getRequestsByDinner']);
$router->post('/api/partecipazione/gestisci', [App\Controllers\ParticipationController::class, 'manageRequest']);

// Dispatch della richiesta
$url = $_SERVER['REQUEST_URI'];
$router->dispatch($url);

?>