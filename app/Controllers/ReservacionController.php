<?php
namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Reservacion;
use App\Models\Security\Bitacora;
use Exception;

class ReservacionController
{
    public function index()
    {
        Helper::verificarSesion();
        $resModel = new Reservacion();

        if (isset($_POST['peticion'])) {
            header('Content-Type: application/json');
            $json = [];

            try {
                switch ($_POST['peticion']) {
                    case 'listar':
                        $json = $resModel->Transaccion([
                            'peticion' => 'listar', 
                            'filtros' => [
                                'desde' => $_POST['start'] ?? null,
                                'hasta' => $_POST['end'] ?? null
                            ]
                        ]);
                        break;

                    case 'registrar':
                    case 'modificar':
                        $id = ($_POST['peticion'] == 'registrar') ? Helper::generarId('RES') : ($_POST['id_reservacion'] ?? '');
                        
                        $resModel->setId($id);
                        $resModel->setCedulaCliente($_POST['cedula_cliente'] ?? '');
                        $resModel->setFecha($_POST['fecha'] ?? '');
                        $resModel->setHora($_POST['hora'] ?? '');
                        $resModel->setEstado($_POST['estado'] ?? 'PENDIENTE');

                        $json = $resModel->Transaccion(['peticion' => $_POST['peticion']]);

                        if ($json['estado'] == 1) {
                            $accion = ($_POST['peticion'] == 'registrar') ? 'REGISTRAR' : 'MODIFICAR';
                            Helper::Bitacora($accion, 'RESERVACIONES', "Reservación {$id} para cliente {$_POST['cedula_cliente']}");
                        }
                        break;

                    case 'mover': // Drag & Drop
                        if (!isset($_POST['id_reservacion'], $_POST['fecha'], $_POST['hora'])) {
                            throw new Exception("Datos incompletos para mover la reservación");
                        }
                        $resModel->setId($_POST['id_reservacion']);
                        $resModel->setFecha($_POST['fecha']);
                        $resModel->setHora($_POST['hora']);
                        
                        // Obtenemos estado actual para no sobreescribirlo si no se envía
                        $detalle = $resModel->Transaccion(['peticion' => 'detalle']);
                        $resModel->setEstado($detalle['response']['registro']['estado'] ?? 'PENDIENTE');

                        $json = $resModel->Transaccion(['peticion' => 'modificar']);
                        if ($json['estado'] == 1) {
                            Helper::Bitacora('MODIFICAR', 'RESERVACIONES', "Se movió la reservación {$_POST['id_reservacion']} a la fecha {$_POST['fecha']} {$_POST['hora']}");
                        }
                        break;

                    case 'eliminar':
                        $resModel->setId($_POST['id_reservacion'] ?? '');
                        $json = $resModel->Transaccion(['peticion' => 'eliminar']);
                        if ($json['estado'] == 1) {
                            Helper::Bitacora('ELIMINAR', 'RESERVACIONES', "Se eliminó la reservación {$_POST['id_reservacion']}");
                        }
                        break;
                }
            } catch (Exception $e) {
                $json = ['estado' => -1, 'response' => ['resultado' => 400, 'mensaje' => $e->getMessage()]];
            }

            echo json_encode($json['response'] ?? $json);
            exit;
        }

        // Cargar vista con dependencias de FullCalendar
        Helper::cargarVista('reservaciones/index', 'Gestión de Reservaciones', [
            'clientes' => $resModel->ObtenerClientes(),
            'extra_css' => [
                'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css',
                BASE_URL . '/assets/css/reservaciones.css'
            ],
            'extra_js' => [
                'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js',
                'https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/es.global.min.js'
            ]
        ]);
    }
}
