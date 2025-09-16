<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
date_default_timezone_set('Europe/Rome');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../src/Core/Autoloader.php';

use App\Core\Router;
use App\Controllers\UserController;
use App\Controllers\DinnerController;
use App\Controllers\ParticipationController;
use App\Controllers\ReviewController;
use App\Controllers\NotificationController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


$router = new Router();

// --- Rotte Utente ---
$router->post('/api/registrati', [UserController::class, 'registrati']);
$router->post('/api/login', [UserController::class, 'login']);

// --- Rotte Cene ---
$router->post('/api/cene', [DinnerController::class, 'crea']); 
$router->get('/api/cene', [DinnerController::class, 'leggiTutte']);
$router->get('/api/cene/mie', [DinnerController::class, 'leggiCeneOrganizzate']);
$router->get('/api/cene/{id}', [DinnerController::class, 'leggiSingola']);
$router->post('/api/cene/annulla/{id}', [DinnerController::class, 'annulla']);

// --- Rotte Richieste di Partecipazione ---
$router->post('/api/richieste', [ParticipationController::class, 'richiediPartecipazione']);
$router->get('/api/cene/{id}/richieste', [ParticipationController::class, 'leggiRichiestePerCena']);
$router->put('/api/richieste/{id}', [ParticipationController::class, 'gestisciRichiesta']);
$router->get('/api/partecipazioni/mie/passate', [ParticipationController::class, 'leggiPartecipazioniPassateUtente']);
$router->get('/api/partecipazioni/mie/future', [ParticipationController::class, 'leggiPartecipazioniFutureUtente']);

$router->get('/api/cene/{id}/partecipanti', [ParticipationController::class, 'leggiPartecipantiCena']);
$router->post('/api/partecipazioni/annulla/{id}', [ParticipationController::class, 'annullaPartecipazione']);

// --- Rotte Recensioni ---
$router->post('/api/recensioni', [ReviewController::class, 'crea']);
$router->get('/api/utenti/{id}/recensioni', [ReviewController::class, 'leggiRecensioniPerUtente']); 

// --- Rotte Notifiche ---
$router->get('/api/notifiche', [NotificationController::class, 'leggiPerUtente']);
$router->post('/api/notifiche/leggi', [NotificationController::class, 'marcaLette']);
$router->get('/api/notifiche/{id}', [NotificationController::class, 'leggiSingola']);

$url = $_SERVER['REQUEST_URI'];
$router->instrada($url);