<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Security\ModuloSistema;


Helper::verificarSesion();

$moduloSistemaModel = new ModuloSistema();
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
		$json = $moduloSistemaModel->Transaccion(['peticion' => $_POST["peticion"]]);
	}
	//Fin del Comprobar
	//Comprobar
	if ($_POST["peticion"] == "reestablecer") {
		$json = $moduloSistemaModel->Transaccion(['peticion' => $_POST["peticion"]]);
	}
	//Fin del Comprobar 

	//Enviar respuesta al navegador usando un encabezado HTTP

	header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
	echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
	exit;
} //Fin de Operaciones

Helper::cargarVista(
	'modulo_sistema/index',
	'Modulos del Sistema - Good Vibes'
);