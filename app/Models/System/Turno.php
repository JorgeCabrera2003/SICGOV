<?php

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;

class Turno extends Database
{
    private $id_turno;
    private $nombre;
    private $hora_inicio;
    private $hora_fin;
    private $minuto_tolerancia;
    private $estatus;

    public function __construct()
    {
        $this->id_turno = '';
        $this->nombre = '';
        $this->hora_inicio = '';
        $this->hora_fin = '';
        $this->minuto_tolerancia = 15;
        $this->estatus = 1;
    }

    // SETTERS
    public function setIdTurno(string $id)
    {
        $this->id_turno = $id;
    }

    public function setNombre(string $nombre)
    {
        $this->nombre = $nombre;
    }

    public function setHoraInicio(string $hora)
    {
        $this->hora_inicio = $hora;
    }

    public function setHoraFin(string $hora)
    {
        $this->hora_fin = $hora;
    }

    public function setMinutoTolerancia(int $minutos)
    {
        $this->minuto_tolerancia = $minutos;
    }

    public function setEstatus(int $estatus)
    {
        $this->estatus = $estatus;
    }

    // GETTERS
    public function getIdTurno()
    {
        return $this->id_turno;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    // Transaction handler
    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $op = $peticion['peticion'];
            switch ($op) {
                case 'registrar':
                    $response = $this->RegistrarTurno();
                    break;
                case 'consultar':
                    $response = $this->ConsultarTurno();
                    break;
                case 'modificar':
                    $response = $this->ModificarTurno();
                    break;
                case 'eliminar':
                    $response = $this->EliminarTurno();
                    break;
                case 'validar':
                    $response = $this->ValidarTurno();
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

    private function ConsultarTurno()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT id_turno, nombre, hora_inicio, hora_fin, minuto_tolerancia FROM turno WHERE estatus = 1 ORDER BY nombre";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => 'OK', 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Ups, intente de nuevo más tarde", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function RegistrarTurno()
    {
        $dato = [];
        try {
            if (empty($this->id_turno)) {
                $this->id_turno = Helper::generarId('TUR');
            }

            $sql = "INSERT INTO turno (id_turno, nombre, hora_inicio, hora_fin, minuto_tolerancia, estatus)
                VALUES (:id_turno, :nombre, :hora_inicio, :hora_fin, :minuto_tolerancia, :estatus)";

            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_turno', $this->id_turno);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->bindParam(':hora_inicio', $this->hora_inicio);
            $stm->bindParam(':hora_fin', $this->hora_fin);
            $stm->bindParam(':minuto_tolerancia', $this->minuto_tolerancia);
            $stm->bindParam(':estatus', $this->estatus);
            $stm->execute();

            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 201, 'icon' => 'success', 'mensaje' => 'Turno registrado correctamente', 'id_turno' => $this->id_turno];
            $dato['HTTP_STATUS'] = ['codigo' => 201, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ModificarTurno()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE turno SET nombre = :nombre, hora_inicio = :hora_inicio, hora_fin = :hora_fin, minuto_tolerancia = :minuto_tolerancia, estatus = :estatus
                WHERE id_turno = :id_turno";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_turno', $this->id_turno);
            $stm->bindParam(':nombre', $this->nombre);
            $stm->bindParam(':hora_inicio', $this->hora_inicio);
            $stm->bindParam(':hora_fin', $this->hora_fin);
            $stm->bindParam(':minuto_tolerancia', $this->minuto_tolerancia);
            $stm->bindParam(':estatus', $this->estatus);
            $stm->execute();

            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Turno actualizado correctamente'];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function EliminarTurno()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "UPDATE turno SET estatus = 0 WHERE id_turno = :id_turno";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_turno', $this->id_turno);
            $stm->execute();
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Turno eliminado correctamente'];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ValidarTurno()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();
            $sql = "SELECT * FROM turno WHERE id_turno = :id_turno";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_turno', $this->id_turno);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $registro = $stm->fetch(PDO::FETCH_ASSOC);
                $dato['bool'] = 1;
                $dato['registro'] = $registro;
            } else {
                $dato['bool'] = 0;
            }
            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'registro' => $dato['registro'] ?? []];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
            $dato['bool'] = -1;
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => 'Error interno del servidor', 'registro' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }
        $this->DestruirConexion();
        return $dato;
    }
}
