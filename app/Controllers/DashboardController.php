<?php
namespace App\Controllers;

use App\Helpers\Helper;

$type = $_REQUEST['type'] ?? 'index';

if ($type === 'index') {
    Helper::verificarSesion();
    Helper::cargarVista('dashboard', 'Dashboard - Good Vibes');

} elseif ($type === 'variables') {
    $datos = Helper::getDatosUsuario();
    $vars = [
        'productos_recientes' => [], // Aquí irían datos del modelo
        'estadisticas' => []
    ];
    Helper::cargarVista('dashboard', 'Dashboard - Good Vibes', $vars);
}