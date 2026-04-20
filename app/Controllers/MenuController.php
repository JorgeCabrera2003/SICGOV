<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Menu;

class MenuController
{
    public function index()
    {
        Helper::verificarSesion();

        // Dispacher de acciones AJAX
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        if ($action && method_exists($this, $action) && $action !== 'index') {
            return $this->$action();
        }

        $menuModel = new Menu();
        $menus = $menuModel->Transaccion(['peticion' => 'listar']);
        $categorias = $menuModel->Transaccion(['peticion' => 'categorias']);
        $ingredientes = $menuModel->Transaccion(['peticion' => 'ingredientes']);
        $unidades = $menuModel->Transaccion(['peticion' => 'unidades']);

        Helper::cargarVista(
            'menu/index',
            'Menú - Good Vibes',
            compact('menus', 'categorias', 'ingredientes', 'unidades')
        );
    }

    public function guardar()
    {
        $this->responderJson(function() {
            Helper::verificarSesion();

            if (empty($_POST['nombre'])) {
                return ['success' => false, 'message' => 'El nombre es requerido'];
            }

            $datos = [
                'peticion' => 'guardar',
                'id_producto' => $_POST['id_producto'] ?? '',
                'nombre_producto' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'] ?? '',
                'precio' => $_POST['precio'] ?? 0,
                'id_categoria' => $_POST['id_categoria'] ?? null,
                'tipo_producto' => $_POST['tipo_producto'] ?? 'COCINA',
                'ingredientes_principales' => $_POST['ingredientes_principales'] ?? '[]',
                'ingredientes_adicionales' => $_POST['ingredientes_adicionales'] ?? '[]'
            ];

            // Prioridad 1: Imagen seleccionada de la galería
            if (!empty($_POST['imagen_galeria'])) {
                $datos['imagen'] = basename($_POST['imagen_galeria']);
                error_log("Imagen seleccionada de galeria: " . $datos['imagen']);
            } 
            // Prioridad 2: Nueva subida de archivo
            elseif (isset($_FILES['imagen'])) {
                error_log("FILES imagen detectado. Error code: " . $_FILES['imagen']['error']);
                if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $menu = new Menu();
                    $imagen = $menu->subirImagen($_FILES['imagen']);
                    if ($imagen) {
                        $datos['imagen'] = $imagen;
                        error_log("Imagen subida exitosamente: " . $imagen);
                    } else {
                        error_log("subirImagen() devolvio false");
                    }
                }
            } else {
                error_log("No hay FILES imagen ni POST imagen_galeria");
            }

            $menu = isset($menu) ? $menu : new Menu();
            $result = $menu->Transaccion($datos);

            if ($result['success']) {
                $accion = empty($datos['id_producto']) ? 'Se creó' : 'Se actualizó';
                $detalle = "$accion el producto del menú '{$datos['nombre_producto']}'";
                Helper::Bitacora(strtoupper(explode(' ', $accion)[1]), "MENU", $detalle);
            }

            return $result;
        });
    }

    public function buscar()
    {
        $this->responderJson(function() {
            Helper::verificarSesion();

            if (empty($_GET['id'])) {
                return ['success' => false, 'message' => 'ID no proporcionado'];
            }

            $menu = new Menu();
            $data = $menu->Transaccion(['peticion' => 'buscar', 'id_producto' => $_GET['id']]);

            return $data 
                ? ['success' => true, 'data' => $data]
                : ['success' => false, 'message' => 'Producto no encontrado'];
        });
    }

    public function eliminar()
    {
        $this->responderJson(function() {
            Helper::verificarSesion();

            if (empty($_POST['id'])) {
                return ['success' => false, 'message' => 'ID no proporcionado'];
            }

            $menu = new Menu();
            $result = $menu->Transaccion(['peticion' => 'eliminar', 'id_producto' => $_POST['id']]);

            if ($result['success']) {
                Helper::Bitacora("ELIMINAR", "MENU", "Se eliminó el producto del menú con ID: " . $_POST['id']);
            }

            return $result;
        });
    }

    public function cambiar_estatus()
    {
        $this->responderJson(function() {
            Helper::verificarSesion();

            if (empty($_POST['id']) || !isset($_POST['estatus'])) {
                return ['success' => false, 'message' => 'Parámetros incompletos'];
            }

            $menu = new Menu();
            $result = $menu->Transaccion([
                'peticion' => 'cambiar_estatus', 
                'id_producto' => $_POST['id'],
                'estatus' => $_POST['estatus']
            ]);

            if ($result['success']) {
                $estado = $_POST['estatus'] == 1 ? 'activó' : 'inactivó';
                Helper::Bitacora("ESTATUS", "MENU", "Se $estado el producto del menú con ID: " . $_POST['id']);
            }

            return $result;
        });
    }
    
    public function listarJson()
    {
        $this->responderJson(function() {
            Helper::verificarSesion();
            $menuModel = new Menu();
            $menus = $menuModel->Transaccion(['peticion' => 'listar']) ?: [];
            return ['data' => $menus];
        });
    }

    /**
     * Helper para respuestas JSON uniformes
     */
    private function responderJson(callable $callback)
    {
        header('Content-Type: application/json');

        try {
            $resultado = $callback();
            echo json_encode($resultado);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}
