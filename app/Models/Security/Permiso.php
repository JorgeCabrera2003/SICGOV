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
use App\Helpers\RegexHelper;
use PDO;
use Exception;
use DateTime;

class Permiso extends Database
{
    private $id;
    private $id_rol;
    private $modulo;
    private $accion;
    private $estado;

    public function __construct()
    {
        $this->id = "";
        $this->id_rol = "";
        $this->modulo = "";
        $this->accion = "";
        $this->estado = "";
    }

    //SETTERS
    public function setId(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, "ID") == 0) {
            throw new Exception('ID no válido.');
        }
        $this->id = $id;
    }

    public function setIdRol(string $id_rol)
    {
        if (RegexHelper::ValidarFormatos($id_rol, "ID") == 0) {
            throw new Exception('ID no válido.');
        }
        $this->id_rol = $id_rol;
    }

    public function setIdModulo(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, "ID") == 0) {
            throw new Exception('ID no válido.');
        }
        $this->modulo = $id;
    }

    public function setAccion(string $accion)
    {
        if (RegexHelper::ValidarFormatos($accion, "Objeto") == 0) {
            throw new Exception('Acción no válida.');
        }
        $this->accion = $accion;
    }

    public function setEstado(string $estado)
    {
        if($estado != 1 && $estado != 0){
            throw new Exception('Estado no válidao.');
        }
        $this->estado = $estado;
    }
    //FIN DE SETTERS

    //GETTERS
    public function getId()
    {
        return $this->id;
    }

    public function getIdRol()
    {
        return $this->id_rol;
    }

    public function getIdModulo()
    {
        return $this->modulo;
    }

    public function getAccion()
    {
        return $this->accion;
    }

    public function getEstado()
    {
        return $this->estado;
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
                'cargar' => $this->CargarPermiso($peticion['permisos']),
                'filtrar' => $this->FiltrarPermiso($peticion['parametro']),
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

    private function FiltrarPermiso($filtro = "nombre_modulo")
    {
        if ($filtro == "nombre_modulo") {
            $columna = "nombre_modulo";
        } else {
            $columna = "id_modulo";
        }
        $dato = [];

        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();
            $query = "SELECT p.id_permiso, p.id_rol, p.id_modulo, p.accion, p.estatus, m.nombre
            FROM permiso AS p 
            INNER JOIN modulo AS m ON p.id_modulo = m.id_modulo
            WHERE p.id_rol = :rol";


            $stm = $this->LlamarConexion()->prepare($query);
            $stm->bindParam(':rol', $this->id_rol);
            $stm->execute();
            $this->LlamarConexion()->commit();
            $resultadoQuery = $stm->fetchAll(PDO::FETCH_ASSOC);
            $permisos = [];

            foreach ($resultadoQuery as $fila) {
                $modulo = $fila[$columna];
                $accion = $fila['accion'];
                $estado = $fila['estatus'];

                if (!isset($permisos[$modulo])) {
                    $permisos[$modulo] = [];
                }
                $permisos[$modulo][$accion] = [
                    'estado' => $estado,
                    'id' => $fila['id_permiso']
                ];
            }
            $dato['response'] = ['resultado' => 200, 'permiso' => $permisos];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'permiso' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }


    private function CargarPermiso($permisos)
    {
        $dato = [];
       
        $query = "INSERT INTO permiso (id_permiso, id_rol, id_modulo, accion, estatus)
                VALUES (:id_permiso, :rol, :modulo, :accion, :estado)
                ON DUPLICATE KEY UPDATE estatus = VALUES(estatus)";

        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();
            $stm = $this->LlamarConexion()->prepare($query);
            foreach ($permisos as $key) {
                foreach ($key['permisos'] as $accion) {
                    $params[] = [
                        'id_permiso' => $accion['id'],
                        'rol' => $this->id_rol,
                        'modulo' => $key['modulo'],
                        'accion' => $accion['accion'],
                        'estado' => $accion['estado']
                    ];
                }
            }

            foreach ($params as $param) {
                $stm->bindParam(":id_permiso", $param['id_permiso']);
                $stm->bindParam(":rol", $param['rol']);
                $stm->bindParam(":modulo", $param['modulo']);
                $stm->bindParam(":accion", $param['accion']);
                $stm->bindParam(":estado", $param['estado']);
                $stm->execute();
            }
            $this->LlamarConexion()->commit();
            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Permisos subidos exitosamente"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }
    //FIN DE OPERACIONES A BASE DE DATOS
}