<?php

namespace App\Controllers;

use App\Helpers\Helper;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    Helper::Bitacora('SALIDA', 'SEGURIDAD', 'El usuario cerró su sesión de forma manual.');
}

$cookieParams = array();

if (ini_get('session.use_cookies')) {
    $cookieParams = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

session_unset();
session_destroy();

header('Location: ' . BASE_URL . '?page=login');
exit();
