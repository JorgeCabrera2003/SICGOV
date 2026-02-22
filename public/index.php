<?php
require_once __DIR__ . '/../vendor/autoload.php';

// ===== FIJA PARA XAMPP =====
// En XAMPP, la URL base es: http://localhost/good-vibes/public/
define('BASE_URL', 'http://localhost/good-vibes/public');
define('BASE_PATH', 'G:\\xampp\\htdocs\\good-vibes'); // Ruta absoluta en Windows

// Activar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ===== ROUTER CON MATCH =====
$page = $_GET['page'] ?? 'login';

use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\ProductoController;
use App\Controllers\CategoriaController;
use App\Controllers\BitacoraController;

try {
    match ($page) {
        'login' => (new LoginController())->index(),
        'home', 'dashboard' => (new DashboardController())->index(),
        'productos' => (new ProductoController())->index(),
        'categorias' => (new CategoriaController())->index(),
        'bitacora' => (new BitacoraController())->index(),
        default => require_once BASE_PATH . '/resources/views/errors/404.php'
    };
} catch (Exception $e) {
    echo "<h1>Error en la aplicación</h1>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}