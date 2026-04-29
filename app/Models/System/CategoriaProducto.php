<?php

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\RegexHelper;
use PDO;
use Exception;

class CategoriaProducto
{
    private $db;
    private $id_categoria;
    private $nombre_categoria;
    private $descripcion;
    private $icono;
    private $estatus;

    public function __construct()
    {
        $this->db = Database::getConnection('business');
    }

    // Getters y Setters
    public function setIdCategoria($id)
    {
        if (empty($id) || RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID de la categoría no es válido.");
        }
        $this->id_categoria = $id;
    }
    
    public function setNombreCategoria($nombre)
    {
        if (empty($nombre) || RegexHelper::ValidarFormatos($nombre, 'Objeto') == 0) {
            throw new Exception("El nombre de la categoría no es válido (solo letras, números y espacios, de 3 a 65 caracteres).");
        }
        $this->nombre_categoria = $nombre;
    }
    
    public function setDescripcion($desc)
    {
        if (!empty($desc) && RegexHelper::ValidarFormatos($desc, 'ObjetoLargo') == 0) {
            throw new Exception("La descripción no es válida (máximo 200 caracteres permitidos).");
        }
        $this->descripcion = $desc;
    }
    
    public function setIcono($icono)
    {
        $this->icono = empty($icono) ? 'default.png' : $icono;
    }
    
    public function setEstatus($estatus)
    {
        if (!in_array((string)$estatus, ['0', '1'], true)) {
            throw new Exception("Estatus de categoría no válido.");
        }
        $this->estatus = $estatus;
    }

    public function Transaccion($peticion)
    {
        try {
            switch ($peticion['peticion']) {
                case 'listar':
                    return $this->listarCategorias();
                case 'consultar':
                    return $this->listarTodasCategorias();
                case 'guardar':
                    return $this->guardarCategoria();
                case 'actualizar':
                    return $this->actualizarCategoria();
                case 'eliminar':
                    return $this->eliminarCategoria();
                case 'buscar':
                    return $this->buscarCategoria();
                default:
                    return ['success' => false, 'message' => 'Petición no válida'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function listarCategorias()
    {
        try {
            $sql = "SELECT * FROM categoria_producto WHERE estatus = 1 ORDER BY nombre_categoria";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarCategorias: " . $e->getMessage());
            return [];
        }
    }

    private function listarTodasCategorias()
    {
        try {
            $sql = "SELECT * FROM categoria_producto WHERE estatus = 1 ORDER BY nombre_categoria";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarTodasCategorias: " . $e->getMessage());
            return [];
        }
    }

    private function guardarCategoria()
    {
        try {
            $this->db->beginTransaction();
            $this->id_categoria = $this->generarIdCategoria();

            $sql = "INSERT INTO categoria_producto (
                    id_categoria, 
                    nombre_categoria, 
                    descripcion, 
                    icono, 
                    estatus
                ) VALUES (
                    :id_categoria,
                    :nombre_categoria, 
                    :descripcion, 
                    :icono, 
                    1
                )";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id_categoria' => $this->id_categoria,
                'nombre_categoria' => $this->nombre_categoria,
                'descripcion' => $this->descripcion ?? '',
                'icono' => $this->icono ?? 'default.png'
            ]);

            if ($result) {
                $this->db->commit();
                return ['success' => true, 'id' => $this->id_categoria, 'message' => 'Categoría guardada exitosamente'];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al guardar la categoría'];
            
        } catch (\PDOException $e) {
            $this->db->rollBack();
            if ($e->errorInfo[1] == 1062) {
                return ['success' => false, 'message' => 'Ya existe una categoría con ese nombre'];
            }
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function actualizarCategoria()
    {
        try {
            $this->db->beginTransaction();
            $sql = "UPDATE categoria_producto SET 
                    nombre_categoria = :nombre_categoria,
                    descripcion = :descripcion,
                    icono = :icono,
                    estatus = :estatus
                    WHERE id_categoria = :id_categoria";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id_categoria' => $this->id_categoria,
                'nombre_categoria' => $this->nombre_categoria,
                'descripcion' => $this->descripcion,
                'icono' => $this->icono,
                'estatus' => $this->estatus
            ]);

            if ($result) {
                $this->db->commit();
                return ['success' => true, 'message' => 'Categoría actualizada'];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al actualizar'];
            
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function eliminarCategoria()
    {
        try {
            $this->db->beginTransaction();
            $checkSql = "SELECT COUNT(*) as total FROM producto WHERE id_categoria = :id_categoria";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['id_categoria' => $this->id_categoria]);
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se puede eliminar: Hay productos usando esta categoría'];
            }

            $sql = "UPDATE categoria_producto SET estatus = 0 WHERE id_categoria = :id_categoria";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id_categoria' => $this->id_categoria]);

            if ($result) {
                $this->db->commit();
                return ['success' => true, 'message' => 'Categoría eliminada'];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al eliminar'];
            
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function buscarCategoria()
    {
        try {
            $sql = "SELECT * FROM categoria_producto WHERE id_categoria = :id_categoria";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_categoria' => $this->id_categoria]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en buscarCategoria: " . $e->getMessage());
            return null;
        }
    }

    private function generarIdCategoria()
    {
        $prefijo = 'CAT';
        $fecha = date('YmdHis');
        $random = rand(1000, 9999);
        return $prefijo . $fecha . $random;
    }
}