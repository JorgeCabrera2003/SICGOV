<?php
namespace App\Controllers;

use App\Helpers\Helper;

class DashboardController {

    public function index() {

        Helper::verificarSesion();

        Helper::cargarVista('dashboard', 'Dashboard - Good Vibes');
    }
    
    public function indexConVariables() {
        $datos = Helper::getDatosUsuario();
        $vars = [
            'productos_recientes' => [], // Aquí irían datos del modelo
            'estadisticas' => []
        ];
        
        Helper::cargarVista('dashboard', 'Dashboard - Good Vibes', $vars);
    }
}