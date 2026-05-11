<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\System\Areas;

class AreasController
{
    public function index()
    {
        Helper::verificarSesion();

        $areaModel = new Areas();

        if (isset($_POST["peticion"])) {

            // Registrar
            if ($_POST["peticion"] == "registrar") {
                $accion_permiso = true;

                if ($accion_permiso) {
                    try {
                        $id = Helper::generarId("AREA");
                        
                        $areaModel->setIdArea($id);
                        $areaModel->setNombre($_POST["nombre"] ?? "");
                        $areaModel->setDescripcion($_POST["descripcion"] ?? null);
                        $areaModel->setEstatus(intval($_POST["estatus"] ?? 1));

                        $json = $areaModel->Transaccion(['peticion' => 'registrar']);

                        // Auditoría
                        if ($json['estado'] == 1) {
                            Helper::Bitacora('REGISTRAR', 'AREAS', "Se registró el área: {$_POST['nombre']} (ID: {$id})");
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
                        $areaModel->setIdArea($_POST["id_area"] ?? "");
                        $areaModel->setNombre($_POST["nombre"] ?? "");
                        $areaModel->setDescripcion($_POST["descripcion"] ?? null);
                        $areaModel->setEstatus(intval($_POST["estatus"] ?? 1));

                        $json = $areaModel->Transaccion(['peticion' => 'modificar']);

                        // Auditoría: Registrar modificación
                        if ($json['estado'] == 1) {
                            Helper::Bitacora('MODIFICAR', 'AREAS', "Se modificó el área ID: {$_POST['id_area']}");
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

            // Consultar todas las áreas
            if ($_POST["peticion"] == "consultar") {
                $json = $areaModel->Transaccion(['peticion' => 'consultar']);
            }

            // Eliminar (borrado lógico)
            if ($_POST["peticion"] == "eliminar") {
                $accion_permiso = true;
                if ($accion_permiso) {
                    try {
                        $areaModel->setIdArea($_POST["id_area"] ?? "");
                        $json = $areaModel->Transaccion(['peticion' => 'eliminar']);

                        // Auditoría
                        if ($json['estado'] == 1) {
                            Helper::Bitacora('ELIMINAR', 'AREAS', "Se eliminó el área ID: {$_POST['id_area']}");
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
            'areas/index',
            'Gestión de Áreas - Good Vibes'
        );
    }
}