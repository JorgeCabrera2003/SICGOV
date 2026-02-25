<?php
require_once __DIR__ . '/../vendor/autoload.php';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';

$host = $_SERVER['HTTP_HOST'];

$scriptName = $_SERVER['SCRIPT_NAME'];

$basePath = rtrim(dirname($scriptName), '/\\');

define('BASE_URL', $protocol . $host . $basePath);
define('BASE_PATH', realpath(__DIR__ . '/..'));

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$page = $_GET['page'] ?? 'login';

use App\Controllers\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\ProductoController;
use App\Controllers\CategoriaController;
use App\Controllers\BitacoraController;
//use App\Controllers\IngredienteController;

try {
    match ($page) {
        'login' => (new LoginController())->index(),
        'home', 'dashboard' => (new DashboardController())->index(),
        'productos' => (new ProductoController())->index(),
        'categorias' => (new CategoriaController())->index(),
        'bitacora' => (new BitacoraController())->index(),
        // 'ingredientes' => (new IngredienteController())->index(),
        default => require_once BASE_PATH . '/resources/views/errors/404.php'
    };
} catch (Exception $e) {
    echo "<h1>Error en la aplicación</h1>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}