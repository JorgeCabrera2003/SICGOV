<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Menu;

$type = $_REQUEST['type'] ?? 'admin';
$peticion = $_POST['peticion'] ?? $_POST['action'] ?? $_GET['action'] ?? '';
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

if ($type === 'admin') {

    if (empty($peticion) && !isset($_POST["peticion"]) && !isset($_GET["action"])) {
        Helper::verificarSesion();
        $menuModel = new Menu();
        $categorias = $menuModel->Transaccion(['peticion' => 'categorias']) ?: [];
        $insumos = $menuModel->Transaccion(['peticion' => 'insumos']) ?: [];
        $unidades = $menuModel->Transaccion(['peticion' => 'unidades']) ?: [];

        Helper::cargarVista(
            'menu/index',
            'Gestión de Menú - Good Vibes',
            [
                'categorias' => $categorias,
                'insumos' => $insumos,
                'unidades' => $unidades
            ]
        );
        exit;
    }

    Helper::verificarSesion();
    $objMenu = new Menu();

    if (!empty($peticion) || $isAjax) {
        
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        


        if ($peticion == 'guardar' || $peticion == 'registrar' || $peticion == 'modificar') {
            try {
                $objMenu->setIdProducto($_POST['id_producto'] ?? '');
                $objMenu->setNombreProducto($_POST['nombre'] ?? '');
                $objMenu->setDescripcion($_POST['descripcion'] ?? '');
                $objMenu->setPrecio($_POST['precio'] ?? 0);
                $objMenu->setIdCategoria($_POST['id_categoria'] ?? null);
                $objMenu->setTipoProducto($_POST['tipo_producto'] ?? 'COCINA');
                $objMenu->setInsumosPrincipales($_POST['insumos_principales'] ?? '[]');
                $objMenu->setInsumosAdicionales($_POST['insumos_adicionales'] ?? '[]');

                $imagen_nombre = null;
                
                if (!empty($_POST['imagen_galeria'])) {
                    $imagen_nombre = basename($_POST['imagen_galeria']);
                    error_log("Imagen seleccionada de galeria: " . $imagen_nombre);
                } elseif (isset($_FILES['imagen'])) {
                    error_log("FILES imagen detectado. Error code: " . $_FILES['imagen']['error']);
                    if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                        $imagen_subida = $objMenu->subirImagen($_FILES['imagen']);
                        if ($imagen_subida) {
                            $imagen_nombre = $imagen_subida;
                            error_log("Imagen subida exitosamente: " . $imagen_subida);
                        } else {
                            error_log("subirImagen() devolvio false");
                        }
                    }
                }

                if ($imagen_nombre) {
                    $objMenu->setImagen($imagen_nombre);
                }

                $peticion_db = empty($_POST['id_producto']) ? 'registrar' : 'modificar';
                $result = $objMenu->Transaccion(['peticion' => $peticion_db]);

                if (isset($result['success']) && $result['success']) {
                    $es_nuevo = empty($_POST['id_producto']);
                    $accion_bitacora = $es_nuevo ? 'REGISTRAR' : 'MODIFICAR';
                    $accion_detalle = $es_nuevo ? 'Se registró' : 'Se modificó';
                    $nombre_producto = $_POST['nombre'] ?? '';
                    $detalle = "$accion_detalle el producto del menú '{$nombre_producto}'";
                    Helper::Bitacora($accion_bitacora, "MENU", $detalle);
                }

                echo json_encode($result);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
        }











        if ($peticion == 'buscar') {
            try {
                if (empty($_GET['id'])) {
                    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                    exit;
                }
                $objMenu->setIdProducto($_GET['id']);
                $data = $objMenu->Transaccion(['peticion' => 'buscar']);
                echo json_encode($data ? ['success' => true, 'data' => $data] : ['success' => false, 'message' => 'Producto no encontrado']);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }









        if ($peticion == 'eliminar') {
            try {
                if (empty($_POST['id'])) {
                    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                    exit;
                }
                $objMenu->setIdProducto($_POST['id']);
                $result = $objMenu->Transaccion(['peticion' => 'eliminar']);
                if (isset($result['success']) && $result['success']) {
                    Helper::Bitacora("ELIMINAR", "MENU", "Se eliminó el producto del menú con ID: " . $_POST['id']);
                }
                echo json_encode($result);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
        }






        if ($peticion == 'listar' || $peticion == 'listarJson') {
            try {
                $menus = $objMenu->Transaccion(['peticion' => 'listar']) ?: [];
                echo json_encode(['data' => $menus]);
            } catch (\Exception $e) {
                echo json_encode(['data' => []]);
            }
            exit;
        }

        
        echo json_encode(['success' => false, 'message' => 'Petición no válida']);
        exit;
    }


} else {
    // MODO PÚBLICO
    $menuModel = new Menu();
    $menus = $menuModel->Transaccion(['peticion' => 'listar']) ?: [];
    $categorias = $menuModel->Transaccion(['peticion' => 'categorias']) ?: [];

    $page = 'menu_publico';
    $titulo = 'Nuestro Menú - Good Vibes';
    
    $extra_css = [BASE_URL . '/assets/css/main.css?v=' . time()];
    $extra_js = [
        BASE_URL . '/assets/js/Controllers/PedidoPublicoController.js?v=' . time(),
        BASE_URL . '/assets/js/Handlers/PedidoPublicoHandler.js?v=' . time()
    ];

    require_once BASE_PATH . '/resources/views/layout/head.php';
    echo '<main class="main-content flex-grow-1 ms-0 w-100" id="main-content"><div class="content-wrapper bg-body">';
    require_once BASE_PATH . '/resources/views/menu/public.php';
    require_once BASE_PATH . '/resources/views/layout/footer.php';
}
