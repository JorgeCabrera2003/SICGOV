<?php

/*
MODELO DE HORARIO (Planificador de Turnos)

OPERACIONES A BASE DE DATOS:
    REGISTRAR (Asignar turno a empleado en una fecha)
    CONSULTAR (Obtener horarios, con filtros por empleado y rango de fechas)
    MODIFICAR (Actualizar una asignación existente)
    ELIMINAR (Físico)
    VALIDAR (Verificar si ya existe una asignación para esa fecha/empleado)
*/

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use Exception;
use PDO;

class Horario extends Database
{
    private $id;
    private $cedula_empleado;
    private $id_turno;
    private $fecha;

    public function __construct()
    {
        $this->id = "";
        $this->cedula_empleado = "";
        $this->id_turno = "";
        $this->fecha = "";
    }

    // --- SETTERS ---
    public function setId(string $id)
    {
        if (!RegexHelper::ValidarFormatos($id, 'ID')) {
            throw new Exception("El ID del Horario no cumple con el formato permitido.");
        }
        $this->id = $id;
    }

    public function setCedulaEmpleado(string $cedula)
    {
        if (empty($cedula) || strlen($cedula) < 5) {
            throw new Exception("La cédula del empleado no es válida.");
        }
        $this->cedula_empleado = $cedula;
    }

    public function setIdTurno(string $id_turno)
    {
        if (!RegexHelper::ValidarFormatos($id_turno, 'ID')) {
            throw new Exception("El ID del Turno no cumple con el formato permitido.");
        }
        $this->id_turno = $id_turno;
    }

    public function setFecha(string $fecha)
    {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha)) {
            throw new Exception("El formato de fecha no es válido (YYYY-MM-DD).");
        }
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$d || $d->format('Y-m-d') !== $fecha) {
            throw new Exception("La fecha proporcionada no es una fecha real.");
        }
        $this->fecha = $fecha;
    }
    // --- FIN SETTERS ---

    // --- GETTERS ---
    public function getId() { return $this->id; }
    public function getCedulaEmpleado() { return $this->cedula_empleado; }
    // --- FIN GETTERS ---

    // --- MANEJADOR DE OPERACIONES ---
    public function Transaccion($peticion)
    {
        $response = [
            'estado' => -1,
            'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
            'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
        ];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarAsignacion(),
                'consultar' => $this->ConsultarAsignaciones($peticion['filtros'] ?? []),
                'modificar' => $this->ModificarAsignacion(),
                'eliminar'  => $this->EliminarAsignacion(),
                'validar'   => $this->ValidarAsignacion(),
                default     => $response
            };
        }
        return $response;
    }
    // --- FIN MANEJADOR DE OPERACIONES ---

    // --- OPERACIONES A BASE DE DATOS ---

    private function RegistrarAsignacion()
    {
        $dato = [];
        $validacion = $this->ValidarAsignacion();

        if ($validacion['bool'] == 1) {
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 409, 'icon' => 'warning', 'mensaje' => "El empleado ya tiene un turno asignado en esta fecha."];
            $dato['HTTP_STATUS'] = ['codigo' => 409, 'mensaje' => "Conflicto: Asignación duplicada"];
            return $dato;
        }

        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "INSERT INTO planificador_turno(id_planificador_turno, cedula_empleado, id_turno, fecha)
                    VALUES (:id, :cedula, :id_turno, :fecha)";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id', $this->id);
            $stm->bindParam(':cedula', $this->cedula_empleado);
            $stm->bindParam(':id_turno', $this->id_turno);
            $stm->bindParam(':fecha', $this->fecha);
            $stm->execute();

            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => "Turno asignado exitosamente."];
            $dato['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => "Creado"];

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Error interno al asignar turno."];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ModificarAsignacion()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE planificador_turno SET id_turno = :id_turno
                    WHERE id_planificador_turno = :id";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id', $this->id);
            $stm->bindParam(':id_turno', $this->id_turno);
            $stm->execute();

            $this->LlamarConexion()->commit();

            if ($stm->rowCount() > 0) {
                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Turno actualizado exitosamente."];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            } else {
                $dato['estado'] = 0;
                $dato['response'] = ['resultado' => 404, 'icon' => 'info', 'mensaje' => "No se encontró la asignación."];
                $dato['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => "No encontrado"];
            }

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Error interno al modificar asignación."];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function EliminarAsignacion()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "DELETE FROM planificador_turno WHERE id_planificador_turno = :id";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id', $this->id);
            $stm->execute();

            $this->LlamarConexion()->commit();

            if ($stm->rowCount() > 0) {
                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Asignación de turno eliminada."];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            } else {
                $dato['estado'] = 0;
                $dato['response'] = ['resultado' => 404, 'icon' => 'error', 'mensaje' => "Asignación no encontrada."];
                $dato['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => "No encontrado"];
            }

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Error al eliminar la asignación."];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ConsultarAsignaciones($filtros = [])
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "SELECT pt.id_planificador_turno, pt.fecha, pt.cedula_empleado,
                           p.nombre, p.apellido, t.id_turno, t.nombre as nombre_turno, 
                           t.hora_inicio, t.hora_fin
                    FROM planificador_turno pt
                    INNER JOIN empleado e ON pt.cedula_empleado = e.cedula
                    INNER JOIN persona p ON e.cedula = p.cedula
                    INNER JOIN turno t ON pt.id_turno = t.id_turno
                    WHERE e.estatus = 1";

            if (!empty($filtros['cedula_empleado'])) {
                $sql .= " AND pt.cedula_empleado = :cedula";
            }
            if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
                $sql .= " AND pt.fecha BETWEEN :fecha_inicio AND :fecha_fin";
            } elseif (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND pt.fecha >= :fecha_inicio";
            } elseif (!empty($filtros['fecha_fin'])) {
                $sql .= " AND pt.fecha <= :fecha_fin";
            }
            
            $sql .= " ORDER BY pt.fecha ASC, t.hora_inicio ASC";

            $stm = $this->LlamarConexion()->prepare($sql);

            if (!empty($filtros['cedula_empleado'])) {
                $stm->bindParam(':cedula', $filtros['cedula_empleado']);
            }
            if (!empty($filtros['fecha_inicio'])) {
                $stm->bindParam(':fecha_inicio', $filtros['fecha_inicio']);
            }
            if (!empty($filtros['fecha_fin'])) {
                $stm->bindParam(':fecha_fin', $filtros['fecha_fin']);
            }

            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "OK", 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Error al consultar horarios.", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ValidarAsignacion()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "SELECT id_planificador_turno FROM planificador_turno
                    WHERE cedula_empleado = :cedula AND fecha = :fecha";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':cedula', $this->cedula_empleado);
            $stm->bindParam(':fecha', $this->fecha);
            $stm->execute();

            $dato['bool'] = ($stm->rowCount() > 0) ? 1 : 0;
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => "OK"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['bool'] = -1;
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error al validar asignación"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

}