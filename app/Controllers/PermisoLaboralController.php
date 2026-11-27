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
$obtenerCampoTexto = static function (string $campo): string {
    $valor = $_POST[$campo] ?? '';
    if (!is_scalar($valor)) {
        throw new Exception("El campo {$campo} no es válido.");
    }
    return (string)$valor;
};
$puedeVerTodosPermisos = $tienePermisoPermisoLaboral('ver_todos');
$cedulaSesion = (string)($_SESSION['user']['cedula'] ?? '');
$puedeAccederPermiso = static function (string $id) use ($puedeVerTodosPermisos, $cedulaSesion, $permisoModel): bool {
    return $puedeVerTodosPermisos || ($cedulaSesion !== '' && $permisoModel->PerteneceAEmpleado($id, $cedulaSesion));
};
$guardarComprobante = static function (): ?string {
    if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $archivo = $_FILES['comprobante'];
    $erroresSubida = [
        UPLOAD_ERR_INI_SIZE => 'El comprobante supera el tamaño máximo permitido.',
        UPLOAD_ERR_FORM_SIZE => 'El comprobante supera el tamaño máximo permitido.',
        UPLOAD_ERR_PARTIAL => 'El comprobante se subió parcialmente.',
        UPLOAD_ERR_NO_TMP_DIR => 'No está disponible la carpeta temporal de subidas.',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo guardar el comprobante.',
        UPLOAD_ERR_EXTENSION => 'La subida del comprobante fue bloqueada por el servidor.'
    ];
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception($erroresSubida[$archivo['error']] ?? 'No se pudo recibir el comprobante.');
    }
    if (!is_uploaded_file($archivo['tmp_name'])) {
        throw new Exception('El archivo del comprobante no es válido.');
    }
    if ($archivo['size'] > 10 * 1024 * 1024) {
        throw new Exception('El comprobante no puede superar los 10 MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        throw new Exception('No se pudo validar el formato del comprobante.');
    }
    $mime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);
    $extensiones = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    if (!isset($extensiones[$mime])) {
        throw new Exception('Formato de comprobante no permitido. Usa JPG, PNG, GIF o WebP.');
    }

    $directorio = BASE_PATH . '/storage/comprobantes-permisos/';
    if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
        throw new Exception('No se pudo preparar el almacenamiento privado de comprobantes.');
    }
    $nombre = 'just_' . bin2hex(random_bytes(16)) . '.' . $extensiones[$mime];
    if (!move_uploaded_file($archivo['tmp_name'], $directorio . $nombre)) {
        throw new Exception('No se pudo guardar el comprobante.');
    }
    return $nombre;
};

if (isset($_GET['comprobante'])) {
    if (!$tienePermisoPermisoLaboral('ver')) {
        http_response_code(403);
        exit('No autorizado');
    }

    try {
        $nombreArchivo = $permisoModel->ObtenerComprobante(
            (string)$_GET['comprobante'],
            $puedeVerTodosPermisos ? null : $cedulaSesion
        );
        if ($nombreArchivo === null || !preg_match('/^just_[a-f0-9]{32}\.(jpg|png|gif|webp)$/', $nombreArchivo)) {
            http_response_code(404);
            exit('Comprobante no encontrado');
        }

        $rutaArchivo = BASE_PATH . '/storage/comprobantes-permisos/' . $nombreArchivo;
        if (!is_file($rutaArchivo)) {
            http_response_code(404);
            exit('Comprobante no encontrado');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            http_response_code(500);
            exit('No se pudo validar el comprobante');
        }
        $mime = finfo_file($finfo, $rutaArchivo);
        finfo_close($finfo);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            http_response_code(415);
            exit('Formato de comprobante no válido');
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($rutaArchivo));
        header('Content-Disposition: inline; filename="comprobante-permiso"');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($rutaArchivo);
        exit;
    } catch (Exception $e) {
        Helper::ErrorLog('Error consultando comprobante de permiso: ' . $e->getMessage());
        http_response_code(500);
        exit('No se pudo consultar el comprobante');
    }
}

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

    $accionesPorPermiso = [
        'modificar' => 'modificar',
        'eliminar' => 'eliminar',
        'aprobar' => 'aprobar_rechazar',
        'rechazar' => 'aprobar_rechazar'
    ];
    $peticionActual = is_string($_POST['peticion']) ? $_POST['peticion'] : '';
    if (
        !$puedeVerTodosPermisos
        && isset($accionesPorPermiso[$peticionActual])
        && $tienePermisoPermisoLaboral($accionesPorPermiso[$peticionActual])
        && !$puedeAccederPermiso((string)($_POST['id_permiso'] ?? ''))
    ) {
        $json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'Permiso no encontrado'];
        $json['response'] = ['resultado' => 404, 'mensaje' => 'Permiso no encontrado'];
        header("HTTP/1.1 404 Permiso no encontrado");
        echo json_encode($json['response']);
        exit;
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
            $permisoModel->setIdTipoPermiso($obtenerCampoTexto('id_tipo_permiso'));
            $permisoModel->setCedulaEmpleado(
                $puedeVerTodosPermisos ? $obtenerCampoTexto('cedula_empleado') : $cedulaSesion
            );
            $permisoModel->setFechaInicio($obtenerCampoTexto('fecha_inicio'));
            $permisoModel->setFechaFin($obtenerCampoTexto('fecha_fin'));

            $comprobanteSubido = $guardarComprobante();
            $permisoModel->setComprobante($comprobanteSubido);

            $json = $permisoModel->Transaccion(['peticion' => 'registrar']);

            if ($json['estado'] == 1) {
                $msg = "(" . $_SESSION['user']['cedula'] . "), Solicitó permiso laboral ID: " . $permisoModel->getId();
                Helper::Bitacora('REGISTRAR', 'PERMISO LABORAL', $msg);
            } elseif ($comprobanteSubido !== null) {
                $rutaComprobante = BASE_PATH . '/storage/comprobantes-permisos/' . $comprobanteSubido;
                if (is_file($rutaComprobante) && !unlink($rutaComprobante)) {
                    Helper::ErrorLog('No se pudo limpiar el comprobante de una solicitud fallida: ' . $rutaComprobante);
                }
            }
        } catch (Exception $e) {
            if (isset($comprobanteSubido) && $comprobanteSubido !== null) {
                $rutaComprobante = BASE_PATH . '/storage/comprobantes-permisos/' . $comprobanteSubido;
                if (is_file($rutaComprobante) && !unlink($rutaComprobante)) {
                    Helper::ErrorLog('No se pudo limpiar el comprobante tras un error: ' . $rutaComprobante);
                }
            }
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
            $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
        }
        }
    }

    // Consultar
    if ($_POST["peticion"] == "consultar") {
        if ($tienePermisoPermisoLaboral('ver')) {
            $vistaSolicitada = isset($_POST['vista']) && is_string($_POST['vista']) ? $_POST['vista'] : 'mis';
            $consultarTodos = $puedeVerTodosPermisos && $vistaSolicitada === 'todos';
            $json = $permisoModel->Transaccion([
                'peticion' => 'consultar',
                'cedula_empleado' => $consultarTodos ? null : $cedulaSesion
            ]);
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
            $permisoModel->setId($obtenerCampoTexto('id_permiso'));
            $permisoModel->setIdTipoPermiso($obtenerCampoTexto('id_tipo_permiso'));
            $permisoModel->setCedulaEmpleado(
                $puedeVerTodosPermisos ? $obtenerCampoTexto('cedula_empleado') : $cedulaSesion
            );
            $permisoModel->setFechaInicio($obtenerCampoTexto('fecha_inicio'));
            $permisoModel->setFechaFin($obtenerCampoTexto('fecha_fin'));
            $comprobanteSubido = $guardarComprobante();
            $permisoModel->setComprobante($comprobanteSubido);
            $json = $permisoModel->Transaccion(['peticion' => 'modificar']);
            if ($json['estado'] == 1) {
                Helper::Bitacora('MODIFICAR', 'PERMISO LABORAL', "(" . $_SESSION['user']['cedula'] . "), Actualizó permiso: " . $_POST['id_permiso']);
            } elseif ($comprobanteSubido !== null) {
                $rutaComprobante = BASE_PATH . '/storage/comprobantes-permisos/' . $comprobanteSubido;
                if (is_file($rutaComprobante) && !unlink($rutaComprobante)) {
                    Helper::ErrorLog('No se pudo limpiar el comprobante de una modificación fallida: ' . $rutaComprobante);
                }
            }
        } catch (Exception $e) {
            if (isset($comprobanteSubido) && $comprobanteSubido !== null) {
                $rutaComprobante = BASE_PATH . '/storage/comprobantes-permisos/' . $comprobanteSubido;
                if (is_file($rutaComprobante) && !unlink($rutaComprobante)) {
                    Helper::ErrorLog('No se pudo limpiar el comprobante tras un error: ' . $rutaComprobante);
                }
            }
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
            if ($puedeVerTodosPermisos) {
                $json = $empleadoModel->Transaccion(['peticion' => 'consultar']);
            } elseif ($cedulaSesion !== '') {
                $usuarioSesion = $_SESSION['user'];
                $json = [
                    'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
                    'response' => [
                        'resultado' => 200,
                        'mensaje' => 'OK',
                        'datos' => [[
                            'cedula' => $cedulaSesion,
                            'nombre' => $usuarioSesion['nombre'] ?? $usuarioSesion['username'] ?? '',
                            'apellido' => $usuarioSesion['apellido'] ?? ''
                        ]]
                    ]
                ];
            } else {
                $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Usuario no válido'];
                $json['response'] = ['resultado' => 403, 'mensaje' => 'No se pudo identificar al usuario actual', 'datos' => []];
            }
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
        'permisosTipoPermiso' => $permisosTipoPermiso,
        'puedeVerTodosPermisos' => $puedeVerTodosPermisos
    ]
);
