<?php

namespace App\Controllers;

use App\Models\Security\Usuario;
use App\Models\Security\LoginSettings;

class RegisterController
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

            if ($_POST['peticion'] == "registrar") {
                $recaptcha = $_POST['g-recaptcha-response'] ?? '';
                if (empty($recaptcha)) {
                    $_SESSION['error_register'] = "Por favor, complete el reCAPTCHA";
                    header("Location: " . BASE_URL . "/?page=crear-cuenta");
                    exit();
                }
                if (empty($_POST['nombres']) || empty($_POST['apellidos']) || empty($_POST['CI']) || empty($_POST['username']) || empty($_POST['telefono']) || empty($_POST['correo']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
                    $_SESSION['error_register'] = "Por favor, complete todos los campos";
                    header("Location: " . BASE_URL . "/?page=crear-cuenta");
                    exit();
                }

                if ($_POST['password'] !== $_POST['confirm_password']) {
                    $_SESSION['error_register'] = "Las contraseñas no coinciden";
                    header("Location: " . BASE_URL . "/?page=crear-cuenta");
                    exit();
                }

                $particle = $_POST['particle'] ?? 'V-';
                $ci = $_POST['CI'] ?? '';
                $cedula = $particle . $ci;
                $pass = $_POST['password'] ?? '';

                $usuarioModel = new Usuario();
                $usuarioModel->setCedula($cedula);
                $usuarioModel->setIdRol('CLIE00420251001');
                $usuarioModel->setUsername($_POST['username']);
                $usuarioModel->setNombres($_POST['nombres']);
                $usuarioModel->setApellidos($_POST['apellidos']);
                $usuarioModel->setTelefono($_POST['telefono']);
                $usuarioModel->setCorreo($_POST['correo']);
                $usuarioModel->setClave($pass);

                $registro = $usuarioModel->Transaccion(['peticion' => 'registrar']);

                if (isset($registro['estado']) && $registro['estado'] == 1) {
                    $usuarioModel->setClave($pass); // Reset clave para login, ya que setClave hashea
                    $validacion = $usuarioModel->Transaccion(['peticion' => 'sesion']);

                    if (isset($validacion['response']['verificacion']) && $validacion['response']['verificacion']) {
                        $datos = $usuarioModel->Transaccion(['peticion' => 'perfil']);

                        if ($datos && isset($datos['response']['datos'])) {
                            $_SESSION['user'] = $datos['response']['datos'];
                            unset($_SESSION['error_register']);
                            header("Location: " . BASE_URL . "/?page=home");
                        } else {
                            $_SESSION['error_register'] = "Error al cargar datos del usuario";
                            header("Location: " . BASE_URL . "/?page=crear-cuenta");
                        }
                    } else {
                        $_SESSION['error_register'] = "Error al iniciar sesión automáticamente";
                        header("Location: " . BASE_URL . "/?page=crear-cuenta");
                    }
                } else {
                    $_SESSION['error_register'] = $registro['response']['mensaje'] ?? "Error al registrar usuario";
                    header("Location: " . BASE_URL . "/?page=crear-cuenta");
                }
                exit();
            }

            if ($_POST['peticion'] == "sesion") {
                $recaptcha = $_POST['g-recaptcha-response'] ?? '';
                if (empty($recaptcha)) {
                    $_SESSION['error_login'] = "Por favor, complete el reCAPTCHA";
                    header("Location: " . BASE_URL . "/?page=login");
                    exit();
                }

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
        $titulo = "Registro - Good Vibes";
        require_once BASE_PATH . '/resources/views/auth/register_form.php';
    }
}