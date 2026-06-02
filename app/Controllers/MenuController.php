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

            $menu = new Menu();
            $menu->setIdProducto($_POST['id_producto'] ?? '');
            $menu->setNombreProducto($_POST['nombre'] ?? '');
            $menu->setDescripcion($_POST['descripcion'] ?? '');
            $menu->setPrecio($_POST['precio'] ?? 0);
            $menu->setIdCategoria($_POST['id_categoria'] ?? null);
            $menu->setTipoProducto($_POST['tipo_producto'] ?? 'COCINA');
            $menu->setIngredientesPrincipales($_POST['ingredientes_principales'] ?? '[]');
            $menu->setIngredientesAdicionales($_POST['ingredientes_adicionales'] ?? '[]');

            $imagen_nombre = null;
            // Prioridad 1: Imagen seleccionada de la galería
            if (!empty($_POST['imagen_galeria'])) {
                $imagen_nombre = basename($_POST['imagen_galeria']);
                error_log("Imagen seleccionada de galeria: " . $imagen_nombre);
            } 
            // Prioridad 2: Nueva subida de archivo
            elseif (isset($_FILES['imagen'])) {
                error_log("FILES imagen detectado. Error code: " . $_FILES['imagen']['error']);
                if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $imagen_subida = $menu->subirImagen($_FILES['imagen']);
                    if ($imagen_subida) {
                        $imagen_nombre = $imagen_subida;
                        error_log("Imagen subida exitosamente: " . $imagen_subida);
                    } else {
                        error_log("subirImagen() devolvio false");
                    }
                }
            } else {
                error_log("No hay FILES imagen ni POST imagen_galeria");
            }

            if ($imagen_nombre) {
                $menu->setImagen($imagen_nombre);
            }

            $peticion = empty($_POST['id_producto']) ? 'registrar' : 'modificar';

            $result = $menu->Transaccion(['peticion' => $peticion]);

            if (isset($result['success']) && $result['success']) {
                $es_nuevo = empty($_POST['id_producto']);
                $accion_bitacora = $es_nuevo ? 'REGISTRAR' : 'MODIFICAR';
                $accion_detalle = $es_nuevo ? 'Se registró' : 'Se modificó';
                $nombre_producto = $_POST['nombre'] ?? '';
                $detalle = "$accion_detalle el producto del menú '{$nombre_producto}'";
                Helper::Bitacora($accion_bitacora, "MENU", $detalle);
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
            $menu->setIdProducto($_GET['id']);
            $data = $menu->Transaccion(['peticion' => 'buscar']);

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
            $menu->setIdProducto($_POST['id']);
            $result = $menu->Transaccion(['peticion' => 'eliminar']);

            if (isset($result['success']) && $result['success']) {
                Helper::Bitacora("ELIMINAR", "MENU", "Se eliminó el producto del menú con ID: " . $_POST['id']);
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







    
    public function indexPublico()
    {
        $menuModel = new Menu();
        // Usamos listarMenu() indirectamente a través de Transaccion(['peticion' => 'listar'])
        $menus = $menuModel->Transaccion(['peticion' => 'listar']) ?: [];
        $categorias = $menuModel->Transaccion(['peticion' => 'categorias']) ?: [];

        $page = 'menu_publico';
        $titulo = 'Nuestro Menú - Good Vibes';
        
        // Puedes agregar css extra si lo necesitas, por ahora lo dejamos vacío o usamos el de main
        $extra_css = [BASE_URL . '/assets/css/main.css?v=' . time()];

        require_once BASE_PATH . '/resources/views/layout/head.php';
        
        // No lateral navigation menu on the public view, ever
        echo '<main class="main-content flex-grow-1 ms-0 w-100" id="main-content"><div class="content-wrapper bg-body">';

        require_once BASE_PATH . '/resources/views/menu/public.php';
        
        require_once BASE_PATH . '/resources/views/layout/footer.php';
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
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }
}
