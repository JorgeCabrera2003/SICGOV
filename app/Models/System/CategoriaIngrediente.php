<?php

/*
MODELO DE CATEGORIA INGREDIENTE

OPERACIONES A BASE DE DATOS:
    REGISTRAR
    CONSULTAR
    MODIFICAR
    ELIMINAR (LÓGICO)
    VALIDAR
*/

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use Exception;
use PDO;

class CategoriaIngrediente extends Database
{
    private $id;
    private $nombre;
    private $descripcion;
    private $estatus;

    public function __construct()
    {
        $this->id = "";
        $this->nombre = "";
        $this->descripcion = "";
        $this->estatus = 0;
    }

    // Getters y Setters

    //SETTERS
    public function setId(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID de la Categoría no cumple con el formato permitido.");
        }
        $this->id = $id;
    }

    public function setNombre(string $nombre)
    {
        if (RegexHelper::ValidarFormatos($nombre, 'Objeto') == 0) {
            throw new Exception("El Nombre de la Categoría no cumple con el formato permitido.");
        }
        $this->nombre = $nombre;
    }

    public function setDescripcion(string $descripcion)
    {
        if (RegexHelper::ValidarFormatos($descripcion, 'ObjetoLargo') == 0) {
            throw new Exception("La Descripción de la Categoría no cumple con el formato permitido");
        }
        $this->descripcion = $descripcion;
    }

    public function setEstatus(int $estatus)
    {
        if ($estatus != 0 && $estatus != 1) {
            throw new Exception("El ID de la noticia no cumple con el formato permitido.");
        }
        $this->estatus = $estatus;
    }

    //FIN SETTERS

    //GETTERS
    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }
    public function getEstatus()
    {
        return $this->estatus;
    }
    //FIN GETTERS

    // MANEJADOR DE OPERACIONES
    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarCategoriaIngrediente(),
                'consultar' => $this->ConsultarCategoriaIngrediente(),
                'actualizar', 'modificar' => $this->ModificarCategoriaIngrediente(),
                'eliminar' => $this->EliminarCategoriaIngrediente(),
                'validar' => $this->ValidarCategoriaIngrediente(),
                default => [
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }
    //FIN DE MANEJADOR DE OPERACIONES

    //OPERACIONES A BASE DE DATOS
    private function ConsultarCategoriaIngrediente()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM categoria_ingrediente WHERE estatus = 1";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }
            $this->LlamarConexion()->commit();
            $stm = NULL;

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

    private function RegistrarCategoriaIngrediente()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarCategoriaIngrediente();
        if ($validacion['bool'] == 0) {
            try {
                $sql = "INSERT INTO categoria_ingrediente(id_categoria, nombre, descripcion) VALUES 
                (:id_categoria, :nombre, :descripcion)";

                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_categoria', $this->id);
                $stm->bindParam(':nombre', $this->nombre);
                $stm->bindParam(':descripcion', $this->descripcion);
                $stm->execute();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Categoria registrada exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

            } catch (\PDOException $e) {
                $this->LlamarConexion()->rollBack();
                Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
            }
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ModificarCategoriaIngrediente()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE categoria_ingrediente SET nombre = :nombre, descripcion = :descripcion 
            WHERE id_categoria = :id_categoria";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_categoria', $this->id);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->bindParam(':descripcion', $this->descripcion);
            $stm->execute();
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Ingrediente actualizado exitosamente"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function EliminarCategoriaIngrediente()
    {
        $dato = [];
        $validacion = $this->ValidarCategoriaIngrediente();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "DELETE categoria_ingrediente WHERE id_categoria = :id_categoria";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam('id_categoria', $this->id);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Ingrediente eliminado exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            } catch (\PDOException $e) {
                Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor"];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
            }
        } else {
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 404, 'icon' => 'error', 'mensaje' => "Registro no encontrado"];
            $dato['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => "No encontrado"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ValidarCategoriaIngrediente()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM categoria_ingrediente WHERE id_categoria = :id_categoria";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_categoria', $this->id);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetch(PDO::FETCH_ASSOC);
                $dato['bool'] = 1;

            } else {
                $dato['bool'] = 0;
            }
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'registro' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['bool'] = -1;
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'registro' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }
}