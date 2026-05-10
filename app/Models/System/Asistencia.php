<?php

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;
use Exception;

class Asistencia extends Database {

    private $idAsistencia;
    private $cedulaEmpleado;
    private $tipoMarcacion;
    private $fecha;
    private $hora;
    private $estado;
    private $observacion;
   
    public function __construct() {

        $this->idAsistencia = "";
        $this->cedulaEmpleado = "";
        $this->tipoMarcacion = "";
        $this->fecha = "";
        $this->hora = "";
        $this->estado = "";
        $this->observacion = "";

    }

    //SETTERS
    public function setIdAsistencia(string $id) {
        $this->idAsistencia = $id;
    }

    public function setCedulaEmpleado(string $cedula) {
        $this->cedulaEmpleado = $cedula;
    }

    public function setTipoMarcacion(string $tipo) {
        $this->tipoMarcacion = $tipo;
    }

    public function setFecha(string $fecha) {
        $this->fecha = $fecha;
    }

    public function setHora(string $hora) {
        $this->hora = $hora;
    }

    public function setEstado(string $estado) {
        $this->estado = $estado;
    }

    public function setObservacion(string $observacion) {
        $this->observacion = $observacion;
    }
    //FIN SETTERS

    //GETTERS
    public function getIdAsistencia() {
        return $this->idAsistencia;
    }

    public function getCedulaEmpleado()
    {
        return $this->cedulaEmpleado;
    }

    public function getTipoMarcacion() {
        return $this->tipoMarcacion;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getHora() {
        return $this->hora;
    }

    public function getEstado()  {
        return $this->estado;
    }

    public function getObservacion() {
        return $this->observacion;
    }
    //FIN GETTERS

    // MANEJADOR DE OPERACIONES
    public function Transaccion($peticion) {

        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {

            $response = match ($peticion['peticion']) {

                'validar' => $this->ValidarAsistencia(),
                'consultar' => $this->ConsultarAsistencia(),
                'registrar' => $this->RegistrarAsistencia(),

                default => [
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }
    //FIN DE MANEJADOR DE OPERACIONES

    //OPERACIONES A LA BASE DE DATOS
    private function ValidarAsistencia() {
        $dato = [];
        $dato['estado'] = 0;
        $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Función de validación no implementada'];
        $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Función no implementada'];
        return $dato;
    }

    private function ConsultarAsistencia() {
        $dato = [];
        $arreglo = [];

        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "SELECT * FROM asistencia ORDER BY fecha DESC, hora DESC";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();

            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }

            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => 'OK', 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde', 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    private function RegistrarAsistencia() {
        $dato = [];
        $dato['estado'] = 0;
        $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Función de registro no implementada'];
        $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Función no implementada'];

        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "INSERT INTO asistencia (id_asistencia, cedula_empleado, tipo_marcacion, fecha, hora, estado, observacion)
                    VALUES (:id_asistencia, :cedula_empleado, :tipo_marcacion, :fecha, :hora, :estado, :observacion)";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':id_asistencia' => $this->idAsistencia,
                ':cedula_empleado' => $this->cedulaEmpleado,
                ':tipo_marcacion' => $this->tipoMarcacion,
                ':fecha' => $this->fecha,
                ':hora' => $this->hora,
                ':estado' => $this->estado,
                ':observacion' => $this->observacion
            ]);

            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Asistencia registrada correctamente'];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        } catch (Exception $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    //FIN DE OPERACIONES A LA BASE DE DATOS

    public function calcularEstadoAsistencia(string $tipoMarcacion, string $horaActual): string {
        if ($tipoMarcacion !== 'ENTRADA') {
            return 'A_TIEMPO';
        }

        try {
            $horaRegistro = new \DateTime($horaActual);
            $horaInicio = new \DateTime('08:00:00');
            $limiteATiempo = (clone $horaInicio)->add(new \DateInterval('PT10M'));
            $limiteTarde = (clone $horaInicio)->add(new \DateInterval('PT120M'));

            if ($horaRegistro <= $limiteATiempo) {
                return 'A_TIEMPO';
            }

            if ($horaRegistro <= $limiteTarde) {
                return 'TARDE';
            }
        } catch (\Exception $e) {
            return 'A_TIEMPO';
        }

        return 'FALTA';
    }


}