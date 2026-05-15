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
use App\Helpers\RegexHelper;
use PDO;
use Exception;

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
    private $estatus;
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
        $this->estatus = 1;
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

    /**
     * Cédula: prefijo (V/E/J/P/G) + 7 a 9 dígitos.
     * El frontend envía ya concatenado, ej. "V12345678".
     */
    public function setCedula(string $cedula)
    {
        $cedula = trim($cedula);
        if (empty($cedula)) {
            throw new Exception('La cédula es obligatoria.');
        }
        if (!preg_match('/^[VEJPGvejpg]\d{7,9}$/', $cedula)) {
            throw new Exception('La cédula debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 9 dígitos.');
        }
        $this->cedula = strtoupper($cedula[0]) . substr($cedula, 1);
    }

    /** Nombre: obligatorio, mínimo 2 caracteres, solo letras y espacios. */
    public function setNombre(string $nombre)
    {
        $nombre = trim($nombre);
        if (empty($nombre)) {
            throw new Exception('El nombre es obligatorio.');
        }
        if (mb_strlen($nombre) < 2) {
            throw new Exception('El nombre debe tener al menos 2 caracteres.');
        }
        if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ][a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ ]*$/', $nombre)) {
            throw new Exception('El nombre solo puede contener letras y espacios.');
        }
        $this->nombre = $nombre;
    }

    /** Apellido: obligatorio, mínimo 2 caracteres, solo letras y espacios. */
    public function setApellido(string $apellido)
    {
        $apellido = trim($apellido);
        if (empty($apellido)) {
            throw new Exception('El apellido es obligatorio.');
        }
        if (mb_strlen($apellido) < 2) {
            throw new Exception('El apellido debe tener al menos 2 caracteres.');
        }
        if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ][a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ ]*$/', $apellido)) {
            throw new Exception('El apellido solo puede contener letras y espacios.');
        }
        $this->apellido = $apellido;
    }

    /** Fecha de nacimiento: obligatoria, formato YYYY-MM-DD, no puede ser hoy ni futura. */
    public function setFechaNacimiento($fecha_nacimiento)
    {
        $fecha_nacimiento = trim($fecha_nacimiento ?? '');
        if (empty($fecha_nacimiento)) {
            throw new Exception('La fecha de nacimiento es obligatoria.');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento)) {
            throw new Exception('El formato de la fecha de nacimiento no es válido.');
        }
        if ($fecha_nacimiento >= date('Y-m-d')) {
            throw new Exception('La fecha de nacimiento debe ser anterior a hoy.');
        }
        $this->fecha_nacimiento = $fecha_nacimiento;
    }

    /**
     * Teléfono: opcional.
     * Si se ingresa, debe ser exactamente 11 dígitos (prefijo 4 + número 7).
     */
    public function setTelefono(string $telefono)
    {
        $telefono = trim($telefono);
        if ($telefono === '') {
            $this->telefono = '';
            return;
        }
        if (!preg_match('/^\d{11}$/', $telefono)) {
            throw new Exception('El teléfono debe incluir el prefijo (4 dígitos) más 7 dígitos de número (11 en total).');
        }
        $this->telefono = $telefono;
    }

    /** Correo: opcional. Si se ingresa debe tener formato válido. */
    public function setCorreo(string $correo)
    {
        $correo = trim($correo);
        if ($correo === '') {
            $this->correo = '';
            return;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del correo electrónico no es válido.');
        }
        $this->correo = $correo;
    }

    /** Dirección: obligatoria, mínimo 3 caracteres. */
    public function setDireccion(string $direccion)
    {
        $direccion = trim($direccion);
        if (empty($direccion)) {
            throw new Exception('La dirección es obligatoria.');
        }
        if (mb_strlen($direccion) < 3) {
            throw new Exception('La dirección debe tener al menos 3 caracteres.');
        }
        $this->direccion = $direccion;
    }

    /** Sexo: obligatorio, debe ser M o F. */
    public function setSexo(string $sexo)
    {
        $sexo = trim($sexo);
        if (!in_array($sexo, ['M', 'F'], true)) {
            throw new Exception('El sexo debe ser M (Masculino) o F (Femenino).');
        }
        $this->sexo = $sexo;
    }

    /** Estatus: 0 (inactivo) o 1 (activo). */
    public function setEstatus(int $estatus)
    {
        if (!in_array($estatus, [0, 1], true)) {
            throw new Exception('El estatus no es válido.');
        }
        $this->estatus = $estatus;
    }

    public function getCedula()
    {
        return $this->cedula;
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
                'cambiar_estatus' => $this->CambiarEstatusCliente(),
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
            $sql = "SELECT p.*, c.fecha_registro, c.estatus FROM persona p INNER JOIN cliente c ON p.cedula = c.cedula";
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
        $db = $this->LlamarConexion();
        try {
            // Primero verificar si ya existe como cliente (sin transacción)
            $stmCheck = $db->prepare(
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

            $db->beginTransaction();

            // Upsert en persona
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

            $db->prepare("INSERT INTO cliente (cedula) VALUES (:cedula)")
               ->execute([':cedula' => $this->cedula]);

            $db->commit();

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Cliente registrado exitosamente.'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
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
        $db = $this->LlamarConexion();
        try {
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

            $db->commit();

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Cliente actualizado exitosamente.'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
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
        $db = $this->LlamarConexion();
        try {
            // Verificar existencia sin transacción
            $stmCheck = $db->prepare(
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

            $db->beginTransaction();

            $db->prepare("UPDATE cliente SET estatus = 0 WHERE cedula = :cedula")
               ->execute([':cedula' => $this->cedula]);

            $db->commit();

            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Cliente eliminado exitosamente.'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
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


    private function CambiarEstatusCliente()
    {
        $db = $this->LlamarConexion();
        try {
            // Verificar existencia sin transacción
            $stmCheck = $db->prepare(
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

            $db->beginTransaction();

            $db->prepare("UPDATE cliente SET estatus = :estatus WHERE cedula = :cedula")
               ->execute([':estatus' => $this->estatus, ':cedula' => $this->cedula]);

            $db->commit();

            $mensaje = $this->estatus == 1 ? 'Cliente reactivado.' : 'Cliente desactivado.';
            return [
                'estado'      => 1,
                'response'    => ['resultado' => 200, 'icon' => 'success', 'mensaje' => $mensaje],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK'],
            ];

        } catch (\PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
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











//################################################################################################################


    /**
     * ValidarCliente: comprueba si la cédula existe ya en la tabla cliente.
     * Solo hace un SELECT, sin transacción.
     */
    private function ValidarCliente()
    {
        try {
            $db   = $this->LlamarConexion();
            $sql  = "SELECT c.cedula FROM cliente c WHERE c.cedula = :cedula";
            $stm  = $db->prepare($sql);
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
            $db  = $this->LlamarConexion();
            $stm = $db->prepare("SELECT cedula FROM cliente WHERE cedula = :cedula LIMIT 1");
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
}
