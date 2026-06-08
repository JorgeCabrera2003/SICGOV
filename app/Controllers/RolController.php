<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Security\Rol;
use App\Models\Security\Permiso;
use Exception;


Helper::verificarSesion();

$rolModel = new Rol();
$permisoModel = new Permiso();
$json['datos_nuevos'] = NULL;
$json['datos_anteriores'] = NULL;
if (isset($_POST["peticion"])) {

	//Entrada
	if ($_POST["peticion"] == "entrada") {
		$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
		$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
	}

	//Registrar y Modificar
	if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
		$accion_permiso = true;
		//Validaciones
		if ($accion_permiso) {
			$bool_formulario = true;
			$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
			$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

			try {
				if ($bool_formulario) {
					$array_permisos = json_decode($_POST['permisos']);
					$permisos = Helper::convertirJSON($array_permisos);


					$str_mensaje = NULL;
					$str_accion = "DESCONOCIDA";
					$contador = 0;
					if ($_POST["peticion"] == "registrar") {
						$id = Helper::generarId("ROLS");
						$str_mensaje = "registró";
						$str_accion = "REGISTRAR";
					}

					if ($_POST["peticion"] == "modificar") {
						$id = $_POST["id"];
						$str_mensaje = "modificó";
						$str_accion = "MODIFICAR";
					}

					$rolModel->setId($id);
					$rolModel->setNombre($_POST["nombre"]);
					$json = $rolModel->Transaccion(['peticion' => $_POST["peticion"]]);
					if ($json['estado'] == 1) {

						foreach ($permisos as &$i) {
							foreach ($i['permisos'] as &$j) {
								$contador++;
								if ($j['id'] == NULL) {
									$j['id'] = Helper::generarId($rolModel->getId(), $j['accion'], $contador);
								}
							}
						}
						$permisoModel->setIdRol($rolModel->getId());
						$permisoModel->Transaccion(['peticion' => 'cargar', 'permisos' => $permisos]);
						$msg = "(" . $_SESSION['user']['cedula'] . "), Se " . $str_mensaje . " un rol con el ID: " . $rolModel->getId();
					} else {
						$msg = "(" . $_SESSION['user']['cedula'] . "), error al " . $_POST["peticion"] . " un rol";
					}
					$json['response']['permisos'] = $permisos;
				}
			} catch (Exception $exception) {
				$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
				$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
			}
		} else {
			$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
			$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un rol'];
			$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
		}
		Helper::Bitacora($str_accion, 'ROL', $msg, $json['datos_anteriores'], $json['datos_nuevos']);
	}
	//Fin del Registrar o Modificar
//Consultar
	if ($_POST["peticion"] == "consultar") {
		$json = $rolModel->Transaccion(['peticion' => $_POST["peticion"]]);
	}
	//Fin del Consultar 
//Eliminar
	if ($_POST["peticion"] == "eliminar") {
		$accion_permiso = true;

		if ($accion_permiso) {
			$bool_formulario = true;
			$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
			$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";
			try {
				if ($bool_formulario) {
					$rolModel->setId($_POST["id"]);
					$json = $rolModel->Transaccion(['peticion' => $_POST["peticion"]]);

					if ($json['estado'] == 1) {
						$msg = "(" . $_SESSION['user']['cedula'] . "), Se eliminó un rol con el ID: " . $rolModel->getId();
					} else {
						$msg = "(" . $_SESSION['user']['cedula'] . "), error al eliminar un rol";
					}
				}
			} catch (Exception $exception) {
				$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
				$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
			}
		} else {
			$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
			$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un rol'];
			$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
		}
		Helper::Bitacora('ELIMINAR', 'ROL', $msg, $json['datos_anteriores'], $json['datos_nuevos']);
	}
	//Fin del Eliminar

	if($_POST["peticion"] == "filtrar_permiso"){
		$permisoModel->setIdRol($_POST['id_rol']);
		$json = $permisoModel->Transaccion(['peticion' => 'filtrar', 'parametro' => $_POST['parametro']]);
	}
	//Enviar respuesta al navegador usando un encabezado HTTP

	header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
	echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
	exit;
} //Fin de Operaciones

Helper::cargarVista(
	'rol/index',
	'Roles - Good Vibes'
);
