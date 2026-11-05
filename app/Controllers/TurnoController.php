<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Turno;
use Exception;

Helper::verificarSesion();

$turnoModel = new Turno();
$permisosTurno = Helper::TraerPermisos('turno');
$tienePermisoTurno = static function (string $accion) use ($permisosTurno): bool {
    return ($permisosTurno['turno'][$accion] ?? 0) == 1;
};

if (isset($_POST["modulo"]) && $_POST["modulo"] == "Turno") {
    if (isset($_POST["peticion"])) {

        // Registrar y Modificar
        if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];

            if (!$tienePermisoTurno($_POST["peticion"])) {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para ' . $_POST["peticion"] . ' turnos'];
            } else {
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
                if (isset($json['estado']) && $json['estado'] == 1) {
                    $accionBitacora = $_POST["peticion"] == "registrar" ? 'REGISTRAR' : 'MODIFICAR';
                    Helper::Bitacora(
                        $accionBitacora,
                        'TURNO',
                        "Se " . ($_POST["peticion"] == "registrar" ? 'registró' : 'modificó') . " el turno " . $turnoModel->getIdTurno() . " (" . ($_POST['nombre'] ?? '') . ")"
                    );
                }
                } catch (Exception $exception) {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
                }
            }
        }

        // Consultar
        if ($_POST["peticion"] == "consultar") {
            if ($tienePermisoTurno('ver')) {
                $json = $turnoModel->Transaccion(['peticion' => $_POST["peticion"]]);
            } else {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para consultar turnos', 'datos' => []];
            }
        }

        // Eliminar
        if ($_POST["peticion"] == "eliminar") {
            if (!$tienePermisoTurno('eliminar')) {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para eliminar turnos'];
            } else {
                try {
                $turnoModel->setIdTurno($_POST['id_turno']);
                $json = $turnoModel->Transaccion(['peticion' => 'eliminar']);
                if (isset($json['estado']) && $json['estado'] == 1) {
                    Helper::Bitacora('ELIMINAR', 'TURNO', "Se eliminó el turno " . $_POST['id_turno']);
                }
                } catch (Exception $exception) {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
                }
            }
        }

        header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
        echo json_encode($json['response']);
        exit;
    }
}

if (!$tienePermisoTurno('ver')) {
    header('Location: ' . BASE_URL . '?page=Dashboard');
    exit;
}

Helper::cargarVista(
    'turno/index',
    'Turnos - Good Vibes',
    [
        'ver' => $permisosTurno['turno']['ver'] ?? 0,
        'permisosTurno' => $permisosTurno
    ]
);