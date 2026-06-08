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

namespace App\Models\Security;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;

use PDO;
use Exception;

class ModuloSistema extends Database
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
        if (RegexHelper::ValidarFormatos($nombre, "NombreModulo") == 0) {
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
                'reestablecer' => $this->ReestablecerModulosSistema(),
                'consultar' => $this->ConsultarModuloSistema(),
                'comprobar' => $this->ComprobarModuloSistema(),
                'validar' => $this->ValidarModuloSistema(),
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
    private function ComprobarModuloSistema()
    {
        $dato = [];
        $validacion = [];

        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM modulo";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            $this->LlamarConexion()->commit();

            $dato['comprobar'] = $stm->fetchAll(PDO::FETCH_ASSOC);
            $dato['resultado'] = "comprobar";

            if ($stm->rowCount() == count(MODULOS)) {

                foreach (MODULOS as $indice) {
                    foreach ($validacion as $llave) {
                        if (($indice['id'] == $llave['id_modulo']) && ($indice['modulo'] == $llave['nombre_modulo'])) {
                            $dato['bool'] = true;
                            $dato['mensaje'] = "Módulos cumplen con la validación";
                            $dato['icon'] = "success";
                            break;
                        } else {
                            $dato['bool'] = false;
                            $dato['mensaje'] = "Validación fallida";
                            $dato['icon'] = "warning";
                        }
                    }
                }
            } else {
                $dato['mensaje'] = "Error al comprobar módulos";
                $dato['icon'] = "warning";
                $dato['bool'] = false;
            }

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => $dato['icon'], 'mensaje' => $dato['mensaje'], 'verificacion' => $dato['bool']];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ReestablecerModulosSistema()
    {
        $dato = [];
        $stmR = NULL;
        $stmM = NULL;

        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();

            $queryRegistrar = "INSERT INTO modulo (id_modulo, nombre) VALUES (:id, :modulo)";
            $queryModificar = "UPDATE modulo SET nombre = :modulo WHERE id_modulo = :id";
            $stmR = $this->LlamarConexion()->prepare($queryRegistrar);
            $stmM = $this->LlamarConexion()->prepare($queryModificar);
            foreach (MODULOS as $key) {
                $this->setId($key['id']);
                $this->setNombre($key['modulo']);
                $busqueda = $this->ValidarModuloSistema(true);

                if ($busqueda['bool'] == 0) {
                    $stmR->bindParam(":id", $this->id);
                    $stmR->bindParam(":modulo", $this->nombre);
                    $stmR->execute();

                } else {
                    $stmM->bindParam(":id", $this->id);
                    $stmM->bindParam(":modulo", $this->nombre);
                    $stmM->execute();
                }
            }

            $this->LlamarConexion()->commit();
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Se han reesablecido los Módulos Correctamente'];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            $dato['estado'] = 1;

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ConsultarModuloSistema()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion("security");
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM modulo";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ValidarModuloSistema($transaccionActiva = false)
    {
        $dato = [];
        $arreglo = [];
        try {
            if (!$transaccionActiva) {
                $this->LlamarConexion("security");
                $this->LlamarConexion()->beginTransaction();
            }

            $sql = "SELECT * FROM modulo WHERE id_modulo = :id_modulo";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_modulo', $this->id);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetch(PDO::FETCH_ASSOC);
                $dato['bool'] = 1;

            } else {
                $dato['bool'] = 0;
            }
            if (!$transaccionActiva) {
                $this->LlamarConexion()->commit();
            }

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
        if (!$transaccionActiva) {
            $this->DestruirConexion();
        }
        return $dato;
    }
}