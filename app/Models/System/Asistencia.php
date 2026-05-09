<?php

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;

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
                'actualizar', 'modificar' => $this->ModificarAsistencia(),

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
       
    }

    private function ConsultarAsistencia() {
       
    }

    private function RegistrarAsistencia() {
    }

    private function ModificarAsistencia() {
   
    }
    //FIN DE OPERACIONES A LA BASE DE DATOS

}