<?php

namespace App\Models\System;

use App\Core\Database;
use PDO;

class Producto
{
    private $db;
    private $id_producto;
    private $nombre;
    private $descripcion;
    private $precio;
    private $costo_preparacion;
    private $stock;
    private $stock_minimo;
    private $id_categoria;
    private $imagen;
    private $estatus;

    public function __construct()
    {
        $this->db = Database::getConnection('business');
    }

    public function setIdProducto($id)
    {
        $this->id_producto = $id;
    }
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }
    public function setDescripcion($desc)
    {
        $this->descripcion = $desc;
    }
    public function setPrecio($precio)
    {
        $this->precio = $precio;
    }
    public function setcosto($costo_preparacion)
    {
        $this->costo_preparacion = $costo_preparacion;
    }
    public function setStock($stock)
    {
        $this->stock = $stock;
    }
    public function setStockMinimo($min)
    {
        $this->stock_minimo = $min;
    }
    public function setIdCategoria($id)
    {
        $this->id_categoria = $id;
    }
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;
    }
    public function setEstatus($estatus)
    {
        $this->estatus = $estatus;
    }


    public function Transaccion($peticion)
    {
        switch ($peticion['peticion']) {
            case 'listar':
                return $this->listarProductos();
            case 'categorias':
                return $this->listarCategorias();
            case 'guardar':
                return $this->guardarProducto();
            case 'actualizar':
                return $this->actualizarProducto();
            case 'eliminar':
                return $this->eliminarProducto();
            case 'buscar':
                return $this->buscarProducto();
            default:
                return false;
        }
    }

    private function listarProductos()
    {
        $sql = "SELECT p.*, c.nombre_categoria as categoria_nombre 
            FROM producto p
            LEFT JOIN categoria_producto c ON p.id_categoria = c.id_categoria
            WHERE p.estatus = 1
            ORDER BY p.id_producto DESC
            ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listarCategorias()
    {
        try {
            $sql = "SELECT id_categoria, nombre_categoria, descripcion, icono 
                FROM categoria_producto 
                WHERE estatus = 1 
                ORDER BY nombre_categoria";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarCategorias: " . $e->getMessage());
            return [];
        }
    }

    private function guardarProducto()
    {
        try {
            $this->id_producto = $this->generarIdProducto();

            $sql = "INSERT INTO producto (
                    id_producto, 
                    nombre_producto, 
                    descripcion, 
                    precio, 
                    costo_preparacion, 
                    stock, 
                    stock_minimo, 
                    id_categoria, 
                    imagen, 
                    estatus,
                    es_personalizable
                ) VALUES (
                    :id_producto,
                    :nombre, 
                    :descripcion, 
                    :precio, 
                    :costo_preparacion, 
                    :stock, 
                    :stock_minimo, 
                    :id_categoria, 
                    :imagen, 
                    1,
                    0
                )";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id_producto' => $this->id_producto, // ¡AHORA SÍ SE ENVÍA!
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'costo_preparacion' => $this->costo_preparacion ?? 0,
                'stock' => $this->stock ?? 0,
                'stock_minimo' => $this->stock_minimo ?? 5,
                'id_categoria' => $this->id_categoria,
                'imagen' => $this->imagen ?? 'default-product.png'
            ]);

            if ($result) {
                return ['success' => true, 'id' => $this->id_producto, 'message' => 'Producto guardado exitosamente'];
            }
            return ['success' => false, 'message' => 'Error al guardar el producto'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function actualizarProducto()
    {
        try {
            $sql = "UPDATE producto SET 
                    nombre_producto = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    costo_preparacion = :costo_preparacion,
                    stock = :stock,
                    stock_minimo = :stock_minimo,
                    id_categoria = :id_categoria,
                    imagen = :imagen,
                    estatus = :estatus
                    WHERE id_producto = :id_producto";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id_producto' => $this->id_producto,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'costo_preparacion' => $this->costo_preparacion,
                'stock' => $this->stock,
                'stock_minimo' => $this->stock_minimo,
                'id_categoria' => $this->id_categoria,
                'imagen' => $this->imagen,
                'estatus' => $this->estatus
            ]);

            return ['success' => $result, 'message' => $result ? 'Producto actualizado' : 'Error al actualizar'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function eliminarProducto()
    {
        try {
            $sql = "UPDATE producto SET estatus = 0 WHERE id_producto = :id_producto";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id_producto' => $this->id_producto]);

            return ['success' => $result, 'message' => $result ? 'Producto eliminado' : 'Error al eliminar'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function buscarProducto()
    {
        $sql = "SELECT p.*, c.nombre_categoria as categoria_nombre 
            FROM producto p
            LEFT JOIN categoria_producto c ON p.id_categoria = c.id_categoria
            WHERE p.id_producto = :id_producto";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_producto' => $this->id_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function subirImagen($archivo)
    {
        try {
            $target_dir = BASE_PATH . '/public/assets/img/productos/';

            // Crear la carpeta si no existe
            if (!file_exists($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    error_log("Error: No se pudo crear la carpeta: " . $target_dir);
                    return false;
                }
            }

            // Verificar que la carpeta tenga permisos de escritura
            if (!is_writable($target_dir)) {
                error_log("Error: La carpeta no tiene permisos de escritura: " . $target_dir);
                return false;
            }

            $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];

            if (!in_array($extension, $allowed)) {
                error_log("Error: Extensión no permitida: " . $extension);
                return false;
            }

            // Validar tamaño (2MB máximo)
            if ($archivo["size"] > 2 * 1024 * 1024) {
                error_log("Error: Archivo demasiado grande: " . $archivo["size"]);
                return false;
            }

            $check = getimagesize($archivo["tmp_name"]);
            if ($check === false) {
                error_log("Error: El archivo no es una imagen válida");
                return false;
            }

            $nombre_archivo = uniqid('prod_') . '.' . $extension;
            $target_file = $target_dir . $nombre_archivo;

            // Mover archivo
            if (move_uploaded_file($archivo["tmp_name"], $target_file)) {
                error_log(" Imagen subida correctamente: " . $nombre_archivo);
                return $nombre_archivo;
            } else {
                error_log(" Error al mover el archivo. Error: " . error_get_last()['message']);
                return false;
            }
        } catch (\Exception $e) {
            error_log(" Excepción en subirImagen: " . $e->getMessage());
            return false;
        }
    }

    private function generarIdProducto()
    {
        $prefijo = 'PROD';
        $fecha = date('YmdHis');
        $random = rand(1000, 9999);
        return $prefijo . $fecha . $random;
    }
}
