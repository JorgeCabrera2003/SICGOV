<?php

namespace App\Models\System;

use Exception;
use App\Helpers\Helper;

class Empleado extends Persona
{
    private $id_cargo;
    private $fecha_ingreso;
    private $fecha_egreso;
    private $estatus;

    public function __construct()
    {
        parent::__construct();
        $this->id_cargo = "";
        $this->fecha_ingreso = "";
        $this->fecha_egreso = null;
        $this->estatus = 1;
    }

    // Mantener set_cedula por compatibilidad con AsistenciaController
    public function set_cedula($c) { 
        $this->cedula = $c; 
    }

    public function setIdCargo(string $id_cargo)
    {
        $this->id_cargo = trim($id_cargo);
    }

    public function setEstatus($estatus)
    {
        $this->estatus = $estatus;
    }






    public function Transaccion($peticion)
    {
        $response = [];
        $response['response']    = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar'       => $this->RegistrarEmpleado(),
                'consultar'       => $this->ConsultarEmpleado(),
                'actualizar', 'modificar' => $this->ModificarEmpleado(),
                'cambiar_estatus' => $this->CambiarEstatusEmpleado(),
                'verificar_cedula'=> $this->verificarCedulaExiste(),
                default => [
                    'response'    => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }














    private function ConsultarEmpleado()
    {
        try {
            $db = $this->LlamarConexion();
            $sql = "SELECT p.cedula, p.nombre, p.apellido, p.fecha_nacimiento, 
                           p.telefono, p.correo, p.direccion, p.sexo, 
                           e.id_cargo, c.nombre_cargo as cargo, e.fecha_ingreso, e.estatus 
                    FROM empleado e 
                    INNER JOIN persona p ON e.cedula = p.cedula
                    LEFT JOIN cargo c ON e.id_cargo = c.id_cargo
                    ORDER BY p.nombre ASC";
                    
            $stm = $db->prepare($sql);
            $stm->execute();
            $arreglo = $stm->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'mensaje' => 'OK', 'datos' => $arreglo],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return [
                'estado'      => -1,
                'response'    => ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error al consultar', 'datos' => []],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Error interno del servidor'],
            ];
        } finally {
            $this->DestruirConexion();
        }
    }






    private function ValidarCargoExistente($id_cargo)
    {
        $db = $this->LlamarConexion();
        $stm = $db->prepare("SELECT id_cargo FROM cargo WHERE id_cargo = :id_cargo AND estatus = 1 LIMIT 1");
        $stm->execute([':id_cargo' => $id_cargo]);
        return $stm->rowCount() > 0;
    }










    private function RegistrarEmpleado()
    {
        try {
            $db = $this->LlamarConexion();

            if (!$this->ValidarCargoExistente($this->id_cargo)) {
                return [
                    'estado'      => -1,
                    'response'    => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El cargo seleccionado no es válido o está inactivo.'],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Cargo inválido'],
                ];
            }

            $stmCheck = $db->prepare("SELECT cedula FROM empleado WHERE cedula = :cedula");
            $stmCheck->execute([':cedula' => $this->cedula]);
            if ($stmCheck->rowCount() > 0) {
                return [
                    'estado'      => -1,
                    'response'    => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El empleado ya se encuentra registrado.'],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'El empleado ya existe'],
                ];
            }

            $db->beginTransaction();

            $stmPCheck = $db->prepare("SELECT cedula FROM persona WHERE cedula = :cedula");
            $stmPCheck->execute([':cedula' => $this->cedula]);

            if ($stmPCheck->rowCount() === 0) {
                $db->prepare(
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
                $db->prepare(
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

            $db->prepare("INSERT INTO empleado (cedula, id_cargo, fecha_ingreso, estatus) VALUES (:cedula, :id_cargo, CURDATE(), 1)")
               ->execute([
                   ':cedula' => $this->cedula,
                   ':id_cargo' => $this->id_cargo
               ]);

            $db->commit();

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Empleado registrado exitosamente.'],
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















    private function ModificarEmpleado()
    {
        try {
            $db = $this->LlamarConexion();

            if (!$this->ValidarCargoExistente($this->id_cargo)) {
                return [
                    'estado'      => -1,
                    'response'    => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El cargo seleccionado no es válido o está inactivo.'],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Cargo inválido'],
                ];
            }

            $db->beginTransaction();

            $db->prepare(
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

            $db->prepare("UPDATE empleado SET id_cargo = :id_cargo WHERE cedula = :cedula")
               ->execute([
                   ':cedula' => $this->cedula,
                   ':id_cargo' => $this->id_cargo
               ]);

            $db->commit();

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Empleado actualizado exitosamente.'],
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










    private function CambiarEstatusEmpleado()
    {
        try {
            $db = $this->LlamarConexion();
            $sql = "UPDATE empleado SET estatus = :estatus WHERE cedula = :cedula";
            $stm = $db->prepare($sql);
            $stm->execute([
                ':estatus' => $this->estatus,
                ':cedula'  => $this->cedula
            ]);

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Estatus cambiado exitosamente.'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return [
                'estado'      => -1,
                'response'    => ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error interno del servidor'],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Error interno del servidor'],
            ];
        } finally {
            $this->DestruirConexion();
        }
    }








    private function verificarCedulaExiste()
    {
        try {
            $db  = $this->LlamarConexion();
            $stm = $db->prepare("SELECT cedula FROM empleado WHERE cedula = :cedula LIMIT 1");
            $stm->execute([':cedula' => $this->cedula]);

            $existe = $stm->rowCount() > 0;

            return [
                'estado'      => 1,
                'response'    => [
                    'resultado' => 200,
                    'existe'    => $existe,
                    'mensaje'   => $existe ? 'Cédula ya registrada' : '',
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

    








    
    public function obtenerDatos() {
        $db = Database::getConnection('business'); 
        
        $ced = strtoupper(trim($this->cedula));
        $cedSin = str_replace('-', '', $ced);

        $cedConGuion = $ced;
        if (!str_contains($cedConGuion, '-') && strlen($cedConGuion) >= 2) {
            $cedConGuion = $cedConGuion[0] . '-' . substr($cedConGuion, 1);
        }

        $sql = "SELECT p.cedula as cedula_personal, p.nombre as nombre_personal, p.apellido as apellido_personal,
                       e.cedula as cedula_empleado, e.id_cargo, c.nombre_cargo as cargo, e.fecha_ingreso
                FROM persona p
                INNER JOIN empleado e ON p.cedula = e.cedula
                LEFT JOIN cargo c ON e.id_cargo = c.id_cargo
                WHERE p.cedula = :ced1 OR p.cedula = :ced2 OR REPLACE(p.cedula, '-', '') = :cedSin
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute(['ced1' => $cedConGuion, 'ced2' => str_replace('-', '', $cedConGuion), 'cedSin' => $cedSin]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            $sql2 = "SELECT cedula as cedula_personal, nombre as nombre_personal, apellido as apellido_personal FROM persona WHERE cedula = :ced1 OR cedula = :ced2 OR REPLACE(cedula, '-', '') = :cedSin LIMIT 1";
            $stmt2 = $db->prepare($sql2);
            $stmt2->execute(['ced1' => $cedConGuion, 'ced2' => str_replace('-', '', $cedConGuion), 'cedSin' => $cedSin]);
            $row = $stmt2->fetch(\PDO::FETCH_ASSOC);
        }

        return $row;
    }
}