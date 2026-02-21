<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Security\Usuario;
use App\Models\Security\Bitacora;
use App\Models\Security\LoginSettings;

class LoginController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/?page=home");
            exit();
        }

        $loginSettings = new \App\Models\Security\LoginSettings();
        $siteKey = $loginSettings->get_recaptcha_sitekey();

        $titulo = "Login - Good Vibes";
        require_once BASE_PATH . '/resources/views/auth/login.php';
    }

    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validar reCAPTCHA
        $recaptcha = $_POST['g-recaptcha-response'] ?? '';
        if (empty($recaptcha)) {
            $_SESSION['error_login'] = "Por favor, complete el reCAPTCHA";
            header("Location: " . BASE_URL . "/?page=login");
            exit();
        }

        // Validar campos
        if (empty($_POST['CI'] ?? '') || empty($_POST['password'] ?? '')) {
            $_SESSION['error_login'] = "Por favor, complete todos los campos";
            header("Location: " . BASE_URL . "/?page=login");
            exit();
        }

        $particle = $_POST['particle'] ?? 'V-';
        $ci = $_POST['CI'] ?? '';
        $cedula = $particle . $ci;
        $pass = $_POST['password'] ?? '';

        $userModel = new Usuario();
        $userModel->set_cedula($cedula);
        $userModel->set_clave($pass);

        if ($userModel->Transaccion(['peticion' => 'sesion'])) {
            $datos = $userModel->Transaccion(['peticion' => 'perfil']);

            if ($datos && isset($datos['datos'])) {
                $_SESSION['user'] = $datos['datos'];
                unset($_SESSION['error_login']);

                header("Location: " . BASE_URL . "/?page=home");
            } else {
                $_SESSION['error_login'] = "Error al cargar datos del usuario";
                header("Location: " . BASE_URL . "/?page=login");
            }
        } else {
            $_SESSION['error_login'] = "Cédula o contraseña incorrectos";
            header("Location: " . BASE_URL . "/?page=login");
        }
        exit();
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user'])) {
            Helper::Bitacora("Cerró sesión", "Seguridad");
        }

        session_unset();
        session_destroy();

        header("Location: " . BASE_URL . "/?page=login");
        exit();
    }
}
