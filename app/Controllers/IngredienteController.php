<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\Ingrediente;

class IngredienteController
{
	public function index()
	{
		Helper::verificarSesion();

		$ingredienteModel = new Ingrediente();
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
					$msg = "(" . $_SESSION['user']['id_usuario'] . "), envió solicitud no válida";

					if ($_POST["peticion"] == "modificar") {
						if (!isset($_POST["id_ingrediente"]) || RegexHelper::ValidarFormatos($_POST["id_ingrediente"], 'ID') == 0) {
							$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Id no válido'];
							$bool_formulario = false;
						}
					}

					if (!isset($_POST["nombre"]) || RegexHelper::ValidarFormatos($_POST["nombre"], "NombreObjeto") == 0) {
						$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Nombre no válido'];
						$bool_formulario = false;
					}
					if (!isset($_POST["unidad_medida"])) {
						$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Unidad de Medida no válida'];
						$bool_formulario = false;
					}
					if (!isset($_POST["costo_unitario"]) || $_POST["costo_unitario"] < 0) {
						$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Costo Unitario no válido'];
						$bool_formulario = false;

					}
					//Fin de las Validaciones
					if ($bool_formulario) {
						$id = NULL;
						$str_mensaje = NULL;
						//Si la petición es registrar, se generarà un ID, 
						//en caso contrario (Modificar) solo se tomará el ID enviada por el formulario
						if ($_POST["peticion"] == "registrar") {
							$id = Helper::generarId("INGR");
							$msgN = "Se registró un nuevo ingrediente con el id";
							$str_mensaje = "registró";
						}

						if ($_POST["peticion"] == "modificar") {
							$id = $_POST["id_ingrediente"];
							$msgN = "Se modificó un ingrediente con el id: " . $id;
							$str_mensaje = "modificó";
						}
						$ingredienteModel->setId($id);
						$ingredienteModel->setNombre($_POST["nombre"]);
						$ingredienteModel->setPrecioUnitario($_POST["costo_unitario"]);
						$ingredienteModel->setUnidadMedida($_POST["unidad_medida"]);
						$json = $ingredienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
						if ($json['estado'] == 1) {
							$msg = "(" . $_SESSION['user']['id_usuario'] . "), Se ".$str_mensaje." un nuevo ingrediente con ID:" . $ingredienteModel->getId();
						} else {
							$msg = "(" . $_SESSION['user']['id_usuario'] . "), error al ".$_POST["peticion"]." un ingrediente";
						}
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un ente'];
					$msg = "(" . $_SESSION['user']['id_usuario'] . "), permiso " . $_POST["peticion"] . " denegado";
				}
			}
			//Fin del Registrar o Modificar
//Consultar
			if ($_POST["peticion"] == "consultar") {
				$json = $ingredienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
			}
			//Fin del Consultar 
//Eliminar
			if ($_POST["peticion"] == "eliminar") {
				$accion_permiso = true;

				if ($accion_permiso) {
					$bool_formulario = true;
					$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
					$msg = "(" . $_SESSION['user']['id_usuario'] . "), envió solicitud no válida";
					//Validar ID del formulario
					if (!isset($_POST["id_ingrediente"]) || RegexHelper::ValidarFormatos($_POST["id_ingrediente"], 'ID') == 0) {
						$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Id no válido'];
						$bool_formulario = false;
					}
					//Fin de la Validación
					if ($bool_formulario) {
						$ingredienteModel->setId($_POST["id_ingrediente"]);
						$json = $ingredienteModel->Transaccion(['peticion' => $_POST["peticion"]]);

						if ($json['estado'] == 1) {
							$msg = "(" . $_SESSION['user']['id_usuario'] . "), Se eliminó un ingrediente con el id:" . $_POST["id_ingrediente"];
						} else {
							$msg = "(" . $_SESSION['user']['id_usuario'] . "), error al eliminar un ingrediente";
						}
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un ente'];
					$msg = "(" . $_SESSION['user']['id_usuario'] . "), permiso " . $_POST["peticion"] . " denegado";
				}
			}
			//Fin del Eliminar

			//Enviar respuesta al navegador usando un encabezado HTTP
			header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
			echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
			exit;
		} //Fin de Operaciones

		Helper::cargarVista(
			'ingrediente/index',
			'Ingredientes - Good Vibes'
		);
	}
}