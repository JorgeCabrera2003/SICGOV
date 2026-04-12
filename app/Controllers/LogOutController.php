<?php

namespace App\Controllers;

use App\Helpers\Helper;

class LogOutController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user'])) {
            Helper::Bitacora("Cerró sesión", "Seguridad", "El usuario cerró su sesión de forma manual.");
        }

        session_unset();
        session_destroy();

        header("Location: ?page=login");
        exit();
    }
}