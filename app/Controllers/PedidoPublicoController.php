<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\System\PedidoPublico;
use App\Models\System\Menu;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'buscarProducto') {
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
        exit;
    }
    $menuModel = new Menu();
    $menuModel->setIdProducto($id);
    $data = $menuModel->Transaccion(['peticion' => 'buscar']);
    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => 'Error desconocido.'];
    
    try {
        // Validar datos básicos
        $cedula = $_POST['cedula'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $referencia = $_POST['referencia'] ?? '';
        $carritoJson = $_POST['carrito'] ?? '';

        if (empty($cedula) || empty($nombre) || empty($carritoJson)) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios (Cédula, Nombre o Carrito).']);
            exit;
        }

        $carrito = json_decode($carritoJson, true);
        if (!$carrito || empty($carrito['items'])) {
            echo json_encode(['success' => false, 'message' => 'El carrito está vacío o es inválido.']);
            exit;
        }

        $datosCliente = [
            'cedula' => $cedula,
            'nombre' => $nombre,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'observacion' => $_POST['observacion'] ?? 'Pedido Web'
        ];

        $datosPago = [
            'id_metodo_pago' => 'MP_PM_2024',
            'referencia' => $referencia,
            'comprobante_url' => null
        ];

        // Manejo de la subida de la imagen (comprobante)
        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['comprobante'];
            $target_dir = __DIR__ . '/../../public/assets/img/comprobantes/';
            
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

            if (!in_array($extension, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Formato de comprobante no permitido.']);
                exit;
            }

            $newFileName = uniqid('comp_') . '.' . $extension;
            $target_file = $target_dir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $datosPago['comprobante_url'] = '/assets/img/comprobantes/' . $newFileName;
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo guardar el comprobante.']);
                exit;
            }
        }

        $pedidoModel = new PedidoPublico();
        $resultado = $pedidoModel->registrarPedidoWeb($datosCliente, $carrito, $datosPago);

        echo json_encode($resultado);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
