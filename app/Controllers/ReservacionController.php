<?php
namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Reservacion;
use App\Models\System\Cliente;
use App\Models\System\Mesas;
use Exception;

$type = $_REQUEST['type'] ?? 'admin';
$esPublico = ($type === 'publico');

Helper::verificarSesion();
$datos = Helper::getDatosUsuario();
$resModel = new Reservacion();
$permisosReservacion = Helper::TraerPermisos("reservacion");

$puedeVerAgenda = isset($permisosReservacion['reservacion']['agenda']) && $permisosReservacion['reservacion']['agenda'] == 1;

if (!$esPublico && !$puedeVerAgenda) {
    if (isset($_POST['peticion'])) {
        header('Content-Type: application/json');
        echo json_encode(['resultado' => 403, 'mensaje' => 'No tienes permiso para realizar esta acción']);
        exit;
    } else {
        header('Location: ' . BASE_URL . '/?page=Dashboard');
        exit;
    }
}

if (isset($_POST['peticion'])) {
    header('Content-Type: application/json');
    try {
        $peticionEnviada = $_POST['peticion'];
        $json = [];

        switch ($peticionEnviada) {
            case 'listar':
                if ($esPublico) {
                    $resp = $resModel->Transaccion(['peticion' => 'listar']);
                    if ($resp['estado'] == 1) {
                        $eventos = array_map(function($e) use ($datos) {
                            if ($e['extendedProps']['cedula'] === $datos['cedula']) {
                                return $e;
                            }
                            $hInicio = date("h:i A", strtotime($e['start']));
                            $hFin = date("h:i A", strtotime($e['end']));
                            return [
                                'id' => 'occ_' . $e['id'],
                                'title' => "{$hInicio} - {$hFin} (Ocupado)",
                                'start' => $e['start'],
                                'end' => $e['end'],
                                'editable' => false,
                                'className' => 'status-ocupado-publico',
                                'extendedProps' => ['ocupado' => true]
                            ];
                        }, $resp['response']['datos']);
                        $json = ['response' => ['resultado' => 200, 'datos' => $eventos]];
                    } else {
                        $json = $resp;
                    }
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
                $peticion = $peticionEnviada;
                
                if ($esPublico) {
                    $_POST['cedula_cliente'] = $datos['cedula'];
                    $_POST['estado'] = 'PENDIENTE';
                    $peticion = 'registrar';
                    
                    $clienteModel = new Cliente();
                    $clienteModel->AsegurarCliente($datos['cedula']);
                } else {
                    $tienePermiso = isset($permisosReservacion['reservacion'][$peticion]) && $permisosReservacion['reservacion'][$peticion] == 1;
                    if (!$tienePermiso) {
                        throw new Exception("Error, No tienes permiso para {$peticion} una Reservación");
                    }
                }

                $id = ($peticion === 'registrar') ? Helper::generarId('RES') : ($_POST['id_reservacion'] ?? '');
                
                $fechaSel = $_POST['fecha'] ?? '';
                if ($esPublico && $peticion === 'registrar' && strtotime($fechaSel) < strtotime(date('Y-m-d'))) {
                    throw new Exception("No puede realizar una reservación en una fecha pasada.");
                }

                $resModel->setId($id);
                $resModel->setCedulaCliente($_POST['cedula_cliente'] ?? '');
                $resModel->setIdMesa(!empty($_POST['id_mesa']) ? $_POST['id_mesa'] : null);
                $resModel->setFecha($fechaSel);
                $resModel->setHora($_POST['hora'] ?? '');
                $resModel->setHoraFin($_POST['hora_fin'] ?? '');
                $resModel->setEstado($_POST['estado'] ?? 'PENDIENTE');

                $json = $resModel->Transaccion(['peticion' => $peticion]);

                if ($json['estado'] == 1) {
                    $accion = strtoupper($peticion);
                    Helper::Bitacora($accion, 'RESERVACIONES', "Reservación {$id} para cliente {$_POST['cedula_cliente']}");
                }
                break;

            case 'mover':
                $resModel->setId($_POST['id_reservacion']);
                
                $detalle = $resModel->Transaccion(['peticion' => 'detalle']);
                if ($detalle['estado'] != 1) {
                    throw new Exception("No se encontró la reservación.");
                }
                $registro = $detalle['response']['registro'];

                if ($esPublico) {
                    if ($registro['cedula_cliente'] !== $datos['cedula']) {
                        throw new Exception("No tienes permiso para mover esta reservación.");
                    }
                    if ($registro['estado'] !== 'PENDIENTE') {
                        throw new Exception("Solo se pueden mover reservaciones que están pendientes.");
                    }
                } else {
                    if (!isset($permisosReservacion['reservacion']['modificar']) || $permisosReservacion['reservacion']['modificar'] != 1) {
                        throw new Exception("Error, No tienes permiso para mover (modificar) una Reservación");
                    }
                }

                $fechaMover = $_POST['fecha'];
                if ($esPublico && strtotime($fechaMover) < strtotime(date('Y-m-d'))) {
                    throw new Exception("No puede mover la reservación a una fecha pasada.");
                }

                $resModel->setFecha($fechaMover);
                $resModel->setHora(!empty($_POST['hora']) ? $_POST['hora'] : $registro['hora']);
                $resModel->setHoraFin(!empty($_POST['hora_fin']) ? $_POST['hora_fin'] : $registro['hora_fin']);
                $resModel->setCedulaCliente($registro['cedula_cliente'] ?? '');
                $resModel->setIdMesa(!empty($registro['id_mesa']) ? $registro['id_mesa'] : null);
                $resModel->setEstado($registro['estado'] ?? 'PENDIENTE');
                
                $json = $resModel->Transaccion(['peticion' => 'modificar']);
                if ($json['estado'] == 1) {
                    Helper::Bitacora('MOVER', 'RESERVACIONES', "Se movió la reservación {$_POST['id_reservacion']} a la fecha {$_POST['fecha']}");
                }
                break;

            case 'eliminar':
                if ($esPublico) {
                    throw new Exception("Acción no permitida");
                }
                
                if (!isset($permisosReservacion['reservacion']['eliminar']) || $permisosReservacion['reservacion']['eliminar'] != 1) {
                    throw new Exception("Error, No tienes permiso para eliminar una Reservación");
                }
                
                $resModel->setId($_POST['id_reservacion'] ?? '');
                
                $res_prev = $resModel->Transaccion(['peticion' => 'detalle']);
                $datos_anteriores = $res_prev['response']['registro'] ?? null;

                $json = $resModel->Transaccion(['peticion' => 'eliminar']);

                if ($json['estado'] == 1) {
                    Helper::Bitacora('ELIMINAR', 'RESERVACIONES', "Se eliminó la reservación {$_POST['id_reservacion']}", $datos_anteriores);
                }
                break;

            default:
                throw new Exception("Petición no válida");
        }

        echo json_encode($json['response'] ?? $json);
    } catch (Exception $e) {
        echo json_encode(['resultado' => 500, 'mensaje' => $e->getMessage()]);
    }
    exit;
}


$vista = $esPublico ? 'reservar/index' : 'reservaciones/index';
$titulo = $esPublico ? 'Mis Reservaciones' : 'Gestión de Reservaciones';

$mesasModel = new Mesas();
$mesasList = $mesasModel->Transaccion(['peticion' => 'consultar']);

Helper::cargarVista($vista, $titulo, [
    'clientes' => $esPublico ? [] : $resModel->ObtenerClientes(),
    'mesas' => ($mesasList['estado'] == 1) ? $mesasList['response']['datos'] : [],
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
    'extra_js_inline_module' => '
        import * as handler from "' . BASE_URL . '/assets/js/Handlers/ReservacionHandler.js?v=' . time() . '";
        
        document.addEventListener("DOMContentLoaded", function() {
            const $ = window.$;
            const calendarEl = document.getElementById("calendarPublico");
            if (!calendarEl) return;

            const pickers = handler.inicializarPickers();
            const $selectCliente = $("#cedula_cliente");
            if ($selectCliente.length) {
                $selectCliente.select2({
                    theme: "bootstrap-5",
                    dropdownParent: $("#modalReservacion"),
                    placeholder: "Seleccione un cliente",
                    width: "100%",
                    templateResult: handler.formatarEstadoCliente,
                    templateSelection: handler.formatarEstadoCliente
                });
            }

            const calendar = handler.inicializarCalendario(calendarEl, pickers);
            const esPublico = window.location.search.includes("type=publico");
            if (!esPublico) {
                calendar.setOption("editable", true);
                calendar.setOption("eventResizableFromStart", true);
            }

            $("#formReservacion, #formReservarPublico").on("submit", function(e) {
                e.preventDefault();
                handler.GestionarEnvio(this, calendar);
            });

            $("#btnEliminar").on("click", function() {
                const id = $("#id_reservacion").val();
                handler.EliminarReservacion(id, calendar);
            });

            $("#btnNuevaReservacion, #btnNuevaReservacionMobile").on("click", function() {
                const hoy = new Date().toISOString().split("T")[0];
                calendar.select(hoy);
            });
        });
    '
]);






