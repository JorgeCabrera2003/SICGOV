<?php
require_once __DIR__ . '/../vendor/autoload.php';

$scriptName = $_SERVER['SCRIPT_NAME'];
$requestUri = $_SERVER['REQUEST_URI'];

// Detectar si estamos en subdirectorio (XAMPP) o raíz (Laragon)
if (strpos($scriptName, '/public/index.php') !== false) {
    // Estamos en XAMPP: /good-vibes/public/index.php
    $basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
} else {
    $basePath = '';
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

define('BASE_URL', $protocol . $host . $basePath);
define('BASE_PATH', dirname(__DIR__));

// Para depuración - puedes comentar después
error_log("BASE_URL: " . BASE_URL);
error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);

session_start();

use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\ProductoController;

$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

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
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
    exit();
}

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
        echo "<h1>Página 404 - No encontrada</h1>";
        echo "<p>La página '<strong>$page</strong>' no existe</p>";
        echo "<p><a href='" . BASE_URL . "/?page=login'>Ir al Login</a></p>";
        break;
}