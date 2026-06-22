<?php
namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use PDO;
use Exception;

class Areas extends Database
{
    private $id_area;
    private $nombre;
    private $descripcion;
    private $estatus;

    public function __construct()
    {
        $this->id_area = "";
        $this->nombre = "";
        $this->descripcion = "";
        $this->estatus = 1;
    }

    public function LlamarConexion($nombreBD = 'system', ?PDO &$pdo = NULL)
    {
        return parent::LlamarConexion($nombreBD, $pdo);
    }

    // SETTERS CON VALIDACIÓN RIGUROSA (RegexHelper)
    public function setIdArea(string $id_area) { 
        if (RegexHelper::ValidarFormatos($id_area, 'ID') == 0) {
            throw new Exception("El ID del área no cumple con el formato permitido.");
        }
        $this->id_area = $id_area; 
    }

    public function setNombre(string $nombre) { 
    $nombre = trim($nombre);
    if (empty($nombre) || strlen($nombre) < 3 || strlen($nombre) > 60) {
        throw new Exception("El nombre del área debe tener entre 3 y 60 caracteres.");
    }
    // Eliminada la validación de caracteres especiales
    $this->nombre = $nombre; 
}

    public function setDescripcion(string $descripcion = null) { 
        if ($descripcion !== null) {
            $descripcion = trim($descripcion);
            if (!empty($descripcion) && strlen($descripcion) > 200) {
                throw new Exception("La descripción no puede exceder los 200 caracteres.");
            }
        }
        $this->descripcion = $descripcion; 
    }

    public function setEstatus(int $estatus) { 
        if (!in_array($estatus, [0, 1])) {
            throw new Exception("El estatus debe ser 0 (inactivo) o 1 (activo).");
        }
        $this->estatus = $estatus; 
    }

    // GETTERS
    public function getIdArea() { 
        return $this->id_area; 
    }

    public function getNombre() { 
        return $this->nombre; 
    }

    public function getDescripcion() { 
        return $this->descripcion; 
    }

    public function getEstatus() { 
        return $this->estatus; 
    }

    // MANEJADOR DE OPERACIONES
    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarArea(),
                'consultar' => $this->ConsultarAreas(),
                'actualizar', 'modificar' => $this->ModificarArea(),
                'eliminar' => $this->EliminarArea(),
                default => [
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }

    // Consultar todas las áreas
    private function ConsultarAreas()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "SELECT id_area, nombre, descripcion, estatus 
                    FROM area_mesa 
                    ORDER BY nombre ASC";
            
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $this->LlamarConexion()->commit();
            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => "OK", 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Ups, intente de nuevo más tarde", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    // Registrar un área
    private function RegistrarArea()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "INSERT INTO area_mesa(id_area, nombre, descripcion, estatus)
                    VALUES (:id_area, :nombre, :descripcion, :estatus)";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_area', $this->id_area);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->bindParam(':descripcion', $this->descripcion);
            $stm->bindParam(':estatus', $this->estatus);
            $stm->execute();

            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Área registrada con éxito"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $dato['estado'] = 0;
                $dato['response'] = ['resultado' => 409, 'icon' => 'warning', 'mensaje' => "El ID del área ya existe. Use un identificador diferente"];
                $dato['HTTP_STATUS'] = ['codigo' => 409, 'mensaje' => "Conflicto - ID duplicado"];
            } else {
                Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
            }
        }
        $this->DestruirConexion();
        return $dato;
    }

    // Modificar un área existente
    private function ModificarArea()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE area_mesa SET 
                        nombre = :nombre, 
                        descripcion = :descripcion, 
                        estatus = :estatus 
                    WHERE id_area = :id_area";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_area', $this->id_area);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->bindParam(':descripcion', $this->descripcion);
            $stm->bindParam(':estatus', $this->estatus);
            $stm->execute();

            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Área actualizada con éxito"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    // Eliminar un área (borrado lógico)
    private function EliminarArea()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            // Borrado lógico: cambiar estatus a 0
            $sql = "UPDATE area_mesa SET estatus = 0 WHERE id_area = :id_area";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_area', $this->id_area);
            $stm->execute();

            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Área eliminada con éxito"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }
}