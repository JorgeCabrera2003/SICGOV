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

// ==========================================
// MÓDULO: HORARIO
// ==========================================

if (isset($_POST["modulo"]) && $_POST["modulo"] == "Horario") {
    if (isset($_POST["peticion"])) {

        // --- REGISTRAR ---
        if ($_POST["peticion"] == "registrar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];

            try {
                $id = Helper::generarId("PLAN");

                $empleadoModel->set_cedula($_POST["cedula_empleado"]);
                $datosEmpleado = $empleadoModel->obtenerDatos();

                if (!$datosEmpleado || !isset($datosEmpleado['cedula_empleado'])) {
                    $json['response'] = ['resultado' => 404, 'mensaje' => 'El Empleado seleccionado no existe o no está activo'];
                    $json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'Empleado no encontrado'];
                } else {
                    $turnoModel->setIdTurno($_POST["id_turno"]);
                    $validarTurno = $turnoModel->Transaccion(['peticion' => 'validar']);

                    if ($validarTurno['bool'] == 1) {
                        $horarioModel->setId($id);
                        $horarioModel->setCedulaEmpleado($_POST["cedula_empleado"]);
                        $horarioModel->setIdTurno($_POST["id_turno"]);
                        $horarioModel->setFecha($_POST["fecha"]);

                        $responseHorario = $horarioModel->Transaccion(['peticion' => 'registrar']);
                        $json = $responseHorario;

                        if ($responseHorario['estado'] == 1) {
                            $json['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => 'Turno asignado exitosamente'];
                            $json['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => 'Turno asignado exitosamente'];
                        }
                    } else {
                        $json['response'] = ['resultado' => 404, 'mensaje' => 'El Turno seleccionado no existe'];
                        $json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'Turno no encontrado'];
                    }
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
        }

        // --- MODIFICAR ---
        if ($_POST["peticion"] == "modificar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];

            try {
                $turnoModel->setIdTurno($_POST["id_turno"]);
                $validarTurno = $turnoModel->Transaccion(['peticion' => 'validar']);

                if ($validarTurno['bool'] == 1) {
                    $horarioModel->setId($_POST["id_planificador_turno"]);
                    $horarioModel->setIdTurno($_POST["id_turno"]);

                    $responseHorario = $horarioModel->Transaccion(['peticion' => 'modificar']);
                    $json = $responseHorario;

                    if ($responseHorario['estado'] == 1) {
                        $json['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Turno actualizado exitosamente'];
                        $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'Turno actualizado exitosamente'];
                    }
                } else {
                    $json['response'] = ['resultado' => 404, 'mensaje' => 'El Turno seleccionado no existe'];
                    $json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'Turno no encontrado'];
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
        }

        // --- CONSULTAR ---
        if ($_POST["peticion"] == "consultar") {
            $filtros = [];
            
            if (isset($_POST['empleado_cedula']) && !empty($_POST['empleado_cedula'])) {
                $filtros['cedula_empleado'] = $_POST['empleado_cedula'];
            }
            
            if (isset($_POST['fecha_inicio']) && !empty($_POST['fecha_inicio'])) {
                $filtros['fecha_inicio'] = $_POST['fecha_inicio'];
            }
            if (isset($_POST['fecha_fin']) && !empty($_POST['fecha_fin'])) {
                $filtros['fecha_fin'] = $_POST['fecha_fin'];
            }

            $json = $horarioModel->Transaccion([
                'peticion' => 'consultar',
                'filtros' => $filtros
            ]);
        }

        // --- ELIMINAR ---
        if ($_POST["peticion"] == "eliminar") {
            try {
                $horarioModel->setId($_POST["id_planificador_turno"]);
                $json = $horarioModel->Transaccion(['peticion' => 'eliminar']);
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
        }

        // --- REGISTRAR POR LOTE ---
        if ($_POST["peticion"] == "registrar_lote") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];

            try {
                $asignaciones = json_decode($_POST["asignaciones"], true);
                // DEBUG
error_log("POST recibido: " . print_r($_POST, true));
error_log("Asignaciones decodificadas: " . print_r($asignaciones, true));

                if (!is_array($asignaciones) || count($asignaciones) === 0) {
                    $json['response'] = ['resultado' => 400, 'mensaje' => 'Debe seleccionar al menos una fecha con turno'];
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Sin asignaciones'];
                } else {
                    $empleadoModel->set_cedula($_POST["cedula_empleado"]);
                    $datosEmpleado = $empleadoModel->obtenerDatos();

                    if (!$datosEmpleado || !isset($datosEmpleado['cedula_empleado'])) {
                        $json['response'] = ['resultado' => 404, 'mensaje' => 'El Empleado no existe'];
                        $json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'Empleado no encontrado'];
                    } else {
                        $registrosExitosos = 0;
                        $errores = [];

                        foreach ($asignaciones as $asignacion) {
                            // Validar turno
                            $turnoModel->setIdTurno($asignacion['id_turno']);
                            $validarTurno = $turnoModel->Transaccion(['peticion' => 'validar']);

                            if ($validarTurno['bool'] == 1) {
                                $id = Helper::generarId("PLAN");
                                $horarioModel->setId($id);
                                $horarioModel->setCedulaEmpleado($_POST["cedula_empleado"]);
                                $horarioModel->setIdTurno($asignacion['id_turno']);
                                $horarioModel->setFecha($asignacion['fecha']);

                                $response = $horarioModel->Transaccion(['peticion' => 'registrar']);
                                
                                if ($response['estado'] == 1) {
                                    $registrosExitosos++;
                                } else {
                                    $errores[] = $asignacion['fecha'];
                                }
                            }
                        }

                        $json['response'] = [
                            'resultado' => 201,
                            'icon' => 'success',
                            'mensaje' => "Se asignaron {$registrosExitosos} turno(s) exitosamente"
                        ];
                        $json['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => 'OK'];
                    }
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
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
        $json = $empleadoModel->Transaccion(['peticion' => 'consultar']);
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

Helper::cargarVista(
    'horario/index',
    'Horarios - Good Vibes'
);