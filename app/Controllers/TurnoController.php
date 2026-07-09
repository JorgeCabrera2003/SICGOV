<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Turno;
use App\Models\System\PlanificadorTurno;
use Exception;

Helper::verificarSesion();

$turnoModel = new Turno();
$planModel = new PlanificadorTurno();

if (isset($_POST["modulo"]) && $_POST["modulo"] == "Turno") {
    if (isset($_POST["peticion"])) {

        // Entrada
        if ($_POST["peticion"] == "entrada") {
            $json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
            $json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
        }

        // Registrar y Modificar
        if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            try {
                if ($_POST["peticion"] == "modificar") {
                    if (isset($_POST['id_turno'])) {
                        $turnoModel->setIdTurno($_POST['id_turno']);
                    }
                }

                $turnoModel->setNombre($_POST['nombre'] ?? '');
                $turnoModel->setHoraInicio($_POST['hora_inicio'] ?? '');
                $turnoModel->setHoraFin($_POST['hora_fin'] ?? '');
                $turnoModel->setMinutoTolerancia(intval($_POST['minuto_tolerancia'] ?? 15));
                $turnoModel->setEstatus(intval($_POST['estatus'] ?? 1));

                $json = $turnoModel->Transaccion(['peticion' => $_POST["peticion"]]);
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
        }

        // Consultar
        if ($_POST["peticion"] == "consultar") {
            $json = $turnoModel->Transaccion(['peticion' => $_POST["peticion"]]);
        }

        // Planificador (turnos asignados)
        if ($_POST["peticion"] == "planificador_consultar") {
            // opcional: recibir fecha
            $fecha = $_POST['fecha'] ?? date('Y-m-d');
            $planModel->setFecha($fecha);
            $json = $planModel->Transaccion(['peticion' => 'consultar']);
        }

        if ($_POST["peticion"] == "planificador_registrar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            try {
                $planModel->setCedulaEmpleado($_POST['cedula_empleado'] ?? '');
                $planModel->setIdTurno($_POST['id_turno'] ?? '');
                $planModel->setFecha($_POST['fecha'] ?? date('Y-m-d'));
                $json = $planModel->Transaccion(['peticion' => 'registrar']);
            } catch (Exception $e) {
                $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
            }
        }

        if ($_POST["peticion"] == "planificador_modificar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            try {
                $planModel->setIdPlanificadorTurno($_POST['id_planificador_turno'] ?? '');
                $planModel->setIdTurno($_POST['id_turno'] ?? '');
                $planModel->setFecha($_POST['fecha'] ?? date('Y-m-d'));
                $json = $planModel->Transaccion(['peticion' => 'modificar']);
            } catch (Exception $e) {
                $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
            }
        }

        if ($_POST["peticion"] == "planificador_eliminar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            try {
                $planModel->setIdPlanificadorTurno($_POST['id_planificador_turno'] ?? '');
                $json = $planModel->Transaccion(['peticion' => 'eliminar']);
            } catch (Exception $e) {
                $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
            }
        }

        // Eliminar (lógico)
        if ($_POST["peticion"] == "eliminar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            try {
                if (isset($_POST['id_turno'])) {
                    $turnoModel->setIdTurno($_POST['id_turno']);
                    $json = $turnoModel->Transaccion(['peticion' => 'eliminar']);
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'ID faltante'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => 'ID de turno requerido'];
                }
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
        }

        // Enviar respuesta
        header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
        echo json_encode($json['response']);
        exit;
    }
}

// Si no es petición POST válida, mostrar la vista
Helper::cargarVista('turno/index', 'Turnos - Good Vibes');
