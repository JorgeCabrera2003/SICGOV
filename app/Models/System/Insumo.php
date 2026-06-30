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

class Insumo extends Database
{
    private $id;
    private $nombre;
    private $precio_unitario;
    private $stock_actual;
    private $stock_minimo;
    private $stock_maximo;
    private $id_categoria_insumo;
    private $id_unidad_medida;

    public function __construct()
    {
        $this->id = "";
        $this->nombre = "";
        $this->id_unidad_medida = "";
        $this->stock_actual = NULL;
        $this->stock_minimo = NULL;
        $this->stock_maximo = NULL;
        $this->precio_unitario = 0.00;
        $this->id_categoria_insumo = "";
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
            throw new Exception("El ID de la Categoría no cumple con el formato permitido.");
        }
        $this->nombre = $nombre;
    }

    public function setStockActual(float $stock)
    {
        if ($stock < 0) {
            throw new Exception("El valor ingresado no puede ser negativo");
        }
        $this->stock_actual = $stock;
    }

    public function setStockMaximo(float $stock)
    {
        if ($stock < 0) {
            throw new Exception("El valor ingresado no puede ser negativo");
        }

        if ($stock == 0) {
            $stock = NULL;
        }
        $this->stock_maximo = $stock;
    }

    public function setStockMinimo(float $stock)
    {
        if ($stock < 0) {
            throw new Exception("El valor ingresado no puede ser negativo");
        }
        $this->stock_minimo = $stock;
    }

    public function setPrecioUnitario(float $precio)
    {
        if ($precio < 0) {
            throw new Exception("El valor ingresado no puede ser negativo");
        }
        $this->precio_unitario = $precio;
    }
    public function setIdCategoria(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID de la Categoría no cumple con el formato permitido.");
        }
        $this->id_categoria_insumo = $id;
    }

    public function setIdUnidadMedida(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID de la Unidad de Medida no cumple con el formato permitido.");
        }
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

    //FIN GETTERS

    // MANEJADOR DE OPERACIONES
    public function Transaccion($peticion, )
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarInsumo(),
                'consultar' => $this->ConsultarInsumo(),
                'actualizar', 'modificar' => $this->ModificarInsumo(),
                'eliminar' => $this->EliminarInsumo(),
                'validar' => $this->ValidarInsumo(),
                'actualizar_stock' => $this->ActualizarStockInsumo(),
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
    private function ConsultarInsumo()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM vw_insumo WHERE estatus = 1";
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

    private function RegistrarInsumo()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarInsumo();
        if ($validacion['bool'] == 0) {
            try {
                $sql = "INSERT INTO insumo(id_insumo, id_categoria, nombre_insumo, 
                id_unidad_medida, precio_unitario, stock_actual, stock_minimo, stock_maximo)
                VALUES (:id_insumo, :id_categoria, :nombre_insumo, 
                :id_unidad_medida, :precio_unitario, :stock_actual, :stock_minimo, :stock_maximo)";

                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_insumo', $this->id);
                $stm->bindParam(':id_categoria', $this->id_categoria_insumo);
                $stm->bindParam(':nombre_insumo', $this->nombre);
                $stm->bindParam(':id_unidad_medida', $this->id_unidad_medida);
                $stm->bindParam(':stock_actual', $this->stock_actual);
                $stm->bindParam(':stock_minimo', $this->stock_minimo);
                $stm->bindParam(':stock_maximo', $this->stock_maximo);
                $stm->bindParam(':precio_unitario', $this->precio_unitario);
                $stm->execute();
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Insumo registrado exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => "OK"];

            } catch (\PDOException $e) {
                $this->LlamarConexion()->rollBack();
                Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
            }
        } else {
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 409, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 409, 'mensaje' => "Registro duplicado"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ModificarInsumo()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE insumo SET id_categoria = :id_categoria, nombre_insumo = :nombre_insumo,
            id_unidad_medida = :id_unidad_medida, precio_unitario = :precio_unitario, stock_minimo = :stock_minimo,
            stock_maximo = :stock_maximo WHERE id_insumo = :id_insumo";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_insumo', $this->id);
            $stm->bindParam(':nombre_insumo', $this->nombre);
            $stm->bindParam(':id_categoria', $this->id_categoria_insumo);
            $stm->bindParam(':id_unidad_medida', $this->id_unidad_medida);
            $stm->bindParam(':stock_minimo', $this->stock_minimo);
            $stm->bindParam(':stock_maximo', $this->stock_maximo);
            $stm->bindParam(':precio_unitario', $this->precio_unitario);
            $stm->execute();
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Insumo actualizado exitosamente"];
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

    private function EliminarInsumo()
    {
        $dato = [];
        $validacion = $this->ValidarInsumo();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "UPDATE insumo SET estatus = 0 WHERE id_insumo = :id_insumo";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam('id_insumo', $this->id);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Insumo eliminado exitosamente"];
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

    private function ValidarInsumo()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM vw_insumo WHERE id_insumo = :id_insumo";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_insumo', $this->id);
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

    private function ActualizarStockInsumo()
    {

        $dato = [];
        $validacion = $this->ValidarInsumo();
        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "UPDATE insumo SET stock_actual = :stock_actual WHERE id_insumo = :id_insumo";

                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_insumo', $this->id);
                $stm->bindParam(':stock_actual', $this->stock_actual);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Stock del Insumo actualizado exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

            } catch (\PDOException $e) {
                $this->LlamarConexion()->rollBack();
                Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
            }
            $this->DestruirConexion();

        } else {
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 404, 'icon' => 'error', 'mensaje' => "Registro no encontrado"];
            $dato['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => "No encontrado"];
        }
        return $dato;
    }

}