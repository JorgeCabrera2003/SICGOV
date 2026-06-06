<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\CategoriaInsumo;
use App\Models\System\UnidadMedida;
use App\Models\System\Insumo;
use Exception;

class InsumoController
{
	public function index()
	{
		Helper::verificarSesion();

		$insumoModel = new Insumo();
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
					$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

					try {
						$id = NULL;
						$str_mensaje = NULL;
						//Si la petición es registrar, se generarà un ID, 
						//en caso contrario (Modificar) solo se tomará el ID enviada por el formulario
						if ($_POST["peticion"] == "registrar") {
							$id = Helper::generarId("INGR");
							$str_mensaje = "registró";
							$insumoModel->setStockActual($_POST["stock_inicial"]);
						}

						if ($_POST["peticion"] == "modificar") {
							$id = $_POST["id_insumo"];
							$str_mensaje = "modificó";
						}

						$insumoModel->setId($id);
						$insumoModel->setNombre($_POST["nombre"]);
						$insumoModel->setPrecioUnitario($_POST["costo_unitario"]);
						$insumoModel->setIdUnidadMedida($_POST["unidad_medida"]);
						$insumoModel->setIdCategoria($_POST["id_categoria"]);
						$insumoModel->setStockMaximo($_POST["stock_maximo"]);
						$insumoModel->setStockMinimo($_POST["stock_minimo"]);
						$json = $insumoModel->Transaccion(['peticion' => $_POST["peticion"]]);
						if ($json['estado'] == 1) {
							$msg = "(" . $_SESSION['user']['cedula'] . "), Se " . $str_mensaje . " un nuevo insumo con ID:" . $insumoModel->getId();
						} else {
							$msg = "(" . $_SESSION['user']['cedula'] . "), error al " . $_POST["peticion"] . " un insumo";
						}

					} catch (Exception $exception) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
						$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un insumo'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
				}
			}
			//Fin del Registrar o Modificar
//Consultar
			if ($_POST["peticion"] == "consultar") {
				$json = $insumoModel->Transaccion(['peticion' => $_POST["peticion"]]);
			}
			//Fin del Consultar 
//Eliminar
			if ($_POST["peticion"] == "eliminar") {
				$accion_permiso = true;

				try {
					if ($accion_permiso) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
						$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

						$insumoModel->setId($_POST["id_insumo"]);
						$json = $insumoModel->Transaccion(['peticion' => $_POST["peticion"]]);

						if ($json['estado'] == 1) {
							$msg = "(" . $_SESSION['user']['cedula'] . "), Se eliminó un insumo con el id:" . $_POST["id_insumo"];
						} else {
							$msg = "(" . $_SESSION['user']['cedula'] . "), error al eliminar un insumo";
						}

					} else {
						$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
						$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a un insumo'];
						$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
					}
				} catch (Exception $exception) {
					$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
					$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
				}

			}
			//Fin del Eliminar

			//Enviar respuesta al navegador usando un encabezado HTTP
			header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
			echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
			exit;
		} //Fin de Operaciones

		Helper::cargarVista(
			'insumo/index',
			'Insumos - Good Vibes'
		);
	}

	public function indexCategoria()
	{

		Helper::verificarSesion();

		$categoriaInsumoModel = new CategoriaInsumo();
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

						$categoriaInsumoModel->setId($id);
						$categoriaInsumoModel->setNombre($_POST["nombre"]);
						$json = $categoriaInsumoModel->Transaccion(['peticion' => $_POST["peticion"]]);
						if ($json['estado'] == 1) {
							$msg = "(" . $_SESSION['user']['cedula'] . "), Se " . $str_mensaje . " un nuevo insumo con ID:" . $categoriaInsumoModel->getId();
						} else {
							$msg = "(" . $_SESSION['user']['cedula'] . "), error al " . $_POST["peticion"] . " un insumo";
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
				$json = $categoriaInsumoModel->Transaccion(['peticion' => $_POST["peticion"]]);
			}
			//Fin del Consultar 
//Eliminar
			if ($_POST["peticion"] == "eliminar") {
				$accion_permiso = true;

				if ($accion_permiso) {
					$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

					try {
						$categoriaInsumoModel->setId($_POST["id_categoria"]);
						$json = $categoriaInsumoModel->Transaccion(['peticion' => $_POST["peticion"]]);
						if ($json['estado'] == 1) {
							$msg = "Se eliminó una categoría de insumo con el ID: " . $_POST["id_categoria"];
						} else {
							$msg = "Error al eliminar una categoría de insumo";
						}
						Helper::Bitacora('ELIMINAR', 'INGREDIENTE/CATEGORÍA DE INGREDIENTE', $msg);
					} catch (Exception $exception) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
						$json['response'] = ['resultado' => 400, 'mensaje' => $exception->getMessage()];
					}

				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' a una categoría de insumo'];
					$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
				}
			}
			//Fin del Eliminar

			//Enviar respuesta al navegador usando un encabezado HTTP
			header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
			echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
			exit;
		} //Fin de Operaciones
		header("Location: ?url=Dashboard");
	}

	public function indexUnidadMedida()
	{
		Helper::verificarSesion();

		$unidadMedidaModel = new UnidadMedida();
		if (isset($_POST["peticion"])) {

			//Entrada
			if ($_POST["peticion"] == "entrada") {
				$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
				$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
			}

			//Consultar
			if ($_POST["peticion"] == "consultar") {
				$json = $unidadMedidaModel->Transaccion(['peticion' => $_POST["peticion"]]);
			}

			//Enviar respuesta al navegador usando un encabezado HTTP
			header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
			echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
			exit;
		} //Fin de Operaciones

		Helper::cargarVista(
			'insumo/index',
			'Insumos - Good Vibes'
		);
	}
}