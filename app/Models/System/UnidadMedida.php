<?php

/*
MODELO DE UNIDAD DE MEDIDA

OPERACIONES A BASE DE DATOS:
    CONSULTAR
    VALIDAR
*/

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use APP\Helpers\RegexHelper;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PDO;

class UnidadMedida extends Database
{
    private $id;
    private $nombre;
    private $abreviatura;
    private $factor_conversion;
    private $tipo;
    private $unidad_base;

    public function __construct()
    {
        $this->id = "";
        $this->nombre = "";
        $this->abreviatura = "";
        $this->factor_conversion = 0.0;
        $this->unidad_base = 0.0;
        $this->tipo = "";
    }

    // Getters y Setters
    public function setId($id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new \Exception("El ID no cumple con el formato permitido.");
        }
        $this->id = $id;
    }
    //GETTERS
    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getAbreviatura()
    {
        return $this->abreviatura;
    }

    public function getFactorConversion()
    {
        return $this->factor_conversion;
    }

    public function getTipo()
    {
        return $this->tipo;
    }
    public function getUnidadBase()
    {
        return $this->unidad_base;
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
                'consultar' => $this->ConsultarUnidadMedida(),
                'validar' => $this->ValidarUnidadMedida(),
                'filtrar' => $this->FiltrarUnidadMedida(),

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
    private function ConsultarUnidadMedida()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM unidad_medida ORDER BY tipo ASC";
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

    private function ValidarUnidadMedida()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM unidad_medida WHERE id_unidad = :id_unidad";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_unidad', $this->id);
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

    private function FiltrarUnidadMedida()
    {
        $dato = [];
        $arreglo = [];
        $datosBD = [];
        try {
            $arreglo = $this->ValidarUnidadMedida();
            if ($arreglo['bool'] == 1) {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "SELECT * FROM unidad_medida WHERE tipo = :tipo ORDER BY tipo ASC";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(":tipo", $arreglo['response']['registro']['tipo']);
                $stm->execute();
                if ($stm->rowCount() > 0) {
                    $datosBD = $stm->fetchAll(PDO::FETCH_ASSOC);
                }
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'mensaje' => "OK", 'datos' => $datosBD];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            } else {

            }

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

    public function TablaConversion(float $valor, float $stock_actual, string $medida_valor, string $medida_stock, string $operacion)
    {
        $resultado = 0;
        $medida_valor = strtolower($medida_valor);
        $medida_stock = strtolower($medida_stock);

        $resultadoBase = 0;

        $valorStock = 0;
        $valorEntrante = 0;

        $validar = false;

        if ($this->DiccionarioMedidas($medida_valor) == "masa" && $this->DiccionarioMedidas($medida_stock) == "masa") {

            $unidadValor = new Mass($valor, $medida_valor);
            $unidadStock = new Mass($stock_actual, $medida_stock);

            $valorStock = (int) round($unidadValor->toUnit('g'));
            $valorEntrante = (int) round($unidadStock->toUnit('g'));

            $resultadoBase = new Mass($this->OperacionMatematatica($valorEntrante, $valorStock, $operacion), 'g');
            $validar = true;
        }

        if ($this->DiccionarioMedidas($medida_valor) == "volumen" && $this->DiccionarioMedidas($medida_stock) == "volumen") {
            $unidadValor = new Volume($valor, $medida_valor);
            $unidadStock = new Volume($stock_actual, $medida_stock);

            $valorStock = (int) round($unidadValor->toUnit('ml'));
            $valorEntrante = (int) round($unidadStock->toUnit('ml'));

            $resultadoBase = new Volume($this->OperacionMatematatica($valorEntrante, $valorStock, $operacion), 'ml');
            $validar = true;
        }

        if ($validar) {

            $resultado = $resultadoBase->toUnit($medida_stock);
            
        } else {
            throw new \Exception("Conversión no válida: ".$medida_valor." y ".$medida_valor);
        }


        if ($resultado < 0) {
            throw new \Exception("El valor resultante no puede ser negativo");
        }

        return $resultado;
    }

    private function OperacionMatematatica($valor, $stock, $operacion)
    {

        $resultado = match ($operacion) {
            'sumar' => $stock + $valor,
            'restar' => $stock - $valor,
            default => new \Exception("Operación no válida")
        };

        return $resultado;
    }

    private function DiccionarioMedidas($medida)
    {
        $resultado = NULL;

        $resultado = match ($medida) {
            'Kg', 'kg', 'gr', 'g', 'oz', 'lb', => "masa",
            'ml', 'l', 'L', => "volumen",
            default => 'error'
        };

        if ($resultado == "error") {
            throw new \Exception("Unidad de Medida no válida");
        }

        return $resultado;
    }

}

