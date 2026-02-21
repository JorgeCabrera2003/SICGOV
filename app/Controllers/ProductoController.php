<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Producto;

class ProductoController
{
    public function index()
    {
        Helper::verificarSesion();

        $productoModel = new Producto();
        $productos = $productoModel->Transaccion(['peticion' => 'listar']);
        $categorias = $productoModel->Transaccion(['peticion' => 'categorias']);

        Helper::cargarVista(
            'productos/index',
            'Productos - Good Vibes',
            compact('productos', 'categorias')
        );
    }

    public function guardar()
    {
        $this->responderJson(function() {
            Helper::verificarSesion();

            if (empty($_POST['nombre'])) {
                return ['success' => false, 'message' => 'El nombre es requerido'];
            }

            $producto = new Producto();
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
                if ($imagen) $producto->setImagen($imagen);
            }

            $esNuevo = empty($_POST['id_producto']);
            
            if (!$esNuevo) {
                $producto->setIdProducto($_POST['id_producto']);
            }

            $result = $producto->Transaccion([
                'peticion' => $esNuevo ? 'guardar' : 'actualizar'
            ]);

            if ($result['success']) {
                $accion = $esNuevo ? 'guardó' : 'actualizó';
                Helper::Bitacora("$accion producto: " . $_POST['nombre'], "Productos");
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

            $producto = new Producto();
            $producto->setIdProducto($_GET['id']);
            $data = $producto->Transaccion(['peticion' => 'buscar']);

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

            $producto = new Producto();
            $producto->setIdProducto($_POST['id']);
            $result = $producto->Transaccion(['peticion' => 'eliminar']);

            if ($result['success']) {
                Helper::Bitacora("Eliminó producto ID: " . $_POST['id'], "Productos");
            }

            return $result;
        });
    }

    public function listarJson()
    {
        $this->responderJson(function() {
            Helper::verificarSesion();

            $producto = new Producto();
            $productos = $producto->Transaccion(['peticion' => 'listar']) ?: [];

            return [
                'data' => array_map([$this, 'formatearProducto'], $productos)
            ];
        });
    }

    /**
     * Formatea un producto para la respuesta JSON
     */
    private function formatearProducto($p)
    {
        return [
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

    private function generarImagenHtml($p)
    {
        if (!empty($p['imagen']) && $p['imagen'] != 'default-product.png') {
            return sprintf(
                '<img src="%s/assets/img/productos/%s" width="40" height="40" class="rounded object-fit-cover">',
                BASE_URL,
                $p['imagen']
            );
        }
        
        return '<div class="bg-secondary bg-opacity-25 text-dark d-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;"><i class="fas fa-box"></i></div>';
    }

    private function generarEstatusHtml($p)
    {
        $activo = ($p['estatus'] ?? 0) == 1;
        $badge = $activo ? 'bg-success' : 'bg-danger';
        $texto = $activo ? 'Activo' : 'Inactivo';
        
        return sprintf('<span class="badge %s">%s</span>', $badge, $texto);
    }

    private function generarAccionesHtml($p)
    {
        $id = $p['id_producto'] ?? '';
        
        return sprintf(
            '<div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-warning border-0 btn-editar" data-id="%s" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger border-0 btn-eliminar" data-id="%s" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>',
            $id,
            $id
        );
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