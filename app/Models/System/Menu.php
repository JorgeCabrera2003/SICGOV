<?php

namespace App\Models\System;

use App\Core\Database;
use PDO;
use Exception;

class Menu
{
    private $db;
    private $id_producto;
    private $nombre_producto;
    private $descripcion;
    private $precio;
    private $id_categoria;
    private $tipo_producto;
    private $imagen;
    private $ingredientes_principales;
    private $ingredientes_adicionales;

    public function __construct()
    {
        $this->db = Database::getConnection('business');
    }

    
    public function setIdProducto($id)
    {
        $this->id_producto = $id;
    }

    public function setNombreProducto($nombre)
    {
        $nombre = trim($nombre);
        if (empty($nombre)) {
            throw new Exception("El nombre del producto es obligatorio.");
        }
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
            throw new Exception("El nombre del producto solo admite letras y espacios.");
        }
        $this->nombre_producto = $nombre;
    }

    public function setDescripcion($descripcion)
    {
        $descripcion = trim($descripcion);
        if (empty($descripcion)) {
            throw new Exception("La descripción es obligatoria.");
        }
        if (!preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.,]+$/', $descripcion)) {
            throw new Exception("La descripción solo admite letras y números.");
        }
        $this->descripcion = $descripcion;
    }

    public function setPrecio($precio)
    {
        if (empty($precio) || !is_numeric($precio) || $precio <= 0) {
            throw new Exception("El precio debe ser un número válido mayor a 0.");
        }
        $this->precio = $precio;
    }

    public function setIdCategoria($id_categoria)
    {
        if (empty($id_categoria)) {
            throw new Exception("La categoría es obligatoria.");
        }
        
        $stmt = $this->db->prepare("SELECT id_categoria FROM categoria_producto WHERE id_categoria = ?");
        $stmt->execute([$id_categoria]);
        if ($stmt->rowCount() === 0) {
            throw new Exception("La categoría seleccionada no existe en la base de datos.");
        }

        $this->id_categoria = $id_categoria;
    }

    public function setTipoProducto($tipo)
    {
        $tipo = trim($tipo);
        if (empty($tipo) || !in_array($tipo, ['COCINA', 'BARRA'])) {
            throw new Exception("El tipo de producto es inválido.");
        }
        $this->tipo_producto = $tipo;
    }

    public function setImagen($imagen)
    {
        $this->imagen = $imagen;
    }

    public function setIngredientesPrincipales($ingredientes_json)
    {
        if ($this->tipo_producto === 'COCINA') {
            $ingredientes = is_string($ingredientes_json) ? json_decode($ingredientes_json, true) : $ingredientes_json;
            if (empty($ingredientes) || !is_array($ingredientes)) {
                throw new Exception("El producto de cocina debe tener al menos un ingrediente principal.");
            }
            foreach ($ingredientes as $ing) {
                if (empty($ing['cantidad']) || !is_numeric($ing['cantidad']) || $ing['cantidad'] <= 0) {
                    throw new Exception("Las cantidades de los ingredientes principales deben ser números mayores a 0.");
                }
                if (empty($ing['unidad'])) {
                    throw new Exception("La unidad de medida es obligatoria para los ingredientes principales.");
                }
                
                $stmt = $this->db->prepare("SELECT id_unidad FROM unidad_medida WHERE id_unidad = ?");
                $stmt->execute([$ing['unidad']]);
                if ($stmt->rowCount() === 0) {
                    throw new Exception("La unidad de medida seleccionada para el ingrediente no existe en la base de datos.");
                }
            }
        }
        $this->ingredientes_principales = $ingredientes_json;
    }

    public function setIngredientesAdicionales($ingredientes_json)
    {
        if ($this->tipo_producto === 'COCINA') {
            $ingredientes = is_string($ingredientes_json) ? json_decode($ingredientes_json, true) : $ingredientes_json;
            if (!empty($ingredientes) && is_array($ingredientes)) {
                foreach ($ingredientes as $ing) {
                    if (empty($ing['cantidad']) || !is_numeric($ing['cantidad']) || $ing['cantidad'] <= 0) {
                        throw new Exception("Las cantidades de los ingredientes adicionales deben ser números mayores a 0.");
                    }
                    if (!isset($ing['precio']) || !is_numeric($ing['precio']) || $ing['precio'] <= 0) {
                        throw new Exception("El precio de los ingredientes adicionales debe ser un número válido mayor a 0.");
                    }
                    if (empty($ing['unidad'])) {
                        throw new Exception("La unidad de medida es obligatoria para los ingredientes adicionales.");
                    }
                    
                    $stmt = $this->db->prepare("SELECT id_unidad FROM unidad_medida WHERE id_unidad = ?");
                    $stmt->execute([$ing['unidad']]);
                    if ($stmt->rowCount() === 0) {
                        throw new Exception("La unidad de medida seleccionada para el ingrediente adicional no existe en la base de datos.");
                    }
                }
            }
        }
        $this->ingredientes_adicionales = $ingredientes_json;
    }




  
    public function getIdProducto()
    {
        return $this->id_producto;
    }

    public function getNombreProducto()
    {
        return $this->nombre_producto;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function getPrecio()
    {
        return $this->precio;
    }

    public function getIdCategoria()
    {
        return $this->id_categoria;
    }

    public function getTipoProducto()
    {
        return $this->tipo_producto;
    }

    public function getImagen()
    {
        return $this->imagen;
    }

    public function getIngredientesPrincipales()
    {
        return $this->ingredientes_principales;
    }

    public function getIngredientesAdicionales()
    {
        return $this->ingredientes_adicionales;
    }

//#########################################################################################


    public function Transaccion($peticion)
    {
        $response = ['success' => false, 'message' => 'Petición no válida'];

        if (isset($peticion['peticion'])) {
            try {
                $response = match ($peticion['peticion']) {
                    'listar'       => $this->listarMenu(),
                    'registrar'    => $this->registrarMenu(),
                    'modificar'    => $this->modificarMenu(),
                    'buscar'       => $this->buscarMenu(),
                    'eliminar'     => $this->eliminarMenu(),
                    'categorias'   => $this->listarCategorias(),
                    'ingredientes' => $this->listarIngredientes(),
                    'unidades'     => $this->listarUnidades(),
                    default        => ['success' => false, 'message' => 'Petición no válida']
                };
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return $response;
    }










//#########################################################################################


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
    
















//#########################################################################################


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

















//#########################################################################################


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



















//#########################################################################################


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

















//#########################################################################################


    private function registrarMenu()
    {
        try {
            $this->db->beginTransaction();
            
            $this->id_producto = $this->generarIdProducto();

            $sql = "INSERT INTO producto (
                    id_producto, nombre_producto, descripcion, precio, 
                    id_categoria, imagen, es_personalizable, estatus, tipo_producto
                ) VALUES (
                    :id_producto, :nombre, :descripcion, :precio, 
                    :id_categoria, :imagen, 1, 1, :tipo_producto
                )";

            $stmt = $this->db->prepare($sql);
            $params = [
                'id_producto' => $this->getIdProducto(),
                'nombre' => $this->getNombreProducto(),
                'descripcion' => $this->getDescripcion() ?? '',
                'precio' => $this->getPrecio(),
                'id_categoria' => $this->getIdCategoria(),
                'tipo_producto' => $this->getTipoProducto() ?? 'COCINA',
                'imagen' => $this->getImagen() ?? 'default-product.png'
            ];
            
            $stmt->execute($params);

            // Insertar Principales (prioridad 1)
            if (!empty($this->getIngredientesPrincipales())) {
                $this->insertarPreparacion($this->getIdProducto(), $this->getIngredientesPrincipales(), 1);
            }

            // Insertar Adicionales (prioridad 2)
            if (!empty($this->getIngredientesAdicionales())) {
                $this->insertarPreparacion($this->getIdProducto(), $this->getIngredientesAdicionales(), 2);
            }

            $this->db->commit();
            return ['success' => true, 'id' => $this->getIdProducto(), 'message' => 'Menú registrado exitosamente'];

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error en registrarMenu: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar el menú: ' . $e->getMessage()];
        }
    }





















//#########################################################################################


    private function modificarMenu()
    {
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE producto SET 
                    nombre_producto = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    id_categoria = :id_categoria,
                    tipo_producto = :tipo_producto";
            
            if ($this->getImagen() !== null) {
                $sql .= ", imagen = :imagen";
            }
            $sql .= " WHERE id_producto = :id_producto";

            $stmt = $this->db->prepare($sql);
            $params = [
                'id_producto' => $this->getIdProducto(),
                'nombre' => $this->getNombreProducto(),
                'descripcion' => $this->getDescripcion() ?? '',
                'precio' => $this->getPrecio(),
                'id_categoria' => $this->getIdCategoria(),
                'tipo_producto' => $this->getTipoProducto() ?? 'COCINA'
            ];
            
            if ($this->getImagen() !== null) {
                $params['imagen'] = $this->getImagen();
            }
            
            $stmt->execute($params);

            // Limpiar receta si es edición
            $del = $this->db->prepare("DELETE FROM preparacion WHERE id_producto = :id_producto");
            $del->execute(['id_producto' => $this->getIdProducto()]);

            // Insertar Principales (prioridad 1)
            if (!empty($this->getIngredientesPrincipales())) {
                $this->insertarPreparacion($this->getIdProducto(), $this->getIngredientesPrincipales(), 1);
            }

            // Insertar Adicionales (prioridad 2)
            if (!empty($this->getIngredientesAdicionales())) {
                $this->insertarPreparacion($this->getIdProducto(), $this->getIngredientesAdicionales(), 2);
            }

            $this->db->commit();
            return ['success' => true, 'id' => $this->getIdProducto(), 'message' => 'Menú guardado exitosamente'];

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error en modificarMenu: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al modificar el menú: ' . $e->getMessage()];
        }
    }





















//#########################################################################################


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













//#########################################################################################


    private function buscarMenu()
    {
        try {
            $sql = "SELECT p.*, c.nombre_categoria 
                    FROM producto p
                    LEFT JOIN categoria_producto c ON p.id_categoria = c.id_categoria
                    WHERE p.id_producto = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $this->getIdProducto()]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto) {
                // Obtener ingredientes
                $sqlPrep = "SELECT pr.*, i.nombre_ingrediente, u.nombre as nombre_unidad 
                            FROM preparacion pr
                            JOIN ingrediente i ON pr.id_ingrediente = i.id_ingrediente
                            JOIN unidad_medida u ON pr.id_unidad_medida = u.id_unidad
                            WHERE pr.id_producto = :id_producto";
                $stPrep = $this->db->prepare($sqlPrep);
                $stPrep->execute(['id_producto' => $this->getIdProducto()]);
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

















//#########################################################################################


    private function eliminarMenu()
    {
        try {
            $sql = "UPDATE producto SET estatus = 0 WHERE id_producto = :id_producto";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id_producto' => $this->getIdProducto()]);
            return ['success' => $result, 'message' => $result ? 'Producto eliminado' : 'Error al eliminar'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }



















//#########################################################################################


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
