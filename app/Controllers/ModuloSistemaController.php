<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Security\ModuloSistema;


Helper::verificarSesion();

$moduloSistemaModel = new ModuloSistema();
$permisosModulo = Helper::TraerPermisos("modulo_sistema");

$json['datos_nuevos'] = NULL;
$json['datos_anteriores'] = NULL;
if (isset($_POST["peticion"])) {

	//Entrada
	if ($_POST["peticion"] == "entrada") {
		$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
		$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
	}
	//Consultar
	if ($_POST["peticion"] == "consultar") {
		$json = $moduloSistemaModel->Transaccion(['peticion' => $_POST["peticion"]]);
	}
	//Fin del Consultar
//Comprobar
	if ($_POST["peticion"] == "comprobar") {
		$accion_permiso = false;
		if (isset($permisosModulo['modulo_sistema']['comprobar']) && $permisosModulo['modulo_sistema']['comprobar'] == 1) {
			$accion_permiso = true;
		}
		if ($accion_permiso) {
			$json = $moduloSistemaModel->Transaccion(['peticion' => $_POST["peticion"]]);
		} else {
			$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
			$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' los módulos del sistema'];
			$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
		}
	}
	//Fin del Comprobar
	//Reetablecer
	if ($_POST["peticion"] == "reestablecer") {
		$accion_permiso = false;
		if (isset($permisosModulo['modulo_sistema']['cargar']) && $permisosModulo['modulo_sistema']['cargar'] == 1) {
			$accion_permiso = true;
		}
		if ($accion_permiso) {
			$json = $moduloSistemaModel->Transaccion(['peticion' => $_POST["peticion"]]);
		} else {
			$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada: ' . $_POST["peticion"]];
			$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para ' . $_POST["peticion"] . ' los módulos del sistema'];
			$msg = "(" . $_SESSION['user']['cedula'] . "), permiso " . $_POST["peticion"] . " denegado";
		}
	}
	//Fin del Reetablecer 

	//Enviar respuesta al navegador usando un encabezado HTTP

	header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
	echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
	exit;
} //Fin de Operaciones

Helper::cargarVista(
	'modulo_sistema/index',
	'Modulos del Sistema - Good Vibes',
	['ver' => $permisosModulo['modulo_sistema']['ver']]
);