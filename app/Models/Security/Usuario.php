<?php
namespace App\Models\Security;

use App\Core\Database;
use PDO;
use DateTime;

class Usuario
{
    private $id_usuario;
    private $cedula;
    private $id_rol;
    private $username;
    private $nombres;
    private $apellidos;
    private $telefono;
    private $correo;

    private $clave;
    private $foto_perfil;
    private $tema_oscuro;
    private $ultimo_acceso;
    private $fecha_registro;

    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection('security');
    }

    private function LlamarConexion(PDO &$db = NULL)
    {
        if ($db != NULL) {
            $this->db = $db;
        }

        if ($this->db == NULL) {
            $this->db = Database::getConnection('security');
        }

        return $this->db;
    }

    private function DestruirConexion()
    {
        $this->db = NULL;
    }
    //SETTERS
    public function set_cedula(string $cedula)
    {
        $this->cedula = $cedula;
    }

    public function set_id_rol(string $id_rol)
    {
        $this->id_rol = $id_rol;
    }

    public function set_username(string $username)
    {
        $this->username = $username;
    }

    public function set_nombres(string $nombres)
    {
        $this->nombres = $nombres;
    }

    public function set_apellidos(string $apellidos)
    {
        $this->apellidos = $apellidos;
    }

    public function set_telefono(string $telefono)
    {
        $this->telefono = $telefono;
    }

    public function set_correo(string $correo)
    {
        $this->correo = $correo;
    }
    public function set_clave(string $clave)
    {
        $this->clave = $clave;
    }

    public function set_foto_perfil(string $foto_perfil)
    {
        $this->foto_perfil = $foto_perfil;
    }

    public function set_tema(string $tema)
    {
        $this->tema_oscuro = $tema;
    }

    public function set_ultimo_acceso(DateTime $ultimo_acceso)
    {
        $this->ultimo_acceso = $ultimo_acceso;
    }

    public function set_fecha_registro(DateTime $fecha_registro)
    {
        $this->fecha_registro = $fecha_registro;
    }

    //GETTERS
    public function get_cedula()
    {
        return $this->cedula;
    }

    public function get_id_rol()
    {
        return $this->id_rol;
    }

    public function get_username()
    {
        return $this->username;
    }

    public function get_nombres()
    {
        return $this->nombres;
    }

    public function get_apellidos()
    {
        return $this->apellidos;
    }

    public function get_telefono()
    {
        return $this->telefono;
    }

    public function get_correo()
    {
        return $this->correo;
    }
    public function get_clave()
    {
        return $this->clave;
    }

    public function get_foto_perfil()
    {
        return $this->foto_perfil;
    }

    public function get_tema()
    {
        return $this->tema_oscuro;
    }

    public function get_ultimo_acceso()
    {
        return $this->ultimo_acceso;
    }

    public function get_fecha_registro()
    {
        return $this->fecha_registro;
    }

    public function Transaccion($peticion)
    {
        $response = [];
        if (isset($peticion['peticion'])) {

            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarUsuario(),
                'consultar' => $this->ConsultarUsuario(),
                'validar' => $this->ValidarUsuario(),
                'sesion' => $this->IniciarSesion(),
                'perfil' => $this->PerfilUsuario(),
                default => [
                    'response' => ['resultado' => 400, 'icon' => 'danger', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };

        } else {
            $response['response'] = ['resultado' => 400, 'icon' => 'danger', 'mensaje' => "Envió solicitud no válida"];
            $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];
        }
        var_dump($response);
        return $response;
    }

    //Consultas a la Base de Datos
    private function ValidarUsuario()
    {
        $dato = [];
        $arreglo = [];

        try {
            $sql = "SELECT * FROM usuario WHERE cedula = :cedula
            OR username = :username OR correo = :correo";
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':correo', $this->correo);
            $stm->bindParam(":cedula", $this->cedula);
            $stm->bindParam(':username', $this->username);
            $stm->execute();

            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetch(PDO::FETCH_ASSOC);
                $dato['bool'] = 1;

            } else {
                $dato['bool'] = 0;
            }
            $this->LlamarConexion()->commit();
            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'registro' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['resultado'] = "registrar";
            $dato['mensaje'] = $e->getMessage();
            $dato['estado'] = -1;
        }

        $this->DestruirConexion();
        return $dato;
    }
    private function IniciarSesion()
    {
        $dato = [];
        $validacion = $this->ValidarUsuario();
        $dato['response'] = ['resultado' => 401, 'mensaje' => "Credenciales Inválidas", 'verificacion' => false];
        $dato['HTTP_STATUS'] = ['codigo' => 401, 'mensaje' => "Credenciales Inválidas"];

        if ($validacion['bool'] == 1) {
            if (password_verify($this->clave, $validacion['response']['registro']['clave'])) {
                $dato['response'] = ['resultado' => 200, 'mensaje' => "OK", 'verificacion' => true];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            }
        }
        return $dato;
    }

    private function PerfilUsuario()
    {
        $dato = [];
        $registro = [];

        try {
            $sql = "SELECT * FROM usuario WHERE cedula = :cedula";
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam('cedula', $this->cedula);
            $stm->execute();
            $registro = $stm->fetch();
            $this->LlamarConexion()->commit();
            $dato['response'] = ['resultado' => 200, 'datos' => $registro];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "Error interno del servidor"];
            $stm = NULL;
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['response'] = ['resultado' => 500, 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function RegistrarUsuario()
    {
        $dato = [];
        $validacion = [];
        $validacion = $this->ValidarUsuario();
        if ($validacion['bool'] == 0) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();

                $sql = "INSERT INTO usuario(id_usuario, cedula, id_rol, username, nombres, apellidos, telefono, correo, clave) 
        VALUES (:id_usuario, :cedula, :id_rol, :username, :nombres, :apellidos, :telefono, :correo, :clave)";

                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_usuario', $this->id_usuario);
                $stm->bindParam(':cedula', $this->cedula);
                $stm->bindParam(':id_rol', $this->id_rol);
                $stm->bindParam(':username', $this->username);
                $stm->bindParam(':nombres', $this->nombres);
                $stm->bindParam(':apellidos', $this->apellidos);
                $stm->bindParam(':telefono', $this->telefono);
                $stm->bindParam(':correo', $this->correo);
                $stm->bindParam(':clave', $this->clave);
                $stm->execute();
                $stm = NULL;
                $this->LlamarConexion()->commit();
                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Usuario registrado exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => "Se registró exitosamente"];
            } catch (\PDOException $e) {
                $this->LlamarConexion()->rollBack();
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'icon' => 'success', 'mensaje' => "Ups, intente de nuevo más tarde"];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
                error_log(
                "\n=============\nError: " . $e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine() . "\n=============",
                3,
                "logs/logs.txt"
            );
            }
        } else {
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 409, 'icon' => 'danger', 'mensaje' => "Registro duplicado"];
                $dato['HTTP_STATUS'] = ['codigo' => 409, 'mensaje' => "Conflicto: Registro duplicado"];
        }
        return $dato;
    }

        private function ConsultarUsuario()
    {
        $dato = [];
        $arreglo = [];

        try {
            $this->LlamarConexion();
           $this->LlamarConexion()->beginTransaction();
            $query = "SELECT * FROM usuario WHERE estatus = 1";

            $stm = $this->LlamarConexion()->prepare($query);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => "OK", 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            $stm = NULL;
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'danger', 'mensaje' => "Ups, intente de nuevo más tarde", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
            error_log(
                "\n=============\nError: " . $e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine() . "\n=============",
                3,
                "logs/logs.txt"
            );
        }
        $this->DestruirConexion();
        return $dato;
    }
}