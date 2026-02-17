<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Definir constante si no está definida
if (!defined('BASE_URL')) {
    define('BASE_URL', '/good-vibes');
}

session_start();

use App\Helpers\Helper;

if (isset($_SESSION['user'])) {
    Helper::Bitacora("Cerró sesión en el sistema", "Seguridad");
    
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

header("Location: " . BASE_URL . "/?page=login"); 
exit();