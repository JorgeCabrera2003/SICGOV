<?php

namespace App\Controllers;

use App\Helpers\Helper;

Helper::verificarSesion();

$json = [
    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Bad Request'],
    'response'    => ['resultado' => 400, 'mensaje' => 'Petición no válida o no soportada']
];

if (isset($_POST["peticion"])) {
    header('Content-Type: application/json');

    // Filtrar permisos por módulo
    if ($_POST["peticion"] === "filtrar") {
        $modulo = $_POST['modulo'] ?? null;
        $arregloPermiso = Helper::TraerPermisos($modulo);
        $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        $json['response'] = ['resultado' => 200, "permisos" => $arregloPermiso];
    }

    // Enviar respuesta al navegador usando encabezado HTTP
    header("HTTP/1.1 " . $json['HTTP_STATUS']['codigo'] . " " . $json['HTTP_STATUS']['mensaje']);
    echo json_encode($json['response']);
    exit;
}

header('Location: ?page=Dashboard');
