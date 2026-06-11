<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Papelera;

class PapeleraController
{
    public function index()
    {
        Helper::verificarSesion();
        
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

        if (isset($_POST["peticion"]) || ($isAjax && isset($_POST['peticion']))) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');

            $papelera = new Papelera();
            $json = $papelera->Transaccion($_POST);

            header("HTTP/1.1 " . implode(' ', $json['HTTP_STATUS']));
            echo json_encode($json['response']);
            exit;
        }

        Helper::cargarVista('papelera/index', 'Papelera de Reciclaje - Good Vibes');
    }
}
