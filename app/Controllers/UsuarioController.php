<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\Security\Usuario;
use App\Models\Security\Rol;

$type = $_REQUEST['type'] ?? 'index';


if ($type === 'index') {

        Helper::verificarSesion();

        $usuarioModel = new Usuario();

        if (isset($_POST["peticion"])) {
            header('Content-Type: application/json');

            if (isset($_POST["cedula"]) && $_POST["cedula"] === 'V-00000000' && in_array($_POST["peticion"], ['modificar', 'toggle-estatus', 'registrar'])) {
                header("HTTP/1.1 403 Forbidden");
                echo json_encode([
                    'resultado' => 403,
                    'icon' => 'error',
                    'mensaje' => 'Operación no permitida: el superusuario principal no puede ser modificado de ninguna forma.'
                ]);
                exit;
            }

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
                $peticion = $_POST["peticion"];



                // 1. Cédula validation format
                if (!isset($_POST["cedula"]) || RegexHelper::ValidarFormatos($_POST["cedula"], 'Cedula') == 0) {
                    $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Cédula no válida'];
                    $bool_formulario = false;
                }


                
                if ($bool_formulario) {
                    if ($peticion === 'registrar') {
                        
                        $empResult = $usuarioModel->Transaccion(['peticion' => 'empleados-sin-usuario']);
                        $validSinUsuarioCedulas = [];
                        if (isset($empResult['response']['datos']) && is_array($empResult['response']['datos'])) {
                            foreach ($empResult['response']['datos'] as $emp) {
                                if (isset($emp['cedula'])) {
                                    $validSinUsuarioCedulas[] = (string)$emp['cedula'];
                                }
                            }
                        }
                        if (!in_array((string)$_POST["cedula"], $validSinUsuarioCedulas, true)) {
                            $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => '¡Modificación detectada! El empleado seleccionado no es válido.'];
                            $bool_formulario = false;
                        }
                    } else {
                        
                        $usersList = $usuarioModel->Transaccion(['peticion' => 'consultar']);
                        $validCedulas = [];
                        if (isset($usersList['response']['datos']) && is_array($usersList['response']['datos'])) {
                            foreach ($usersList['response']['datos'] as $u) {
                                if (isset($u['cedula'])) {
                                    $validCedulas[] = (string)$u['cedula'];
                                }
                            }
                        }
                        if (!in_array((string)$_POST["cedula"], $validCedulas, true)) {
                            $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => '¡Modificación detectada! El usuario a modificar no existe.'];
                            $bool_formulario = false;
                        }
                    }
                }
                
                
                $username = isset($_POST["username"]) ? trim($_POST["username"]) : '';
                if ($bool_formulario) {
                    if (empty($username)) {
                        $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El nombre de usuario es obligatorio.'];
                        $bool_formulario = false;
                    } elseif (mb_strlen($username) < 3) {
                        $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El nombre de usuario debe tener al menos 3 letras.'];
                        $bool_formulario = false;
                    } elseif (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ]+$/u', $username)) {
                        $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El nombre de usuario debe contener solamente letras.'];
                        $bool_formulario = false;
                    }
                }

                
                if ($bool_formulario) {
                    if (!isset($_POST["rol"]) || empty(trim($_POST["rol"]))) {
                        $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El rol es obligatorio.'];
                        $bool_formulario = false;
                    } else {
                        
                        $rolModel = new Rol();
                        $rolesResult = $rolModel->Transaccion(['peticion' => 'consultar']);
                        $validRoles = [];
                        if (isset($rolesResult['response']['datos']) && is_array($rolesResult['response']['datos'])) {
                            foreach ($rolesResult['response']['datos'] as $r) {
                                if (isset($r['id_rol'])) {
                                    $validRoles[] = (string)$r['id_rol'];
                                }
                            }
                        }
                        if (!in_array((string)$_POST["rol"], $validRoles, true)) {
                            $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => '¡Modificación detectada! El rol seleccionado no es válido o está inactivo.'];
                            $bool_formulario = false;
                        }
                    }
                }

                
                if ($bool_formulario) {
                    $clave = isset($_POST["clave"]) ? $_POST["clave"] : '';
                    if ($peticion == "registrar") {
                        if (empty($clave) || strlen($clave) < 4) {
                            $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'La contraseña es obligatoria y debe tener al menos 4 caracteres.'];
                            $bool_formulario = false;
                        }
                    } else { // Modificar
                        if (!empty($clave) && strlen($clave) < 4) {
                            $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'La nueva contraseña debe tener al menos 4 caracteres.'];
                            $bool_formulario = false;
                        }
                    }
                }

                if ($bool_formulario) {
                    $usuarioModel->setCedula($_POST["cedula"]);
                    $usuarioModel->setUsername(trim($_POST["username"]));
                    $usuarioModel->setIdRol($_POST["rol"]);
                    
                    if (isset($_POST["clave"]) && !empty($_POST["clave"])) {
                        $usuarioModel->setClave($_POST["clave"], false);
                    } else {
                        $usuarioModel->setClave("", false);
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



            // ── PETICIÓN: FORZAR CAMBIO DE CLAVE ───────────────
            if ($_POST["peticion"] == "forzar-clave") {
                $bool_formulario = true;

                if (!isset($_POST["cedula"]) || RegexHelper::ValidarFormatos($_POST["cedula"], 'Cedula') == 0) {
                    $json['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Cédula no válida'];
                    $bool_formulario = false;
                }

                if ($bool_formulario) {
                    $usuarioModel->setCedula($_POST["cedula"]);
                    $json = $usuarioModel->Transaccion(['peticion' => 'forzar-clave']);

                    if (isset($json['estado']) && $json['estado'] == 1) {
                        Helper::Bitacora("MODIFICAR", "USUARIOS", "Se forzó cambio de clave al usuario con Cédula: " . $_POST["cedula"]);
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
                        if ($_POST["estatus"] == 1) {
                            Helper::Bitacora("MODIFICAR", "USUARIOS", "Se activó el usuario con Cédula: " . $_POST["cedula"]);
                        } else {
                            Helper::Bitacora("ELIMINAR", "USUARIOS", "Se eliminó (inactivó) el usuario con Cédula: " . $_POST["cedula"]);
                        }
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