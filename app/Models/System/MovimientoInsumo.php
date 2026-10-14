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

class MovimientoInsumo extends Database
{
    private $id;
    private $id_insumo;
    private $id_detalle;
    private $id_unidad_medida;
    
    private $cantidad;
    
    private $descripcion;
    private $tipo;
    private $fecha;

    public function __construct()
    {
        $this->id = "";
        $this->id_insumo = "";
        $this->id_detalle = "";
        $this->id_unidad_medida = "";
        $this->cantidad = 0;
        $this->tipo = "";
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

    public function setIdInsumo(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID del Insumo no cumple con el formato permitido.");
        }
        $this->id_insumo = $id;
    }

        public function setIdDetalle(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID del Detalle no cumple con el formato permitido.");
        }
        $this->id_detalle = $id;
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
            throw new Exception("Descripcion no válida");
        }
        $this->descripcion = $descripcion;
    }

        public function setTipo(string $tipo)
    {
        if (RegexHelper::ValidarFormatos($tipo, 'Descripcion') == 0) {
            throw new Exception("Tipo no válido");
        }
        $this->tipo = $tipo;
    }

    public function setFecha(\DateTime $fecha)
    {
        $this->fecha = $fecha;
    }
    //FIN SETTERS

    //GETTERS
    public function getId()
    {
        return $this->id;
    }

    public function getIdInsumo()
    {
        return $this->id_insumo;
    }

    public function getIdUnidad()
    {
        return $this->id_unidad_medida;
    }

    public function getCantidad()
    {
        return $this->cantidad;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function getFecha()
    {
        return $this->fecha;
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
                'registrar' => $this->RegistrarMovimientoInsumo(),
                'consultar' => $this->ConsultarMovimientoInsumo(),
                'historial_insumo' => $this->ConsultarMovimientoInsumo($peticion['filtro']),
                'validar' => $this->ValidarMovimientoInsumo(),
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
    private function ConsultarMovimientoInsumo(string $filtro = NULL)
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
            $sql = "SELECT * FROM vw_movimiento_insumo";

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

    private function RegistrarMovimientoInsumo()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarMovimientoInsumo();
        if ($validacion['bool'] == 0) {
            try {
                $sql = "INSERT INTO movimiento_insumo(id_movimiento, id_insumo, id_detalle, cantidad, id_unidad_medida, tipo, descripcion, fecha)
                VALUES (:id, :id_insumo, :id_detalle, :cantidad, :id_unidad_medida, :tipo, :descripcion, :fecha)";

                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id', $this->id);
                $stm->bindParam(':id_insumo', $this->id_insumo);
                $stm->bindParam(':id_unidad_medida', $this->id_unidad_medida);
                $stm->bindParam(':id_detalle', $this->id_detalle);
                $stm->bindParam(':tipo', $this->tipo);
                $stm->bindParam(':cantidad', $this->cantidad);
                $stm->bindParam(':descripcion', $this->descripcion);
                
                // Formatear la fecha si es objeto DateTime o un string
                $fechaFormateada = $this->fecha instanceof \DateTime ? $this->fecha->format('Y-m-d H:i:s') : (empty($this->fecha) ? date('Y-m-d H:i:s') : $this->fecha);
                $stm->bindParam(':fecha', $fechaFormateada);
                
                $stm->execute();
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Movimiento de Insumo registrado exitosamente"];
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

    private function ValidarMovimientoInsumo()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM movimiento_insumo WHERE id_movimiento = :id";
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