<?php
namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Reservacion;
use Exception;

class ReservacionController
{
    public function index($esPublico = false)
    {
        Helper::verificarSesion();
        $datos = Helper::getDatosUsuario();
        $resModel = new Reservacion();

        if (isset($_POST['peticion'])) {
            header('Content-Type: application/json');
            try {
                switch ($_POST['peticion']) {
                    case 'listar':
                        // Los administradores ven todo, los clientes solo lo suyo si es vista pública
                        if ($esPublico) {
                            $json = $this->ListarPropias($resModel, $datos['cedula']);
                        } else {
                            $json = $resModel->Transaccion([
                                'peticion' => 'listar', 
                                'filtros' => [
                                    'desde' => $_POST['start'] ?? null,
                                    'hasta' => $_POST['end'] ?? null
                                ]
                            ]);
                        }
                        break;

                    case 'registrar':
                    case 'modificar':
                        // Si es público, forzamos datos por seguridad
                        if ($esPublico) {
                            $_POST['cedula_cliente'] = $datos['cedula'];
                            $_POST['estado'] = 'PENDIENTE';
                            $peticion = 'registrar'; // Clientes siempre registran nuevas

                            // SEGURIDAD: Asegurar que el usuario existe en la tabla 'cliente'
                            // para evitar el error de llave foránea (FK)
                            $this->AsegurarRegistroCliente($datos['cedula']);
                        } else {

                            $peticion = $_POST['peticion'];
                        }

                        $id = ($peticion == 'registrar') ? Helper::generarId('RES') : ($_POST['id_reservacion'] ?? '');
                        
                        $resModel->setId($id);
                        $resModel->setCedulaCliente($_POST['cedula_cliente'] ?? '');
                        $resModel->setFecha($_POST['fecha'] ?? '');
                        $resModel->setHora($_POST['hora'] ?? '');
                        $resModel->setHoraFin($_POST['hora_fin'] ?? '');
                        $resModel->setEstado($_POST['estado'] ?? 'PENDIENTE');

                        $json = $resModel->Transaccion(['peticion' => $peticion]);

                        if ($json['estado'] == 1) {
                            $accion = ($peticion == 'registrar') ? 'REGISTRAR' : 'MODIFICAR';
                            $tipo = $esPublico ? 'PÚBLICO' : 'ADMIN';
                            Helper::Bitacora($accion . '_' . $tipo, 'RESERVACIONES', "Reservación {$id} para cliente {$_POST['cedula_cliente']}");
                        }
                        break;

                    case 'mover':
                        if ($esPublico) throw new Exception("Acción no permitida");
                        $resModel->setId($_POST['id_reservacion']);
                        $resModel->setFecha($_POST['fecha']);
                        $resModel->setHora($_POST['hora']);
                        $resModel->setHoraFin($_POST['hora_fin'] ?? '');
                        $detalle = $resModel->Transaccion(['peticion' => 'detalle']);
                        $resModel->setEstado($detalle['response']['registro']['estado'] ?? 'PENDIENTE');
                        $json = $resModel->Transaccion(['peticion' => 'modificar']);
                        break;

                    case 'eliminar':
                        if ($esPublico) throw new Exception("Acción no permitida");
                        $resModel->setId($_POST['id_reservacion'] ?? '');
                        $json = $resModel->Transaccion(['peticion' => 'eliminar']);
                        break;
                }
                echo json_encode($json['response'] ?? $json);
            } catch (Exception $e) {
                echo json_encode(['resultado' => 500, 'mensaje' => $e->getMessage()]);
            }
            exit;
        }

        // Determinar qué vista y título cargar
        $vista = $esPublico ? 'reservar/index' : 'reservaciones/index';
        $titulo = $esPublico ? 'Mis Reservaciones' : 'Gestión de Reservaciones';

        Helper::cargarVista($vista, $titulo, [
            'clientes' => $esPublico ? [] : $resModel->ObtenerClientes(),
            'extra_css' => [
                'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css',
                'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
                BASE_URL . '/assets/css/reservaciones.css'
            ],
            'extra_js' => [
                'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js',
                'https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/es.global.min.js',
                'https://cdn.jsdelivr.net/npm/flatpickr',
                'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js'
            ],
            'extra_js_modules' => [
                BASE_URL . '/assets/js/Controllers/ReservacionController.js'
            ]
        ]);

    }

    private function ListarPropias($model, $cedula)
    {
        $resp = $model->Transaccion(['peticion' => 'listar']);
        if ($resp['estado'] == 1) {
            $eventos = array_map(function($e) use ($cedula) {
                if ($e['extendedProps']['cedula'] === $cedula) {
                    return $e; // Es mío, lo veo normal
                } else {
                    // Es de otro, mostrar solo horas
                    $hInicio = date("h:i A", strtotime($e['start']));
                    $hFin = date("h:i A", strtotime($e['end']));
                    
                    return [
                        'id' => 'occ_' . $e['id'],
                        'title' => "{$hInicio} - {$hFin} (Ocupado)",
                        'start' => $e['start'],
                        'end' => $e['end'],
                        'editable' => false,
                        'className' => 'status-ocupado-publico',
                        'extendedProps' => [
                            'ocupado' => true
                        ]
                    ];
                }
            }, $resp['response']['datos']);
            
            return ['response' => ['resultado' => 200, 'datos' => $eventos]];
        }
        return $resp;
    }



    /**
     * Asegura que el usuario esté registrado como cliente para evitar fallos de integridad (FK)
     */
    private function AsegurarRegistroCliente($cedula)
    {
        try {
            $db = \App\Core\Database::getConnection();
            $stm = $db->prepare("SELECT COUNT(*) FROM cliente WHERE cedula = ?");
            $stm->execute([$cedula]);
            
            if ($stm->fetchColumn() == 0) {
                $stm = $db->prepare("INSERT INTO cliente (cedula, estatus) VALUES (?, 1)");
                $stm->execute([$cedula]);
            }
        } catch (Exception $e) {
            Helper::ErrorLog("Error auto-registrando cliente: " . $e->getMessage());
        }
    }
}

