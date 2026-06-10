<?php

/*
MODELO DE USUARIOS

OPERACIONES A BASE DE DATOS:
    REGISTRAR
    CONSULTAR
    MODIFICAR
    ELIMINAR (LÓGICO)
    VALIDAR
    INICIAR SESIÓN
    TRAER PERFIL DE USUARIO
    EDITAR PERFIL DE USUARIO
*/


namespace App\Models\Security;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;
use DateTime;

class Usuario extends \App\Models\System\Persona
{
    private $id_rol;
    private $username;
    private $clave;
    private $foto_perfil;
    private $tema_oscuro;
    private $ultimo_acceso;
    private $fecha_registro;
    private $estatus;
    private $estatus_clave;

    public function __construct()
    {
        parent::__construct();
        $this->id_rol = "";
        $this->username = "";
        $this->clave = "";
        $this->foto_perfil = "";
        $this->tema_oscuro = 0;
        $this->ultimo_acceso = "";
        $this->fecha_registro = "";
        $this->estatus = 1;
        $this->estatus_clave = 0;
    }



    //SETTERS
    public function setIdRol(string $id_rol)
    {
        $this->id_rol = $id_rol;
    }

    public function setUsername(string $username)
    {
        $username = trim($username);
        if (mb_strlen($username) < 3) {
            throw new \Exception('El usuario debe tener al menos 3 caracteres.');
        }
        if (!preg_match('/^[a-zA-Z]+$/', $username)) {
            throw new \Exception('El usuario solo puede contener letras, sin números ni símbolos.');
        }
        $this->username = $username;
    }

    public function setClave(string $clave, bool $validar_dificultad = true)
    {
        if ($validar_dificultad && $clave !== '') {
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/', $clave)) {
                throw new \Exception('La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo.');
            }
        }
        $this->clave = $clave;
    }

    public function setFotoPerfil(string $foto_perfil)
    {
        $this->foto_perfil = $foto_perfil;
    }

    public function setTema(string $tema)
    {
        $this->tema_oscuro = $tema;
    }

    public function setUltimoAcceso(DateTime $ultimo_acceso)
    {
        $this->ultimo_acceso = $ultimo_acceso;
    }

    public function setFechaRegistro(DateTime $fecha_registro)
    {
        $this->fecha_registro = $fecha_registro;
    }
    public function setEstatus($estatus)
    {
        $this->estatus = $estatus;
    }

    public function setEstatusClave(int $estatus_clave)
    {
        $this->estatus_clave = $estatus_clave;
    }
    //FIN DE SETTERS

    //GETTERS
    public function getIdRol()
    {
        return $this->id_rol;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getClave()
    {
        return $this->clave;
    }

    public function getFotoPerfil()
    {
        return $this->foto_perfil;
    }

    public function getTema()
    {
        return $this->tema_oscuro;
    }

    public function getUltimoAcceso()
    {
        return $this->ultimo_acceso;
    }

    public function getFechaRegistro()
    {
        return $this->fecha_registro;
    }
    public function getEstatus()
    {
        return $this->estatus;
    }
    //FIN DE GETTERS

    //MANEJADOR DE OPERACIONES
    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'danger', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];
        if (isset($peticion['peticion'])) {

            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarUsuario(),
                'consultar' => $this->ConsultarUsuario(),
                'validar' => $this->ValidarUsuario(),
                'sesion' => $this->IniciarSesion(),
                'perfil' => $this->TraerPerfilUsuario(),
                'empleados-sin-usuario' => $this->empleadosSinUsuario(),
                'actualizar', 'modificar' => $this->actualizarUsuario(),
                'actualizar-clave' => $this->actualizarSoloClave(),
                'toggle-estatus' => $this->toggleEstatus(),
                'forzar-clave' => $this->forzarCambioClave(),
                'guardar-codigo' => $this->guardarCodigoRecuperacion($peticion['codigo'] ?? ''),
                'validar-codigo' => $this->validarCodigoRecuperacion($peticion['codigo'] ?? ''),
                'limpiar-codigo' => $this->limpiarCodigoRecuperacion(),
                default => [
                    'response' => ['resultado' => 400, 'icon' => 'danger', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }
    //FIN DE MANEJADOR DE OPERACIONES

    //OPERACIONES A BASE DE DATOS
    private function ValidarUsuario()
    {
        $dato = [];
        $arreglo = [];

        try {
            $sql = "SELECT * FROM vw_validar_usuario WHERE cedula = :cedula
            OR username = :username OR correo = :correo";
            $this->LlamarConexion("security");
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
            $dato['estado'] = -1;
            $dato['bool'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'registro' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
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
            $dato['response']['usuario_existe'] = true;
            $cedula = $validacion['response']['registro']['cedula'] ?? $this->cedula;
            
            // Consultar el estatus directamente de la tabla usuario
            try {
                $dbSec = \App\Core\Database::getConnection('security');
                $stmtSt = $dbSec->prepare("SELECT estatus FROM usuario WHERE cedula = ?");
                $stmtSt->execute([$cedula]);
                if ($resSt = $stmtSt->fetch(\PDO::FETCH_ASSOC)) {
                    $dato['response']['estatus'] = $resSt['estatus'];
                } else {
                    $dato['response']['estatus'] = $validacion['response']['registro']['estatus'] ?? 1;
                }
            } catch (\Exception $e) {
                $dato['response']['estatus'] = $validacion['response']['registro']['estatus'] ?? 1;
            }

            if (password_verify($this->clave, $validacion['response']['registro']['clave'])) {
                $dato['response']['verificacion'] = true;
                $dato['response']['resultado'] = 200;
                $dato['response']['mensaje'] = "OK";
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            }
        }
        return $dato;
    }

    private function TraerPerfilUsuario()
    {
        $dato = [];
        $registro = [];

        try {
            $sql = "SELECT * FROM vw_perfil_usuario WHERE cedula = :cedula
            OR username = :username OR correo = :correo";
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':correo', $this->correo);
            $stm->bindParam(":cedula", $this->cedula);
            $stm->bindParam(':username', $this->username);
            $stm->execute();
            $registro = $stm->fetch();
            $this->LlamarConexion()->commit();
            $dato['response'] = ['resultado' => 200, 'datos' => $registro];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "Error interno del servidor"];
            $stm = NULL;
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'registro' => []];
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
                $this->LlamarConexion("security");
                $this->LlamarConexion()->beginTransaction();

                $hashed_clave = password_hash($this->clave, PASSWORD_DEFAULT);

                $sql = "INSERT INTO usuario(cedula, id_rol, username, clave, estatus, estatus_clave) 
        VALUES (:cedula, :id_rol, :username, :clave, 1, :estatus_clave)";

                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':cedula', $this->cedula);
                $stm->bindParam(':id_rol', $this->id_rol);
                $stm->bindParam(':username', $this->username);
                $stm->bindParam(':clave', $hashed_clave);
                $stm->bindParam(':estatus_clave', $this->estatus_clave);
                $stm->execute();
                $stm = NULL;
                $this->LlamarConexion()->commit();
                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Usuario registrado exitosamente"];
                $dato['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => "Se registró exitosamente"];
            } catch (\PDOException $e) {
                $this->LlamarConexion()->rollBack();
                $dato['estado'] = -1;
                Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
                $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'registro' => []];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
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
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();
            $query = "SELECT p.*, u.estatus FROM vw_perfil_usuario p JOIN usuario u ON p.cedula = u.cedula WHERE u.cedula != 'V-00000000'";

            $stm = $this->LlamarConexion()->prepare($query);
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
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'registro' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function empleadosSinUsuario()
    {
        $dato = [];
        $arreglo = [];
        try {
            $systemDb = Database::getSystemDb();
            $securityDb = Database::getSecurityDb();
            
            $sql = "SELECT e.cedula, p.nombre, p.apellido 
                    FROM `{$systemDb}`.empleado e 
                    INNER JOIN `{$systemDb}`.persona p ON e.cedula = p.cedula 
                    LEFT JOIN `{$securityDb}`.usuario u ON e.cedula = u.cedula 
                    WHERE u.cedula IS NULL";
            
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();
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
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function actualizarUsuario()
    {
        $dato = [];
        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            if (!empty($this->clave)) {
                $hashed_clave = password_hash($this->clave, PASSWORD_DEFAULT);
                $sql = "UPDATE usuario SET id_rol = :id_rol, username = :username, clave = :clave WHERE cedula = :cedula";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':clave', $hashed_clave);
            } else {
                $sql = "UPDATE usuario SET id_rol = :id_rol, username = :username WHERE cedula = :cedula";
                $stm = $this->LlamarConexion()->prepare($sql);
            }

            $stm->bindParam(':id_rol', $this->id_rol);
            $stm->bindParam(':username', $this->username);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->execute();
            $stm = NULL;
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Usuario actualizado exitosamente"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "Se actualizó exitosamente"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    public function actualizarSoloClave()
    {
        $dato = [];
        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            $hashed_clave = password_hash($this->clave, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario SET clave = :clave WHERE cedula = :cedula";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':clave', $hashed_clave);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->execute();
            $stm = NULL;
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Contraseña actualizada exitosamente"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "Se actualizó exitosamente"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function toggleEstatus()
    {
        $dato = [];
        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE usuario SET estatus = :estatus WHERE cedula = :cedula";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':estatus', $this->estatus);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->execute();
            $stm = NULL;
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Estado de usuario cambiado exitosamente"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "Se cambió de estado exitosamente"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function forzarCambioClave()
    {
        $dato = [];
        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE usuario SET estatus_clave = 0 WHERE cedula = :cedula";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->execute();
            $stm = NULL;
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Se forzó a este usuario a cambiar su contraseña"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "Se forzó cambio de clave exitosamente"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function guardarCodigoRecuperacion($codigo)
    {
        $dato = [];
        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE usuario SET token_recuperacion = :codigo, fecha_expiracion_token = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE cedula = :cedula";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':codigo', $codigo);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->execute();
            
            $this->LlamarConexion()->commit();
            $stm = NULL;
            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => "Token guardado exitosamente"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function validarCodigoRecuperacion($codigo)
    {
        $dato = [];
        try {
            $this->LlamarConexion("security");
            $sql = "SELECT cedula FROM usuario WHERE cedula = :cedula AND token_recuperacion = :codigo AND fecha_expiracion_token > NOW()";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':codigo', $codigo);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->execute();
            
            if ($stm->rowCount() > 0) {
                $dato['estado'] = 1;
                $dato['bool'] = 1;
            } else {
                $dato['estado'] = 1;
                $dato['bool'] = 0;
            }
            $stm = NULL;
        } catch (\PDOException $e) {
            $dato['estado'] = -1;
            $dato['bool'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function limpiarCodigoRecuperacion()
    {
        $dato = [];
        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE usuario SET token_recuperacion = NULL, fecha_expiracion_token = NULL WHERE cedula = :cedula";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->execute();
            
            $this->LlamarConexion()->commit();
            $stm = NULL;
            $dato['estado'] = 1;
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
        }
        $this->DestruirConexion();
        return $dato;
    }
    //FIN DE OPERACIONES A BASE DE DATOS
}