<?php

/*
MODELO DE INGREDIENTE

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
use App\Models\System\UnidadMedida;
use App\Models\System\CategoriaIngrediente;
use PDO;

class Ingrediente extends Database
{
    private $id;
    private $nombre;
    private $precio_unitario;
    private $stock_actual;
    private $stock_minimo;
    private $stock_maximo;
    private $estatus;
    private $categoria_ingrediente;
    private $unidad_medida;

    public function __construct()
    {
        $this->id = "";
        $this->nombre = "";
        $this->unidad_medida = "";
        $this->precio_unitario = 0.0;
        $this->estatus = 0;
        $this->unidad_medida = new UnidadMedida();
        $this->categoria_ingrediente = new CategoriaIngrediente();
    }

    private function LlamarUnidadMedida()
    {
        if ($this->unidad_medida == NULL) {
            $this->unidad_medida = new UnidadMedida();
        }
        return $this->unidad_medida;
    }

    private function LlamarCategoriaIngrediente()
    {
        if ($this->categoria_ingrediente == NULL) {
            $this->categoria_ingrediente = new CategoriaIngrediente();
        }
        return $this->categoria_ingrediente;
    }

    // Getters y Setters

    //SETTERS
    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function setNombre(string $nombre)
    {
        $this->nombre = $nombre;
    }

    public function setUnidadMedida(string $unidad)
    {
        $this->unidad_medida = $unidad;
    }

    public function setPrecioUnitario(float $precio)
    {
        $this->precio_unitario = $precio;
    }

    public function setEstatus(int $estatus)
    {
        $this->estatus = $estatus;
    }

    public function setIdCategoria(string $id)
    {
        $this->LlamarCategoriaIngrediente()->setId($id);
    }

    public function setIdUnidadMedida(string $id)
    {
        $this->LlamarUnidadMedida()->setId($id);
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

    public function getUnidadMedida()
    {
        return $this->unidad_medida;
    }

    public function getPrecioUnitario()
    {
        return $this->precio_unitario;
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
                'registrar' => $this->RegistrarIngrediente(),
                'consultar' => $this->ConsultarIngrediente(),
                'actualizar', 'modificar' => $this->ModificarIngrediente(),
                'eliminar' => $this->EliminarIngrediente(),
                'validar' => $this->ValidarIngrediente(),
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
    private function ConsultarIngrediente()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM vw_ingrediente";
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

    private function RegistrarIngrediente()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarIngrediente();
        if ($validacion['bool'] == 0) {
            try {
                $sql = "INSERT INTO ingrediente(id_ingrediente, nombre_ingrediente, unidad_medida, precio_unitario)
                VALUES (:id_ingrediente, :nombre_ingrediente, :unidad_medida, :precio_unitario)";

                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_ingrediente', $this->id);
                $stm->bindParam(':nombre_ingrediente', $this->nombre);
                $stm->bindParam(':unidad_medida', $this->unidad_medida);
                $stm->bindParam(':precio_unitario', $this->precio_unitario);
                $stm->execute();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Ingrediente actualizado exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => "OK"];

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

    private function ModificarIngrediente()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE ingrediente SET nombre_ingrediente = :nombre_ingrediente, unidad_medida = :unidad_medida, 
            precio_unitario = :precio_unitario WHERE id_ingrediente = :id_ingrediente";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_ingrediente', $this->id);
            $stm->bindParam(':nombre_ingrediente', $this->nombre);
            $stm->bindParam(':unidad_medida', $this->unidad_medida);
            $stm->bindParam(':precio_unitario', $this->precio_unitario);
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

    private function EliminarIngrediente()
    {
        $dato = [];
        $validacion = $this->ValidarIngrediente();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "UPDATE ingrediente SET estatus = 0 WHERE id_ingrediente = :id_ingrediente";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam('id_ingrediente', $this->id);
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

    private function ValidarIngrediente()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM vw_ingrediente WHERE id_ingrediente = :id_ingrediente";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_ingrediente', $this->id);
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