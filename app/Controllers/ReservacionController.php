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

        // VALIDACIÓN DE SEGURIDAD PARA LA VISTA ADMIN (Agenda Global)
        if (!$esPublico && !in_array(strtoupper($datos['rol']), ['ADMINISTRADOR', 'VENTAS', 'SUPERUSUARIO'])) {
            if (isset($_POST['peticion'])) {
                header('Content-Type: application/json');
                echo json_encode(['resultado' => 403, 'mensaje' => 'No tienes permiso para realizar esta acción']);
                exit;
            } else {
                // Redirigir al dashboard u otra página con permiso
                header('Location: ' . BASE_URL . '/?page=Dashboard');
                exit;
            }
        }

if (!function_exists('App\Controllers\ListarPropiasReservaciones')) {
    function ListarPropiasReservaciones($model, $cedula)
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
}

        if (isset($_POST['peticion'])) {
            header('Content-Type: application/json');
            try {
                switch ($_POST['peticion']) {
                    case 'listar':
                        // Los administradores ven todo, los clientes solo lo suyo si es vista pública
                        if ($esPublico) {
                            $json = ListarPropiasReservaciones($resModel, $datos['cedula']);
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
                            $clienteModel = new Cliente();
                            $clienteModel->AsegurarCliente($datos['cedula']);
                        } else {

                            $peticion = $_POST['peticion'];
                        }

                        $id = ($peticion == 'registrar') ? Helper::generarId('RES') : ($_POST['id_reservacion'] ?? '');
                        
                        $resModel->setId($id);
                        $resModel->setCedulaCliente($_POST['cedula_cliente'] ?? '');
                        $resModel->setIdMesa(!empty($_POST['id_mesa']) ? $_POST['id_mesa'] : null);
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
                        $resModel->setId($_POST['id_reservacion']);
                        
                        $detalle = $resModel->Transaccion(['peticion' => 'detalle']);
                        if ($detalle['estado'] != 1) {
                            throw new Exception("No se encontró la reservación.");
                        }
                        $registro = $detalle['response']['registro'];

                        // Validaciones de seguridad si es cliente público
                        if ($esPublico) {
                            if ($registro['cedula_cliente'] !== $datos['cedula']) {
                                throw new Exception("No tienes permiso para mover esta reservación.");
                            }
                            if ($registro['estado'] !== 'PENDIENTE') {
                                throw new Exception("Solo se pueden mover reservaciones que están pendientes.");
                            }
                        }

                        $resModel->setFecha($_POST['fecha']);
                        
                        $hora = !empty($_POST['hora']) ? $_POST['hora'] : $registro['hora'];
                        $hora_fin = !empty($_POST['hora_fin']) ? $_POST['hora_fin'] : $registro['hora_fin'];
                        
                        $resModel->setHora($hora);
                        $resModel->setHoraFin($hora_fin);
                        $resModel->setCedulaCliente($registro['cedula_cliente'] ?? '');
                        $resModel->setIdMesa(!empty($registro['id_mesa']) ? $registro['id_mesa'] : null);
                        $resModel->setEstado($registro['estado'] ?? 'PENDIENTE');
                        
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






