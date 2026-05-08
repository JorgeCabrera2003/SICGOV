<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\CategoriaIngrediente;
use App\Models\System\Ingrediente;
use Exception;

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
					$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

					try {
						if ($bool_formulario) {
							$id = NULL;
							$str_mensaje = NULL;
							//Si la petición es registrar, se generarà un ID, 
							//en caso contrario (Modificar) solo se tomará el ID enviada por el formulario
							if ($_POST["peticion"] == "registrar") {
								$id = Helper::generarId("INGR");
								$str_mensaje = "registró";
							}

							if ($_POST["peticion"] == "modificar") {
								$id = $_POST["id_ingrediente"];
								$str_mensaje = "modificó";
							}

							$ingredienteModel->setId($id);
							$ingredienteModel->setNombre($_POST["nombre"]);
							$ingredienteModel->setPrecioUnitario($_POST["costo_unitario"]);
							$ingredienteModel->setUnidadMedida($_POST["unidad_medida"]);
							$json = $ingredienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
							if ($json['estado'] == 1) {
								$msg = "(" . $_SESSION['user']['cedula'] . "), Se " . $str_mensaje . " un nuevo ingrediente con ID:" . $ingredienteModel->getId();
							} else {
								$msg = "(" . $_SESSION['user']['cedula'] . "), error al " . $_POST["peticion"] . " un ingrediente";
							}
						}
					} catch (Exception $exception) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
						$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un ente'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
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
					$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";
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
							$msg = "(" . $_SESSION['user']['cedula'] . "), Se eliminó un ingrediente con el id:" . $_POST["id_ingrediente"];
						} else {
							$msg = "(" . $_SESSION['user']['cedula'] . "), error al eliminar un ingrediente";
						}
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un ente'];
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
			'ingrediente/index',
			'Ingredientes - Good Vibes'
		);
	}

	public function indexCategoria()
	{

		Helper::verificarSesion();

		$categoriaIngredienteModel = new CategoriaIngrediente();
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
					$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

					try {
						$id = NULL;
						$str_mensaje = NULL;
						if ($_POST["peticion"] == "registrar") {
							$id = Helper::generarId("INGR");
							$str_mensaje = "registró";
						}

						if ($_POST["peticion"] == "modificar") {
							$id = $_POST["id_categoria"];
							$str_mensaje = "modificó";
						}

						$categoriaIngredienteModel->setId($id);
						$categoriaIngredienteModel->setNombre($_POST["nombre"]);
						$categoriaIngredienteModel->setDescripcion($_POST["descripcion"]);
						$json = $categoriaIngredienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
						if ($json['estado'] == 1) {
							$msg = "(" . $_SESSION['user']['cedula'] . "), Se " . $str_mensaje . " un nuevo ingrediente con ID:" . $categoriaIngredienteModel->getId();
						} else {
							$msg = "(" . $_SESSION['user']['cedula'] . "), error al " . $_POST["peticion"] . " un ingrediente";
						}
					} catch (Exception $exception) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
						$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
					}

				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a una Categoría'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
				}
			}
			//Fin del Registrar o Modificar
//Consultar
			if ($_POST["peticion"] == "consultar") {
				$json = $categoriaIngredienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
			}
			//Fin del Consultar 
//Eliminar
			if ($_POST["peticion"] == "eliminar") {
				$accion_permiso = true;

				if ($accion_permiso) {
					$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

					try {
						$categoriaIngredienteModel->setId($_POST["id_categoria"]);
						$json = $categoriaIngredienteModel->Transaccion(['peticion' => $_POST["peticion"]]);
						if ($json['estado'] == 1) {
							$msg = "Se eliminó una categoría de ingrediente con el ID: " . $_POST["id_categoria"];
						} else {
							$msg = "Error al eliminar una categoría de ingrediente";
						}
						Helper::Bitacora('ELIMINAR', 'INGREDIENTE/CATEGORÍA DE INGREDIENTE', $msg);
					} catch (Exception $exception) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
						$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
					}

				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a una categoría de ingrediente'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
				}
			}
			//Fin del Eliminar

			//Enviar respuesta al navegador usando un encabezado HTTP
			header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
			echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
			exit;
		} //Fin de Operaciones
		header("Location: ?page=home");
	}
}