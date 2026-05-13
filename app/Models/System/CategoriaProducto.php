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
        $nombre = trim($nombre ?? '');
        if (empty($nombre)) {
            throw new Exception("El nombre de la categoría es obligatorio.");
        }
        if (mb_strlen($nombre) < 2) {
            throw new Exception("El nombre de la categoría debe tener al menos 2 caracteres.");
        }
        if (!preg_match('/^[A-ZÁÉÍÓÚÑ]/', $nombre)) {
            throw new Exception("El nombre de la categoría debe comenzar con una letra mayúscula.");
        }
        if (RegexHelper::ValidarFormatos($nombre, 'CategoriaMenu') == 0) {
            throw new Exception("El nombre de la categoría solo puede contener letras y espacios (2 a 65 caracteres).");
        }
        $this->nombre_categoria = $nombre;
    }
    
    public function setDescripcion($desc)
    {
        $desc = trim($desc ?? '');
        if (empty($desc)) {
            $this->descripcion = '';
            return;
        }
        if (mb_strlen($desc) < 2) {
            throw new Exception("La descripción debe tener al menos 2 caracteres.");
        }
        if (!preg_match('/^[A-ZÁÉÍÓÚÑ]/', $desc)) {
            throw new Exception("La descripción debe comenzar con una letra mayúscula.");
        }
        if (RegexHelper::ValidarFormatos($desc, 'CategoriaMenuDesc') == 0) {
            throw new Exception("La descripción solo puede contener letras y espacios (2 a 200 caracteres).");
        }
        $this->descripcion = $desc;
    }
    
    public function setEstatus($estatus)
    {
        if (!in_array((string)$estatus, ['0', '1'], true)) {
            throw new Exception("Estatus de categoría no válido.");
        }
        $this->estatus = $estatus;
    }






    public function getIdCategoria() 
    { 
        return $this->id_categoria; 
    }
    public function getNombreCategoria() 
    { 
        return $this->nombre_categoria; 
    }
    public function getDescripcion() 
    { 
        return $this->descripcion; 
    }
    public function getEstatus() 
    { 
        return $this->estatus; 
    }






    

//#########################################################################################


    public function Transaccion($peticion)
    {
        $response = ['success' => false, 'message' => 'Petición no válida'];

        if (isset($peticion['peticion'])) {
            try {
                $response = match ($peticion['peticion']) {
                    'listar'     => $this->listarCategorias(),
                    'consultar'  => $this->listarTodasCategorias(),
                    'guardar'    => $this->guardarCategoria(),
                    'actualizar' => $this->actualizarCategoria(),
                    'eliminar'   => $this->eliminarCategoria(),
                    'buscar'     => $this->buscarCategoria(),
                    'verificar'  => $this->verificarNombreExiste($peticion['id_excluir'] ?? null),
                    default      => ['success' => false, 'message' => 'Petición no válida']
                };
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return $response;
    }












//#########################################################################################


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










//#########################################################################################


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













//#########################################################################################


    private function guardarCategoria()
    {
        try {
            $this->db->beginTransaction();
            $this->id_categoria = $this->generarIdCategoria();

            $sql = "INSERT INTO categoria_producto (
                    id_categoria, 
                    nombre_categoria, 
                    descripcion, 
                    estatus
                ) VALUES (
                    :id_categoria,
                    :nombre_categoria, 
                    :descripcion, 
                    1
                )";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id_categoria'     => $this->getIdCategoria(),
                'nombre_categoria' => $this->getNombreCategoria(),
                'descripcion'      => $this->getDescripcion() ?? ''
            ]);

            if ($result) {
                $this->db->commit();
                return ['success' => true, 'id' => $this->getIdCategoria(), 'message' => 'Categoría guardada exitosamente'];
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
















//#########################################################################################


    private function actualizarCategoria()
    {
        try {
            $this->db->beginTransaction();
            $sql = "UPDATE categoria_producto SET 
                    nombre_categoria = :nombre_categoria,
                    descripcion = :descripcion,
                    estatus = :estatus
                    WHERE id_categoria = :id_categoria";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'id_categoria'     => $this->getIdCategoria(),
                'nombre_categoria' => $this->getNombreCategoria(),
                'descripcion'      => $this->getDescripcion(),
                'estatus'          => $this->getEstatus()
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










    


//#########################################################################################


    private function eliminarCategoria()
    {
        try {
            $this->db->beginTransaction();
            $checkSql = "SELECT COUNT(*) as total FROM producto WHERE id_categoria = :id_categoria";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute(['id_categoria' => $this->getIdCategoria()]);
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se puede eliminar: Hay productos usando esta categoría'];
            }

            $sql = "UPDATE categoria_producto SET estatus = 0 WHERE id_categoria = :id_categoria";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id_categoria' => $this->getIdCategoria()]);

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












//#########################################################################################


    private function buscarCategoria()
    {
        try {
            $sql = "SELECT * FROM categoria_producto WHERE id_categoria = :id_categoria";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_categoria' => $this->getIdCategoria()]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en buscarCategoria: " . $e->getMessage());
            return null;
        }
    }


//#########################################################################################


    /**
     * Verifica si ya existe una categoría activa con el mismo nombre.
     * Al editar, se excluye el propio registro usando $id_excluir.
     *
     * @param string|null $id_excluir ID de la categoría que se está editando (null al registrar)
     * @return array ['existe' => bool, 'message' => string]
     */
    private function verificarNombreExiste(?string $id_excluir = null): array
    {
        try {
            if (empty($this->nombre_categoria)) {
                return ['existe' => false, 'message' => ''];
            }

            if ($id_excluir) {
                // Al editar: excluir el registro actual para permitir guardar sin cambiar nombre
                $sql = "SELECT COUNT(*) as total FROM categoria_producto
                        WHERE LOWER(nombre_categoria) = LOWER(:nombre)
                          AND estatus = 1
                          AND id_categoria != :id_excluir";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'nombre'     => $this->nombre_categoria,
                    'id_excluir' => $id_excluir
                ]);
            } else {
                // Al registrar: buscar cualquier coincidencia activa
                $sql = "SELECT COUNT(*) as total FROM categoria_producto
                        WHERE LOWER(nombre_categoria) = LOWER(:nombre)
                          AND estatus = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['nombre' => $this->nombre_categoria]);
            }

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $existe = ($resultado['total'] ?? 0) > 0;

            return [
                'existe'  => $existe,
                'message' => $existe ? 'Ya existe una categoría con ese nombre.' : ''
            ];

        } catch (\PDOException $e) {
            error_log("Error en verificarNombreExiste: " . $e->getMessage());
            return ['existe' => false, 'message' => 'Error al verificar el nombre.'];
        }
    }










//#########################################################################################


    private function generarIdCategoria()
    {
        $prefijo = 'CAT';
        $fecha = date('YmdHis');
        $random = rand(1000, 9999);
        return $prefijo . $fecha . $random;
    }
}