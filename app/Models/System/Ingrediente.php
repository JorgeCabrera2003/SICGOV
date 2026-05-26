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
    private $id_categoria_ingrediente;
    private $id_unidad_medida;

    public function __construct()
    {
        $this->id = "";
        $this->nombre = "";
        $this->id_unidad_medida = "";
        $this->stock_actual = NULL;
        $this->stock_minimo = NULL;
        $this->stock_maximo = NULL;
        $this->precio_unitario = 0.0;
        $this->estatus = 0;
        $this->id_categoria_ingrediente = "";
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
        $this->id_unidad_medida = $unidad;
    }

    public function setStockActual(float $stock)
    {
        $this->stock_actual = $stock;
    }

    public function setStockMaximo(float $stock)
    {
        $this->stock_maximo = $stock;
    }

    public function setStockMinimo(float $stock)
    {
        $this->stock_minimo = $stock;
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
        $this->id_categoria_ingrediente = $id;
    }

    public function setIdUnidadMedida(string $id)
    {
        $this->id_unidad_medida = $id;
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
        return $this->id_unidad_medida;
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
    public function Transaccion($peticion, )
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
                $sql = "INSERT INTO ingrediente(id_ingrediente, id_categoria, nombre_ingrediente, 
                id_unidad_medida, precio_unitario, stock_actual, stock_minimo, stock_maximo)
                VALUES (:id_ingrediente, :id_categoria, :nombre_ingrediente, 
                :id_unidad_medida, :precio_unitario, :stock_actual, :stock_minimo, :stock_maximo)";

                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_ingrediente', $this->id);
                $stm->bindParam(':id_categoria', $this->id_categoria_ingrediente);
                $stm->bindParam(':nombre_ingrediente', $this->nombre);
                $stm->bindParam(':id_unidad_medida', $this->id_unidad_medida);
                $stm->bindParam(':stock_actual', $this->stock_actual);
                $stm->bindParam(':stock_minimo', $this->stock_minimo);
                $stm->bindParam(':stock_maximo', $this->stock_maximo);
                $stm->bindParam(':precio_unitario', $this->precio_unitario);
                $stm->execute();
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Ingrediente registrado exitosamente"];
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
            $sql = "UPDATE ingrediente SET id_categoria = :id_categoria, nombre_ingrediente = :nombre_ingrediente,
            id_unidad_medida = :id_unidad_medida, precio_unitario = :precio_unitario, stock_minimo = :stock_minimo,
            stock_maximo = :stock_maximo WHERE id_ingrediente = :id_ingrediente";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_ingrediente', $this->id);
            $stm->bindParam(':nombre_ingrediente', $this->nombre);
            $stm->bindParam(':id_categoria', $this->id_categoria_ingrediente);
            $stm->bindParam(':id_unidad_medida', $this->id_unidad_medida);
            $stm->bindParam(':stock_minimo', $this->stock_minimo);
            $stm->bindParam(':stock_maximo', $this->stock_maximo);
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