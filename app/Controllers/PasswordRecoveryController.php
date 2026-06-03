<?php

namespace App\Controllers;

use App\Models\Security\Usuario;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordRecoveryController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_POST['peticion']) && $_POST['peticion'] === 'recuperar') {
            $correo = $_POST['correo'] ?? '';
            if (empty($correo)) {
                $_SESSION['error_recovery'] = 'Por favor, ingrese su correo electrónico.';
            } else {
                $usuarioModel = new Usuario();
                $usuarioModel->setCorreo($correo);
                $validacion = $usuarioModel->Transaccion(['peticion' => 'validar']);

                if ($validacion['bool'] == 1 && isset($validacion['response']['registro']['cedula'])) {
                    $cedula = $validacion['response']['registro']['cedula'];
                    $codigo = sprintf("%06d", mt_rand(1, 999999));
                    
                    $usuarioModel->setCedula($cedula);
                    $guardado = $usuarioModel->Transaccion(['peticion' => 'guardar-codigo', 'codigo' => $codigo]);
                    
                    if (isset($guardado['estado']) && $guardado['estado'] == 1) {
                        if ($this->enviarCorreo($correo, $codigo)) {
                            $_SESSION['recovery_correo'] = $correo;
                            header('Location: ' . BASE_URL . '/?page=verificar-codigo');
                            exit();
                        } else {
                            $_SESSION['error_recovery'] = 'Error al enviar el correo. Por favor intente más tarde.';
                        }
                    } else {
                        $_SESSION['error_recovery'] = 'Error al generar el código de recuperación.';
                    }
                } else {
                    $_SESSION['error_recovery'] = 'No se encontró un usuario con ese correo electrónico.';
                }
            }
        }

        $titulo = 'Recuperar Contraseña';
        require_once BASE_PATH . '/resources/views/auth/recuperar_password.php';
    }

    public function verificar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['recovery_correo'])) {
            header('Location: ' . BASE_URL . '/?page=recuperar-password');
            exit();
        }

        if (isset($_POST['peticion']) && $_POST['peticion'] === 'verificar') {
            $codigo = $_POST['codigo'] ?? '';
            $correo = $_SESSION['recovery_correo'];
            
            if (empty($codigo)) {
                $_SESSION['error_verificar'] = 'Por favor, ingrese el código de verificación.';
            } else {
                $usuarioModel = new Usuario();
                $usuarioModel->setCorreo($correo);
                $validacion = $usuarioModel->Transaccion(['peticion' => 'validar']);
                
                if ($validacion['bool'] == 1 && isset($validacion['response']['registro']['cedula'])) {
                    $usuarioModel->setCedula($validacion['response']['registro']['cedula']);
                    $validacionCodigo = $usuarioModel->Transaccion(['peticion' => 'validar-codigo', 'codigo' => $codigo]);
                    
                    if (isset($validacionCodigo['bool']) && $validacionCodigo['bool'] == 1) {
                    $_SESSION['recovery_codigo_valido'] = $codigo;
                    header('Location: ' . BASE_URL . '/?page=restablecer-password');
                    exit();
                    } else {
                        $_SESSION['error_verificar'] = 'Código inválido o expirado.';
                    }
                } else {
                    $_SESSION['error_verificar'] = 'Error al identificar al usuario.';
                }
            }
        }

        $titulo = 'Verificar Código';
        require_once BASE_PATH . '/resources/views/auth/verificar_codigo.php';
    }

    public function restablecer()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['recovery_correo']) || !isset($_SESSION['recovery_codigo_valido'])) {
            header('Location: ' . BASE_URL . '/?page=recuperar-password');
            exit();
        }

        if (isset($_POST['peticion']) && $_POST['peticion'] === 'restablecer') {
            $clave = $_POST['clave'] ?? '';
            $rclave = $_POST['rclave'] ?? '';
            $correo = $_SESSION['recovery_correo'];
            $codigo = $_SESSION['recovery_codigo_valido'];

            if (empty($clave) || empty($rclave)) {
                $_SESSION['error_restablecer'] = 'Por favor, complete todos los campos.';
            } elseif (strlen($clave) < 8 || !preg_match('/[A-Z]/', $clave) || !preg_match('/[0-9]/', $clave) || !preg_match('/[\W_]/', $clave)) {
                $_SESSION['error_restablecer'] = 'La contraseña debe tener mínimo 8 caracteres, incluir una mayúscula, un número y un carácter especial.';
            } elseif ($clave !== $rclave) {
                $_SESSION['error_restablecer'] = 'Las contraseñas no coinciden.';
            } else {
                $usuarioModel = new Usuario();
                $usuarioModel->setCorreo($correo);
                $validacion = $usuarioModel->Transaccion(['peticion' => 'validar']);
                
                if ($validacion['bool'] == 1 && isset($validacion['response']['registro']['cedula'])) {
                    $usuarioModel->setCedula($validacion['response']['registro']['cedula']);
                    $usuarioModel->setClave($clave);
                    $resultado = $usuarioModel->Transaccion(['peticion' => 'actualizar-clave']);
                    
                    if ($resultado['estado'] == 1) {
                        $usuarioModel->Transaccion(['peticion' => 'limpiar-codigo']);
                        
                        unset($_SESSION['recovery_correo']);
                        unset($_SESSION['recovery_codigo_valido']);
                        $_SESSION['success_login'] = 'Contraseña actualizada exitosamente. Ya puede iniciar sesión.';
                        header('Location: ' . BASE_URL . '/?page=login');
                        exit();
                    } else {
                        $_SESSION['error_restablecer'] = 'Error al actualizar la contraseña.';
                    }
                } else {
                    $_SESSION['error_restablecer'] = 'Error al identificar al usuario.';
                }
            }
        }

        $titulo = 'Nueva Contraseña';
        require_once BASE_PATH . '/resources/views/auth/nueva_password.php';
    }

    private function enviarCorreo($destinatario, $codigo)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.example.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? 'user@example.com';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? 'secret';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;
            
            // Allow unverified certificates for dev (remove in prod)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            //Recipients
            $fromEmail = $_ENV['SMTP_FROM'] ?? 'noreply@example.com';
            $fromName = $_ENV['SMTP_FROM_NAME'] ?? 'SICGOV';
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($destinatario);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Codigo de Recuperacion de Contrasena';
            $mail->Body    = "<h2>Recuperación de Contraseña</h2>
                              <p>Su código de verificación es: <strong>{$codigo}</strong></p>
                              <p>Este código expirará en 15 minutos.</p>";
            $mail->AltBody = "Su código de verificación es: {$codigo}. Este código expirará en 15 minutos.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            \App\Helpers\Helper::ErrorLog("Error enviando correo PHPMailer: {$mail->ErrorInfo}");
            return false;
        }
    }
}
