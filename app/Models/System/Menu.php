<?php

namespace App\Models\System;

use App\Core\Database;
use PDO;

class Menu
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection('business');
    }

    public function Transaccion($datos)
    {
        switch ($datos['peticion']) {
            case 'listar':
                return $this->listarMenu();
            case 'guardar':
                return $this->guardarMenu($datos);
            case 'buscar':
                return $this->buscarMenu($datos['id_producto']);
            case 'eliminar':
                return $this->eliminarMenu($datos['id_producto']);
            case 'categorias':
                return $this->listarCategorias();
            case 'ingredientes':
                return $this->listarIngredientes();
            case 'unidades':
                return $this->listarUnidades();
            default:
                return false;
        }
    }

    private function listarMenu()
    {
        try {
            $sql = "SELECT p.*, c.nombre_categoria as categoria_nombre 
                    FROM producto p
                    LEFT JOIN categoria_producto c ON p.id_categoria = c.id_categoria
                    WHERE p.tipo_producto IN ('COCINA', 'BARRA', 'POSTRE')
                    AND p.estatus = 1
                    ORDER BY p.fecha_creacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarMenu: " . $e->getMessage());
            return [];
        }
    }
    
    private function listarCategorias()
    {
        try {
            $sql = "SELECT id_categoria, nombre_categoria FROM categoria_producto WHERE estatus = 1 ORDER BY nombre_categoria";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarCategorias: " . $e->getMessage());
            return [];
        }
    }

    private function listarIngredientes()
    {
        try {
            $sql = "SELECT id_ingrediente, nombre_ingrediente, id_unidad_medida, unidad_medida as nombre_unidad 
                    FROM vw_ingrediente WHERE estatus = 1 ORDER BY nombre_ingrediente";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarIngredientes: " . $e->getMessage());
            return [];
        }
    }

    private function listarUnidades()
    {
        try {
            $sql = "SELECT id_unidad, nombre, abreviatura FROM unidad_medida ORDER BY nombre";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarUnidades: " . $e->getMessage());
            return [];
        }
    }

    private function guardarMenu($datos)
    {
        try {
            $this->db->beginTransaction();
            
            $id_producto = !empty($datos['id_producto']) ? $datos['id_producto'] : $this->generarIdProducto();
            $es_nuevo = empty($datos['id_producto']);

            if ($es_nuevo) {
                $sql = "INSERT INTO producto (
                        id_producto, nombre_producto, descripcion, precio, 
                        id_categoria, imagen, es_personalizable, estatus, tipo_producto
                    ) VALUES (
                        :id_producto, :nombre, :descripcion, :precio, 
                        :id_categoria, :imagen, 1, 1, :tipo_producto
                    )";
            } else {
                $sql = "UPDATE producto SET 
                        nombre_producto = :nombre,
                        descripcion = :descripcion,
                        precio = :precio,
                        id_categoria = :id_categoria,
                        tipo_producto = :tipo_producto";
                
                if (isset($datos['imagen'])) {
                    $sql .= ", imagen = :imagen";
                }
                $sql .= " WHERE id_producto = :id_producto";
            }

            $stmt = $this->db->prepare($sql);
            $params = [
                'id_producto' => $id_producto,
                'nombre' => $datos['nombre_producto'],
                'descripcion' => $datos['descripcion'] ?? '',
                'precio' => $datos['precio'],
                'id_categoria' => $datos['id_categoria'],
                'tipo_producto' => $datos['tipo_producto'] ?? 'COCINA'
            ];
            
            if ($es_nuevo || isset($datos['imagen'])) {
                $params['imagen'] = $datos['imagen'] ?? 'default-product.png';
            }
            
            $stmt->execute($params);

            // Limpiar receta si es edición
            if (!$es_nuevo) {
                $del = $this->db->prepare("DELETE FROM preparacion WHERE id_producto = :id_producto");
                $del->execute(['id_producto' => $id_producto]);
            }

            // Insertar Principales (prioridad 1)
            if (!empty($datos['ingredientes_principales'])) {
                $this->insertarPreparacion($id_producto, $datos['ingredientes_principales'], 1);
            }

            // Insertar Adicionales (prioridad 2)
            if (!empty($datos['ingredientes_adicionales'])) {
                $this->insertarPreparacion($id_producto, $datos['ingredientes_adicionales'], 2);
            }

            $this->db->commit();
            return ['success' => true, 'id' => $id_producto, 'message' => 'Menú guardado exitosamente'];

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error en guardarMenu: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al guardar el menú: ' . $e->getMessage()];
        }
    }

    private function insertarPreparacion($id_producto, $ingredientes_json, $prioridad)
    {
        error_log("Recibido ingredientes_json para prioridad $prioridad: " . print_r($ingredientes_json, true));
        $ingredientes = is_string($ingredientes_json) ? json_decode($ingredientes_json, true) : $ingredientes_json;
        if (!is_array($ingredientes)) {
            error_log("Error: ingredientes no es un array valido. Valor: " . json_last_error_msg());
            return;
        }

        $sql = "INSERT INTO preparacion (id_preparacion, id_producto, id_ingrediente, prioridad_ingrediente, cantidad, id_unidad_medida, precio_ingrediente) 
                VALUES (:id_preparacion, :id_producto, :id_ingrediente, :prioridad, :cantidad, :id_unidad, :precio_ingrediente)";
        $stmt = $this->db->prepare($sql);

        foreach ($ingredientes as $ing) {
            $id_preparacion = 'PREP' . date('YmdHis') . rand(100, 999);
            $stmt->execute([
                'id_preparacion' => $id_preparacion,
                'id_producto' => $id_producto,
                'id_ingrediente' => $ing['id'],
                'prioridad' => $prioridad,
                'cantidad' => $ing['cantidad'] ?? 1,
                'id_unidad' => $ing['unidad'] ?? 'UN',
                'precio_ingrediente' => !empty($ing['precio']) ? (float)$ing['precio'] : 0
            ]);
            usleep(1000); // Pequeña pausa para asegurar id_preparacion único si se insertan muy rápido
        }
    }

    private function buscarMenu($id_producto)
    {
        try {
            $sql = "SELECT p.*, c.nombre_categoria 
                    FROM producto p
                    LEFT JOIN categoria_producto c ON p.id_categoria = c.id_categoria
                    WHERE p.id_producto = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id_producto]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto) {
                // Obtener ingredientes
                $sqlPrep = "SELECT pr.*, i.nombre_ingrediente, u.nombre as nombre_unidad 
                            FROM preparacion pr
                            JOIN ingrediente i ON pr.id_ingrediente = i.id_ingrediente
                            JOIN unidad_medida u ON pr.id_unidad_medida = u.id_unidad
                            WHERE pr.id_producto = :id_producto";
                $stPrep = $this->db->prepare($sqlPrep);
                $stPrep->execute(['id_producto' => $id_producto]);
                $preparacion = $stPrep->fetchAll(PDO::FETCH_ASSOC);

                $principales = [];
                $adicionales = [];
                foreach ($preparacion as $prep) {
                    if ($prep['prioridad_ingrediente'] == 1) {
                        $principales[] = $prep;
                    } else if ($prep['prioridad_ingrediente'] == 2) {
                        $adicionales[] = $prep;
                    }
                }
                
                $producto['ingredientes_principales'] = $principales;
                $producto['ingredientes_adicionales'] = $adicionales;
            }

            return $producto;
        } catch (\PDOException $e) {
            error_log("Error en buscarMenu: " . $e->getMessage());
            return null;
        }
    }

    private function eliminarMenu($id_producto)
    {
        try {
            $sql = "UPDATE producto SET estatus = 0 WHERE id_producto = :id_producto";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id_producto' => $id_producto]);
            return ['success' => $result, 'message' => $result ? 'Producto eliminado' : 'Error al eliminar'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function generarIdProducto()
    {
        return 'PROD' . date('YmdHis') . rand(1000, 9999);
    }

    public function subirImagen($archivo)
    {
        try {
            $target_dir = BASE_PATH . '/public/assets/img/productos/';

            if (!file_exists($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    error_log("Error: No se pudo crear la carpeta: " . $target_dir);
                    return false;
                }
            }

            if (!is_writable($target_dir)) {
                error_log("Error: La carpeta no tiene permisos de escritura: " . $target_dir);
                return false;
            }

            $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'jfif', 'webp'];

            if (!in_array($extension, $allowed)) {
                error_log("Error: Extensión no permitida: " . $extension);
                return false;
            }

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

            if (move_uploaded_file($archivo["tmp_name"], $target_file)) {
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
}
