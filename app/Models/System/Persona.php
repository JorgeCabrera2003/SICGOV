<?php

/*
MODELO DE PERSONAS

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

class Persona extends Database
{
    private $cedula;
    private $documento;
    private $nombre;
    private $apellido;
    private $fecha_nacimiento;
    private $sexo;
    private $telefono;
    private $correo;
    private $direccion;

    public function __construct()
    {
        $this->cedula = "";
        $this->documento = NULL;
        $this->nombre = "";
        $this->apellido = "";
        $this->fecha_nacimiento = "";
        $this->sexo = "";
        $this->telefono = "";
        $this->correo = "";
        $this->direccion = "";
    }

    // Getters y Setters


    public function setCedula(string $cedula)
    {
        $cedula = trim($cedula);
        if (RegexHelper::ValidarFormatos($cedula, "cedula") == 0) {
            throw new Exception('La cédula debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.');
        }
        $this->cedula = strtoupper($cedula[0]) . substr($cedula, 1);
    }

    /**
     * Documento: prefijo (V/E/J/P/G) + 7 a 9 dígitos.
     * El frontend envía ya concatenado, ej. "V12345678".
     */
    public function setDocumento(string $documento)
    {
        $documento = trim($documento);
        if (RegexHelper::ValidarFormatos($documento, "DocumentoLegal") == 0) {
            throw new Exception('El documento legal debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.');
        }
        $this->documento = strtoupper($documento[0]) . substr($documento, 1);
    }

    public function setNombre(string $nombre)
    {
        $nombre = trim($nombre);
        if (RegexHelper::ValidarFormatos($nombre, "NombrePersona") == 0) {
            throw new Exception('Nombre no válido. Debe tener al menos entre 3 a 150 carácteres');
        }

        $this->nombre = $nombre;
    }

    public function setApellido(string $apellido)
    {
        $apellido = trim($apellido);
        if (RegexHelper::ValidarFormatos($apellido, "NombrePersona") == 0) {
            throw new Exception('Apellido no válido. Debe tener al menos entre 3 a 65 carácteres');
        }

        $this->apellido = $apellido;
    }

    public function setSexo(string $sexo)
    {
        $sexo = trim($sexo);
        if (RegexHelper::ValidarFormatos($sexo, "Sexo") == 0) {
            throw new Exception('Sexo no válido. Debe ser Masculino (M) o Femenino (F)');
        }

        $this->sexo = $sexo;
    }

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

    public function getDocumento()
    {
        return $this->documento;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getApellido()
    {
        return $this->apellido;
    }

    public function getSexo()
    {
        return $this->sexo;
    }

    public function getFechaNacimiento()
    {
        return $this->sexo;
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
                'registrar' => $this->RegistrarPersona(),
                'consultar' => $this->ConsultarPersona(),
                'actualizar', 'modificar' => $this->ModificarPersona(),
                'eliminar' => $this->EliminarPersona(),
                'validar' => $this->ValidarPersona(),
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
    private function ConsultarPersona()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM cedula WHERE estatus = 1";
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

    private function RegistrarPersona()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarPersona();
        if ($validacion['bool'] == 0) {
            try {
                $sql = "INSERT INTO persona (cedula, documento, nombre, apellido, fecha_nacimiento, 
                telefono, correo, direccion, sexo)
                VALUES (:cedula, :documento, :nombre, :apellido, :fecha_nacimiento, 
                :telefono, :correo, :direccion, :sexo)";

                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':cedula', $this->cedula);
                $stm->bindParam(':documento', $this->documento);
                $stm->bindParam(':nombre', $this->nombre);
                $stm->bindParam(':apellido', $this->apellido);
                $stm->bindParam(':fecha_nacimiento', $this->fecha_nacimiento);
                $stm->bindParam(':telefono', $this->telefono);
                $stm->bindParam(':correo', $this->correo);
                $stm->bindParam(':direccion', $this->direccion);
                $stm->bindParam(':sexo', $this->sexo);
                $stm->execute();
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Persona registrada exitosamente"];
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

    private function ModificarPersona()
    {
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE persona SET documento = :documento, 
            nombre = :nombre, apellido = :apellido, fecha_nacimiento = :fecha_nacimiento, 
            telefono = :telefono, correo = :correo, direccion = :direccion, sexo = :sexo WHERE cedula = :cedula,";

            $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':cedula', $this->cedula);
                $stm->bindParam(':documento', $this->documento);
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
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Persona actualizada exitosamente"];
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

    private function EliminarPersona()
    {
        $dato = [];
        $validacion = $this->ValidarPersona();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "UPDATE persona SET estatus = 0 WHERE cedula = :cedula";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':cedula', $this->cedula);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = NULL;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Persona eliminada exitosamente"];
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

    private function ValidarPersona()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM persona WHERE cedula = :cedula";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':cedula', $this->cedula);
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