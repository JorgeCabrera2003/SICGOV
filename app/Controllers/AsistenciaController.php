<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\Asistencia;
use App\Models\System\Empleado;
use Exception;

class AsistenciaController {

	public function index() {

		Helper::verificarSesion();

		$AsistenciaModel = new Asistencia();

		if (isset($_POST['peticion'])) {
			$json = [
				'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Solicitud no válida'],
				'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Solicitud no válida']
			];

			// Entrada
			if ($_POST['peticion'] == 'entrada') {
				$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
				$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
			}

			// Registrar
			if ($_POST['peticion'] == 'registrar') {
				$accion_permiso = true;

				if ($accion_permiso) {
					$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

					try {

						$idAsistencia = Helper::generarId('ASIS');
						$horaActual = date('H:i:s');
						$fechaHoy = date('Y-m-d');
						$cedulaCompleta = trim(($_POST['tipo_doc'] ?? '') . ($_POST['cedula_empleado'] ?? ''));

						$AsistenciaModel->setIdAsistencia($idAsistencia);
						$AsistenciaModel->setCedulaEmpleado($cedulaCompleta);
						$AsistenciaModel->setTipoMarcacion($_POST['tipo_marcacion'] ?? '');
						$AsistenciaModel->setFecha($fechaHoy);
						$AsistenciaModel->setHora($horaActual);
						$AsistenciaModel->setEstado($AsistenciaModel->calcularEstadoAsistencia($_POST['tipo_marcacion'] ?? '', $horaActual));
						$AsistenciaModel->setObservacion($_POST['observacion'] ?? '');

						$json = $AsistenciaModel->Transaccion(['peticion' => 'registrar']);

						if (isset($json['estado']) && $json['estado'] == 1) {
							Helper::Bitacora('REGISTRAR', 'ASISTENCIA', "Registro de asistencia {$idAsistencia} para {$cedulaCompleta} (Tipo de Marcación: {$_POST['tipo_marcacion']} - Observaciones: {$_POST['observacion']})");
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
					$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST['peticion'] . " denegado";
				}
			}

			// Agregar observación
			if ($_POST['peticion'] == 'agregar_observacion') {
				try {
					$idAsistencia = trim($_POST['id_asistencia'] ?? '');
					$observacion = trim($_POST['observacion'] ?? '');

					if (empty($idAsistencia)) {
						throw new Exception('Identificador de asistencia inválido.');
					}
					if ($observacion === '') {
						throw new Exception('La observación no puede estar vacía.');
					}

					$AsistenciaModel->setIdAsistencia($idAsistencia);
					$AsistenciaModel->setObservacion('- ' . $observacion);

					$json = $AsistenciaModel->Transaccion(['peticion' => 'agregar_observacion']);
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

			// Eliminar observación
			if ($_POST['peticion'] == 'eliminar_observacion') {
				try {
					$idAsistencia = trim($_POST['id_asistencia'] ?? '');
					$indice = isset($_POST['indice']) ? (int) $_POST['indice'] : -1;

					if (empty($idAsistencia)) {
						throw new Exception('Identificador de asistencia inválido.');
					}
					if ($indice < 0) {
						throw new Exception('Índice de observación inválido.');
					}

					$AsistenciaModel->setIdAsistencia($idAsistencia);
					$AsistenciaModel->setIndiceObservacion($indice);

					$json = $AsistenciaModel->Transaccion(['peticion' => 'eliminar_observacion']);
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

			// Consultas
			if ($_POST['peticion'] == 'consultar' || $_POST['peticion'] == 'consultar_hoy') {
				$json = $AsistenciaModel->Transaccion(['peticion' => $_POST['peticion']]);
			}

			// Enviar respuesta con encabezado HTTP (compatibilidad con estructura de CategoriaInsumoController)
			$httpCode = $json['HTTP_STATUS']['codigo'] ?? 200;
			$httpMsg = $json['HTTP_STATUS']['mensaje'] ?? 'OK';
			header('Content-Type: application/json');
			header("HTTP/1.1 {$httpCode} {$httpMsg}");
			echo json_encode($json['response']);
			exit;
		}

		Helper::cargarVista(
			'asistencia/index',
			'Asistencia - Good Vibes'
		);
	}

	public function indexPublico() {
		$AsistenciaModel = new Asistencia();
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

					// Antes de intentar crear la asistencia, verificar que el empleado exista
					$empleadoModel = new Empleado();
					$empleadoModel->set_cedula($cedulaCompleta);
					$empleado = $empleadoModel->obtenerDatos();

					if (!$empleado) {
						$json = [
							'HTTP_STATUS' => ['codigo' => 404, 'mensaje' => 'No encontrado'],
							'response' => ['resultado' => 404, 'icon' => 'error', 'mensaje' => 'Empleado no encontrado.']
						];
					} else {
						$AsistenciaModel->setIdAsistencia($idAsistencia);
						$AsistenciaModel->setCedulaEmpleado($cedulaCompleta);
						$AsistenciaModel->setTipoMarcacion($_POST['tipo_marcacion'] ?? '');
						$AsistenciaModel->setFecha($fechaHoy);
						$AsistenciaModel->setHora($horaActual);
						$AsistenciaModel->setEstado($AsistenciaModel->calcularEstadoAsistencia($_POST['tipo_marcacion'] ?? '', $horaActual));
						$AsistenciaModel->setObservacion($_POST['observacion'] ?? '');

						$json = $AsistenciaModel->Transaccion(['peticion' => 'registrar']);
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

					$empleadoModel = new Empleado();
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
		$extra_css = [];
		$extra_js_modules = [BASE_URL . '/assets/js/Controllers/AsistenciaPublicController.js'];

		require_once BASE_PATH . '/resources/views/layout/head.php';

		$hideSidebar = true;
		$datos = $_SESSION['user'] ?? null;
		require_once BASE_PATH . '/resources/views/layout/menu.php';

		require_once BASE_PATH . '/resources/views/asistencia/public.php';

		echo '</div></main>';

		require_once BASE_PATH . '/resources/views/layout/footer.php';
	}

}

$type = $_REQUEST['type'] ?? 'index';

if ($type === 'index') {
	$controller = new AsistenciaController();
	$controller->index();
} elseif ($type === 'publico') {
	$controller = new AsistenciaController();
	$controller->indexPublico();
}