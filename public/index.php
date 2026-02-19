<?php
require_once __DIR__ . '/../vendor/autoload.php';

// ===== CONFIGURACIÓN PARA XAMPP =====
// En XAMPP, la URL base es: http://localhost/good-vibes/public/
define('BASE_URL', 'http://localhost/good-vibes/public');
define('BASE_PATH', dirname(__DIR__));

// Para depuración - puedes comentar después
error_log("===== DEBUG INDEX.PHP =====");
error_log("BASE_URL: " . BASE_URL);
error_log("BASE_PATH: " . BASE_PATH);
error_log("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NO DEFINIDO'));
error_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NO DEFINIDO'));
error_log("Página solicitada: " . ($_GET['page'] ?? 'login'));

session_start();

use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\ProductoController;
use App\Controllers\CategoriaController; // Si existe

$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ===== MANEJO DE ACCIONES AJAX =====

// Productos
if ($page === 'productos' && !empty($action)) {
    $controller = new ProductoController();
    switch ($action) {
        case 'guardar':
            $controller->guardar();
            break;
        case 'buscar':
            $controller->buscar();
            break;
        case 'eliminar':
            $controller->eliminar();
            break;
        case 'listarJson':
            $controller->listarJson();
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
    exit();
}

if ($page === 'categorias' && !empty($action)) {
    $controller = new \App\Controllers\CategoriaController();
    switch ($action) {
        case 'listar':
            $controller->listar();
            break;
        case 'guardar':
            $controller->guardar();
            break;
        case 'eliminar':
            $controller->eliminar();
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
    exit();
}

// ===== RUTAS DE PÁGINAS =====
switch ($page) {
    case 'login':
        $controller = new LoginController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            $controller->index();
        }
        break;
        
    case 'home':
    case 'dashboard':
        $controller = new DashboardController();
        $controller->index();
        break;
        
    case 'productos':
        $controller = new ProductoController();
        $controller->index();
        break;
        
    default:
        // Página 404
        echo "<!DOCTYPE html>";
        echo "<html lang='es'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        echo "<title>404 - Página no encontrada</title>";
        echo "<link href='" . BASE_URL . "/assets/bootstrap/css/bootstrap.min.css' rel='stylesheet'>";
        echo "</head>";
        echo "<body class='bg-light'>";
        echo "<div class='container mt-5'>";
        echo "<div class='card shadow'>";
        echo "<div class='card-header bg-danger text-white'>";
        echo "<h3><i class='fas fa-exclamation-triangle me-2'></i>Error 404</h3>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<h4>La página '<strong>$page</strong>' no existe</h4>";
        echo "<p class='text-muted'>Verifica la URL o contacta al administrador.</p>";
        echo "<hr>";
        echo "<a href='" . BASE_URL . "/?page=login' class='btn btn-primary'>";
        echo "<i class='fas fa-sign-in-alt me-2'></i>Ir al Login</a>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
        break;
}