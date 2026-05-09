<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\Asistencia;
use Exception;

class AsistenciaController {

	public function index() {

		Helper::verificarSesion();

		$AsistenciaModel = new Asistencia();

		if (isset($_POST["peticion"])) {

			//Entrada
			if ($_POST["peticion"] == "entrada") {
				$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
				$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
			}

		//Consultar
			if ($_POST["peticion"] == "consultar") {
				$json = $AsistenciaModel->Transaccion(['peticion' => $_POST["peticion"]]);
			}
			//Fin del Consultar 

			//Enviar respuesta al navegador usando un encabezado HTTP
			header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje'] . "");
			echo json_encode($json['response']); //Conversión del Arreglo a un formato JSON
			exit;
			
		} //Fin de Operaciones

		Helper::cargarVista(
			'asistencia/index',
			'Asistencia - Good Vibes'
		);
	}

}