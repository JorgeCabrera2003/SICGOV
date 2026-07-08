<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\PermisoLaboral;
use App\Models\System\TipoPermiso;
use App\Models\System\Empleado;
use Exception;

Helper::verificarSesion();

$permisoModel = new PermisoLaboral();
$tipoModel = new TipoPermiso();
$empleadoModel = new Empleado();

$json = [
    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Solicitud no válida'],
    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Envió solicitud no válida']
];

if (isset($_POST["peticion"])) {
    $modulo = $_POST['modulo'] ?? 'PermisoLaboral';

    if ($modulo !== 'PermisoLaboral') {
        $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Módulo no válido'];
        $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Módulo no válido'];
    }

    if ($_POST["peticion"] == "entrada") {
        $json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
        $json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
    }

    // Registrar
    if ($_POST["peticion"] == "registrar") {
        try {
            $id = Helper::generarId("PERM");
            $permisoModel->setId($id);
            $permisoModel->setIdTipoPermiso($_POST['id_tipo_permiso']);
            $permisoModel->setCedulaEmpleado($_POST['cedula_empleado']);
            $permisoModel->setFechaInicio($_POST['fecha_inicio']);
            $permisoModel->setFechaFin($_POST['fecha_fin']);

            $json = $permisoModel->Transaccion(['peticion' => 'registrar']);

            if ($json['estado'] == 1) {
                $msg = "(" . $_SESSION['user']['cedula'] . "), Solicitó permiso laboral ID: " . $permisoModel->getId();
                Helper::Bitacora('REGISTRAR', 'PERMISO LABORAL', $msg);
            }
        } catch (Exception $e) {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
        }
    }

    // Consultar
    if ($_POST["peticion"] == "consultar") {
        $json = $permisoModel->Transaccion(['peticion' => 'consultar']);
    }

    if ($_POST["peticion"] == "aprobar") {
        try {
            $permisoModel->setId($_POST['id_permiso']);
            $permisoModel->setEstado('APROBADO');
            $permisoModel->setFechaAprobacion(date('Y-m-d H:i:s'));
            $json = $permisoModel->Transaccion(['peticion' => 'aprobar']);
            if ($json['estado'] == 1) {
                Helper::Bitacora('MODIFICAR', 'PERMISO LABORAL', "(" . $_SESSION['user']['cedula'] . "), Aprobó permiso: " . $_POST['id_permiso']);
            }
        } catch (Exception $e) {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
        }
    }

    if ($_POST["peticion"] == "rechazar") {
        try {
            $permisoModel->setId($_POST['id_permiso']);
            $permisoModel->setEstado('RECHAZADO');
            $permisoModel->setFechaAprobacion(date('Y-m-d H:i:s'));
            $json = $permisoModel->Transaccion(['peticion' => 'rechazar']);
            if ($json['estado'] == 1) {
                Helper::Bitacora('MODIFICAR', 'PERMISO LABORAL', "(" . $_SESSION['user']['cedula'] . "), Rechazó permiso: " . $_POST['id_permiso']);
            }
        } catch (Exception $e) {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
        }
    }

    // Modificar (ej: aprobar/rechazar)
    if ($_POST["peticion"] == "modificar") {
        try {
            $permisoModel->setId($_POST['id_permiso']);
            $permisoModel->setIdTipoPermiso($_POST['id_tipo_permiso']);
            $permisoModel->setCedulaEmpleado($_POST['cedula_empleado']);
            $permisoModel->setFechaInicio($_POST['fecha_inicio']);
            $permisoModel->setFechaFin($_POST['fecha_fin']);
            $json = $permisoModel->Transaccion(['peticion' => 'modificar']);
            if ($json['estado'] == 1) {
                Helper::Bitacora('MODIFICAR', 'PERMISO LABORAL', "(" . $_SESSION['user']['cedula'] . "), Actualizó permiso: " . $_POST['id_permiso']);
            }
        } catch (Exception $e) {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
        }
    }

    // Eliminar
    if ($_POST["peticion"] == "eliminar") {
        try {
            $permisoModel->setId($_POST['id_permiso']);
            $json = $permisoModel->Transaccion(['peticion' => 'eliminar']);
            if ($json['estado'] == 1) {
                Helper::Bitacora('ELIMINAR', 'PERMISO LABORAL', "(" . $_SESSION['user']['cedula'] . "), Eliminó permiso: " . $_POST['id_permiso']);
            }
        } catch (Exception $e) {
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
        }
    }

    // Consultar tipos de permiso (para select)
    if ($_POST["peticion"] == "consultar_tipos") {
        $json = $tipoModel->Transaccion(['peticion' => 'consultar']);
    }

    // Consultar empleados (para select)
    if ($_POST["peticion"] == "consultar_empleados") {
        $json = $empleadoModel->Transaccion(['peticion' => 'consultar']);
    }

    header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
    echo json_encode($json['response']);
    exit;
}

Helper::cargarVista(
    'permiso_laboral/index',
    'Permisos Laborales - Good Vibes'
);
