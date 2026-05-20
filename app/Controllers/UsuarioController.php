<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\Security\Usuario;
use App\Models\Security\Rol;

class UsuarioController
{
    public function index()
    {
        Helper::verificarSesion();

        $usuarioModel = new Usuario();

        if (isset($_POST["peticion"])) {
            header('Content-Type: application/json');
            $json = [];
            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Solicitud Incorrecta'];
            $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Envió solicitud no válida'];

            // ── PETICIÓN: CONSULTAR ─────────────────────────────
            if ($_POST["peticion"] == "consultar") {
                $json = $usuarioModel->Transaccion(['peticion' => 'consultar']);
                header("HTTP/1.1 " . ($json['HTTP_STATUS']['codigo'] ?? 200) . " " . ($json['HTTP_STATUS']['mensaje'] ?? "OK"));
                echo json_encode($json['response'] ?? []);
                exit;
            }

            // ── PETICIÓN: EMPLEADOS SIN USUARIO ────────────────
            if ($_POST["peticion"] == "empleados-sin-usuario") {
                $json = $usuarioModel->Transaccion(['peticion' => 'empleados-sin-usuario']);
                header("HTTP/1.1 " . ($json['HTTP_STATUS']['codigo'] ?? 200) . " " . ($json['HTTP_STATUS']['mensaje'] ?? "OK"));
                echo json_encode($json['response'] ?? []);
                exit;
            }

            // ── PETICIÓN: ROLES ACTIVOS ──────────────────────────
            if ($_POST["peticion"] == "roles-activos") {
                $rolModel = new Rol();
                $rolesResult = $rolModel->Transaccion(['peticion' => 'consultar']);
                header("HTTP/1.1 " . ($rolesResult['HTTP_STATUS']['codigo'] ?? 200) . " " . ($rolesResult['HTTP_STATUS']['mensaje'] ?? "OK"));
                echo json_encode($rolesResult['response'] ?? []);
                exit;
            }

            // ── PETICIÓN: REGISTRAR Y MODIFICAR ──────────────────
            if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
                $bool_formulario = true;
                
                // Cédula validation
                if (!isset($_POST["cedula"]) || RegexHelper::ValidarFormatos($_POST["cedula"], 'Cedula') == 0) {
                    $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Cédula no válida'];
                    $bool_formulario = false;
                }
                
                // Username validation (Al menos 4 caracteres alfanuméricos)
                if ($bool_formulario && (!isset($_POST["username"]) || strlen(trim($_POST["username"])) < 4)) {
                    $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Nombre de usuario debe tener al menos 4 caracteres'];
                    $bool_formulario = false;
                }

                // Rol validation
                if ($bool_formulario && (!isset($_POST["rol"]) || empty(trim($_POST["rol"])))) {
                    $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El rol es obligatorio'];
                    $bool_formulario = false;
                }

                // Clave validation (Registrar requiere clave de min 4 caracteres; Modificar es opcional)
                if ($bool_formulario) {
                    if ($_POST["peticion"] == "registrar") {
                        if (!isset($_POST["clave"]) || strlen($_POST["clave"]) < 4) {
                            $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'La contraseña debe tener al menos 4 caracteres'];
                            $bool_formulario = false;
                        }
                    } else { // Modificar
                        if (isset($_POST["clave"]) && !empty($_POST["clave"]) && strlen($_POST["clave"]) < 4) {
                            $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'La nueva contraseña debe tener al menos 4 caracteres'];
                            $bool_formulario = false;
                        }
                    }
                }

                if ($bool_formulario) {
                    $usuarioModel->setCedula($_POST["cedula"]);
                    $usuarioModel->setUsername(trim($_POST["username"]));
                    $usuarioModel->setIdRol($_POST["rol"]);
                    
                    if (isset($_POST["clave"]) && !empty($_POST["clave"])) {
                        $usuarioModel->setClave($_POST["clave"]);
                    } else {
                        $usuarioModel->setClave("");
                    }

                    $json = $usuarioModel->Transaccion(['peticion' => $_POST["peticion"]]);

                    if (isset($json['estado']) && $json['estado'] == 1) {
                        if ($_POST["peticion"] == "registrar") {
                            Helper::Bitacora("REGISTRAR", "USUARIOS", "Se registró un nuevo usuario con Cédula: " . $_POST["cedula"]);
                        } else {
                            Helper::Bitacora("MODIFICAR", "USUARIOS", "Se modificó el usuario con Cédula: " . $_POST["cedula"]);
                        }
                    }
                }
            }

            // ── PETICIÓN: TOGGLE ESTATUS (ACTIVAR/INACTIVAR) ───
            if ($_POST["peticion"] == "toggle-estatus") {
                $bool_formulario = true;

                if (!isset($_POST["cedula"]) || RegexHelper::ValidarFormatos($_POST["cedula"], 'Cedula') == 0) {
                    $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Cédula no válida'];
                    $bool_formulario = false;
                }

                if ($bool_formulario && (!isset($_POST["estatus"]) || !in_array((string)$_POST["estatus"], ['0', '1'], true))) {
                    $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Estatus no válido'];
                    $bool_formulario = false;
                }

                if ($bool_formulario) {
                    $usuarioModel->setCedula($_POST["cedula"]);
                    $usuarioModel->setEstatus($_POST["estatus"]);

                    $json = $usuarioModel->Transaccion(['peticion' => 'toggle-estatus']);

                    if (isset($json['estado']) && $json['estado'] == 1) {
                        $accionNombre = ($_POST["estatus"] == 1) ? "ACTIVÓ" : "INACTIVÓ";
                        Helper::Bitacora("MODIFICAR", "USUARIOS", "Se " . $accionNombre . " el usuario con Cédula: " . $_POST["cedula"]);
                    }
                }
            }

            header("HTTP/1.1 " . ($json['HTTP_STATUS']['codigo'] ?? 200) . " " . ($json['HTTP_STATUS']['mensaje'] ?? "OK"));
            echo json_encode($json['response'] ?? []);
            exit;
        }

        Helper::cargarVista(
            'usuario/index',
            'Usuarios - Good Vibes'
        );
    }
}