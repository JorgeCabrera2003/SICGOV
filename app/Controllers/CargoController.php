<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Cargo;
use Exception;

Helper::verificarSesion();

$cargoModel = new Cargo();
$permisosCargo = Helper::TraerPermisos();

if (isset($_POST["peticion"])) {

	//Entrada
	if ($_POST["peticion"] == "entrada") {
		$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
		$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
	}

	//Registrar y Modificar
	if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
		$accion_permiso = false;

		if (isset($permisosCargo['cargo']['registrar']) && $permisosCargo['cargo']['registrar'] == 1 && $_POST["peticion"] == "registrar") {
			$accion_permiso = true;
		}

		if (isset($permisosCargo['cargo']['modificar']) && $permisosCargo['cargo']['modificar'] == 1 && $_POST["peticion"] == "modificar") {
			$accion_permiso = true;
		}

		if ($accion_permiso) {
			$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

			try {
				$id = NULL;
				$str_mensaje = NULL;
				if ($_POST["peticion"] == "registrar") {
					$id = Helper::generarId("CARG");
					$str_mensaje = "registró";
				}

				if ($_POST["peticion"] == "modificar") {
					$id = $_POST["id_cargo"];
					$str_mensaje = "modificó";
				}

				$cargoModel->setId($id);
				$cargoModel->setNombre($_POST["nombre"]);
				$json = $cargoModel->Transaccion(['peticion' => $_POST["peticion"]]);
				if ($json['estado'] == 1) {
					$msg = "(" . $_SESSION['user']['cedula'] . "), Se " . $str_mensaje . " una nuevo cargo con ID:" . $cargoModel->getId();
				} else {
					$msg = "(" . $_SESSION['user']['cedula'] . "), error al " . $_POST["peticion"] . " un cargo";
				}
			} catch (Exception $exception) {
				$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
				$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
			}

		} else {
			$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
			$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un Cargo'];
			$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
		}
	}
	//Fin del Registrar o Modificar
//Consultar
	if ($_POST["peticion"] == "consultar") {
		$json = $cargoModel->Transaccion(['peticion' => $_POST["peticion"]]);
	}
	//Fin del Consultar 
//Eliminar
	if ($_POST["peticion"] == "eliminar") {
		$accion_permiso = false;

		if (isset($permisosCargo['cargo']['eliminar']) && $permisosCargo['cargo']['eliminar'] == 1) {
			$accion_permiso = true;
		}

		if ($accion_permiso) {
			$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
			$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

			try {
				$cargoModel->setId($_POST["id_cargo"]);
				$json = $cargoModel->Transaccion(['peticion' => $_POST["peticion"]]);
				if ($json['estado'] == 1) {
					$msg = "Se eliminó una cargo con el ID: " . $_POST["id_cargo"];
				} else {
					$msg = "Error al eliminar una cargo";
				}
				Helper::Bitacora('ELIMINAR', 'CARGO', $msg);
			} catch (Exception $exception) {
				$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
				$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
			}

		} else {
			$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
			$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a una cargo'];
			$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
		}
	}
	//Fin del Eliminar

	//Enviar respuesta al navegador usando un encabezado HTTP
	header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
	echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
	exit;
} //Fin de Operaciones
Helper::cargarVista(
	'cargo/index',
	'Cargos - Good Vibes',
	['ver' => $permisosCargo['cargo']['ver']]
);