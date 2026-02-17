<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Producto;

class ProductoController
{

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/?page=login");
            exit();
        }

        $productoModel = new Producto();
        $productos = $productoModel->Transaccion(['peticion' => 'listar']);
        $categorias = $productoModel->Transaccion(['peticion' => 'categorias']);

        $titulo = "Productos - Good Vibes";
        $page = 'productos';

        require_once BASE_PATH . '/resources/views/layout/head.php';
        require_once BASE_PATH . '/resources/views/layout/menu.php';
        require_once BASE_PATH . '/resources/views/productos/index.php';
        require_once BASE_PATH . '/resources/views/layout/footer.php';
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
            exit();
        }

        $producto = new Producto();

        if (empty($_POST['nombre'])) {
            echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
            exit();
        }

        $producto->setNombre($_POST['nombre']);
        $producto->setDescripcion($_POST['descripcion'] ?? '');
        $producto->setPrecio($_POST['precio'] ?? 0);
        $producto->setCosto($_POST['costo'] ?? 0);
        $producto->setStock($_POST['stock'] ?? 0);
        $producto->setStockMinimo($_POST['stock_minimo'] ?? 5);
        $producto->setIdCategoria($_POST['id_categoria'] ?? null);
        $producto->setEstatus(isset($_POST['estatus']) ? 1 : 0);

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $imagen = $producto->subirImagen($_FILES['imagen']);
            if ($imagen) {
                $producto->setImagen($imagen);
            }
        }

        if (!empty($_POST['id_producto'])) {
            $producto->setIdProducto($_POST['id_producto']);
            $result = $producto->Transaccion(['peticion' => 'actualizar']);
        } else {
            $result = $producto->Transaccion(['peticion' => 'guardar']);
        }

        if (isset($result['success']) && $result['success']) {
            Helper::Bitacora("Guardó producto: " . $_POST['nombre'], "Productos");
        }

        echo json_encode($result);
        exit();
    }

    public function buscar()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
            exit();
        }

        if (empty($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit();
        }

        $producto = new Producto();
        $producto->setIdProducto($_GET['id']);
        $data = $producto->Transaccion(['peticion' => 'buscar']);

        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
        exit();
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
            exit();
        }

        if (empty($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit();
        }

        $producto = new Producto();
        $producto->setIdProducto($_POST['id']);
        $result = $producto->Transaccion(['peticion' => 'eliminar']);

        if (isset($result['success']) && $result['success']) {
            Helper::Bitacora("Eliminó producto ID: " . $_POST['id'], "Productos");
        }

        echo json_encode($result);
        exit();
    }

    public function listarJson()
    {
        header('Content-Type: application/json');

        try {
            if (session_status() === PHP_SESSION_NONE) session_start();

            if (!isset($_SESSION['user'])) {
                echo json_encode(['data' => []]);
                exit();
            }

            $producto = new Producto();
            $productos = $producto->Transaccion(['peticion' => 'listar']);

            if (!is_array($productos)) {
                $productos = [];
            }

            $data = [];
            foreach ($productos as $p) {
                $data[] = [
                    'id' => $p['id_producto'] ?? '',
                    'imagen' => $this->generarImagenHtml($p),
                    'nombre' => $p['nombre_producto'] ?? '',
                    'categoria' => $p['categoria_nombre'] ?? 'Sin categoría',
                    'precio' => '$ ' . number_format($p['precio'] ?? 0, 2),
                    'stock' => $p['stock'] ?? 0,
                    'stock_minimo' => $p['stock_minimo'] ?? 5,
                    'estatus' => $this->generarEstatusHtml($p),
                    'acciones' => $this->generarAccionesHtml($p)
                ];
            }

            echo json_encode(['data' => $data]);
        } catch (\Exception $e) {
            echo json_encode([
                'error' => $e->getMessage(),
                'data' => []
            ]);
        }
        exit();
    }

    private function generarImagenHtml($p)
    {
        if (!empty($p['imagen']) && $p['imagen'] != 'default-product.png') {
            return '<img src="' . BASE_URL . '/assets/img/productos/' . $p['imagen'] .
                '" width="40" height="40" style="object-fit: cover; border-radius: 4px;">';
        }
        return '<div class="bg-secondary text-white d-flex align-items-center justify-content-center" ' .
            'style="width:40px;height:40px;border-radius:4px;"><i class="fas fa-box"></i></div>';
    }

    private function generarEstatusHtml($p)
    {
        return ($p['estatus'] ?? 0) == 1 ?
            '<span class="badge bg-success">Activo</span>' :
            '<span class="badge bg-danger">Inactivo</span>';
    }

    private function generarAccionesHtml($p)
    {
        return '<div class="btn-group" role="group">' .
            '<button class="btn btn-sm btn-primary btn-editar" data-id="' . $p['id_producto'] . '" title="Editar">' .
            '<i class="fas fa-edit"></i></button>' .
            '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' . $p['id_producto'] . '" title="Eliminar">' .
            '<i class="fas fa-trash"></i></button>' .
            '</div>';
    }
}
