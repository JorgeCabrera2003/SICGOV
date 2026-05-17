<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Proveedor;
use Exception;

class ProveedorController
{
	public function index()
	{
		Helper::verificarSesion();

		$proveedorModel = new Proveedor();
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
							$str_mensaje = NULL;

							if ($_POST["peticion"] == "registrar") {
								$str_mensaje = "registró";
							}

							if ($_POST["peticion"] == "modificar") {
								$str_mensaje = "modificó";
							}

							$proveedorModel->setDocumentoLegal($_POST["documento_legal"]);
							$proveedorModel->setNombre($_POST["nombre"]);
							$proveedorModel->setDireccion($_POST["direccion"]);
							$proveedorModel->setCorreo($_POST["correo"]);
							$proveedorModel->setTelefono($_POST["telefono"]);
							$json = $proveedorModel->Transaccion(['peticion' => $_POST["peticion"]]);
							if ($json['estado'] == 1) {
								$msg = "(" . $_SESSION['user']['cedula'] . "), Se " . $str_mensaje . " un nuevo proveedor con el Documento Legal: " . $proveedorModel->getDocumentoLegal();
							} else {
								$msg = "(" . $_SESSION['user']['cedula'] . "), error al " . $_POST["peticion"] . " un proveedor";
							}
						}
					} catch (Exception $exception) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
						$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un proveedor'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
				}
			}
			//Fin del Registrar o Modificar
//Consultar
			if ($_POST["peticion"] == "consultar") {
				$json = $proveedorModel->Transaccion(['peticion' => $_POST["peticion"]]);
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
							$proveedorModel->setDocumentoLegal($_POST["documento_legal"]);
							$json = $proveedorModel->Transaccion(['peticion' => $_POST["peticion"]]);

							if ($json['estado'] == 1) {
								$msg = "(" . $_SESSION['user']['cedula'] . "), Se eliminó un proveedor con el Documento Legal: " . $_POST["documento_legal"];
							} else {
								$msg = "(" . $_SESSION['user']['cedula'] . "), error al eliminar un proveedor";
							}
						}
					} catch (Exception $exception) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
						$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un proveedor'];
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
			'proveedor/index',
			'Proveedores - Good Vibes'
		);
	}
}