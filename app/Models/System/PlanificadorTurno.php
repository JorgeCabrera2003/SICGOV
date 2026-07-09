<?php

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;

class PlanificadorTurno extends Database
{
    private $id_planificador_turno;
    private $cedula_empleado;
    private $id_turno;
    private $fecha;

    public function __construct()
    {
        $this->id_planificador_turno = '';
        $this->cedula_empleado = '';
        $this->id_turno = '';
        $this->fecha = date('Y-m-d');
    }

    public function setIdPlanificadorTurno(string $id)
    {
        $this->id_planificador_turno = $id;
    }

    public function setCedulaEmpleado(string $cedula)
    {
        $this->cedula_empleado = trim($cedula);
    }

    public function setIdTurno(string $id)
    {
        $this->id_turno = trim($id);
    }

    public function setFecha(string $fecha)
    {
        $this->fecha = $fecha;
    }

    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            switch ($peticion['peticion']) {
                case 'registrar':
                    $response = $this->RegistrarPlanificador();
                    break;
                case 'consultar':
                    $response = $this->ConsultarPlanificador();
                    break;
                case 'modificar':
                    $response = $this->ModificarPlanificador();
                    break;
                case 'eliminar':
                    $response = $this->EliminarPlanificador();
                    break;
                default:
                    $response = [
                        'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                        'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                    ];
            }
        }

        return $response;
    }

    private function ConsultarPlanificador()
    {
        $dato = [];
        $arreglo = [];
        try {
            $db = $this->LlamarConexion();
            $db->beginTransaction();

            $fecha = $this->fecha ?: date('Y-m-d');

            $sql = "SELECT pt.id_planificador_turno, pt.cedula_empleado, per.nombre, per.apellido, pt.id_turno, t.nombre as nombre_turno, pt.fecha
                    FROM planificador_turno pt
                    INNER JOIN persona per ON pt.cedula_empleado = per.cedula
                    INNER JOIN turno t ON pt.id_turno = t.id_turno
                    WHERE pt.fecha = :fecha
                    ORDER BY per.nombre ASC";

            $stm = $db->prepare($sql);
            $stm->execute([':fecha' => $fecha]);
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }

            $db->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => 'OK', 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde', 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    private function RegistrarPlanificador()
    {
        $dato = [];
        try {
            if (empty($this->id_planificador_turno)) {
                $this->id_planificador_turno = Helper::generarId('PTU');
            }

            $db = $this->LlamarConexion();
            $db->beginTransaction();

            $sql = "INSERT INTO planificador_turno (id_planificador_turno, cedula_empleado, id_turno, fecha)
                    VALUES (:id_planificador_turno, :cedula_empleado, :id_turno, :fecha)";

            $stm = $db->prepare($sql);
            $stm->execute([
                ':id_planificador_turno' => $this->id_planificador_turno,
                ':cedula_empleado' => $this->cedula_empleado,
                ':id_turno' => $this->id_turno,
                ':fecha' => $this->fecha
            ]);

            $db->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => 'Turno asignado correctamente', 'id_planificador_turno' => $this->id_planificador_turno];
            $dato['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    private function ModificarPlanificador()
    {
        $dato = [];
        try {
            $db = $this->LlamarConexion();
            $db->beginTransaction();

            $sql = "UPDATE planificador_turno SET id_turno = :id_turno, fecha = :fecha WHERE id_planificador_turno = :id_planificador_turno";
            $stm = $db->prepare($sql);
            $stm->execute([
                ':id_turno' => $this->id_turno,
                ':fecha' => $this->fecha,
                ':id_planificador_turno' => $this->id_planificador_turno
            ]);

            $db->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Asignación actualizada correctamente'];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    private function EliminarPlanificador()
    {
        $dato = [];
        try {
            $db = $this->LlamarConexion();
            $db->beginTransaction();

            $sql = "DELETE FROM planificador_turno WHERE id_planificador_turno = :id_planificador_turno";
            $stm = $db->prepare($sql);
            $stm->execute([':id_planificador_turno' => $this->id_planificador_turno]);

            $db->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Asignación eliminada correctamente'];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }

        $this->DestruirConexion();
        return $dato;
    }
}
