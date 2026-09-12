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
use App\Helpers\RegexHelper;
use Exception;
use PDO;

class DetalleEntrada extends Database
{
    private $id;
    private $id_entrada;
    private $cantidad;
    private $id_unidad_medida;
    private $descripcion;
    private $fecha;

    public function __construct()
    {
        $this->id = "";
        $this->id_entrada = "";
        $this->cantidad = 0.0;
        $this->id_unidad_medida = "";
        $this->descripcion = "";
        $this->fecha = "";
    }

    // Getters y Setters

    //SETTERS
    public function setId(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID no cumple con el formato permitido.");
        }
        $this->id = $id;
    }

    public function setIdEntrada(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID de la Entrada no cumple con el formato permitido.");
        }
        $this->id_entrada = $id;
    }

    public function setIdUnidad(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID de la Unidad de Medida no cumple con el formato permitido.");
        }
        $this->id_unidad_medida = $id;
    }

    public function setCantidad(string $cantidad)
    {
        if ($cantidad < 0) {
            throw new Exception("El valor ingresado no puede ser negativo");
        }
        $this->cantidad = $cantidad;
    }

    public function setDescripcion(string $descripcion)
    {
        if (RegexHelper::ValidarFormatos($descripcion, 'Descripcion') == 0) {
            throw new Exception("El valor ingresado no puede ser negativo");
        }
        $this->descripcion = $descripcion;
    }

    public function setFecha(\DateTime $fecha)
    {
        $this->fecha = $fecha;
    }
    //FIN SETTERS

    //GETTERS
    public function getId(string $id)
    {
        return $this->id = $id;
    }

    public function getIdEntrada(string $id)
    {
        return $this->id_entrada = $id;
    }

    public function getIdUnidad(string $id)
    {
        return $this->id_unidad_medida = $id;
    }

    public function getCantidad(string $cantidad)
    {
        return $this->cantidad = $cantidad;
    }

    public function getDescripcion(string $descripcion)
    {
        return $this->descripcion = $descripcion;
    }

    public function getFecha(\DateTime $fecha)
    {
        return $this->fecha = $fecha;
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
                'registrar' => $this->RegistrarDetalleEntrada(),
                'consultar' => $this->ConsultarDetalleEntrada(),
                'historial_insumo' => $this->ConsultarDetalleEntrada($peticion['filtro']),
                'validar' => $this->ValidarDetalleEntrada(),
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
    private function ConsultarDetalleEntrada(string $filtro = NULL)
    {
        if ($filtro != NULL) {
            if (RegexHelper::ValidarFormatos($filtro, 'ID') == 0) {
                throw new Exception("El ID no cumple con el formato permitido.");
            }
        }

        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM vw_detalle_entrada_insumo";

            if ($filtro != NULL) {
                $sql .= " WHERE id_insumo = :id_insumo";
            }
            $stm = $this->LlamarConexion()->prepare($sql);

            if ($filtro != NULL) {
                $stm->bindParam(':id_insumo', $filtro);
            }
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

    private function RegistrarDetalleEntrada()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarDetalleEntrada();
        if ($validacion['bool'] == 0) {
            try {
                $sql = "INSERT INTO detalle_entrada(id_detalle, id_entrada, id_unidad_medida, cantidad, descripcion) 
                VALUES (:id, :id_entrada, :id_unidad_medida, :cantidad, :descripcion)";

                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id', $this->id);
                $stm->bindParam(':id_entrada', $this->id_entrada);
                $stm->bindParam(':id_unidad_medida', $this->id_unidad_medida);
                $stm->bindParam(':cantidad', $this->cantidad);
                $stm->bindParam(':descripcion', $this->descripcion);
                $stm->execute();
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Relación de Entrada registrado exitosamente"];
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

    private function ValidarDetalleEntrada()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM entrada_insumo WHERE id_entrada = :id";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id', $this->id);
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