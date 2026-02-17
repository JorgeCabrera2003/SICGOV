<?php
namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Security\Usuario;
use App\Models\Security\Bitacora;
use App\Models\Security\LoginSettings;
use App\Core\Database;

class LoginController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user'])) {
            header("Location: ?page=home");
            exit();
        }
        
        $titulo = "Login - Good Vibes";
        require_once __DIR__ . '/../resources/views/auth/login.php';
    }

    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userModel = new Usuario();
        $loginSettings = new LoginSettings();

        $recaptcha = $_POST['g-recaptcha-response'] ?? '';
        if (empty($recaptcha)) {
            $_SESSION['error_login'] = "Por favor, complete el reCAPTCHA";
            header("Location: ?page=login");
            exit();
        }
        
        $secret = $loginSettings->get_recaptcha_secret();
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$recaptcha");
        $arr = json_decode($response, true);

        if (!$arr['success']) {
            $_SESSION['error_login'] = "Error en verificación reCAPTCHA";
            header("Location: ?page=login");
            exit();
        }

        if (empty($_POST['CI'] ?? '') || empty($_POST['password'] ?? '')) {
            $_SESSION['error_login'] = "Por favor, complete todos los campos";
            header("Location: ?page=login");
            exit();
        }

        $particle = $_POST['particle'] ?? 'V-';
        $ci = $_POST['CI'] ?? '';
        $cedula = $particle . $ci;
        $pass = $_POST['password'] ?? '';
        
        $userModel->set_cedula($cedula);
        $userModel->set_clave($pass);

        if ($userModel->Transaccion(['peticion' => 'sesion'])) {
            $datos = $userModel->Transaccion(['peticion' => 'perfil']);
            
            if ($datos && isset($datos['datos'])) {
                $_SESSION['user'] = $datos['datos'];
                
                $this->registrarBitacora($cedula, "Inicio de sesión exitoso");
                unset($_SESSION['error_login']);
                
                header("Location: ?page=home");
            } else {
                $_SESSION['error_login'] = "Error al cargar datos del usuario";
                header("Location: ?page=login");
            }
        } else {
            $this->registrarBitacora($cedula, "Intento fallido: Credenciales incorrectas");
            $_SESSION['error_login'] = "Cédula o contraseña incorrectos";
            header("Location: ?page=login");
        }
        exit();
    }

    private function registrarBitacora($cedula, $accion) {
        try {
            $bitacora = new Bitacora();
            $bitacora->set_usuario($cedula);
            $bitacora->set_modulo("Seguridad/Login");
            $bitacora->set_accion($accion);
            $bitacora->set_fecha(date('Y-m-d'));
            $bitacora->set_hora(date('H:i:s'));
            $bitacora->Transaccion(['peticion' => 'registrar']);
        } catch (\Exception $e) {
            error_log("Error al registrar en bitácora: " . $e->getMessage());
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        \App\Helpers\Helper::Bitacora("Cerró sesión", "Seguridad");
        
        session_unset();
        session_destroy();
        
        header("Location: ?page=login");
        exit();
    }
}