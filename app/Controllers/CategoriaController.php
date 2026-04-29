<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\CategoriaProducto;

class CategoriaController
{
    public function listar()
    {
        header('Content-Type: application/json');

        try {
            if (!Helper::verificarSesion()) {
                echo json_encode([]);
                exit();
            }

            $categoria = new CategoriaProducto();
            $categorias = $categoria->Transaccion(['peticion' => 'listar']);

            echo json_encode($categorias ?: []);
            
        } catch (\Exception $e) {
            error_log("Error en listar categorías: " . $e->getMessage());
            echo json_encode([]);
        }
        exit();
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        try {
            if (!Helper::verificarSesion()) {
                echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
                exit();
            }

            $categoria = new CategoriaProducto();
            $categoria->setNombreCategoria($_POST['nombre'] ?? '');
            $categoria->setDescripcion($_POST['descripcion'] ?? '');
            
            $result = $categoria->Transaccion(['peticion' => 'guardar']);

            if ($result['success']) {
                Helper::Bitacora("REGISTRAR", "CATEGORIAS", "Se registró la categoría de productos: " . $_POST['nombre']);
            }

            echo json_encode($result);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        try {
            if (!Helper::verificarSesion()) {
                echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
                exit();
            }

            $categoria = new CategoriaProducto();
            $categoria->setIdCategoria($_POST['id'] ?? '');
            $result = $categoria->Transaccion(['peticion' => 'eliminar']);

            if ($result['success']) {
                Helper::Bitacora("ELIMINAR", "CATEGORIAS", "Se eliminó la categoría con ID: " . $_POST['id']);
            }

            echo json_encode($result);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    public function index()
    {
        Helper::verificarSesion();

        $categoriaModel = new CategoriaProducto();

        if (isset($_POST["peticion"])) {

            // Entrada
            if ($_POST["peticion"] == "entrada") {
                $json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => ''];
                $json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
            }

            // Registrar y Modificar
            if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
                $accion_permiso = true; 

                if ($accion_permiso) {
                    try {
                        $categoriaModel->setNombreCategoria($_POST["nombre_categoria"] ?? '');
                        $categoriaModel->setDescripcion($_POST["descripcion"] ?? '');
                        $categoriaModel->setIcono('default.png');
                        
                        if ($_POST["peticion"] == "modificar") {
                            // En actualizar, se requiere el estatus
                            $categoriaModel->setIdCategoria($_POST["id_categoria"] ?? '');
                            $categoriaModel->setEstatus($_POST["estatus"] ?? 1); 
                            $jsonResult = $categoriaModel->Transaccion(['peticion' => 'actualizar']);
                        } else {
                            $categoriaModel->setEstatus(1);
                            $jsonResult = $categoriaModel->Transaccion(['peticion' => 'guardar']);
                        }
                        
                        if ($jsonResult && isset($jsonResult['success']) && $jsonResult['success']) {
                            $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                            $json['response'] = ['resultado' => 200, 'mensaje' => $jsonResult['message']];
                        } else {
                            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'OK'];
                            $json['response'] = ['resultado' => 400, 'mensaje' => $jsonResult['message'] ?? 'Error desconocido'];
                        }
                    } catch (\Exception $e) {
                        $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];
                        $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
                    }
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                    $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para esto'];
                }
            }
            
            // Consultar
            if ($_POST["peticion"] == "consultar") {
                $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => ''];
                $json['response'] = $categoriaModel->Transaccion(['peticion' => 'consultar']);
            }
            
            // Cambiar Estatus (Eliminado lógico / Desactivar / Activar)
            if ($_POST["peticion"] == "cambiar_estatus") {
                $accion_permiso = true;

                if ($accion_permiso) {
                    try {
                        $categoriaModel->setIdCategoria($_POST["id_categoria"] ?? '');
                        $categoriaModel->setEstatus($_POST["estatus"] ?? '');
                        $jsonResult = $categoriaModel->Transaccion(['peticion' => 'cambiar_estatus']);
                        
                        if ($jsonResult && isset($jsonResult['success']) && $jsonResult['success']) {
                            $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                            $json['response'] = ['resultado' => 200, 'mensaje' => $jsonResult['message']];
                        } else {
                            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'OK'];
                            $json['response'] = ['resultado' => 400, 'mensaje' => $jsonResult['message'] ?? 'Error al actualizar estatus'];
                        }
                    } catch (\Exception $e) {
                        $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos mínimos no válidos'];
                        $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
                    }
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
                    $json['response'] = ['resultado' => 403, 'mensaje' => 'Error, No tienes permiso para esto'];
                }
            }

            header("Content-Type: application/json");
            header("HTTP/1.1 " . ($json['HTTP_STATUS']['codigo'] ?? 200) . " " . ($json['HTTP_STATUS']['mensaje'] ?? ""));
            echo json_encode($json['response'] ?? []);
            exit;
        }

        Helper::cargarVista(
            'categoria/index',
            'Categorías - Good Vibes'
        );
    }
}