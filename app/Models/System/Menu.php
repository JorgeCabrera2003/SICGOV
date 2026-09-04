<?php

namespace App\Models\System;

use App\Core\Database;
use PDO;
use App\Helpers\Helper;
use Exception;

class Menu extends Database
{
    private $id_producto;
    private $nombre_producto;
    private $descripcion;
    private $precio;
    private $id_categoria;
    private $tipo_producto;
    private $imagen;
    private $insumos_principales;
    private $insumos_adicionales;

    
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

    public function setInsumosPrincipales($insumos_json)
    {
        if ($this->tipo_producto === 'COCINA' || $this->tipo_producto === 'BARRA') {
            $insumos = is_string($insumos_json) ? json_decode($insumos_json, true) : $insumos_json;
            
            if ($this->tipo_producto === 'COCINA' && (empty($insumos) || !is_array($insumos))) {
                throw new Exception("El producto de cocina debe tener al menos un insumo principal.");
            }
            if ($this->tipo_producto === 'BARRA' && (empty($insumos) || !is_array($insumos) || count($insumos) !== 1)) {
                throw new Exception("El producto de barra debe tener exactamente un insumo principal.");
            }

            if (!empty($insumos) && is_array($insumos)) {
                foreach ($insumos as $ing) {
                    if (empty($ing['id'])) {
                        throw new Exception("El insumo es obligatorio para los insumos principales.");
                    }
                    if (empty($ing['cantidad']) || !is_numeric($ing['cantidad']) || $ing['cantidad'] <= 0) {
                        throw new Exception("Las cantidades de los insumos principales deben ser números mayores a 0.");
                    }
                    if (empty($ing['unidad'])) {
                        throw new Exception("La unidad de medida es obligatoria para los insumos principales.");
                    }
                }
            }
        }
        $this->insumos_principales = $insumos_json;
    }


    
    public function setInsumosAdicionales($insumos_json)
    {
        if ($this->tipo_producto === 'COCINA') {
            $insumos = is_string($insumos_json) ? json_decode($insumos_json, true) : $insumos_json;
            if (!empty($insumos) && is_array($insumos)) {
                foreach ($insumos as $ing) {
                    if (empty($ing['id'])) {
                        throw new Exception("El insumo es obligatorio para los insumos adicionales.");
                    }
                    if (empty($ing['cantidad']) || !is_numeric($ing['cantidad']) || $ing['cantidad'] <= 0) {
                        throw new Exception("Las cantidades de los insumos adicionales deben ser números mayores a 0.");
                    }
                    if (!isset($ing['precio']) || !is_numeric($ing['precio']) || $ing['precio'] <= 0) {
                        throw new Exception("El precio de los insumos adicionales debe ser un número válido mayor a 0.");
                    }
                    if (empty($ing['unidad'])) {
                        throw new Exception("La unidad de medida es obligatoria para los insumos adicionales.");
                    }
                }
            }
        } else if ($this->tipo_producto === 'BARRA') {
            $insumos = is_string($insumos_json) ? json_decode($insumos_json, true) : $insumos_json;
            if (!empty($insumos) && is_array($insumos) && count($insumos) > 0) {
                throw new Exception("Los productos de barra no pueden tener insumos adicionales.");
            }
        }
        $this->insumos_adicionales = $insumos_json;
    }




    //#########################################################################################


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

    public function getInsumosPrincipales()
    {
        return $this->insumos_principales;
    }

    public function getInsumosAdicionales()
    {
        return $this->insumos_adicionales;
    }










//#########################################################################################






    












    
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
                    'insumos' => $this->listarInsumos(),
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
            $this->LlamarConexion();
            $sql = "SELECT p.*, c.nombre_categoria as categoria_nombre 
                    FROM producto p
                    LEFT JOIN categoria_producto c ON p.id_categoria = c.id_categoria
                    WHERE p.tipo_producto IN ('COCINA', 'BARRA', 'POSTRE')
                    AND p.estatus = 1
                    ORDER BY p.fecha_creacion DESC";
            $stmt = $this->LlamarConexion()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->DestruirConexion();
            return $result;
        } catch (\PDOException $e) {
            error_log("Error en listarMenu: " . $e->getMessage());
            $this->DestruirConexion();
            return [];
        }
    }
    
















//#########################################################################################


    private function listarCategorias()
    {
        try {
            $this->LlamarConexion();
            $sql = "SELECT id_categoria, nombre_categoria FROM categoria_producto WHERE estatus = 1 ORDER BY nombre_categoria";
            $stmt = $this->LlamarConexion()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->DestruirConexion();
            return $result;
        } catch (\PDOException $e) {
            error_log("Error en listarCategorias: " . $e->getMessage());
            $this->DestruirConexion();
            return [];
        }
    }

















//#########################################################################################


    private function listarInsumos()
    {
        try {
            $this->LlamarConexion();
            $sql = "SELECT id_insumo, nombre_insumo, id_unidad_medida, unidad_medida as nombre_unidad 
                    FROM vw_insumo WHERE estatus = 1 ORDER BY nombre_insumo";
            $stmt = $this->LlamarConexion()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->DestruirConexion();
            return $result;
        } catch (\PDOException $e) {
            error_log("Error en listarInsumos: " . $e->getMessage());
            $this->DestruirConexion();
            return [];
        }
    }



















//#########################################################################################


    private function listarUnidades()
    {
        try {
            $this->LlamarConexion();
            $sql = "SELECT id_unidad, nombre, abreviatura, tipo FROM unidad_medida ORDER BY nombre";
            $stmt = $this->LlamarConexion()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->DestruirConexion();
            return $result;
        } catch (\PDOException $e) {
            error_log("Error en listarUnidades: " . $e->getMessage());
            $this->DestruirConexion();
            return [];
        }
    }

















//#########################################################################################


    private function registrarMenu()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            
            $this->id_producto = 'PROD' . date('YmdHis') . rand(1000, 9999);

            $sql = "INSERT INTO producto (
                    id_producto, nombre_producto, descripcion, precio, 
                    id_categoria, imagen, es_personalizable, estatus, tipo_producto
                ) VALUES (
                    :id_producto, :nombre, :descripcion, :precio, 
                    :id_categoria, :imagen, 1, 1, :tipo_producto
                )";

            $stmt = $this->LlamarConexion()->prepare($sql);
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

            
            if (!empty($this->getInsumosPrincipales())) {
                $this->insertarPreparacion($this->getIdProducto(), $this->getInsumosPrincipales(), 1);
            }

            
            if (!empty($this->getInsumosAdicionales())) {
                $this->insertarPreparacion($this->getIdProducto(), $this->getInsumosAdicionales(), 2);
            }

            $this->LlamarConexion()->commit();
            $this->DestruirConexion();
            return ['success' => true, 'id' => $this->getIdProducto(), 'message' => 'Menú registrado exitosamente'];

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            error_log("Error en registrarMenu: " . $e->getMessage());
            $this->DestruirConexion();
            return ['success' => false, 'message' => 'Error al registrar el menú: ' . $e->getMessage()];
        }
    }





















//#########################################################################################


    private function modificarMenu()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            
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

            $stmt = $this->LlamarConexion()->prepare($sql);
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

            
            $del = $this->LlamarConexion()->prepare("DELETE FROM preparacion WHERE id_producto = :id_producto");
            $del->execute(['id_producto' => $this->getIdProducto()]);

            
            if (!empty($this->getInsumosPrincipales())) {
                $this->insertarPreparacion($this->getIdProducto(), $this->getInsumosPrincipales(), 1);
            }


            if (!empty($this->getInsumosAdicionales())) {
                $this->insertarPreparacion($this->getIdProducto(), $this->getInsumosAdicionales(), 2);
            }

            $this->LlamarConexion()->commit();
            $this->DestruirConexion();
            return ['success' => true, 'id' => $this->getIdProducto(), 'message' => 'Menú guardado exitosamente'];

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            error_log("Error en modificarMenu: " . $e->getMessage());
            $this->DestruirConexion();
            return ['success' => false, 'message' => 'Error al modificar el menú: ' . $e->getMessage()];
        }
    }





















//#########################################################################################


    private function insertarPreparacion($id_producto, $insumos_json, $prioridad)
    {
        
        $insumos = is_string($insumos_json) ? json_decode($insumos_json, true) : $insumos_json;
        if (!is_array($insumos)) {
            error_log("Error: insumos no es un array valido. Valor: " . json_last_error_msg());
            return;
        }

        $sql = "INSERT INTO preparacion (id_preparacion, id_producto, id_insumo, prioridad_insumo, cantidad, id_unidad_medida, precio_insumo) 
                VALUES (:id_preparacion, :id_producto, :id_insumo, :prioridad, :cantidad, :id_unidad, :precio_insumo)";
        $stmt = $this->LlamarConexion()->prepare($sql);

        foreach ($insumos as $ing) {
            $id_preparacion = 'PREP' . date('YmdHis') . rand(100, 999);
            $stmt->execute([
                'id_preparacion' => $id_preparacion,
                'id_producto' => $id_producto,
                'id_insumo' => $ing['id'],
                'prioridad' => $prioridad,
                'cantidad' => $ing['cantidad'] ?? 1,
                'id_unidad' => $ing['unidad'] ?? 'UN',
                'precio_insumo' => !empty($ing['precio']) ? (float)$ing['precio'] : 0
            ]);
            usleep(1000); 
        }
    }













//#########################################################################################


    private function buscarMenu()
    {
        try {
            $this->LlamarConexion();
            $sql = "SELECT p.*, c.nombre_categoria 
                    FROM producto p
                    LEFT JOIN categoria_producto c ON p.id_categoria = c.id_categoria
                    WHERE p.id_producto = :id";
            $stmt = $this->LlamarConexion()->prepare($sql);
            $stmt->execute(['id' => $this->getIdProducto()]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto) {
                
                $sqlPrep = "SELECT pr.*, i.nombre_insumo, u.nombre as nombre_unidad 
                            FROM preparacion pr
                            JOIN insumo i ON pr.id_insumo = i.id_insumo
                            JOIN unidad_medida u ON pr.id_unidad_medida = u.id_unidad
                            WHERE pr.id_producto = :id_producto";
                $stPrep = $this->LlamarConexion()->prepare($sqlPrep);
                $stPrep->execute(['id_producto' => $this->getIdProducto()]);
                $preparacion = $stPrep->fetchAll(PDO::FETCH_ASSOC);

                $principales = [];
                $adicionales = [];
                foreach ($preparacion as $prep) {
                    if ($prep['prioridad_insumo'] == 1) {
                        $principales[] = $prep;
                    } else if ($prep['prioridad_insumo'] == 2) {
                        $adicionales[] = $prep;
                    }
                }
                
                $producto['insumos_principales'] = $principales;
                $producto['insumos_adicionales'] = $adicionales;
            }

            $this->DestruirConexion();
            return $producto;
        } catch (\PDOException $e) {
            error_log("Error en buscarMenu: " . $e->getMessage());
            $this->DestruirConexion();
            return null;
        }
    }

















//#########################################################################################


    private function eliminarMenu()
    {
        try {
            $this->LlamarConexion();
            $sql = "UPDATE producto SET estatus = 0 WHERE id_producto = :id_producto";
            $stmt = $this->LlamarConexion()->prepare($sql);
            $result = $stmt->execute(['id_producto' => $this->getIdProducto()]);
            $this->DestruirConexion();
            return ['success' => $result, 'message' => $result ? 'Producto eliminado' : 'Error al eliminar'];
        } catch (\PDOException $e) {
            $this->DestruirConexion();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }



















//#########################################################################################



    

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














    

//#########################################################################################


public function obtenerInsumosProducto($id_producto)
{
    try {
        $this->LlamarConexion();
        
        $sql = "SELECT p.id_preparacion, p.id_insumo, p.prioridad_insumo, p.cantidad, 
                       p.id_unidad_medida, p.precio_insumo,
                       i.nombre_insumo, i.stock_actual, u.nombre as nombre_unidad
                FROM preparacion p
                JOIN insumo i ON p.id_insumo = i.id_insumo
                JOIN unidad_medida u ON p.id_unidad_medida = u.id_unidad
                WHERE p.id_producto = ? AND i.estatus = 1
                ORDER BY p.prioridad_insumo ASC, i.nombre_insumo ASC";
        
        $stmt = $this->LlamarConexion()->prepare($sql);
        $stmt->execute([$id_producto]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->DestruirConexion();
        
        $principales = [];
        $adicionales = [];
        
        foreach ($result as $item) {
            // ==============================================
            // LIMPIAR DECIMALES REDUNDANTES USANDO HELPER
            // ==============================================
            $cantidad = Helper::limpiarDecimales($item['cantidad']);
            $precio_insumo = Helper::limpiarDecimales($item['precio_insumo'] ?? 0);
            
            $preparacion = [
                'id_preparacion' => $item['id_preparacion'],
                'id_insumo' => $item['id_insumo'],
                'prioridad_insumo' => $item['prioridad_insumo'],
                'cantidad' => $cantidad,
                'id_unidad_medida' => $item['id_unidad_medida'],
                'precio_insumo' => $precio_insumo,
                'nombre_insumo' => $item['nombre_insumo'],
                'stock_actual' => $item['stock_actual'],
                'nombre_unidad' => $item['nombre_unidad']
            ];
            
            if ($item['prioridad_insumo'] == 1) {
                $principales[] = $preparacion;
            } else {
                $adicionales[] = $preparacion;
            }
        }
        
        return ['principales' => $principales, 'adicionales' => $adicionales];
        
    } catch (\PDOException $e) {
        error_log("Error en obtenerInsumosProducto: " . $e->getMessage());
        $this->DestruirConexion();
        return ['principales' => [], 'adicionales' => []];
    }
}

/**
 * Verifica si un producto tiene suficiente stock para una cantidad específica
 * @param string $id_producto ID del producto
 * @param int $cantidad Cantidad solicitada
 * @return array ['success' => bool, 'message' => string, 'stock_disponible' => int, 'porcentaje' => float]
 */
public function verificarStockProducto($id_producto, $cantidad = 1)
{
    try {
        $this->LlamarConexion();
        
        // Obtener todos los insumos del producto con su stock
        $sql = "SELECT p.id_insumo, p.cantidad as cantidad_requerida, 
                       i.stock_actual, i.nombre_insumo,
                       i.stock_minimo, i.stock_maximo,
                       up.abreviatura as abrev_req,
                       ui.abreviatura as abrev_stock
                FROM preparacion p
                JOIN insumo i ON p.id_insumo = i.id_insumo
                LEFT JOIN unidad_medida up ON p.id_unidad_medida = up.id_unidad
                LEFT JOIN unidad_medida ui ON i.id_unidad_medida = ui.id_unidad
                WHERE p.id_producto = ? AND i.estatus = 1 AND p.prioridad_insumo = 1";
        
        $stmt = $this->LlamarConexion()->prepare($sql);
        $stmt->execute([$id_producto]);
        $insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->DestruirConexion();
        
        if (empty($insumos)) {
            return [
                'success' => true, 
                'message' => 'Producto sin insumos registrados',
                'stock_disponible' => 999,
                'porcentaje' => 100
            ];
        }
        
        // Calcular cuántas unidades se pueden hacer con el stock actual
        $unidadesPosibles = PHP_INT_MAX;
        $insumoLimitante = null;
        $insumosStock = [];
        
        $unidadMedida = new \App\Models\System\UnidadMedida();

        $insumosAgrupados = [];
        foreach ($insumos as $insumo) {
            if ($insumo['cantidad_requerida'] > 0) {
                $abrevReq = $insumo['abrev_req'] ?? 'U';
                $abrevStock = $insumo['abrev_stock'] ?? 'U';
                
                try {
                    $reqEnUnidadStock = $unidadMedida->TablaConversion($insumo['cantidad_requerida'], 0, $abrevReq, $abrevStock, 'sumar');
                } catch (\Exception $e) {
                    error_log("Error de conversión en verificarStockProducto: " . $e->getMessage());
                    $reqEnUnidadStock = $insumo['cantidad_requerida'];
                }
                
                $id = $insumo['id_insumo'];
                if (!isset($insumosAgrupados[$id])) {
                    $insumosAgrupados[$id] = [
                        'nombre_insumo' => $insumo['nombre_insumo'],
                        'stock_actual' => $insumo['stock_actual'],
                        'cantidad_requerida' => 0
                    ];
                }
                $insumosAgrupados[$id]['cantidad_requerida'] += $reqEnUnidadStock;
            }
        }

        foreach ($insumosAgrupados as $insumo) {
            if ($insumo['cantidad_requerida'] > 0) {
                $unidades = floor($insumo['stock_actual'] / $insumo['cantidad_requerida']);
            } else {
                $unidades = 999;
            }

            $insumosStock[] = [
                'nombre' => $insumo['nombre_insumo'],
                'stock_actual' => $insumo['stock_actual'],
                'requerido' => $insumo['cantidad_requerida'],
                'unidades_posibles' => $unidades
            ];
            
            if ($unidades < $unidadesPosibles) {
                $unidadesPosibles = $unidades;
                $insumoLimitante = $insumo['nombre_insumo'];
            }
        }
        
        // Calcular porcentaje de stock disponible (basado en el insumo más crítico)
        $porcentaje = 0;
        if (!empty($insumosStock)) {
            // Buscar el insumo con menor porcentaje
            $menorPorcentaje = 100;
            foreach ($insumosStock as $item) {
                $stockMinimo = $insumos[array_search($item['nombre'], array_column($insumos, 'nombre_insumo'))]['stock_minimo'] ?? 0;
                $porcentajeInsumo = $item['stock_actual'] > 0 ? ($item['stock_actual'] / ($item['stock_actual'] + $stockMinimo)) * 100 : 0;
                if ($porcentajeInsumo < $menorPorcentaje) {
                    $menorPorcentaje = $porcentajeInsumo;
                }
            }
            $porcentaje = min(100, $menorPorcentaje);
        }
        
        // Verificar si la cantidad solicitada es posible
        $stockDisponible = $unidadesPosibles;
        
        if ($cantidad > $stockDisponible) {
            return [
                'success' => false,
                'message' => "Stock insuficiente. Solo hay stock para $stockDisponible unidades. Insumo limitante: $insumoLimitante",
                'stock_disponible' => $stockDisponible,
                'porcentaje' => $porcentaje,
                'insumo_limitante' => $insumoLimitante
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Stock suficiente',
            'stock_disponible' => $stockDisponible,
            'porcentaje' => $porcentaje,
            'insumos' => $insumosStock
        ];
        
    } catch (\PDOException $e) {
        error_log("Error en verificarStockProducto: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al verificar stock: ' . $e->getMessage(),
            'stock_disponible' => 0,
            'porcentaje' => 0
        ];
    }
}



}
