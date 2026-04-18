<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\Cliente;

class ClienteController
{
	public function index()
	{
		Helper::verificarSesion();

		$clienteModel = new Cliente();
		if (isset($_POST["peticion"])) {

			//Entrada
			if ($_POST["peticion"] == "entrada") {
				$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
				$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
			}

			//Registrar y Modificar
			if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
				$accion_permiso = true; // Aquí se podría acoplar a permisos de roles en un futuro

				//Validaciones
				if ($accion_permiso) {
					$bool_formulario = true;
					$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];

					if (!isset($_POST["cedula"]) || RegexHelper::ValidarFormatos($_POST["cedula"], 'Cedula') == 0) {
                        $json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Cédula no válida'];
                        $bool_formulario = false;
                    }
					if (!isset($_POST["nombre"]) || RegexHelper::ValidarFormatos($_POST["nombre"], "NombrePersona") == 0) {
						$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Nombre no válido'];
						$bool_formulario = false;
					}
					if (!isset($_POST["apellido"]) || RegexHelper::ValidarFormatos($_POST["apellido"], "NombrePersona") == 0) {
						$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Apellido no válido'];
						$bool_formulario = false;
					}
					if (isset($_POST["telefono"]) && $_POST["telefono"] !== '') {
						if (!preg_match('/^[0-9]{11}$/', $_POST["telefono"])) {
							$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Teléfono no válido'];
							$bool_formulario = false;
						}
					}

					//Fin de las Validaciones
					if ($bool_formulario) {
						$id = NULL;
						$str_mensaje = NULL;
						
						if ($_POST["peticion"] == "registrar") {
							$msgN = "Se registró un nuevo cliente con la cédula";
							$str_mensaje = "registró";
						}

						if ($_POST["peticion"] == "modificar") {
							$msgN = "Se modificó un cliente con la cédula: " . $_POST["cedula"];
							$str_mensaje = "modificó";
						}
						
						$clienteModel->setCedula($_POST["cedula"]);
						$clienteModel->setNombre($_POST["nombre"]);
						$clienteModel->setApellido($_POST["apellido"]);
						$clienteModel->setFechaNacimiento($_POST["fecha_nacimiento"]);
						$clienteModel->setTelefono($_POST["telefono"]);
						$clienteModel->setCorreo($_POST["correo"] ?? '');
						$clienteModel->setDireccion($_POST["direccion"] ?? '');
						$clienteModel->setSexo($_POST["sexo"] ?? '');

						$json = $clienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' un cliente'];
				}
			}
			//Fin del Registrar o Modificar
            
            //Consultar
			if ($_POST["peticion"] == "consultar") {
				$json = $clienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
			}
			//Fin del Consultar 
            
            //Eliminar
			if ($_POST["peticion"] == "eliminar") {
				$accion_permiso = true;

				if ($accion_permiso) {
					$bool_formulario = true;
					$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
					
					//Validar Cédula del formulario
					if (!isset($_POST["cedula"]) || RegexHelper::ValidarFormatos($_POST["cedula"], 'Cedula') == 0) {
						$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Cédula no válida'];
						$bool_formulario = false;
					}
					//Fin de la Validación

					if ($bool_formulario) {
						$clienteModel->setCedula($_POST["cedula"]);
						$json = $clienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' un cliente'];
				}
			}
			//Fin del Eliminar

			//Enviar respuesta al navegador usando un encabezado HTTP
			header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
			echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
			exit;
		} //Fin de Operaciones

		Helper::cargarVista(
			'cliente/index',
			'Clientes - Good Vibes'
		);
	}
}
