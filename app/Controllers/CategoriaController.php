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

            if (empty($_POST['nombre'])) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                exit();
            }

            $categoria = new CategoriaProducto();
            $categoria->setNombreCategoria($_POST['nombre']);
            $categoria->setDescripcion($_POST['descripcion'] ?? '');
            
            $result = $categoria->Transaccion(['peticion' => 'guardar']);

            if ($result['success']) {
                Helper::Bitacora("Guardó categoría: " . $_POST['nombre'], "Categorías");
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

            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                exit();
            }

            $categoria = new CategoriaProducto();
            $categoria->setIdCategoria($_POST['id']);
            $result = $categoria->Transaccion(['peticion' => 'eliminar']);

            if ($result['success']) {
                Helper::Bitacora("Eliminó categoría ID: " . $_POST['id'], "Categorías");
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
}