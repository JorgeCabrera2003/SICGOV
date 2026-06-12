<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\Mesas;

Helper::verificarSesion();


        $mesaModel = new Mesas();  

        if (isset($_POST["peticion"])) {

            // Registrar
            if ($_POST["peticion"] == "registrar") {
                $accion_permiso = true;

                if ($accion_permiso) {
                    try {
                        $id = Helper::generarId("MESA");
                        
                        $mesaModel->setIdMesa($id);
                        $mesaModel->setIdArea($_POST["id_area"] ?? "");
                        $mesaModel->setNumeroMesa(intval($_POST["numero_mesa"] ?? 0));
                        $mesaModel->setCapacidad(intval($_POST["capacidad"] ?? 0));
                        $mesaModel->setEstado($_POST["estado"] ?? 'DISPONIBLE');
                        $mesaModel->setEstatus(intval($_POST["estatus"] ?? 1));

                        $json = $mesaModel->Transaccion(['peticion' => 'registrar']);

                        // Auditoría
                        if ($json['estado'] == 1) {
                            Helper::Bitacora('REGISTRAR', 'MESAS', "Se registró la mesa N°: {$_POST['numero_mesa']} (ID: {$id})");
                        }
                    } catch (\Exception $e) {
                        $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error de validación'];
                        $json['response'] = ['resultado' => 400, 'icon' => 'warning', 'mensaje' => $e->getMessage()];
                    }
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                    $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, Permiso denegado'];
                }
            }

            // Modificar
            if ($_POST["peticion"] == "modificar") {
                $accion_permiso = true;

                if ($accion_permiso) {
                    try {
                        $mesaModel->setIdMesa($_POST["id_mesa"] ?? "");
                        $mesaModel->setIdArea($_POST["id_area"] ?? "");
                        $mesaModel->setNumeroMesa(intval($_POST["numero_mesa"] ?? 0));
                        $mesaModel->setCapacidad(intval($_POST["capacidad"] ?? 0));
                        $mesaModel->setEstado($_POST["estado"] ?? 'DISPONIBLE');
                        $mesaModel->setEstatus(intval($_POST["estatus"] ?? 1));

                        // Auditoría: Capturar estado previo
                        $datos_anteriores = null;
                        $res_prev = $mesaModel->Transaccion(['peticion' => 'modificar', 'id_mesa' => $_POST["id_mesa"]]);
                        $datos_anteriores = $res_prev['response']['datos'] ?? null;

                        $json = $mesaModel->Transaccion(['peticion' => 'modificar']);

                        // Auditoría: Registrar modificación
                        if ($json['estado'] == 1) {
                            $datos_nuevos = [
                                'id_mesa' => $_POST['id_mesa'] ?? "",
                                'id_area' => $_POST['id_area'] ?? "",
                                'numero_mesa' => $_POST['numero_mesa'] ?? 0,
                                'capacidad' => $_POST['capacidad'] ?? 0,
                                'estado' => $_POST['estado'] ?? 'DISPONIBLE',
                                'estatus' => $_POST['estatus'] ?? 1
                            ];
                            Helper::Bitacora('MODIFICAR', 'MESAS', "Se modificó la mesa ID: {$_POST['id_mesa']}", $datos_anteriores, $datos_nuevos);
                        }
                    } catch (\Exception $e) {
                        $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error de validación'];
                        $json['response'] = ['resultado' => 400, 'icon' => 'warning', 'mensaje' => $e->getMessage()];
                    }
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                    $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, Permiso denegado'];
                }
            }

            // Consultar todas las mesas
            if ($_POST["peticion"] == "consultar") {
                $json = $mesaModel->Transaccion(['peticion' => 'consultar']);
            }

            if ($_POST["peticion"] == "consultar_area") {
                $json = $mesaModel->Transaccion(['peticion' => 'consultar_area']);
            }

            // Consultar mesas por área
            if ($_POST["peticion"] == "consultar_por_area") {
                try {
                    if (empty($_POST["id_area"])) {
                        throw new \Exception("ID de área no proporcionado");
                    }
                    $json = $mesaModel->Transaccion(['peticion' => 'consultar_por_area', 'id_area' => $_POST["id_area"]]);
                } catch (\Exception $e) {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error en la consulta'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
                }
            }

            // Cambiar estado de la mesa
            if ($_POST["peticion"] == "cambiar_estado") {
                try {
                    if (empty($_POST["id_mesa"]) || empty($_POST["estado"])) {
                        throw new \Exception("Datos incompletos para cambiar estado");
                    }
                    
                    $mesaModel->setIdMesa($_POST["id_mesa"]);
                    $mesaModel->setEstado($_POST["estado"]);
                    
                    $json = $mesaModel->Transaccion(['peticion' => 'cambiar_estado', 'estado' => $_POST["estado"]]);
                    
                    if ($json['estado'] == 1) {
                        Helper::Bitacora('CAMBIO ESTADO', 'MESAS', "Se cambió el estado de la mesa {$_POST['id_mesa']} a {$_POST['estado']}");
                    }
                } catch (\Exception $e) {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error al cambiar estado'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
                }
            }

            // Cambiar estatus (activar/desactivar)
            if ($_POST["peticion"] == "cambiar_estatus") {
                try {
                    if (empty($_POST["id_mesa"]) || !isset($_POST["estatus"])) {
                        throw new \Exception("Datos incompletos para cambiar estatus");
                    }
                    
                    $mesaModel->setIdMesa($_POST["id_mesa"]);
                    $mesaModel->setEstatus(intval($_POST["estatus"]));
                    
                    $json = $mesaModel->Transaccion(['peticion' => 'cambiar_estatus', 'estatus' => $_POST["estatus"]]);
                    
                    if ($json['estado'] == 1) {
                        $estatus_texto = $_POST["estatus"] == 1 ? 'activó' : 'desactivó';
                        Helper::Bitacora('CAMBIO ESTATUS', 'MESAS', "Se {$estatus_texto} la mesa {$_POST['id_mesa']}");
                    }
                } catch (\Exception $e) {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error al cambiar estatus'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
                }
            }

            // Eliminar
            if ($_POST["peticion"] == "eliminar") {
                $accion_permiso = true;
                if ($accion_permiso) {
                    try {
                        $mesaModel->setIdMesa($_POST["id_mesa"] ?? "");
                        $json = $mesaModel->Transaccion(['peticion' => 'eliminar']);

                        // Auditoría
                        if ($json['estado'] == 1) {
                            Helper::Bitacora('ELIMINAR', 'MESAS', "Se eliminó la mesa ID: {$_POST['id_mesa']}");
                        }
                    } catch (\Exception $e) {
                        $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error al eliminar'];
                        $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
                    }
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'No autorizado'];
                    $json['response'] = ['resultado' => 403, 'mensaje' => 'Permiso denegado'];
                }
            }

            // Respuesta JSON
            if (isset($json)) {
                header("HTTP/1.1 " . implode(' ', $json['HTTP_STATUS']));
                echo json_encode($json['response']);
                exit;
            }
        }

        Helper::cargarVista(
            'mesas/index',
            'Gestión de Mesas - Good Vibes',
            [
                'extra_js' => [
                    BASE_URL . '/assets/js/mesas.js'
                ]
            ]
        );