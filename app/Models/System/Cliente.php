<?php

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use PDO;
use Exception;

class Cliente extends Persona
{
    private $estatus;

    public function __construct()
    {
        parent::__construct();
        $this->estatus = 1;
    }



    
    public function setEstatus(int $estatus)
    {
        if (!in_array($estatus, [0, 1], true)) {
            throw new Exception('El estatus no es válido.');
        }
        $this->estatus = $estatus;
    }
















//########################################################################################


    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar'       => $this->RegistrarCliente(),
                'consultar'       => $this->ConsultarCliente(),
                'actualizar', 'modificar' => $this->ModificarCliente(),
                'eliminar'        => $this->EliminarCliente(),
                'validar'         => $this->ValidarCliente(),
                'verificar_cedula' => $this->verificarCedulaExiste(),
                default => [
                    'response'    => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }
























 //########################################################################################


    private function ConsultarCliente()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT p.*, c.fecha_registro, c.estatus FROM persona p INNER JOIN cliente c ON p.cedula = c.cedula WHERE c.estatus = 1";
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


























//########################################################################################


    private function RegistrarCliente()
    {
        try {
            $this->LlamarConexion();
            // Primero verificar si ya existe como cliente (sin transacción)
            $stmCheck = $this->LlamarConexion()->prepare(
                "SELECT c.cedula FROM cliente c WHERE c.cedula = :cedula"
            );
            $stmCheck->execute([':cedula' => $this->cedula]);
            if ($stmCheck->rowCount() > 0) {
                return [
                    'estado'      => -1,
                    'response'    => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El cliente ya se encuentra registrado.'],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'El cliente ya existe'],
                ];
            }

            $this->LlamarConexion()->beginTransaction();

            // Upsert en persona
            $stmPCheck = $this->LlamarConexion()->prepare("SELECT cedula FROM persona WHERE cedula = :cedula");
            $stmPCheck->execute([':cedula' => $this->cedula]);

            if ($stmPCheck->rowCount() === 0) {
                $this->LlamarConexion()->prepare(
                    "INSERT INTO persona (cedula, nombre, apellido, fecha_nacimiento, telefono, correo, direccion, sexo)
                     VALUES (:cedula, :nombre, :apellido, :fecha_nacimiento, :telefono, :correo, :direccion, :sexo)"
                )->execute([
                    ':cedula'           => $this->cedula,
                    ':nombre'           => $this->nombre,
                    ':apellido'         => $this->apellido,
                    ':fecha_nacimiento' => $this->fecha_nacimiento,
                    ':telefono'         => $this->telefono,
                    ':correo'           => $this->correo,
                    ':direccion'        => $this->direccion,
                    ':sexo'             => $this->sexo,
                ]);
            } else {
                $this->LlamarConexion()->prepare(
                    "UPDATE persona SET nombre = :nombre, apellido = :apellido,
                     fecha_nacimiento = :fecha_nacimiento, telefono = :telefono,
                     correo = :correo, direccion = :direccion, sexo = :sexo
                     WHERE cedula = :cedula"
                )->execute([
                    ':cedula'           => $this->cedula,
                    ':nombre'           => $this->nombre,
                    ':apellido'         => $this->apellido,
                    ':fecha_nacimiento' => $this->fecha_nacimiento,
                    ':telefono'         => $this->telefono,
                    ':correo'           => $this->correo,
                    ':direccion'        => $this->direccion,
                    ':sexo'             => $this->sexo,
                ]);
            }

            $this->LlamarConexion()->prepare("INSERT INTO cliente (cedula) VALUES (:cedula)")
               ->execute([':cedula' => $this->cedula]);

            $this->LlamarConexion()->commit();

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Cliente registrado exitosamente.'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'correo') !== false) {
                return [
                    'estado'      => -1,
                    'response'    => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El correo electrónico ya se encuentra registrado.'],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'El correo ya existe'],
                ];
            }
            
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return [
                'estado'      => -1,
                'response'    => ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde.'],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Error interno del servidor'],
            ];
        } finally {
            $this->DestruirConexion();
        }
    }




















    





//########################################################################################


    private function ModificarCliente()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $this->LlamarConexion()->prepare(
                "UPDATE persona SET nombre = :nombre, apellido = :apellido,
                 fecha_nacimiento = :fecha_nacimiento, telefono = :telefono,
                 correo = :correo, direccion = :direccion, sexo = :sexo
                 WHERE cedula = :cedula"
            )->execute([
                ':cedula'           => $this->cedula,
                ':nombre'           => $this->nombre,
                ':apellido'         => $this->apellido,
                ':fecha_nacimiento' => $this->fecha_nacimiento,
                ':telefono'         => $this->telefono,
                ':correo'           => $this->correo,
                ':direccion'        => $this->direccion,
                ':sexo'             => $this->sexo,
            ]);

            $this->LlamarConexion()->commit();

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Cliente actualizado exitosamente.'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();

            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'correo') !== false) {
                return [
                    'estado'      => -1,
                    'response'    => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El correo electrónico ya se encuentra registrado a otra persona.'],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'El correo ya existe'],
                ];
            }

            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return [
                'estado'      => -1,
                'response'    => ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde.'],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Error interno del servidor'],
            ];
        } finally {
            $this->DestruirConexion();
        }
    }


















//########################################################################################


    private function EliminarCliente()
    {
        try {
            $this->LlamarConexion();
            // Verificar existencia sin transacción
            $stmCheck = $this->LlamarConexion()->prepare(
                "SELECT c.cedula FROM cliente c WHERE c.cedula = :cedula"
            );
            $stmCheck->execute([':cedula' => $this->cedula]);
            if ($stmCheck->rowCount() === 0) {
                return [
                    'estado'      => -1,
                    'response'    => ['resultado' => 404, 'icon' => 'error', 'mensaje' => 'Registro no encontrado.'],
                    'HTTP_STATUS' => ['codigo' => 404, 'mensaje' => 'No encontrado'],
                ];
            }

            $this->LlamarConexion()->beginTransaction();

            $this->LlamarConexion()->prepare("UPDATE cliente SET estatus = 0 WHERE cedula = :cedula")
               ->execute([':cedula' => $this->cedula]);

            $this->LlamarConexion()->commit();

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Cliente eliminado exitosamente.'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return [
                'estado'      => -1,
                'response'    => ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error interno del servidor.'],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Error interno del servidor'],
            ];
        } finally {
            $this->DestruirConexion();
        }
    }











//########################################################################################














//################################################################################################################


    /**
     * ValidarCliente: comprueba si la cédula existe ya en la tabla cliente.
     * Solo hace un SELECT, sin transacción.
     */
    private function ValidarCliente()
    {
        try {
            $this->LlamarConexion();
            $sql  = "SELECT c.cedula FROM cliente c WHERE c.cedula = :cedula";
            $stm  = $this->LlamarConexion()->prepare($sql);
            $stm->execute([':cedula' => $this->cedula]);

            $dato['bool']        = $stm->rowCount() > 0 ? 1 : 0;
            $dato['estado']      = 1;
            $dato['response']    = ['resultado' => 200];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];

        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['bool']        = -1;
            $dato['estado']      = -1;
            $dato['response']    = ['resultado' => 500, 'mensaje' => 'Error interno del servidor'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        } finally {
            $this->DestruirConexion();
        }

        return $dato;
    }

    /**
     * Verifica si ya existe un cliente con la cédula indicada.
     * Usado por la validación asíncrona del frontend (peticion=verificar_cedula).
     * Retorna: { resultado: 200, existe: true|false }
     */
    private function verificarCedulaExiste()
    {
        try {
            $this->LlamarConexion();
            $stm = $this->LlamarConexion()->prepare("SELECT cedula FROM cliente WHERE cedula = :cedula LIMIT 1");
            $stm->execute([':cedula' => $this->cedula]);

            $existe = $stm->rowCount() > 0;

            return [
                'estado'      => 1,
                'response'    => [
                    'resultado' => 200,
                    'existe'    => $existe,
                    'mensaje'   => $existe ? 'Cedula ya registrada' : '',
                ],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return [
                'estado'      => -1,
                'response'    => ['resultado' => 500, 'existe' => false, 'mensaje' => 'Error interno del servidor'],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Error interno del servidor'],
            ];
        } finally {
            $this->DestruirConexion();
        }
    }

    /**
     * Asegura que el usuario esté registrado como cliente para evitar fallos de integridad (FK)
     */
    public function AsegurarCliente($cedula)
    {
        try {
            $this->LlamarConexion();
            $stm = $this->LlamarConexion()->prepare("SELECT COUNT(*) FROM cliente WHERE cedula = ?");
            $stm->execute([$cedula]);
            
            if ($stm->fetchColumn() == 0) {
                $stm = $this->LlamarConexion()->prepare("INSERT INTO cliente (cedula, estatus) VALUES (?, 1)");
                $stm->execute([$cedula]);
            }
        } catch (\PDOException $e) {
            Helper::ErrorLog("Error auto-registrando cliente: " . $e->getMessage());
        } finally {
            $this->DestruirConexion();
        }
    }
}
