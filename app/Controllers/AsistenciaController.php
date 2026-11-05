<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\Asistencia;
use App\Models\System\Empleado;
use Exception;

$type = $_REQUEST['type'] ?? 'index';

$asistenciaModel = new Asistencia();
$empleadoModel = new Empleado();

if ($type === 'publico') {

    if (isset($_POST['peticion'])) {
        $json = [
            'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Solicitud no válida'],
            'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Solicitud no válida']
        ];

        if ($_POST['peticion'] === 'registrar') {
            try {
                $idAsistencia = Helper::generarId('ASIS');
                $horaActual = date('H:i:s');
                $fechaHoy = date('Y-m-d');
                $cedulaCompleta = trim(($_POST['tipo_doc'] ?? '') . ($_POST['cedula_empleado'] ?? ''));

                $empleadoModel->set_cedula($cedulaCompleta);
                $empleado = $empleadoModel->obtenerDatos();

                if (!$empleado) {
                    $json = [
                        'HTTP_STATUS' => ['codigo' => 404, 'mensaje' => 'No encontrado'],
                        'response' => ['resultado' => 404, 'icon' => 'error', 'mensaje' => 'Empleado no encontrado.']
                    ];
                } else {
                    $asistenciaModel->setIdAsistencia($idAsistencia);
                    $asistenciaModel->setCedulaEmpleado($cedulaCompleta);
                    $asistenciaModel->setTipoMarcacion($_POST['tipo_marcacion'] ?? '');
                    $asistenciaModel->setFecha($fechaHoy);
                    $asistenciaModel->setHora($horaActual);
                    $turnoAsignado = $asistenciaModel->obtenerTurnoAsignado();
                    $asistenciaModel->setEstado($asistenciaModel->calcularEstadoAsistencia(
                        $_POST['tipo_marcacion'] ?? '',
                        $horaActual,
                        $turnoAsignado['hora_inicio'] ?? null,
                        isset($turnoAsignado['minuto_tolerancia']) ? (int) $turnoAsignado['minuto_tolerancia'] : null,
                        $turnoAsignado['hora_fin'] ?? null
                    ));
                    $asistenciaModel->setObservacion($_POST['observacion'] ?? '');

                    $json = $asistenciaModel->Transaccion(['peticion' => 'registrar']);
                }
            } catch (Exception $e) {
                $json = [
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Datos no válidos'],
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => $e->getMessage()]
                ];
            }
        }

        if ($_POST['peticion'] === 'verificar_empleado') {
            try {
                $tipoDoc = trim($_POST['tipo_doc'] ?? '');
                $cedula = trim($_POST['cedula_empleado'] ?? '');
                if (empty($tipoDoc) || $tipoDoc === 'default' || empty($cedula)) {
                    throw new Exception('Cédula de empleado inválida.');
                }
                $cedulaCompleta = $tipoDoc . $cedula;

                $empleadoModel->set_cedula($cedulaCompleta);
                $empleado = $empleadoModel->obtenerDatos();

                if (!$empleado) {
                    throw new Exception('Empleado no encontrado.');
                }

                $json = [
                    'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
                    'response' => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Empleado encontrado.', 'datos' => $empleado]
                ];
            } catch (Exception $e) {
                $json = [
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Error'],
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => $e->getMessage()]
                ];
            }
        }

        $httpCode = $json['HTTP_STATUS']['codigo'] ?? 200;
        $httpMsg = $json['HTTP_STATUS']['mensaje'] ?? 'OK';
        header('Content-Type: application/json');
        header("HTTP/1.1 {$httpCode} {$httpMsg}");
        echo json_encode($json['response']);
        exit;
    }

    $page = 'asistencia-publica';
    $titulo = 'Asistencia Pública - Good Vibes';
    $extra_css = [
        BASE_URL . '/assets/css/landing.css?v=' . time()
    ];
    $extra_js_modules = [BASE_URL . '/assets/js/Controllers/AsistenciaPublicController.js'];

    require_once BASE_PATH . '/resources/views/layout/head.php';

    $hideSidebar = true;
    $datos = $_SESSION['user'] ?? null;
    require_once BASE_PATH . '/resources/views/layout/menu.php';

    require_once BASE_PATH . '/resources/views/asistencia/public.php';

    echo '</div></main>';

    require_once BASE_PATH . '/resources/views/layout/footer.php';

    exit;
}

Helper::verificarSesion();
$permisosAsistencia = Helper::TraerPermisos('asistencia');
$tienePermisoAsistencia = static function (string $accion) use ($permisosAsistencia): bool {
    return ($permisosAsistencia['asistencia'][$accion] ?? 0) == 1;
};

if (isset($_POST['peticion'])) {
    $json = [
        'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Solicitud no válida'],
        'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Solicitud no válida']
    ];

    if ($_POST['peticion'] == 'entrada') {
        $json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
        $json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
    }

    if ($_POST['peticion'] == 'registrar') {
        $accion_permiso = $tienePermisoAsistencia('registrar');

        if ($accion_permiso) {
            try {
                $idAsistencia = Helper::generarId('ASIS');
                $horaActual = date('H:i:s');
                $fechaHoy = date('Y-m-d');
                $cedulaCompleta = trim(($_POST['tipo_doc'] ?? '') . ($_POST['cedula_empleado'] ?? ''));

                $asistenciaModel->setIdAsistencia($idAsistencia);
                $asistenciaModel->setCedulaEmpleado($cedulaCompleta);
                $asistenciaModel->setTipoMarcacion($_POST['tipo_marcacion'] ?? '');
                $asistenciaModel->setFecha($fechaHoy);
                $asistenciaModel->setHora($horaActual);
                $turnoAsignado = $asistenciaModel->obtenerTurnoAsignado();
                $asistenciaModel->setEstado($asistenciaModel->calcularEstadoAsistencia(
                    $_POST['tipo_marcacion'] ?? '',
                    $horaActual,
                    $turnoAsignado['hora_inicio'] ?? null,
                    isset($turnoAsignado['minuto_tolerancia']) ? (int) $turnoAsignado['minuto_tolerancia'] : null,
                    $turnoAsignado['hora_fin'] ?? null
                ));
                $asistenciaModel->setObservacion($_POST['observacion'] ?? '');

                $json = $asistenciaModel->Transaccion(['peticion' => 'registrar']);

                if (isset($json['estado']) && $json['estado'] == 1) {
                    Helper::Bitacora('REGISTRAR', 'ASISTENCIA', "Registro de asistencia {$idAsistencia} para {$cedulaCompleta}");
                }
            } catch (Exception $e) {
                $json = [
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Datos no válidos'],
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => $e->getMessage()]
                ];
            }
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST['peticion']];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST['peticion'] . ' una asistencia'];
        }
    }

    if ($_POST['peticion'] == 'agregar_observacion' || $_POST['peticion'] == 'eliminar_observacion') {
        $accion_permiso = $tienePermisoAsistencia($_POST['peticion']);

        if (!$accion_permiso) {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para ' . str_replace('_', ' ', $_POST['peticion'])];
        } elseif ($_POST['peticion'] == 'agregar_observacion') {
        try {
            $idAsistencia = trim($_POST['id_asistencia'] ?? '');
            $observacion = trim($_POST['observacion'] ?? '');

            if (empty($idAsistencia)) {
                throw new Exception('Identificador de asistencia inválido.');
            }
            if ($observacion === '') {
                throw new Exception('La observación no puede estar vacía.');
            }

            $asistenciaModel->setIdAsistencia($idAsistencia);
            // La observación se pasa tal cual, sin el prefijo "- ". El modelo se encarga de formatearla.
            $asistenciaModel->setObservacion($observacion);

            $json = $asistenciaModel->Transaccion(['peticion' => 'agregar_observacion']);
            if (isset($json['estado']) && $json['estado'] == 1) {
                Helper::Bitacora('ACTUALIZAR', 'ASISTENCIA', "Agregó observación a asistencia {$idAsistencia}");
            }
        } catch (Exception $e) {
            $json = [
                'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Datos no válidos'],
                'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => $e->getMessage()]
            ];
        }
        }
    }

    if ($_POST['peticion'] == 'eliminar_observacion' && $accion_permiso) {
        try {
            $idAsistencia = trim($_POST['id_asistencia'] ?? '');
            $idObservacion = trim($_POST['id_observacion'] ?? '');

            if (empty($idAsistencia)) {
                throw new Exception('Identificador de asistencia inválido.');
            }
            if (empty($idObservacion)) {
                throw new Exception('Identificador de observación inválido.');
            }

            $asistenciaModel->setIdAsistencia($idAsistencia);
            // En lugar del índice, ahora pasamos el ID de la observación a eliminar.
            // Reutilizamos el setter setObservacion para pasar el ID.
            $asistenciaModel->setObservacion($idObservacion);

            $json = $asistenciaModel->Transaccion(['peticion' => 'eliminar_observacion']);
            if (isset($json['estado']) && $json['estado'] == 1) {
                Helper::Bitacora('ACTUALIZAR', 'ASISTENCIA', "Eliminó observación de asistencia {$idAsistencia}");
            }
        } catch (Exception $e) {
            $json = [
                'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Datos no válidos'],
                'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => $e->getMessage()]
            ];
        }
    }

    if ($_POST['peticion'] == 'consultar' || $_POST['peticion'] == 'consultar_hoy') {
        if ($tienePermisoAsistencia('ver')) {
            $json = $asistenciaModel->Transaccion(['peticion' => $_POST['peticion']]);
        } else {
            $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
            $json['response'] = ['resultado' => 403, 'mensaje' => 'No tienes permiso para consultar asistencias', 'datos' => []];
        }
    }

    header('Content-Type: application/json');
    $httpCode = $json['HTTP_STATUS']['codigo'] ?? 200;
    $httpMsg = $json['HTTP_STATUS']['mensaje'] ?? 'OK';
    header("HTTP/1.1 {$httpCode} {$httpMsg}");
    echo json_encode($json['response']);
    exit;
}

if (!$tienePermisoAsistencia('ver')) {
    header('Location: ' . BASE_URL . '?page=Dashboard');
    exit;
}

Helper::cargarVista(
    'asistencia/index',
    'Asistencia - Good Vibes',
    [
        'ver' => $permisosAsistencia['asistencia']['ver'] ?? 0,
        'permisosAsistencia' => $permisosAsistencia,
        'extra_css' => [BASE_URL . '/assets/css/asistencia.css?v=' . time()]
    ]
);