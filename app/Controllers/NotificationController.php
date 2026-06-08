<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\NotificacionHelper;

$type = $_REQUEST['type'] ?? 'index';

if ($type === 'index') {
        // ===== 1. VERIFICAR SESIÓN =====
        Helper::verificarSesion();

        // ===== 2. DETECTAR PETICIÓN AJAX Y PARÁMETRO PETICION =====
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        $peticion = $_POST["peticion"] ?? $_GET["peticion"] ?? "";

        if (!empty($peticion)) {
            // Limpiar cualquier búfer previo para asegurar JSON limpio
            if (ob_get_length()) ob_clean();
            
            header('Content-Type: application/json');
            $cedula = $_SESSION['user']['cedula'] ?? "";

            // Inicialización de respuesta estándar
            $json = [
                'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Petición no válida'],
                'response' => ['resultado' => 400, 'mensaje' => 'Acción no reconocida']
            ];

            // ── PETICIÓN: ENTRADA (Test de enlace) ──
            if ($peticion === "entrada") {
                $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                $json['response'] = ['resultado' => 200, 'mensaje' => 'Conexión establecida'];
            }

            // ── PETICIÓN: LISTAR (Notificaciones del Navbar) ──
            if ($peticion === "listar") {
                $notificaciones = NotificacionHelper::obtenerRecientes($cedula, 10);
                $noLeidas = NotificacionHelper::obtenerCantidadNoLeidas($cedula);

                $datosFormateados = [];
                foreach ($notificaciones as $n) {
                    $fechaObj = new \DateTime($n['fecha_envio']);
                    $hace = Helper::tiempoTranscurrido($fechaObj);

                    $datosFormateados[] = [
                        'id' => $n['id_notificacion'],
                        'titulo' => $n['titulo'],
                        'mensaje' => $n['mensaje'],
                        'tipo' => strtolower($n['tipo']), // 'info', 'alerta', 'exito', 'error'
                        'leida' => (int)$n['leido'],
                        'hace' => $hace,
                        'fecha' => $fechaObj->format('d/m/Y H:i:s')
                    ];
                }

                $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                $json['response'] = [
                    'resultado' => 200,
                    'success' => true,
                    'noLeidas' => $noLeidas,
                    'data' => $datosFormateados
                ];
            }

            // ── PETICIÓN: MARCAR UNA COMO LEÍDA ──
            if ($peticion === "marcar-leida") {
                $id = $_POST['id_notificacion'] ?? $_GET['id_notificacion'] ?? '';
                if (!empty($id)) {
                    $resultado = NotificacionHelper::marcarComoLeida($id);
                    if ($resultado) {
                        $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                        $json['response'] = [
                            'resultado' => 200, 
                            'success' => true, 
                            'mensaje' => 'Notificación marcada como leída'
                        ];
                    } else {
                        $json['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Internal Server Error'];
                        $json['response'] = [
                            'resultado' => 500, 
                            'success' => false, 
                            'mensaje' => 'No se pudo actualizar la notificación'
                        ];
                    }
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Bad Request'];
                    $json['response'] = [
                        'resultado' => 400, 
                        'success' => false, 
                        'mensaje' => 'ID de notificación no proporcionado'
                    ];
                }
            }

            // ── PETICIÓN: MARCAR TODAS COMO LEÍDAS ──
            if ($peticion === "marcar-todas") {
                $resultado = NotificacionHelper::marcarTodasComoLeidas($cedula);
                if ($resultado) {
                    $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                    $json['response'] = [
                        'resultado' => 200, 
                        'success' => true, 
                        'mensaje' => 'Todas las notificaciones marcadas como leídas'
                    ];
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Internal Server Error'];
                    $json['response'] = [
                        'resultado' => 500, 
                        'success' => false, 
                        'mensaje' => 'No se pudieron marcar las notificaciones como leídas'
                    ];
                }
            }

            // Retornar cabecera e imprimir JSON estandarizado
            header("HTTP/1.1 " . implode(' ', $json['HTTP_STATUS']));
            echo json_encode($json['response']);
            exit;
        }

        // ===== 3. CARGAR VISTA NORMAL =====
        Helper::cargarVista('notificaciones/index', 'Mis Notificaciones - Good Vibes');
}
