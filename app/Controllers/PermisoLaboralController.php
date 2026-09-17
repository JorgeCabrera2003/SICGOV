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
$permisosPermisoLaboral = Helper::TraerPermisos('permiso_laboral');
$permisosTipoPermiso = Helper::TraerPermisos('tipo_permiso');
$tienePermisoPermisoLaboral = static function (string $accion) use ($permisosPermisoLaboral): bool {
    return ($permisosPermisoLaboral['permiso_laboral'][$accion] ?? 0) == 1;
};

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
        if (!$tienePermisoPermisoLaboral('registrar')) {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para solicitar permisos laborales'];
        } else {
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
    }

    // Consultar
    if ($_POST["peticion"] == "consultar") {
        if ($tienePermisoPermisoLaboral('ver')) {
            $json = $permisoModel->Transaccion(['peticion' => 'consultar']);
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para consultar permisos laborales', 'datos' => []];
        }
    }

    if ($_POST["peticion"] == "aprobar") {
        if (!$tienePermisoPermisoLaboral('aprobar_rechazar')) {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para aprobar permisos laborales'];
        } else {
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
    }

    if ($_POST["peticion"] == "rechazar") {
        if (!$tienePermisoPermisoLaboral('aprobar_rechazar')) {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para rechazar permisos laborales'];
        } else {
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
    }

    // Modificar (ej: aprobar/rechazar)
    if ($_POST["peticion"] == "modificar") {
        if (!$tienePermisoPermisoLaboral('modificar')) {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para modificar permisos laborales'];
        } else {
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
    }

    // Eliminar
    if ($_POST["peticion"] == "eliminar") {
        if (!$tienePermisoPermisoLaboral('eliminar')) {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para eliminar permisos laborales'];
        } else {
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
    }

    // Consultar tipos de permiso (para select)
    if ($_POST["peticion"] == "consultar_tipos") {
        if ($tienePermisoPermisoLaboral('ver') || $tienePermisoPermisoLaboral('registrar')) {
            $json = $tipoModel->Transaccion(['peticion' => 'consultar']);
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para cargar Tipo de Permiso', 'datos' => []];
        }
    }

    // Consultar empleados (para select)
    if ($_POST["peticion"] == "consultar_empleados") {
        if ($tienePermisoPermisoLaboral('ver') || $tienePermisoPermisoLaboral('registrar')) {
            $json = $empleadoModel->Transaccion(['peticion' => 'consultar']);
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para cargar empleados', 'datos' => []];
        }
    }

    header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
    echo json_encode($json['response']);
    exit;
}

if (!$tienePermisoPermisoLaboral('ver')) {
    header('Location: ' . BASE_URL . '?page=Dashboard');
    exit;
}

Helper::cargarVista(
    'permiso_laboral/index',
    'Permisos Laborales - Good Vibes',
    [
        'ver' => $permisosPermisoLaboral['permiso_laboral']['ver'] ?? 0,
        'permisosPermisoLaboral' => $permisosPermisoLaboral,
        'permisosTipoPermiso' => $permisosTipoPermiso
    ]
);
