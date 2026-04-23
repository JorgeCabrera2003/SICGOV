<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Definir separador de directorios compatible con el OS (Windows \ o Linux /)
define('DS', DIRECTORY_SEPARATOR);

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

$page = $_GET['page'] ?? 'noticias';

use App\Controllers\LoginController;
use App\Controllers\LogOutController;
use App\Controllers\DashboardController;
use App\Controllers\MenuController;
use App\Controllers\ProductoController;
use App\Controllers\CategoriaController;
use App\Controllers\BitacoraController;
use App\Controllers\IngredienteController;
use App\Controllers\UsuarioController;
use App\Controllers\NoticiaController;
use App\Controllers\MediaController;
use App\Controllers\ClienteController;

try {
    match ($page) {
        'login' => (new LoginController())->index(),
        'logout' => (new LogOutController())->index(),
        'crear-cuenta' => (new LoginController())->index(),
        'home', 'dashboard' => (new DashboardController())->index(),
        'usuario', 'user' => (new UsuarioController())->index(),
        'productos' => (new ProductoController())->index(),
        'menu' => (new MenuController())->index(),
        'ingredientes' => (new IngredienteController())->index(),
        'categoria-ingrediente' => (new IngredienteController())->indexCategoria(),
        'categorias' => (new CategoriaController())->index(),
        'bitacora' => (new BitacoraController())->index(),
        'noticias-admin' => (new NoticiaController())->indexAdmin(),
        'noticias' => (new NoticiaController())->indexPublico(),
        'noticias-detalle' => (new NoticiaController())->detallePublico(),
        'multimedia' => (new MediaController())->index(),
        'clientes' => (new ClienteController())->index(),
        default => require_once BASE_PATH . '/resources/views/errors/404.php'
    };
} catch (Exception $e) {
    echo "<h1>Error en la aplicación</h1>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}