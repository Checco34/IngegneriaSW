<?php
namespace App\Controllers;

use App\Models\Notification;
use App\Core\AuthMiddleware;

class NotificationController
{
    public function leggiPerUtente()
    {
        $userData = AuthMiddleware::proteggi();
        $notificationModel = new Notification();
        
        $notifications = $notificationModel->trovaPerUtente($userData->id);
        $unreadCount = $notificationModel->contaNonLette($userData->id);

        echo json_encode([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    public function marcaLette()
    {
        $userData = AuthMiddleware::proteggi();
        $notificationModel = new Notification();
        
        if ($notificationModel->segnaComeLette($userData->id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Notifiche segnate come lette.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Errore durante l\'aggiornamento delle notifiche.']);
        }
    }

    public function leggiSingola($id)
    {
        AuthMiddleware::proteggi();
        $notificationModel = new Notification();
        
        $notification = $notificationModel->trovaSingolaConDettagliRecensione($id);

        if ($notification) {
            echo json_encode($notification);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Notifica non trovata.']);
        }
    }
}