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

class EntradaInsumo extends Database
{
    private $id;
    private $id_insumo;
    private $documento_legal;

    public function __construct()
    {
        $this->id = "";
        $this->id_insumo = "";
        $this->documento_legal = "";
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

    public function setIdInsumo(string $id_insumo)
    {
        if (RegexHelper::ValidarFormatos($id_insumo, 'ID') == 0) {
            throw new Exception("El ID del Insumo no cumple con el formato permitido.");
        }
        $this->id_insumo = $id_insumo;
    }

    public function setDocumentoLegal(string $documento_legal)
    {
        if (RegexHelper::ValidarFormatos($documento_legal, "DocumentoLegal") == 0) {
            throw new Exception('El documento legal debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.');
        }
        $this->documento_legal = $documento_legal;
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

    public function getDocumentoLegal()
    {
        return $this->documento_legal;
    }

    //FIN GETTERS

    // MANEJADOR DE OPERACIONES
    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];
        $bool = true;
        if ($peticion['peticion'] == 'asociar_proveedores' && !isset($peticion['proveedores'])) {
            $bool = false;
        }

        if (isset($peticion['peticion']) && $bool) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarEntradaInsumo(),
                'consultar' => $this->ConsultarEntradaInsumo(),
                'reactivar' => $this->ReactivarEntradaInsumo(),
                'eliminar' => $this->EliminarEntradaInsumo(),
                'filtrar' => $this->FiltrarEntradaInsumo(),
                'asociar_proveedores' => $this->AsociarProveedores($peticion['proveedores']),
                'validar' => $this->ValidarEntradaInsumo(),
                'validar_asociacion' => $this->ValidarAsociacion(),
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
    private function ConsultarEntradaInsumo($filtro = NULL)
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM entrada_insumo WHERE estatus = 1";
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

    private function RegistrarEntradaInsumo()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarEntradaInsumo();
        if ($validacion['bool'] == 0) {
            try {
                $sql = "INSERT INTO entrada_insumo(id_entrada, id_insumo, documento_proveedor) 
                VALUES (:id, :id_insumo, :documento_legal)";

                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id', $this->id);
                $stm->bindParam(':id_insumo', $this->id_insumo);
                $stm->bindParam(':documento_legal', $this->documento_legal);
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

    private function AsociarProveedores($proveedores)
    {
        $dato = [];
        $validar_registros = true;

        foreach ($proveedores as $i) {

            if (!isset($i['id_entrada']) || !isset($i['documento'])) {
                $validar_registros = false;
                break;
            }

            $this->setId($i['id_entrada']);
            $this->setDocumentoLegal($i['documento']);
        }
        if ($validar_registros) {
            try {
                $sql = "INSERT INTO entrada_insumo(id_entrada, id_insumo, documento_proveedor) 
                VALUES (:id, :id_insumo, :documento_legal)
                ON DUPLICATE KEY UPDATE estatus = 1";

                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $stm = $this->LlamarConexion()->prepare($sql);
                foreach ($proveedores as $registro) {
                    $stm->bindParam(':id', $registro['id_entrada']);
                    $stm->bindParam(':id_insumo', $this->id_insumo);
                    $stm->bindParam(':documento_legal', $registro['documento']);
                    $stm->execute();
                }
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Insumo asociado a proveedores correctamente"];
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
            $dato['response'] = ['resultado' => 400, 'icon' => 'danger', 'mensaje' => "Datos no válidos"];
            $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Datos no válidos"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ReactivarEntradaInsumo()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE entrada_insumo SET estatus = 1 WHERE id_entrada = :id";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id', $this->id);
            $stm->execute();
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Relación de Entrada actualizado exitosamente"];
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

    private function EliminarEntradaInsumo()
    {
        $dato = [];
        $validacion = $this->ValidarEntradaInsumo();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "UPDATE entrada_insumo SET estatus = 0 WHERE id_entrada = :id";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam('id', $this->id);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Asociación con el Proveedor eliminada"];
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

    private function ValidarEntradaInsumo()
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


        private function ValidarAsociacion()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM entrada_insumo WHERE (id_insumo = :id_insumo AND documento_proveedor = :documento_proveedor)";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_insumo', $this->id_insumo);
            $stm->bindParam(':documento_proveedor', $this->documento_legal);
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

    private function FiltrarEntradaInsumo()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM vw_entrada_insumo WHERE id_insumo  = :id_insumo AND estatus = 1";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_insumo', $this->id_insumo);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
                $dato['bool'] = 1;

            } else {
                $dato['bool'] = 0;
            }
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'datos' => $arreglo];
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