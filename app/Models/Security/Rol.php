<?php

/*
MODELO DE ROL

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

class Rol extends Database
{
    private $id;
    private $nombre;

    public function __construct()
    {
        $this->id = "";
        $this->nombre = "";
    }

    // Getters y Setters

    public function setId(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, "ID") == 0) {
            throw new Exception('ID no válido.');
        }
        $this->id = strtoupper($id[0]) . substr($id, 1);
    }

    public function setNombre(string $nombre)
    {
        $nombre = trim($nombre);
        if (RegexHelper::ValidarFormatos($nombre, "NombrePersona") == 0) {
            throw new Exception('Nombre no válido. Debe tener al menos entre 3 a 150 carácteres');
        }

        $this->nombre = $nombre;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }
    public function Transaccion($peticion, )
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarRol(),
                'consultar' => $this->ConsultarRol(),
                'actualizar', 'modificar' => $this->ModificarRol(),
                'eliminar' => $this->EliminarRol(),
                'validar' => $this->ValidarRol(),
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
    private function ConsultarRol()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM rol WHERE estatus = 1";
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

    private function RegistrarRol()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarRol();
        if ($validacion['bool'] == 0) {
            try {
                $sql = "INSERT INTO rol (id_rol`, `nombre_rol`) VALUES (:id_rol, :nombre_rol)";

                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_rol', $this->id);
                $stm->bindParam(':nombre_rol', $this->nombre);
                $stm->execute();
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Rol registrado exitosamente"];
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

    private function ModificarRol()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE rol SET nombre_rol = :nombre_rol WHERE id_rol = :id_rol";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id', $this->id);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->execute();
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Rol actualizado exitosamente"];
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

    private function EliminarRol()
    {
        $dato = [];
        $validacion = $this->ValidarRol();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "UPDATE rol SET estatus = 0 WHERE id_rol = :id_rol";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id', $this->id);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Rol eliminado exitosamente"];
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

    private function ValidarRol()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM rol WHERE id_rol = :id_rol";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_rol', $this->id);
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