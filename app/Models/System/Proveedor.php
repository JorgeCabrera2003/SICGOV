<?php

/*
MODELO DE PROVEEDORES

OPERACIONES A BASE DE DATOS:
    REGISTRAR
    CONSULTAR
    MODIFICAR
    ELIMINAR
    VALIDAR
*/

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use PDO;
use Exception;

class Proveedor extends Database
{
    private $documento_legal;
    private $nombre;
    private $telefono;
    private $correo;
    private $direccion;

    public function __construct()
    {
        $this->documento_legal = "";
        $this->nombre = "";
        $this->telefono = "";
        $this->correo = "";
        $this->direccion = "";
    }

    // Getters y Setters

    /**
     * Documento Legal: prefijo (V/E/J/P/G) + 7 a 9 dígitos.
     * El frontend envía ya concatenado, ej. "V12345678".
     */
    public function setDocumentoLegal(string $documento)
    {
        $documento = trim($documento);
        if (RegexHelper::ValidarFormatos($documento, "DocumentoLegal") == 0) {
            throw new Exception('El documento legal debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.');
        }
        $this->documento_legal = strtoupper($documento[0]) . substr($documento, 1);
    }

    public function setNombre(string $nombre)
    {
        $nombre = trim($nombre);
        if (RegexHelper::ValidarFormatos($nombre, "Titulo") == 0) {
            throw new Exception('Nombre no válido. Debe tener al menos entre 3 a 150 carácteres');
        }

        $this->nombre = $nombre;
    }

    public function setTelefono(string $telefono)
    {
        if (RegexHelper::ValidarFormatos($telefono, "Telefono") == 0) {
            throw new Exception('Teléfono no válido. Debe tener el siguiente formato: 0424-1234567');
        }
        $this->telefono = $telefono;
    }

    public function setCorreo(string $correo)
    {
        if (RegexHelper::ValidarFormatos($correo, "Correo") == 0) {
            throw new Exception('El formato del correo electrónico no es válido.');
        }
        $this->correo = $correo;
    }

    /** Dirección: obligatoria, mínimo 3 caracteres. */
    public function setDireccion(string $direccion)
    {
        if (RegexHelper::ValidarFormatos($direccion, "Direccion") == 0) {
            throw new Exception('Dirección no válida');
        }
        $this->direccion = $direccion;
    }

    public function getDocumentoLegal()
    {
        return $this->documento_legal;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getTelefono()
    {
        return $this->telefono;
    }

    public function getCorreo()
    {
        return $this->correo;
    }

    public function getDireccion()
    {
        return $this->direccion;
    }
    public function Transaccion($peticion, )
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarProveedor(),
                'consultar' => $this->ConsultarProveedor(),
                'actualizar', 'modificar' => $this->ModificarProveedor(),
                'eliminar' => $this->EliminarProveedor(),
                'validar' => $this->ValidarProveedor(),
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
    private function ConsultarProveedor()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM proveedor WHERE estatus = 1";
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

    private function RegistrarProveedor()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarProveedor();
        if ($validacion['bool'] == 0) {
            try {
                $sql = "INSERT INTO proveedor(documento_legal, nombre, telefono, correo, direccion)
                VALUES (:documento_legal, :nombre, :telefono, :correo, :direccion)";

                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':documento_legal', $this->documento_legal);
                $stm->bindParam(':nombre', $this->nombre);
                $stm->bindParam(':telefono', $this->telefono);
                $stm->bindParam(':correo', $this->correo);
                $stm->bindParam(':direccion', $this->direccion);
                $stm->execute();
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Proveedor registrar exitosamente"];
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

    private function ModificarProveedor()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE proveedor SET nombre = :nombre, telefono = :telefono, 
            correo = :correo, direccion = :direccion WHERE documento_legal = :documento_legal";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':documento_legal', $this->documento_legal);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->bindParam(':telefono', $this->telefono);
            $stm->bindParam(':correo', $this->correo);
            $stm->bindParam(':direccion', $this->direccion);
            $stm->execute();
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Proveedor actualizado exitosamente"];
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

    private function EliminarProveedor()
    {
        $dato = [];
        $validacion = $this->ValidarProveedor();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "UPDATE proveedor SET estatus = 0 WHERE documento_legal = :documento_legal";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':documento_legal', $this->documento_legal);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Proveedor eliminado exitosamente"];
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

    private function ValidarProveedor()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM proveedor WHERE documento_legal = :documento_legal";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':documento_legal', $this->documento_legal);
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