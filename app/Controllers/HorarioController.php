<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Horario;
use App\Models\System\Empleado;
use App\Models\System\Turno;
use Exception;

Helper::verificarSesion();

$horarioModel = new Horario();
$empleadoModel = new Empleado();
$turnoModel = new Turno();
$permisosHorario = Helper::TraerPermisos('horario');
$permisosTurno = Helper::TraerPermisos('turno');
$tienePermisoHorario = static function (string $accion) use ($permisosHorario): bool {
    return ($permisosHorario['horario'][$accion] ?? 0) == 1;
};
$puedeVerTodosHorarios = $tienePermisoHorario('ver_todos');
$cedulaSesion = (string)($_SESSION['user']['cedula'] ?? '');

// ==========================================
// MÓDULO: HORARIO
// ==========================================

if (isset($_POST["modulo"]) && $_POST["modulo"] == "Horario") {
    if (isset($_POST["peticion"])) {

        // --- REGISTRAR ---
        if ($_POST["peticion"] == "registrar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];

            if (!$tienePermisoHorario('registrar')) {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para registrar horarios'];
            } else {
            try {
                if (!is_scalar($_POST['cedula_empleado'] ?? '') || !is_scalar($_POST['id_turno'] ?? null) || !is_scalar($_POST['fecha'] ?? null)) {
                    throw new Exception('Los datos del horario no son válidos.');
                }
                $cedulaEmpleado = $puedeVerTodosHorarios
                    ? (string)($_POST["cedula_empleado"] ?? '')
                    : $cedulaSesion;
                if ($cedulaEmpleado === '') {
                    throw new Exception('No se pudo identificar al empleado de la sesión.');
                }
                $id = Helper::generarId("PLAN");

                $empleadoModel->set_cedula($cedulaEmpleado);
                $datosEmpleado = $empleadoModel->obtenerDatos();

                if (!$datosEmpleado || !isset($datosEmpleado['cedula_empleado'])) {
                    $json['response'] = ['resultado' => 404, 'mensaje' => 'El Empleado seleccionado no existe o no está activo'];
                    $json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'Empleado no encontrado'];
                } else {
                    $turnoModel->setIdTurno((string)$_POST["id_turno"]);
                    $validarTurno = $turnoModel->Transaccion(['peticion' => 'validar']);

                    if (($validarTurno['bool'] ?? 0) === -1) {
                        $json = $validarTurno;
                    } elseif (($validarTurno['bool'] ?? 0) == 1) {
                        $horarioModel->setId($id);
                        $horarioModel->setCedulaEmpleado($cedulaEmpleado);
                        $horarioModel->setIdTurno((string)$_POST["id_turno"]);
                        $horarioModel->setFecha((string)$_POST["fecha"]);

                        $responseHorario = $horarioModel->Transaccion(['peticion' => 'registrar']);
                        $json = $responseHorario;

                        if ($responseHorario['estado'] == 1) {
                            $json['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => 'Turno asignado exitosamente'];
                            $json['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => 'Turno asignado exitosamente'];
                            Helper::Bitacora('REGISTRAR', 'HORARIO', "Se asignó el turno {$_POST['id_turno']} al empleado {$cedulaEmpleado} para la fecha {$_POST['fecha']}");
                        }
                    } else {
                        $json['response'] = ['resultado' => 404, 'mensaje' => 'El turno seleccionado no existe o está inactivo'];
                        $json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'Turno no encontrado'];
                    }
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
            }
        }

        // --- MODIFICAR ---
        if ($_POST["peticion"] == "modificar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];

            if (!$tienePermisoHorario('modificar')) {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para modificar horarios'];
            } else {
            try {
                if (!is_scalar($_POST['id_turno'] ?? null) || !is_scalar($_POST['id_planificador_turno'] ?? null)) {
                    throw new Exception('Los datos de la asignación no son válidos.');
                }
                $turnoModel->setIdTurno((string)$_POST["id_turno"]);
                $validarTurno = $turnoModel->Transaccion(['peticion' => 'validar']);

                if (($validarTurno['bool'] ?? 0) === -1) {
                    $json = $validarTurno;
                } elseif (($validarTurno['bool'] ?? 0) == 1) {
                    $horarioModel->setId((string)$_POST["id_planificador_turno"]);
                    $horarioModel->setIdTurno((string)$_POST["id_turno"]);

                    $responseHorario = $horarioModel->Transaccion([
                        'peticion' => 'modificar',
                        'cedula_empleado' => $puedeVerTodosHorarios ? null : $cedulaSesion
                    ]);
                    $json = $responseHorario;

                    if ($responseHorario['estado'] == 1) {
                        $json['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Turno actualizado exitosamente'];
                        $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'Turno actualizado exitosamente'];
                        Helper::Bitacora('MODIFICAR', 'HORARIO', "Se modificó la asignación de horario {$_POST['id_planificador_turno']} al turno {$_POST['id_turno']}");
                    }
                } else {
                    $json['response'] = ['resultado' => 404, 'mensaje' => 'El turno seleccionado no existe o está inactivo'];
                    $json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'Turno no encontrado'];
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
            }
        }

        // --- CONSULTAR ---
        if ($_POST["peticion"] == "consultar") {
            $filtros = [];
            
            $vistaSolicitada = is_string($_POST['vista'] ?? null) ? $_POST['vista'] : 'mis';
            if ($puedeVerTodosHorarios && $vistaSolicitada === 'todos') {
                if (!empty($_POST['empleado_cedula'])) {
                    $filtros['cedula_empleado'] = $_POST['empleado_cedula'];
                }
            } elseif ($cedulaSesion !== '') {
                $filtros['cedula_empleado'] = $cedulaSesion;
            } else {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Usuario no válido'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No se pudo identificar al usuario actual', 'datos' => []];
            }
            
            if (isset($_POST['fecha_inicio']) && !empty($_POST['fecha_inicio'])) {
                $filtros['fecha_inicio'] = $_POST['fecha_inicio'];
            }
            if (isset($_POST['fecha_fin']) && !empty($_POST['fecha_fin'])) {
                $filtros['fecha_fin'] = $_POST['fecha_fin'];
            }

            if (isset($json['response']['resultado']) && $json['response']['resultado'] === 403) {
                // La sesión no tiene una cédula asociada a un empleado.
            } elseif ($tienePermisoHorario('ver')) {
                $json = $horarioModel->Transaccion([
                    'peticion' => 'consultar',
                    'filtros' => $filtros
                ]);
            } else {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para consultar horarios', 'datos' => []];
            }
        }

        // --- ELIMINAR ---
        if ($_POST["peticion"] == "eliminar") {
            if (!$tienePermisoHorario('eliminar')) {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para eliminar horarios'];
            } else {
            try {
                if (!is_scalar($_POST['id_planificador_turno'] ?? null)) {
                    throw new Exception('La asignación no es válida.');
                }
                $horarioModel->setId((string)$_POST["id_planificador_turno"]);
                $json = $horarioModel->Transaccion([
                    'peticion' => 'eliminar',
                    'cedula_empleado' => $puedeVerTodosHorarios ? null : $cedulaSesion
                ]);
                if (isset($json['estado']) && $json['estado'] == 1) {
                    Helper::Bitacora('ELIMINAR', 'HORARIO', "Se eliminó la asignación de horario {$_POST['id_planificador_turno']}");
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
            }
        }

        // --- REGISTRAR POR LOTE ---
        if ($_POST["peticion"] == "registrar_lote") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];

            if (!$tienePermisoHorario('registrar')) {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para registrar horarios'];
            } else {
            try {
                $asignacionesJson = $_POST["asignaciones"] ?? '';
                if (!is_string($asignacionesJson)) {
                    throw new Exception('La lista de asignaciones no es válida.');
                }
                $asignaciones = json_decode($asignacionesJson, true);

                if (!is_array($asignaciones) || count($asignaciones) === 0) {
                    $json['response'] = ['resultado' => 400, 'mensaje' => 'Debe seleccionar al menos una fecha con turno'];
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Sin asignaciones'];
                } else {
                    if (!is_scalar($_POST['cedula_empleado'] ?? '')) {
                        throw new Exception('El empleado seleccionado no es válido.');
                    }
                    $cedulaEmpleado = $puedeVerTodosHorarios
                        ? (string)($_POST["cedula_empleado"] ?? '')
                        : $cedulaSesion;
                    if ($cedulaEmpleado === '') {
                        throw new Exception('No se pudo identificar al empleado de la sesión.');
                    }
                    $empleadoModel->set_cedula($cedulaEmpleado);
                    $datosEmpleado = $empleadoModel->obtenerDatos();

                    if (!$datosEmpleado || !isset($datosEmpleado['cedula_empleado'])) {
                        $json['response'] = ['resultado' => 404, 'mensaje' => 'El Empleado no existe'];
                        $json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'Empleado no encontrado'];
                    } else {
                        $registrosExitosos = 0;
                        $errores = [];

                        foreach ($asignaciones as $asignacion) {
                            $fecha = is_array($asignacion) && is_scalar($asignacion['fecha'] ?? null)
                                ? (string)$asignacion['fecha']
                                : 'Fecha no válida';
                            try {
                                if (!is_array($asignacion)
                                    || !is_scalar($asignacion['id_turno'] ?? null)
                                    || !is_scalar($asignacion['fecha'] ?? null)
                                ) {
                                    throw new Exception('Faltan datos de fecha o turno.');
                                }

                                $turnoModel->setIdTurno((string)$asignacion['id_turno']);
                                $validarTurno = $turnoModel->Transaccion(['peticion' => 'validar']);
                                if (($validarTurno['bool'] ?? 0) === -1) {
                                    throw new Exception($validarTurno['response']['mensaje'] ?? 'No se pudo validar el turno.');
                                }
                                if (($validarTurno['bool'] ?? 0) !== 1) {
                                    throw new Exception('El turno no existe o está inactivo.');
                                }

                                $horarioModel->setId(Helper::generarId("PLAN"));
                                $horarioModel->setCedulaEmpleado($cedulaEmpleado);
                                $horarioModel->setIdTurno((string)$asignacion['id_turno']);
                                $horarioModel->setFecha((string)$asignacion['fecha']);

                                $response = $horarioModel->Transaccion(['peticion' => 'registrar']);
                                if (($response['estado'] ?? 0) === 1) {
                                    $registrosExitosos++;
                                } else {
                                    throw new Exception($response['response']['mensaje'] ?? 'No se pudo guardar la asignación.');
                                }
                            } catch (Exception $exception) {
                                $errores[] = [
                                    'fecha' => $fecha,
                                    'mensaje' => $exception->getMessage()
                                ];
                            }
                        }

                        $hayErrores = count($errores) > 0;
                        $codigo = $hayErrores ? 207 : 201;
                        $mensajeHttp = $hayErrores ? 'Multi-Status' : 'Created';
                        $mensaje = $hayErrores
                            ? "Se asignaron {$registrosExitosos} turno(s); " . count($errores) . " fecha(s) tuvieron error."
                            : "Se asignaron {$registrosExitosos} turno(s) exitosamente";
                        $json['response'] = [
                            'resultado' => $codigo,
                            'icon' => $hayErrores ? ($registrosExitosos > 0 ? 'warning' : 'error') : 'success',
                            'mensaje' => $mensaje,
                            'exitosos' => $registrosExitosos,
                            'errores' => $errores
                        ];
                        $json['HTTP_STATUS'] = ['codigo' => $codigo, 'mensaje' => $mensajeHttp];
                        if ($registrosExitosos > 0) {
                            Helper::Bitacora('REGISTRAR LOTE', 'HORARIO', "Se asignaron {$registrosExitosos} turno(s) al empleado {$cedulaEmpleado}");
                        }
                    }
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
            }
        }

        header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
        echo json_encode($json['response']);
        exit;
    }
}

// ==========================================
// MÓDULO: EMPLEADO (Consultas para Select)
// ==========================================

if (isset($_POST["modulo"]) && $_POST["modulo"] == "Empleado") {
    if (isset($_POST["peticion"]) && $_POST["peticion"] == "consultar") {
        $json = $empleadoModel->Transaccion([
            'peticion' => 'consultar',
            'solo_sin_horario' => ($_POST['solo_sin_horario'] ?? '') === '1'
        ]);
        header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
        echo json_encode($json['response']);
        exit;
    }
}

// ==========================================
// MÓDULO: TURNO (Consultas para Select)
// ==========================================

if (isset($_POST["modulo"]) && $_POST["modulo"] == "Turno") {
    if (isset($_POST["peticion"]) && $_POST["peticion"] == "consultar") {
        $json = $turnoModel->Transaccion(['peticion' => 'consultar']);
        header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
        echo json_encode($json['response']);
        exit;
    }
}

// ==========================================
// CARGAR VISTA PRINCIPAL
// ==========================================

if (!$tienePermisoHorario('ver')) {
    header('Location: ' . BASE_URL . '?page=Dashboard');
    exit;
}

Helper::cargarVista(
    'horario/index',
    'Horarios - Good Vibes',
    [
        'ver' => $permisosHorario['horario']['ver'] ?? 0,
        'verTodos' => $puedeVerTodosHorarios,
        'permisosHorario' => $permisosHorario,
        'permisosTurno' => $permisosTurno
    ]
);