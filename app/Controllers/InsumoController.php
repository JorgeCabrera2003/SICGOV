<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\CategoriaInsumo;
use App\Models\System\UnidadMedida;
use App\Models\System\Proveedor;
use App\Models\System\EntradaInsumo;
use App\Models\System\DetalleEntrada;
use App\Models\System\Insumo;
use Exception;

Helper::verificarSesion();

$insumoModel = new Insumo();
$categoriaInsumoModel = new CategoriaInsumo();
$proveedorModel = new Proveedor();
$entradaInsumoModel = new EntradaInsumo();
$detalleEntradaModel = new DetalleEntrada();
$unidadMedidaModel = new UnidadMedida();

$permisosInsumo = Helper::TraerPermisos("insumo");
$permisosCategoriaInsumo = Helper::TraerPermisos("categoria_insumo");

//Entrada
if (isset($_POST["peticion"]) && $_POST["peticion"] == "entrada") {
	$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
	$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
}

if (isset($_POST["modulo"]) && $_POST["modulo"] == "Insumo") {
	if (isset($_POST["peticion"])) {

		//Registrar y Modificar
		if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {

			$accion_permiso = false;

			if (isset($permisosInsumo["insumo"]["registrar"]) && $permisosInsumo["insumo"]["registrar"] == 1 && $_POST["peticion"] == "registrar") {
				$accion_permiso = true;
			}

			if (isset($permisosInsumo["insumo"]["modificar"]) && $permisosInsumo["insumo"]["modificar"] == 1 && $_POST["peticion"] == "modificar") {
				$accion_permiso = true;
			}
			//Validaciones
			if ($accion_permiso) {
				$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
				$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";

				try {
					$id = NULL;
					$str_mensaje = NULL;
					$validarIdCategoria = ['bool' => 0];

					$categoriaInsumoModel->setId($_POST["id_categoria"]);
					$validarIdCategoria = $categoriaInsumoModel->Transaccion(['peticion' => "validar"]);

					if ($validarIdCategoria['bool'] == 1) {
						$validarUnidadMedida = ['bool' => 0];
						$unidadMedidaModel->setId($_POST["unidad_medida"]);
						$validarUnidadMedida = $unidadMedidaModel->Transaccion(['peticion' => "validar"]);

						if ($validarUnidadMedida['bool'] == 1) {
							$boolModificar = 1;

							if ($_POST["peticion"] == "registrar") {

								$validarProveedor = ['bool' => 0];
								$proveedorModel->setDocumentoLegal($_POST['id_proveedor']);
								$validarProveedor = $proveedorModel->Transaccion(['peticion' => "validar"]);

								$boolModificar = $validarProveedor['bool'];
							}

							if ($boolModificar == 1) {

								if ($_POST["peticion"] == "registrar") {
									$id = Helper::generarId("INSUM");
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
								$responseInsumo = $insumoModel->Transaccion(['peticion' => $_POST["peticion"]]);
								$json = $responseInsumo;
								if ($responseInsumo['estado'] == 1) {
									$json['HTTP_STATUS'] = ['codigo' => 201, 'icon' => '', 'mensaje' => 'Insumo registrado exitosamente'];
									if ($_POST["peticion"] == "registrar") {
										$id_entrada = Helper::generarId("ENTRA");
										$entradaInsumoModel->setId($id_entrada);
										$entradaInsumoModel->setIdInsumo($insumoModel->getId());
										$entradaInsumoModel->setDocumentoLegal($_POST['id_proveedor']);
										$responseEntrada = $entradaInsumoModel->Transaccion(['peticion' => "registrar"]);

										if ($responseEntrada['estado'] == 1) {
											$id_detalle = Helper::generarId("DETAL");
											$detalleEntradaModel->setId($id_detalle);
											$detalleEntradaModel->setIdUnidad($_POST["unidad_medida"]);
											$detalleEntradaModel->setIdEntrada($entradaInsumoModel->getId());
											$detalleEntradaModel->setDescripcion("Ingresado por primera vez");
											$detalleEntradaModel->setCantidad($_POST["stock_inicial"]);
											$responseDetalle = $detalleEntradaModel->Transaccion(['peticion' => "registrar"]);

											if ($responseDetalle['estado'] == 1) {
												$json['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => 'Insumo registrado exitosamente'];
												$json['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => 'Insumo registrado exitosamente'];
											} else {
												$json['response'] = ['resultado' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
												$json['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
											}
										} else {
											$json['response'] = ['resultado' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
											$json['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
										}
									}
									$msg = "(" . $_SESSION['user']['cedula'] . "), Se " . $str_mensaje . " un nuevo insumo con ID:" . $insumoModel->getId();
								} else {
									$json['response'] = ['resultado' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
									$json['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
									$msg = "(" . $_SESSION['user']['cedula'] . "), error al " . $_POST["peticion"] . " un insumo";
								}
							} else {
								$json['response'] = ['resultado' => 404, 'mensaje' => 'No existe Proveedor'];
								$json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'No existe Proveedor'];
								$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";
							}
						} else {
							$json['response'] = ['resultado' => 404, 'mensaje' => 'No existe la Unidad de Medida'];
							$json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'No existe la Unidad de Medida'];
							$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";
						}
					} else {
						$json['response'] = ['resultado' => 404, 'mensaje' => 'No existe la Categoría de Insumo'];
						$json['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'No existe la Categoría de Insumo'];
						$msg = "(" . $_SESSION['user']['cedula'] . "), envió solicitud no válida";
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
			$accion_permiso = false;

			if (isset($permisosInsumo["insumo"]["eliminar"]) && $permisosInsumo["insumo"]["eliminar"] == 1) {
				$accion_permiso = true;
			}

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
}


if (isset($_POST["modulo"]) && $_POST["modulo"] == "CategoriaInsumo") {
	if (isset($_POST["peticion"])) {

		//Entrada
		if ($_POST["peticion"] == "entrada") {
			$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
			$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
		}

		//Registrar y Modificar
		if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
			$accion_permiso = false;

			if (isset($permisosCategoriaInsumo["categoria_insumo"]["registrar"]) && $permisosCategoriaInsumo["categoria_insumo"]["registrar"] == 1 && $_POST["peticion"] == "registrar") {
				$accion_permiso = true;
			}

			if (isset($permisosCategoriaInsumo["categoria_insumo"]["modificar"]) && $permisosCategoriaInsumo["categoria_insumo"]["modificar"] == 1 && $_POST["peticion"] == "modificar") {
				$accion_permiso = true;
			}

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
			$accion_permiso = false;

			if (isset($permisosCategoriaInsumo["categoria_insumo"]["eliminar"]) && $permisosCategoriaInsumo["categoria_insumo"]["eliminar"] == 1 && $_POST["peticion"] == "eliminar") {
				$accion_permiso = true;
			}

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
}


if (isset($_POST["modulo"]) && $_POST["modulo"] == "UnidadMedida") {
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
}

Helper::cargarVista(
	'insumo/index',
	'Insumos - Good Vibes',
	['ver' => $permisosInsumo['insumo']['ver']]
);