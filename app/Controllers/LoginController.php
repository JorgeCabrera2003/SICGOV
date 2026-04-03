<?php

namespace App\Controllers;

use App\Models\Security\Usuario;
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

        $validacion = [];
        $loginSettings = new LoginSettings();
        $siteKey = $loginSettings->get_recaptcha_sitekey();

        if (isset($_POST['peticion'])) {


            if ($_POST['peticion'] == "sesion") {
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


                $usuarioModel = new Usuario();
                $usuarioModel->setCedula($cedula);
                $usuarioModel->setClave($pass);
                $validacion = $usuarioModel->Transaccion(['peticion' => 'sesion']);

                if (isset($validacion['response']['verificacion']) && $validacion['response']['verificacion']) {
                    $datos = $usuarioModel->Transaccion(['peticion' => 'perfil']);

                    if ($datos && isset($datos['response']['datos'])) {
                        $_SESSION['user'] = $datos['response']['datos'];
                        unset($_SESSION['error_login']);

                        header("Location: " . BASE_URL . "/?page=home");
                    } else {
                        $_SESSION['error_login'] = "Error al cargar datos del usuario";
                    }
                } else {
                    $_SESSION['error_login'] = "Cédula o contraseña incorrectos";
                }
            }
        }
        $titulo = "Login - Good Vibes";
        require_once BASE_PATH . '/resources/views/auth/login.php';
    }
}