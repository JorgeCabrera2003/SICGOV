<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Menu;

$type = $_REQUEST['type'] ?? 'admin';

if ($type === 'admin') {
    Helper::verificarSesion();
    $menuModel = new Menu();
    
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    
    $peticion = $_POST['peticion'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

    if (!empty($peticion) || $isAjax) {
        
        // Limpiar cualquier salida previa (avisos de PHP, etc) para no corromper el JSON
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');

        if ($peticion == 'guardar' || $peticion == 'registrar' || $peticion == 'modificar') {
            try {
                $menuModel->setIdProducto($_POST['id_producto'] ?? '');
                $menuModel->setNombreProducto($_POST['nombre'] ?? '');
                $menuModel->setDescripcion($_POST['descripcion'] ?? '');
                $menuModel->setPrecio($_POST['precio'] ?? 0);
                $menuModel->setIdCategoria($_POST['id_categoria'] ?? null);
                $menuModel->setTipoProducto($_POST['tipo_producto'] ?? 'COCINA');
                $menuModel->setInsumosPrincipales($_POST['insumos_principales'] ?? '[]');
                $menuModel->setInsumosAdicionales($_POST['insumos_adicionales'] ?? '[]');

                $imagen_nombre = null;
                // Prioridad 1: Imagen seleccionada de la galería
                if (!empty($_POST['imagen_galeria'])) {
                    $imagen_nombre = basename($_POST['imagen_galeria']);
                } 
                // Prioridad 2: Nueva subida de archivo
                elseif (isset($_FILES['imagen'])) {
                    if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                        $imagen_subida = $menuModel->subirImagen($_FILES['imagen']);
                        if ($imagen_subida) {
                            $imagen_nombre = $imagen_subida;
                        }
                    }
                }

                if ($imagen_nombre) {
                    $menuModel->setImagen($imagen_nombre);
                }

                $pet = empty($_POST['id_producto']) ? 'registrar' : 'modificar';
                $result = $menuModel->Transaccion(['peticion' => $pet]);

                if (isset($result['success']) && $result['success']) {
                    $es_nuevo = empty($_POST['id_producto']);
                    $accion_bitacora = $es_nuevo ? 'REGISTRAR' : 'MODIFICAR';
                    $accion_detalle = $es_nuevo ? 'Se registró' : 'Se modificó';
                    $nombre_producto = $_POST['nombre'] ?? '';
                    $detalle = "$accion_detalle el producto del menú '{$nombre_producto}'";
                    Helper::Bitacora($accion_bitacora, "MENU", $detalle);
                }

                echo json_encode($result);
                exit;

            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }

        if ($peticion == 'buscar' || $peticion == 'consultar') {
            try {
                $id = $_GET['id'] ?? $_POST['id'] ?? '';
                if (empty($id)) {
                    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                    exit;
                }

                $menuModel->setIdProducto($id);
                $data = $menuModel->Transaccion(['peticion' => 'buscar']);

                if ($data) {
                    echo json_encode(['success' => true, 'data' => $data]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                }
                exit;

            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }

        if ($peticion == 'eliminar') {
            try {
                if (empty($_POST['id'])) {
                    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                    exit;
                }

                $menuModel->setIdProducto($_POST['id']);
                $result = $menuModel->Transaccion(['peticion' => 'eliminar']);

                if (isset($result['success']) && $result['success']) {
                    Helper::Bitacora("ELIMINAR", "MENU", "Se eliminó el producto del menú con ID: " . $_POST['id']);
                }

                echo json_encode($result);
                exit;

            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }

        if ($peticion == 'listarJson' || $peticion == 'listar') {
            try {
                $menus = $menuModel->Transaccion(['peticion' => 'listar']) ?: [];
                echo json_encode(['data' => $menus]);
                exit;
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }
    }

    $menus = $menuModel->Transaccion(['peticion' => 'listar']);
    $categorias = $menuModel->Transaccion(['peticion' => 'categorias']);
    $insumos = $menuModel->Transaccion(['peticion' => 'insumos']);
    $unidades = $menuModel->Transaccion(['peticion' => 'unidades']);

    Helper::cargarVista(
        'menu/index',
        'Menú - Good Vibes',
        compact('menus', 'categorias', 'insumos', 'unidades')
    );

} elseif ($type === 'publico') {
    $menuModel = new Menu();
    $menus = $menuModel->Transaccion(['peticion' => 'listar']) ?: [];
    $categorias = $menuModel->Transaccion(['peticion' => 'categorias']) ?: [];

    $page = 'menu_publico';
    $titulo = 'Nuestro Menú - Good Vibes';
    
    $extra_css = [BASE_URL . '/assets/css/main.css?v=' . time()];

    require_once BASE_PATH . '/resources/views/layout/head.php';
    
    // No lateral navigation menu on the public view, ever
    echo '<main class="main-content flex-grow-1 ms-0 w-100" id="main-content"><div class="content-wrapper bg-body">';

    require_once BASE_PATH . '/resources/views/menu/public.php';
    
    require_once BASE_PATH . '/resources/views/layout/footer.php';
}
