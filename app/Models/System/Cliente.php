<?php

/*
MODELO DE CLIENTES

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
use PDO;

class Cliente
{
    private $cedula;
    private $nombre;
    private $apellido;
    private $fecha_nacimiento;
    private $telefono;
    private $correo;
    private $direccion;
    private $sexo;
    private $db;

    public function __construct()
    {
        $this->cedula = "";
        $this->nombre = "";
        $this->apellido = "";
        $this->fecha_nacimiento = null;
        $this->telefono = "";
        $this->correo = "";
        $this->direccion = "";
        $this->sexo = "";
        $this->db = NULL;
    }

    private function LlamarConexion(PDO &$db = NULL)
    {
        if ($db != NULL) {
            $this->db = $db;
        }

        if ($this->db == NULL) {
            $this->db = Database::getConnection('business');
        }

        return $this->db;
    }

    private function DestruirConexion()
    {
        $this->db = NULL;
    }

    // Getters y Setters
    public function setCedula(string $cedula)
    {
        $this->cedula = $cedula;
    }

    public function setNombre(string $nombre)
    {
        $this->nombre = $nombre;
    }

    public function setApellido(string $apellido)
    {
        $this->apellido = $apellido;
    }

    public function setFechaNacimiento($fecha_nacimiento)
    {
        $this->fecha_nacimiento = $fecha_nacimiento;
    }

    public function setTelefono(string $telefono)
    {
        $this->telefono = $telefono;
    }

    public function setCorreo(string $correo)
    {
        $this->correo = $correo;
    }

    public function setDireccion(string $direccion)
    {
        $this->direccion = $direccion;
    }

    public function setSexo(string $sexo)
    {
        $this->sexo = $sexo;
    }

    public function getCedula()
    {
        return $this->cedula;
    }

    // MANEJADOR DE OPERACIONES
    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarCliente(),
                'consultar' => $this->ConsultarCliente(),
                'actualizar', 'modificar' => $this->ModificarCliente(),
                'eliminar' => $this->EliminarCliente(),
                'validar' => $this->ValidarCliente(),
                default => [
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }

    //OPERACIONES A BASE DE DATOS
    private function ConsultarCliente()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT p.*, c.fecha_registro FROM persona p INNER JOIN cliente c ON p.cedula = c.cedula";
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
            if($this->LlamarConexion()->inTransaction()) {
                $this->LlamarConexion()->rollBack();
            }
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Ups, intente de nuevo más tarde", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function RegistrarCliente()
    {
        $dato = [];
        $validacion = $this->ValidarCliente();
        if ($validacion['bool'] == 0) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                
                // Verificar si la persona ya existe
                $sqlCheck = "SELECT cedula FROM persona WHERE cedula = :cedula";
                $stmCheck = $this->LlamarConexion()->prepare($sqlCheck);
                $stmCheck->bindParam(':cedula', $this->cedula);
                $stmCheck->execute();
                
                if ($stmCheck->rowCount() == 0) {
                    $sqlPersona = "INSERT INTO persona(cedula, nombre, apellido, fecha_nacimiento, telefono, correo, direccion, sexo)
                    VALUES (:cedula, :nombre, :apellido, :fecha_nacimiento, :telefono, :correo, :direccion, :sexo)";
                    $stmPersona = $this->LlamarConexion()->prepare($sqlPersona);
                    $stmPersona->bindParam(':cedula', $this->cedula);
                    $stmPersona->bindParam(':nombre', $this->nombre);
                    $stmPersona->bindParam(':apellido', $this->apellido);
                    $stmPersona->bindParam(':fecha_nacimiento', $this->fecha_nacimiento);
                    $stmPersona->bindParam(':telefono', $this->telefono);
                    $stmPersona->bindParam(':correo', $this->correo);
                    $stmPersona->bindParam(':direccion', $this->direccion);
                    $stmPersona->bindParam(':sexo', $this->sexo);
                    $stmPersona->execute();
                } else {
                    $sqlPersona = "UPDATE persona SET nombre = :nombre, apellido = :apellido, fecha_nacimiento = :fecha_nacimiento, 
                    telefono = :telefono, correo = :correo, direccion = :direccion, sexo = :sexo WHERE cedula = :cedula";
                    $stmPersona = $this->LlamarConexion()->prepare($sqlPersona);
                    $stmPersona->bindParam(':cedula', $this->cedula);
                    $stmPersona->bindParam(':nombre', $this->nombre);
                    $stmPersona->bindParam(':apellido', $this->apellido);
                    $stmPersona->bindParam(':fecha_nacimiento', $this->fecha_nacimiento);
                    $stmPersona->bindParam(':telefono', $this->telefono);
                    $stmPersona->bindParam(':correo', $this->correo);
                    $stmPersona->bindParam(':direccion', $this->direccion);
                    $stmPersona->bindParam(':sexo', $this->sexo);
                    $stmPersona->execute();
                }

                $sqlCliente = "INSERT INTO cliente(cedula) VALUES (:cedula)";
                $stmCliente = $this->LlamarConexion()->prepare($sqlCliente);
                $stmCliente->bindParam(':cedula', $this->cedula);
                $stmCliente->execute();
                
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Cliente registrado exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

            } catch (\PDOException $e) {
                if($this->LlamarConexion()->inTransaction()) {
                    $this->LlamarConexion()->rollBack();
                }
                Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
            }
        } else {
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "El cliente ya se encuentra registrado"];
            $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "El cliente ya existe"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ModificarCliente()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE persona SET nombre = :nombre, apellido = :apellido, fecha_nacimiento = :fecha_nacimiento, 
            telefono = :telefono, correo = :correo, direccion = :direccion, sexo = :sexo WHERE cedula = :cedula";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->bindParam(':apellido', $this->apellido);
            $stm->bindParam(':fecha_nacimiento', $this->fecha_nacimiento);
            $stm->bindParam(':telefono', $this->telefono);
            $stm->bindParam(':correo', $this->correo);
            $stm->bindParam(':direccion', $this->direccion);
            $stm->bindParam(':sexo', $this->sexo);
            $stm->execute();
            
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Cliente actualizado exitosamente"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            if($this->LlamarConexion()->inTransaction()) {
                $this->LlamarConexion()->rollBack();
            }
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function EliminarCliente()
    {
        $dato = [];
        $validacion = $this->ValidarCliente();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                
                $sql = "DELETE FROM cliente WHERE cedula = :cedula";
                $stm = $this->db->prepare($sql);
                $stm->bindParam('cedula', $this->cedula);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Cliente eliminado exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            } catch (\PDOException $e) {
                if($this->LlamarConexion()->inTransaction()) {
                    $this->LlamarConexion()->rollBack();
                }
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

    private function ValidarCliente()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            if (!$this->LlamarConexion()->inTransaction()) {
                $this->LlamarConexion()->beginTransaction();
            }
            $sql = "SELECT p.*, c.fecha_registro FROM persona p INNER JOIN cliente c ON p.cedula = c.cedula WHERE c.cedula = :cedula";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetch(PDO::FETCH_ASSOC);
                $dato['bool'] = 1;
            } else {
                $dato['bool'] = 0;
            }
            
            // Note: This matches the Ingrediente code, although it might be better to just select without transactions
            if ($this->LlamarConexion()->inTransaction()) {
                $this->LlamarConexion()->commit();
            }
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'registro' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            if($this->LlamarConexion()->inTransaction()) {
                $this->LlamarConexion()->rollBack();
            }
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
