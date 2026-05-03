<?php

namespace App\Controllers;

use App\Helpers\Helper;

class NotificationController
{
    public function index()
    {
        Helper::verificarSesion();

        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action === 'listar') {
                // Mock de notificaciones para evitar errores de consola
                echo json_encode([
                    'success' => true,
                    'noLeidas' => 0,
                    'data' => []
                ]);
                exit;
            }

            if ($action === 'marcar-todas') {
                echo json_encode(['success' => true]);
                exit;
            }
        }

        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        exit;
    }
}
