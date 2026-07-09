<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Turno;
use Exception;

Helper::verificarSesion();

$turnoModel = new Turno();

if (isset($_POST["modulo"]) && $_POST["modulo"] == "Turno") {
    if (isset($_POST["peticion"])) {

        // Registrar y Modificar
        if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            
            try {
                if ($_POST["peticion"] == "registrar") {
                    $id = Helper::generarId("TUR");
                    $turnoModel->setIdTurno($id);
                }
                
                if ($_POST["peticion"] == "modificar") {
                    $turnoModel->setIdTurno($_POST['id_turno']);
                }

                $turnoModel->setNombre($_POST['nombre'] ?? '');
                $turnoModel->setHoraInicio($_POST['hora_inicio'] ?? '');
                $turnoModel->setHoraFin($_POST['hora_fin'] ?? '');
                $turnoModel->setMinutoTolerancia(intval($_POST['minuto_tolerancia'] ?? 15));
                $turnoModel->setEstatus(1);

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

        // Eliminar
        if ($_POST["peticion"] == "eliminar") {
            try {
                $turnoModel->setIdTurno($_POST['id_turno']);
                $json = $turnoModel->Transaccion(['peticion' => 'eliminar']);
            } catch (Exception $exception) {
                $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
            }
        }

        header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
        echo json_encode($json['response']);
        exit;
    }
}

Helper::cargarVista('turno/index', 'Turnos - Good Vibes');