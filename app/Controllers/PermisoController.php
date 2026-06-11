<?php

namespace App\Controllers;

use App\Helpers\Helper;

Helper::verificarSesion();

$arrelgoPermiso = [];
if (isset($_POST["peticion"])) {

	//Filtrar
	if ($_POST["peticion"] == "filtrar") {
		$arregloPermiso = Helper::TraerPermisos($_POST['modulo']);
		$json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
		$json['response'] = ['resultado' => 200, "permisos" => $arregloPermiso];
	}

	//Enviar respuesta al navegador usando un encabezado HTTP
	header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
	echo json_encode($json['response']);
	exit;
} //Fin de Operaciones
header('Location: ?page=Dashboard');