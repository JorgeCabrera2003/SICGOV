<?php

/*
MODELO DE ASISTENCIA

OPERACIONES A BASE DE DATOS:
    VALIDAR
    CONSULTAR
    REGISTRAR
    AGREGAR OBSERVACIONES
    ELIMINAR OBSERVACIONES
   
*/


namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
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
    private $indiceObservacion;
   
    public function __construct() {

        $this->idAsistencia = "";
        $this->cedulaEmpleado = "";
        $this->tipoMarcacion = "";
        $this->fecha = "";
        $this->hora = "";
        $this->estado = "";
        $this->observacion = "";
        $this->indiceObservacion = -1;

    }
/*
 $bool = match ($config) {
            "Cedula" => preg_match('/^[VEJPGvejpg]{1}[0-9]{7,15}$/', $valor),
            "ID" => preg_match('/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/', $valor),
            "NombrePersona", "Persona" => preg_match('/^[a-z A-ZáéíóúüñÑçÇ]{3,65}$/', $valor),
            "NombreUsuario", "Usuario" => preg_match('/^[0-9a-zA-Z_]{4,20}$/', $valor),
            "NombreObjeto", "Objeto" => preg_match('/^[0-9 a-zA-ZáéíóúüñÑçÇ]{3,65}$/', $valor),
            "NombreObjetoLargo", "ObjetoLargo" => preg_match('/^[0-9 a-zA-ZáéíóúüñÑçÇ\s\-.,()!?]{3,200}$/', $valor),
            "Titulo" => preg_match('/^[0-9a-zA-ZÁÉÍÓÚÜáéíóúüñÑçÇ\s\-.,()!?"\'%:;\/]{3,150}$/', $valor),
            "Telefono" => preg_match('/^[0-9]{4}[-][0-9]{3}[-][0-9]{4}$/', $valor),
            "Correo" => preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._%+-]{1,63}@[a-zA-Z0-9][a-zA-Z0-9.-]{1,50}\.(com|es|mx|co\.uk|org|net)$/', $valor),
            "Sexo" => preg_match('/^[MF]{1}$/', $valor),
            // Solo letras (incluyendo tildes y ñ) y espacios, mínimo 2 caracteres, máximo 65
            "CategoriaMenu" => preg_match('/^[A-ZÁÉÍÓÚÑa-záéíóúñ][A-ZÁÉÍÓÚÑa-záéíóúñ\s]{1,64}$/', $valor),
            // Igual que CategoriaMenu pero hasta 200 chars (descripción opcional)
            "CategoriaMenuDesc" => preg_match('/^[A-ZÁÉÍÓÚÑa-záéíóúñ][A-ZÁÉÍÓÚÑa-záéíóúñ\s]{1,199}$/', $valor),
            default => 0
        };
*/
    //SETTERS
    public function setIdAsistencia(string $id) {
        $this->idAsistencia = $id;
    }

    public function setCedulaEmpleado(string $cedulaEmpleado) {
        $cedulaEmpleado = trim($cedulaEmpleado);
        if (RegexHelper::ValidarFormatos($cedulaEmpleado, "Cedula") == 0) {
            throw new Exception('La Cédula debe tener un prefijo válido (V, E) seguido de 7 a 8 dígitos.');
        }
        $this->cedulaEmpleado = strtoupper($cedulaEmpleado[0]) . substr($cedulaEmpleado, 1);
    }

    public function setTipoMarcacion(string $tipoMarcacion) {
        $this->tipoMarcacion = $tipoMarcacion;
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

    public function setIndiceObservacion(int $indice) {
        $this->indiceObservacion = $indice;
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
                'agregar_observacion' => $this->AgregarObservacion(),
                'eliminar_observacion' => $this->EliminarObservacion(),

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

            $sql = "SELECT a.*, 
                           SUBSTRING_INDEX(v.nombre, ' ', 1) AS primer_nombre, 
                           SUBSTRING_INDEX(v.apellido, ' ', 1) AS primer_apellido 
                    FROM asistencia a 
                    LEFT JOIN vw_directorio_empleados v ON v.cedula = a.cedula_empleado 
                    ORDER BY a.fecha DESC, a.hora DESC";
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

    private function AgregarObservacion() {
        $dato = [];
        $dato['estado'] = 0;
        $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Función de actualización no implementada'];
        $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Función no implementada'];

        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE asistencia
                    SET observacion = CONCAT(COALESCE(observacion, ''), CASE WHEN COALESCE(observacion, '') = '' THEN '' ELSE '\n' END, :observacion)
                    WHERE id_asistencia = :id_asistencia";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':id_asistencia' => $this->idAsistencia,
                ':observacion' => $this->observacion
            ]);

            if ($stm->rowCount() === 0) {
                throw new Exception('No se encontró el registro de asistencia para actualizar.');
            }

            $sql = "SELECT observacion FROM asistencia WHERE id_asistencia = :id_asistencia";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([':id_asistencia' => $this->idAsistencia]);
            $observacionActualizada = $stm->fetchColumn() ?: '';

            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Observación agregada correctamente', 'datos' => ['observacion' => $observacionActualizada]];
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

    private function EliminarObservacion() {
        $dato = [];
        $dato['estado'] = 0;
        $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Función de eliminación no implementada'];
        $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Función no implementada'];

        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "SELECT observacion FROM asistencia WHERE id_asistencia = :id_asistencia";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([':id_asistencia' => $this->idAsistencia]);
            $observacionActual = $stm->fetchColumn() ?: '';

            $lines = preg_split('/\r\n|\r|\n/', $observacionActual);
            $observacionesArray = array_filter($lines, function($line) {
                return trim($line) !== '';
            });

            // Convertir a array indexado
            $observacionesArray = array_values($observacionesArray);

            if ($this->indiceObservacion >= count($observacionesArray) || $this->indiceObservacion < 0) {
                throw new Exception('Índice de observación fuera de rango. Indice: ' . $this->indiceObservacion . ', Total: ' . count($observacionesArray));
            }

            // Eliminar la observación específica
            array_splice($observacionesArray, $this->indiceObservacion, 1);

            // Reconstruir el string de observaciones
            $nuevaObservacion = implode("\n", $observacionesArray);

            $sql = "UPDATE asistencia SET observacion = :observacion WHERE id_asistencia = :id_asistencia";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':observacion' => $nuevaObservacion,
                ':id_asistencia' => $this->idAsistencia
            ]);

            if ($stm->rowCount() === 0) {
                throw new Exception('No se encontró el registro de asistencia para actualizar.');
            }

            $this->LlamarConexion()->commit();
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Observación eliminada correctamente', 'datos' => ['observacion' => $nuevaObservacion]];
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