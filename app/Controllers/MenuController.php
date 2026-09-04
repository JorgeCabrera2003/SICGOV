<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Menu;
use App\Models\System\CategoriaProducto;
use App\Models\System\Insumo;
use App\Models\System\UnidadMedida;

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
        $permisosMenu = Helper::TraerPermisos("producto");

        Helper::cargarVista(
            'menu/index',
            'Gestión de Menú - Good Vibes',
            [
                'categorias' => $categorias,
                'insumos' => $insumos,
                'unidades' => $unidades,
                'permisos' => $permisosMenu,
                'ver' => $permisosMenu['producto']['ver']
            ]
        );
        exit;
    }

    Helper::verificarSesion();
    $objMenu = new Menu();
    $permisosMenu = Helper::TraerPermisos("producto");

    if (!empty($peticion) || $isAjax) {
        
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($peticion == 'guardar' || $peticion == 'registrar' || $peticion == 'modificar') {
            $accion_permiso = false;
            $peticion_real = empty($_POST['id_producto']) ? 'registrar' : 'modificar';

            if (isset($permisosMenu['producto']['registrar']) && $permisosMenu['producto']['registrar'] == 1 && $peticion_real == 'registrar') {
                $accion_permiso = true;
            }
            if (isset($permisosMenu['producto']['modificar']) && $permisosMenu['producto']['modificar'] == 1 && $peticion_real == 'modificar') {
                $accion_permiso = true;
            }

            if (!$accion_permiso) {
                echo json_encode(['success' => false, 'message' => 'Error, No tienes permiso para ' . $peticion_real . ' un producto del menú']);
                exit;
            }

            try {
                $objInsumo = new Insumo();
                $objUnidad = new UnidadMedida();
                
                $id_categoria = $_POST['id_categoria'] ?? null;
                $objCategoria = new CategoriaProducto();
                if (!empty($id_categoria) && !$objCategoria->validarCategoria($id_categoria)) {
                    throw new Exception("La categoría seleccionada no existe en la base de datos.");
                }

                $insumos_principales_json = $_POST['insumos_principales'] ?? '[]';
                $insumos_principales = is_string($insumos_principales_json) ? json_decode($insumos_principales_json, true) : $insumos_principales_json;
                if (!empty($insumos_principales) && is_array($insumos_principales)) {
                    foreach ($insumos_principales as $ing) {
                        if (!empty($ing['id'])) {
                            $objInsumo->setId($ing['id']);
                            $validacion = $objInsumo->Transaccion(["peticion" => "validar"]);
                            if (!isset($validacion['bool']) || $validacion['bool'] != 1 || (isset($validacion['response']['registro']['estatus']) && $validacion['response']['registro']['estatus'] != 1)) {
                                throw new Exception("El insumo principal seleccionado no existe o está inactivo.");
                            }
                        }
                        if (!empty($ing['unidad'])) {
                            $objUnidad->setId($ing['unidad']);
                            $validacionUnidad = $objUnidad->Transaccion(["peticion" => "validar"]);
                            if (!isset($validacionUnidad['bool']) || $validacionUnidad['bool'] != 1) {
                                throw new Exception("La unidad de medida seleccionada para el insumo no existe en la base de datos.");
                            }
                        }
                    }
                }

                $insumos_adicionales_json = $_POST['insumos_adicionales'] ?? '[]';
                $insumos_adicionales = is_string($insumos_adicionales_json) ? json_decode($insumos_adicionales_json, true) : $insumos_adicionales_json;
                if (!empty($insumos_adicionales) && is_array($insumos_adicionales)) {
                    foreach ($insumos_adicionales as $ing) {
                        if (!empty($ing['id'])) {
                            $objInsumo->setId($ing['id']);
                            $validacion = $objInsumo->Transaccion(["peticion" => "validar"]);
                            if (!isset($validacion['bool']) || $validacion['bool'] != 1 || (isset($validacion['response']['registro']['estatus']) && $validacion['response']['registro']['estatus'] != 1)) {
                                throw new Exception("El insumo adicional seleccionado no existe o está inactivo.");
                            }
                        }
                        if (!empty($ing['unidad'])) {
                            $objUnidad->setId($ing['unidad']);
                            $validacionUnidad = $objUnidad->Transaccion(["peticion" => "validar"]);
                            if (!isset($validacionUnidad['bool']) || $validacionUnidad['bool'] != 1) {
                                throw new Exception("La unidad de medida seleccionada para el insumo adicional no existe en la base de datos.");
                            }
                        }
                    }
                }

                $objMenu->setIdProducto($_POST['id_producto'] ?? '');
                $objMenu->setNombreProducto($_POST['nombre'] ?? '');
                $objMenu->setDescripcion($_POST['descripcion'] ?? '');
                $objMenu->setPrecio($_POST['precio'] ?? 0);
                $objMenu->setIdCategoria($id_categoria);
                $objMenu->setTipoProducto($_POST['tipo_producto'] ?? 'COCINA');
                $objMenu->setInsumosPrincipales($insumos_principales_json);
                $objMenu->setInsumosAdicionales($insumos_adicionales_json);

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
            $accion_permiso = false;
            if (isset($permisosMenu['producto']['eliminar']) && $permisosMenu['producto']['eliminar'] == 1) {
                $accion_permiso = true;
            }

            if (!$accion_permiso) {
                echo json_encode(['success' => false, 'message' => 'Error, No tienes permiso para eliminar un producto del menú']);
                exit;
            }

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
            $accion_permiso = false;
            if (isset($permisosMenu['producto']['ver']) && $permisosMenu['producto']['ver'] == 1) {
                $accion_permiso = true;
            }

            if (!$accion_permiso) {
                echo json_encode(['data' => []]);
                exit;
            }

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
    
    $menuModel = new Menu();
    $menus = $menuModel->Transaccion(['peticion' => 'listar']) ?: [];
    $categorias = $menuModel->Transaccion(['peticion' => 'categorias']) ?: [];

    $page = 'menu_publico';
    $titulo = 'Nuestro Menú - Good Vibes';
    
    $extra_css = [
        BASE_URL . '/assets/css/landing.css?v=' . time(),
        BASE_URL . '/assets/css/main.css?v=' . time()
    ];
    $extra_js = [
        BASE_URL . '/assets/js/Controllers/PedidoPublicoController.js?v=' . time(),
        BASE_URL . '/assets/js/Handlers/PedidoPublicoHandler.js?v=' . time()
    ];

    require_once BASE_PATH . '/resources/views/layout/head.php';
    
    $hideSidebar = true;
    $datos = $_SESSION['user'] ?? null;
    require_once BASE_PATH . '/resources/views/layout/menu.php';

    require_once BASE_PATH . '/resources/views/menu/public.php';

    echo '</div></main>';

    require_once BASE_PATH . '/resources/views/layout/footer.php';
}
