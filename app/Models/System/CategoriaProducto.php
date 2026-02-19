<?php

namespace App\Models\System;

use App\Core\Database;
use PDO;

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
        $this->id_categoria = $id;
    }
    
    public function setNombreCategoria($nombre)
    {
        $this->nombre_categoria = $nombre;
    }
    
    public function setDescripcion($desc)
    {
        $this->descripcion = $desc;
    }
    
    public function setIcono($icono)
    {
        $this->icono = $icono;
    }
    
    public function setEstatus($estatus)
    {
        $this->estatus = $estatus;
    }

    public function Transaccion($peticion)
    {
        switch ($peticion['peticion']) {
            case 'listar':
                return $this->listarCategorias();
            case 'guardar':
                return $this->guardarCategoria();
            case 'actualizar':
                return $this->actualizarCategoria();
            case 'eliminar':
                return $this->eliminarCategoria();
            case 'buscar':
                return $this->buscarCategoria();
            default:
                return false;
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

    private function guardarCategoria()
    {
        try {
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
                return ['success' => true, 'id' => $this->id_categoria, 'message' => 'Categoría guardada exitosamente'];
            }
            return ['success' => false, 'message' => 'Error al guardar la categoría'];
            
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return ['success' => false, 'message' => 'Ya existe una categoría con ese nombre'];
            }
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function actualizarCategoria()
    {
        try {
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

            return ['success' => $result, 'message' => $result ? 'Categoría actualizada' : 'Error al actualizar'];
            
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function eliminarCategoria()
    {
        try {
            $checkSql = "SELECT COUNT(*) as total FROM producto WHERE id_categoria = :id_categoria";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['id_categoria' => $this->id_categoria]);
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                return ['success' => false, 'message' => 'No se puede eliminar: Hay productos usando esta categoría'];
            }

            $sql = "UPDATE categoria_producto SET estatus = 0 WHERE id_categoria = :id_categoria";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id_categoria' => $this->id_categoria]);

            return ['success' => $result, 'message' => $result ? 'Categoría eliminada' : 'Error al eliminar'];
            
        } catch (\PDOException $e) {
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