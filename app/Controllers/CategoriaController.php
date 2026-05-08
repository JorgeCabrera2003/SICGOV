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
                        
                        if ($_POST["peticion"] == "modificar") {
                            // En actualizar
                            $categoriaModel->setIdCategoria($_POST["id_categoria"] ?? '');
                            $categoriaModel->setEstatus(1); // Siempre 1 para activos modificados
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
            
            // Verificar nombre duplicado (llamada asíncrona del frontend)
            if ($_POST["peticion"] == "verificar") {
                try {
                    $nombre = trim($_POST["nombre_categoria"] ?? '');
                    $id_excluir = trim($_POST["id_categoria"] ?? '');

                    if (empty($nombre)) {
                        $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                        $json['response'] = ['resultado' => 200, 'existe' => false, 'mensaje' => ''];
                    } else {
                        $categoriaModel->setNombreCategoria($nombre);
                        $jsonResult = $categoriaModel->Transaccion([
                            'peticion'   => 'verificar',
                            'id_excluir' => $id_excluir ?: null
                        ]);
                        $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                        $json['response'] = [
                            'resultado' => 200,
                            'existe'    => $jsonResult['existe'],
                            'mensaje'   => $jsonResult['message']
                        ];
                    }
                } catch (\Exception $e) {
                    // Si falla la validación del setter, no hay duplicado que reportar
                    $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                    $json['response'] = ['resultado' => 200, 'existe' => false, 'mensaje' => ''];
                }
            }

            // Consultar
            if ($_POST["peticion"] == "consultar") {
                $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => ''];
                $json['response'] = $categoriaModel->Transaccion(['peticion' => 'consultar']);
            }
            
            // Eliminado Lógico
            if ($_POST["peticion"] == "eliminar") {
                $accion_permiso = true;

                if ($accion_permiso) {
                    try {
                        $categoriaModel->setIdCategoria($_POST["id_categoria"] ?? $_POST["id"] ?? '');
                        $jsonResult = $categoriaModel->Transaccion(['peticion' => 'eliminar']);
                        
                        if ($jsonResult && isset($jsonResult['success']) && $jsonResult['success']) {
                            $json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
                            $json['response'] = ['resultado' => 200, 'mensaje' => $jsonResult['message']];
                        } else {
                            $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'OK'];
                            $json['response'] = ['resultado' => 400, 'mensaje' => $jsonResult['message'] ?? 'Error al eliminar'];
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