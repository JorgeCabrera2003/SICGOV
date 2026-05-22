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

        $currentPage = $_GET['page'] ?? 'login';
        $openRegisterSlide = $currentPage === 'crear-cuenta';

        if (isset($_POST['peticion'])) {

            if ($_POST['peticion'] == 'registrar') {
                $openRegisterSlide = true;
                $recaptcha = $_POST['g-recaptcha-response'] ?? '';
                if (empty($recaptcha)) {
                    $_SESSION['error_register'] = 'Por favor, complete el reCAPTCHA';
                } elseif (empty($_POST['nacionalidad'] ?? '') || empty($_POST['cedula'] ?? '') || empty($_POST['username'] ?? '') || empty($_POST['nombre'] ?? '') || empty($_POST['apellido'] ?? '') || empty($_POST['correo'] ?? '') || empty($_POST['clave'] ?? '') || empty($_POST['rclave'] ?? '')) {
                    $_SESSION['error_register'] = 'Por favor, complete todos los campos';
                } elseif ($_POST['clave'] !== $_POST['rclave']) {
                    $_SESSION['error_register'] = 'Las contraseñas no coinciden';
                } else {
                    $cedula = ($_POST['nacionalidad'] ?? '') . ($_POST['cedula'] ?? '');
                    $usuarioModel = new Usuario();
                    $usuarioModel->setCedula($cedula);
                    $usuarioModel->setIdRol('CLIE00420251001');
                    $usuarioModel->setUsername($_POST['username']);
                    $usuarioModel->setNombres($_POST['nombre']);
                    $usuarioModel->setApellidos($_POST['apellido']);
                    $usuarioModel->setTelefono($_POST['telefono'] ?? '');
                    $usuarioModel->setCorreo($_POST['correo']);
                    $usuarioModel->setClave($_POST['clave']);

                    $registro = $usuarioModel->Transaccion(['peticion' => 'registrar']);
                    if (isset($registro['estado']) && $registro['estado'] == 1) {
                        $validacion = $usuarioModel->Transaccion(['peticion' => 'sesion']);
                        if (isset($validacion['response']['verificacion']) && $validacion['response']['verificacion']) {
                            $datos = $usuarioModel->Transaccion(['peticion' => 'perfil']);
                            if ($datos && isset($datos['response']['datos'])) {
                                $_SESSION['user'] = $datos['response']['datos'];
                                unset($_SESSION['error_register']);
                                header('Location: ' . BASE_URL . '/?page=home');
                                exit();
                            }
                        }
                        $_SESSION['error_register'] = 'Usuario registrado pero no se pudo iniciar sesión automáticamente';
                    } else {
                        $_SESSION['error_register'] = $registro['response']['mensaje'] ?? 'Error al registrar usuario';
                    }
                }
            }

            if ($_POST['peticion'] == 'sesion') {
                $recaptcha = $_POST['g-recaptcha-response'] ?? '';
                if (empty($recaptcha)) {
                    $_SESSION['error_login'] = 'Por favor, complete el reCAPTCHA';
                    header('Location: ' . BASE_URL . '/?page=login');
                    exit();
                }

                if (empty($_POST['CI'] ?? '') || empty($_POST['password'] ?? '')) {
                    $_SESSION['error_login'] = 'Por favor, complete todos los campos';
                    header('Location: ' . BASE_URL . '/?page=login');
                    exit();
                }

                $particle = $_POST['particle'] ?? 'V-';
                $ci = $_POST['CI'] ?? '';
                $cedula = $particle ."-". $ci;
                $pass = $_POST['password'] ?? '';

                $usuarioModel = new Usuario();
                $usuarioModel->setCedula($cedula);
                $usuarioModel->setClave($pass);
                $validacion = $usuarioModel->Transaccion(['peticion' => 'sesion']);

                if (isset($validacion['response']['verificacion']) && $validacion['response']['verificacion']) {
                    $datos = $usuarioModel->Transaccion(['peticion' => 'perfil']);
                    if ($datos && isset($datos['response']['datos'])) {
                        $_SESSION['user'] = $datos['response']['datos'];
                        
                        // Asegurar que tenemos el estatus_clave actualizado
                        try {
                            $dbSec = \App\Core\Database::getConnection('security');
                            $stmtSt = $dbSec->prepare("SELECT estatus_clave FROM usuario WHERE cedula = ?");
                            $stmtSt->execute([$cedula]);
                            if ($resSt = $stmtSt->fetch(\PDO::FETCH_ASSOC)) {
                                $_SESSION['user']['estatus_clave'] = $resSt['estatus_clave'];
                            }
                        } catch (\Exception $e) { }

                        unset($_SESSION['error_login']);
                        header('Location: ' . BASE_URL . '/?page=home');
                    } else {
                        $_SESSION['error_login'] = 'Error al cargar datos del usuario';
                    }
                } else {
                    $_SESSION['error_login'] = 'Cédula o contraseña incorrectos';
                }
            }
        }
        $titulo = 'Login - Good Vibes';
        require_once BASE_PATH . '/resources/views/auth/login.php';
    }
}