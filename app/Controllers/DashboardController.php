<?php
namespace App\Controllers;

use App\Helpers\Helper;

class DashboardController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/?page=login");
            exit();
        }
        $user = $_SESSION['user'];
        $datos = [
            'nombres' => $user['nombres'] ?? $user['username'] ?? 'Usuario',
            'apellidos' => $user['apellidos'] ?? '',
            'cedula' => $user['cedula'] ?? '',
            'rol' => $user['rol'] ?? 'Usuario',
            'foto' => '/assets/img/default.jpg'
        ];
        $titulo = "Dashboard - Good Vibes";
        $page = $_GET['page'] ?? 'home';
        $tema_actual = $_SESSION['tema'] ?? 0;
        $basePath = dirname(__DIR__, 2);
        

        require_once $basePath . '/resources/views/layout/head.php';
        require_once $basePath . '/resources/views/layout/menu.php';
        require_once $basePath . '/resources/views/dashboard.php';
        require_once $basePath . '/resources/views/layout/footer.php';
    }
}