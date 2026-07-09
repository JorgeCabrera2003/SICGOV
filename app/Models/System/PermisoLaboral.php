<?php

/*
MODELO DE PERMISO LABORAL

OPERACIONES A BASE DE DATOS:
    REGISTRAR
    CONSULTAR
    MODIFICAR
    ELIMINAR (LÓGICO)
    VALIDAR
*/

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use Exception;
use PDO;
use PDOException;

class PermisoLaboral extends Database
{
    private $id;
    private $id_tipo_permiso;
    private $cedula_empleado;
    private $fecha_inicio;
    private $fecha_fin;
    private $fecha_solicitud;
    private $fecha_aprobacion;
    private $estado;
    private $estatus;

    public function __construct()
    {
        $this->id = "";
        $this->id_tipo_permiso = "";
        $this->cedula_empleado = "";
        $this->fecha_inicio = "";
        $this->fecha_fin = "";
        $this->fecha_solicitud = null;
        $this->fecha_aprobacion = null;
        $this->estado = "PENDIENTE";
        $this->estatus = 1;
    }

    public function setId(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID del permiso no cumple con el formato permitido.");
        }
        $this->id = $id;
    }

    public function setIdTipoPermiso(string $id)
    {
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El tipo de permiso no es válido.");
        }
        $this->id_tipo_permiso = $id;
    }

    public function setCedulaEmpleado(string $cedula)
    {
        if (empty(trim($cedula))) {
            throw new Exception("Debe seleccionar un empleado.");
        }
        $this->cedula_empleado = $cedula;
    }

    public function setFechaInicio(string $fecha)
    {
        if (empty(trim($fecha))) {
            throw new Exception("La fecha de inicio es obligatoria.");
        }
        $this->fecha_inicio = $fecha;
    }

    public function setFechaFin(string $fecha)
    {
        if (empty(trim($fecha))) {
            throw new Exception("La fecha de fin es obligatoria.");
        }
        $this->fecha_fin = $fecha;
    }

    public function setEstado(string $estado)
    {
        $estado = strtoupper(trim($estado));
        if (!in_array($estado, ['PENDIENTE', 'APROBADO', 'RECHAZADO'], true)) {
            throw new Exception("Estado no válido.");
        }
        $this->estado = $estado;
    }

    public function setFechaAprobacion(string $fecha)
    {
        if (empty(trim($fecha))) {
            throw new Exception("Fecha de aprobación no puede estar vacía.");
        }
        $this->fecha_aprobacion = $fecha;
    }

    public function setEstatus(int $estatus)
    {
        if ($estatus != 0 && $estatus != 1) {
            throw new Exception("Estatus no válido.");
        }
        $this->estatus = $estatus;
    }

    public function getId()
    {
        return $this->id;
    }

    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarPermiso(),
                'consultar' => $this->ConsultarPermisos(),
                'actualizar', 'modificar' => $this->ModificarPermiso(),
                'aprobar' => $this->CambiarEstadoPermiso('APROBADO'),
                'rechazar' => $this->CambiarEstadoPermiso('RECHAZADO'),
                'eliminar' => $this->EliminarPermiso(),
                'validar' => $this->ValidarPermiso(),
                default => [
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }

        return $response;
    }

    private function ConsultarPermisos()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT pl.id_permiso, pl.id_tipo_permiso, pl.cedula_empleado, pl.fecha_inicio, pl.fecha_fin, pl.fecha_aprobacion, pl.estado, pl.estatus,
                    tp.nombre AS tipo_nombre,
                    CONCAT(p.nombre, ' ', p.apellido) AS empleado
                    FROM permiso_laboral pl
                    INNER JOIN tipo_permiso tp ON tp.id_tipo_permiso = pl.id_tipo_permiso
                    INNER JOIN empleado e ON e.cedula = pl.cedula_empleado
                    INNER JOIN persona p ON p.cedula = e.cedula
                    WHERE pl.estatus = 1
                    ORDER BY pl.fecha_solicitud DESC";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            $this->LlamarConexion()->commit();
            $stm = null;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'OK', 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde', 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function RegistrarPermiso()
    {
        $dato = [];
        $validacion = $this->ValidarPermiso();
        if ($validacion['bool'] == 0) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "INSERT INTO permiso_laboral (id_permiso, id_tipo_permiso, cedula_empleado, fecha_inicio, fecha_fin, estado)
                        VALUES (:id_permiso, :id_tipo_permiso, :cedula_empleado, :fecha_inicio, :fecha_fin, :estado)";

                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_permiso', $this->id);
                $stm->bindParam(':id_tipo_permiso', $this->id_tipo_permiso);
                $stm->bindParam(':cedula_empleado', $this->cedula_empleado);
                $stm->bindParam(':fecha_inicio', $this->fecha_inicio);
                $stm->bindParam(':fecha_fin', $this->fecha_fin);
                $stm->bindParam(':estado', $this->estado);
                $stm->execute();
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Permiso registrado exitosamente'];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
            } catch (PDOException $e) {
                $this->LlamarConexion()->rollBack();
                Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
            }
        } else {
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 409, 'mensaje' => 'El permiso ya existe'];
            $dato['HTTP_STATUS'] = ['codigo' => 409, 'mensaje' => 'Registro duplicado'];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ModificarPermiso()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE permiso_laboral SET id_tipo_permiso = :id_tipo_permiso, cedula_empleado = :cedula_empleado, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin
                    WHERE id_permiso = :id_permiso AND estatus = 1";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_tipo_permiso', $this->id_tipo_permiso);
            $stm->bindParam(':cedula_empleado', $this->cedula_empleado);
            $stm->bindParam(':fecha_inicio', $this->fecha_inicio);
            $stm->bindParam(':fecha_fin', $this->fecha_fin);
            $stm->bindParam(':id_permiso', $this->id);
            $stm->execute();
            $this->LlamarConexion()->commit();
            $stm = null;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Permiso actualizado exitosamente'];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function CambiarEstadoPermiso(string $estado)
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE permiso_laboral SET estado = :estado, fecha_aprobacion = :fecha_aprobacion WHERE id_permiso = :id_permiso AND estatus = 1";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':estado', $estado);
            $stm->bindParam(':fecha_aprobacion', $this->fecha_aprobacion);
            $stm->bindParam(':id_permiso', $this->id);
            $stm->execute();
            $this->LlamarConexion()->commit();
            $stm = null;

            $mensaje = $estado === 'APROBADO' ? 'Permiso aprobado correctamente' : 'Permiso rechazado correctamente';
            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => $mensaje];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => 'Error interno del servidor'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function EliminarPermiso()
    {
        $dato = [];
        $validacion = $this->ValidarPermiso();

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "UPDATE permiso_laboral SET estatus = 0 WHERE id_permiso = :id_permiso";
                $stm = $this->LlamarConexion()->prepare($sql);
                $stm->bindParam(':id_permiso', $this->id);
                $stm->execute();
                $this->LlamarConexion()->commit();
                $stm = null;

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Permiso eliminado exitosamente'];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
            } catch (PDOException $e) {
                $this->LlamarConexion()->rollBack();
                Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => 'Error interno del servidor'];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
            }
        } else {
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 404, 'icon' => 'error', 'mensaje' => 'Registro no encontrado'];
            $dato['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => 'No encontrado'];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ValidarPermiso()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM permiso_laboral WHERE id_permiso = :id_permiso";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_permiso', $this->id);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetch(PDO::FETCH_ASSOC);
                $dato['bool'] = 1;
            } else {
                $dato['bool'] = 0;
            }
            $this->LlamarConexion()->commit();
            $stm = null;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'registro' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => 'Error interno del servidor'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }
        $this->DestruirConexion();
        return $dato;
    }
}
