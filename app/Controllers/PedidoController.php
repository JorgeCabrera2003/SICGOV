<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\System\Pedido;
use App\Models\System\Menu; 
use App\Helpers\Helper;

// Verificamos sesión para el área de administración
Helper::verificarSesion();

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($isAjax || !empty($action)) {
    header('Content-Type: application/json');
    $pedidoModel = new Pedido();

    try {
        switch ($action) {
            case 'verificar_stock':
                $id_producto = $_POST['id_producto'] ?? '';
                $cantidad = intval($_POST['cantidad'] ?? 1);
                
                if (!$id_producto) {
                    echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado']);
                    break;
                }
                
                $menuModel = new Menu();
                $resultado = $menuModel->verificarStockProducto($id_producto, $cantidad);
                echo json_encode($resultado);
                break;
                
            case 'listar':
                $data = $pedidoModel->Transaccion(['peticion' => 'listar']);
                echo json_encode(['success' => true, 'data' => $data]);
                break;

            case 'obtener_insumos':
                $id_producto = $_POST['id_producto'] ?? $_GET['id_producto'] ?? '';
                if (!$id_producto) {
                    echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado']);
                    break;
                }
                $menuModel = new Menu(); // <-- Usar el modelo MENU
                $insumos = $menuModel->obtenerInsumosProducto($id_producto);
                echo json_encode(['success' => true, 'data' => $insumos]);
                break;

            case 'buscar':
                $id = $_POST['id_pedido'] ?? $_GET['id_pedido'] ?? '';
                $data = $pedidoModel->Transaccion(['peticion' => 'buscar', 'id_pedido' => $id]);
                if ($data) {
                    echo json_encode(['success' => true, 'data' => $data]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
                }
                break;

            case 'cambiar_estado':
                $id = $_POST['id_pedido'] ?? '';
                $estado = $_POST['estado'] ?? '';
                $res = $pedidoModel->Transaccion([
                    'peticion' => 'cambiar_estado',
                    'id_pedido' => $id,
                    'estado' => $estado
                ]);
                echo json_encode($res);
                break;

            case 'obtener_comprobante':
                $id = $_POST['id_pedido'] ?? $_GET['id_pedido'] ?? '';
                $res = $pedidoModel->Transaccion([
                    'peticion' => 'obtener_comprobante',
                    'id_pedido' => $id
                ]);
                echo json_encode($res);
                break;

            case 'crear_pos':
                // Lógica de POS similar al público pero sin tantas validaciones forzadas (el admin sabe lo que hace)
                $carritoJson = $_POST['carrito'] ?? '';
                $carrito = json_decode($carritoJson, true);
                
                if (!$carrito || empty($carrito['items'])) {
                    echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
                    exit;
                }

                $datosCliente = [
                    'cedula' => $_POST['cedula'] ?? null,
                    'nombre' => $_POST['nombre'] ?? 'Cliente Mostrador',
                    'telefono' => $_POST['telefono'] ?? null,
                    'direccion' => $_POST['direccion'] ?? null,
                    'tipo_pedido' => $_POST['tipo_pedido'] ?? 'LLEVAR',
                    'id_mesa' => $_POST['id_mesa'] ?? null,
                    'observacion' => $_POST['observacion'] ?? 'Pedido POS'
                ];

                $datosPago = [
                    'id_metodo_pago' => $_POST['id_metodo_pago'] ?? 'METOD00120260519200547232', // Efectivo
                    'referencia' => $_POST['referencia'] ?? null
                ];

                $res = $pedidoModel->Transaccion([
                    'peticion' => 'crear_pos',
                    'datosCliente' => $datosCliente,
                    'carrito' => $carrito,
                    'datosPago' => $datosPago
                ]);

                // Si el pedido se creó exitosamente, agregar numero_pedido a la respuesta
                if ($res['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => $res['message'],
                        'id_pedido' => $res['id_pedido'],
                        'numero_pedido' => $res['numero_pedido'] ?? null
                    ]);
                } else {
                    echo json_encode($res);
                }
                break;
                case 'listar_mesas_disponibles':
                    $mesas = $pedidoModel->Transaccion(['peticion' => 'listar_mesas_disponibles']);
                    echo json_encode(['success' => true, 'data' => $mesas]);
                break;   
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Si no es AJAX, cargamos las vistas
$page = $_GET['page'] ?? 'pedidos';

if ($page === 'pedidos') {
    
    $menuModel = new Menu();
    $categorias = $menuModel->Transaccion(['peticion' => 'categorias']);

    $extra_css = [
        BASE_URL . '/assets/css/pedidos-admin.css'
    ];
    $extra_js = [
        BASE_URL . '/assets/js/Controllers/PedidoAdminController.js',
        BASE_URL . '/assets/js/Handlers/PedidoAdminHandler.js',
        BASE_URL . '/assets/js/Handlers/PosHandler.js'
    ];

     $permisosUsuario = Helper::TraerPermisos("pedido");

    Helper::cargarVista(
        'pedidos/index',
        'Gestión de Pedidos - Good Vibes',
        [
            'ver' => $permisosUsuario['pedido']['ver'] ?? 1,
            'categorias' => $categorias,
            'extra_css' => $extra_css,
            'extra_js' => $extra_js
        ]
    );
}
