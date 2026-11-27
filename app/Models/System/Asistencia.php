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

    // Declarar todas las propiedades de la clase
    private string $idAsistencia;
    private string $cedulaEmpleado;
    private string $tipoMarcacion;
    private string $fecha;
    private string $hora;
    private string $estado;
    private string $observacion;
    // Ya no se usa, pero lo mantengo por compatibilidad. Se puede eliminar si no se usa en otros lados.
    // private int $indiceObservacion; 
   
    public function __construct() {
        // Inicializar propiedades con valores por defecto
        $this->idAsistencia = "";
        $this->cedulaEmpleado = "";
        $this->tipoMarcacion = "";
        $this->fecha = "";
        $this->hora = "";
        $this->estado = "";
        $this->observacion = "";
        // $this->indiceObservacion = -1; // Comentado porque ya no se usa
    }

    //SETTERS
    public function setIdAsistencia(string $id): void {
        $this->idAsistencia = $id;
    }

    public function setCedulaEmpleado(string $cedulaEmpleado): void {
        $cedulaEmpleado = trim($cedulaEmpleado);

        // Normalizar: aceptar formatos 'V12345678' o 'V-12345678' y convertir a 'V-12345678'
        $cedulaEmpleado = str_replace(' ', '', $cedulaEmpleado);
        $cedulaEmpleado = strtoupper($cedulaEmpleado);

        // Si no contiene guion, insertarlo después del prefijo
        if (!str_contains($cedulaEmpleado, '-')) {
            if (strlen($cedulaEmpleado) >= 2) {
                $prefijo = $cedulaEmpleado[0];
                $numeros = substr($cedulaEmpleado, 1);
                $cedulaEmpleado = $prefijo . '-' . $numeros;
            }
        }

        if (RegexHelper::ValidarFormatos($cedulaEmpleado, "Cedula") == 0) {
            throw new Exception('La cédula debe tener un prefijo válido (V, E, J, P, G), un guion y 7 a 9 dígitos.');
        }

        $this->cedulaEmpleado = $cedulaEmpleado;
    }

    public function setTipoMarcacion(string $tipoMarcacion): void {
        $this->tipoMarcacion = $tipoMarcacion;
    }

    public function setFecha(string $fecha): void {
        $this->fecha = $fecha;
    }

    public function setHora(string $hora): void {
        $this->hora = $hora;
    }

    public function setEstado(string $estado): void {
        $this->estado = $estado;
    }

    public function setObservacion(string $observacion): void {
        $this->observacion = $observacion;
    }

    public function prepararMarcacion(string $cedulaEmpleado, string $tipoMarcacion, string $observacion = '', ?\DateTimeImmutable $momento = null): void {
        $momento = $momento ?? new \DateTimeImmutable('now');
        $this->setCedulaEmpleado($cedulaEmpleado);

        if (!in_array($tipoMarcacion, ['ENTRADA', 'DESCANSO_IN', 'DESCANSO_OUT', 'SALIDA'], true)) {
            throw new \InvalidArgumentException('Tipo de marcación inválido.');
        }

        $fechaActual = $momento->format('Y-m-d');
        $horaActual = $momento->format('H:i:s');
        $turno = $this->obtenerContextoTurno($fechaActual, $horaActual);
        $fechaTurno = $turno['fecha_turno'] ?? $fechaActual;

        $this->setFecha($fechaTurno);
        $this->setHora($horaActual);
        $this->setTipoMarcacion($tipoMarcacion);
        $this->setEstado($this->calcularEstadoMarcacion(
            $tipoMarcacion,
            $horaActual,
            $turno,
            $fechaTurno,
            $momento
        ));
        $this->setObservacion($observacion);
    }

    public function empleadoActivo(): bool {
        $this->LlamarConexion();

        try {
            $sql = "SELECT estatus FROM empleado WHERE cedula = :cedula LIMIT 1";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([':cedula' => $this->cedulaEmpleado]);
            return (int) $stm->fetchColumn() === 1;
        } finally {
            $this->DestruirConexion();
        }
    }

    public function obtenerContextoTurno(string $fechaActual, string $horaActual): ?array {
        $this->LlamarConexion();

        try {
            $fechaAnterior = (new \DateTimeImmutable($fechaActual))->modify('-1 day')->format('Y-m-d');
            $sqlAnterior = "SELECT t.hora_inicio, t.hora_fin, t.minuto_tolerancia,
                                   pt.fecha AS fecha_turno,
                                   EXISTS (
                                       SELECT 1 FROM asistencia a
                                       WHERE a.cedula_empleado = pt.cedula_empleado
                                         AND a.fecha = pt.fecha
                                         AND a.tipo_marcacion = 'ENTRADA'
                                   ) AS tiene_entrada,
                                   EXISTS (
                                       SELECT 1 FROM asistencia a
                                       WHERE a.cedula_empleado = pt.cedula_empleado
                                         AND a.fecha = pt.fecha
                                         AND a.tipo_marcacion = 'SALIDA'
                                   ) AS tiene_salida
                            FROM planificador_turno pt
                            INNER JOIN turno t ON t.id_turno = pt.id_turno
                            WHERE pt.cedula_empleado = :cedula
                              AND pt.fecha = :fecha_anterior
                              AND t.hora_fin < t.hora_inicio
                            LIMIT 1";
            $stm = $this->LlamarConexion()->prepare($sqlAnterior);
            $stm->execute([
                ':cedula' => $this->cedulaEmpleado,
                ':fecha_anterior' => $fechaAnterior,
            ]);
            $turnoAnterior = $stm->fetch(PDO::FETCH_ASSOC);

            if (
                $turnoAnterior
                && (int) $turnoAnterior['tiene_salida'] === 0
                && ($horaActual <= $turnoAnterior['hora_fin'] || (int) $turnoAnterior['tiene_entrada'] === 1)
            ) {
                return $turnoAnterior;
            }

            $sqlActual = "SELECT t.hora_inicio, t.hora_fin, t.minuto_tolerancia,
                                 pt.fecha AS fecha_turno
                          FROM planificador_turno pt
                          INNER JOIN turno t ON t.id_turno = pt.id_turno
                          WHERE pt.cedula_empleado = :cedula
                            AND pt.fecha = :fecha_actual
                          LIMIT 1";
            $stm = $this->LlamarConexion()->prepare($sqlActual);
            $stm->execute([
                ':cedula' => $this->cedulaEmpleado,
                ':fecha_actual' => $fechaActual,
            ]);
            $turnoActual = $stm->fetch(PDO::FETCH_ASSOC);

            return $turnoActual ?: null;
        } finally {
            $this->DestruirConexion();
        }
    }

    // Este setter ya no se usa con la nueva lógica, pero lo mantengo por compatibilidad
    // public function setIndiceObservacion(int $indice): void {
    //     $this->indiceObservacion = $indice;
    // }
    //FIN SETTERS

    //GETTERS
    public function getIdAsistencia(): string {
        return $this->idAsistencia;
    }

    public function getCedulaEmpleado(): string {
        return $this->cedulaEmpleado;
    }

    public function getTipoMarcacion(): string {
        return $this->tipoMarcacion;
    }

    public function getFecha(): string {
        return $this->fecha;
    }

    public function getHora(): string {
        return $this->hora;
    }

    public function getEstado(): string {
        return $this->estado;
    }

    public function getObservacion(): string {
        return $this->observacion;
    }
    //FIN GETTERS

    // MANEJADOR DE OPERACIONES
    public function Transaccion(array $peticion): array {
        $response = [
            'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
            'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
        ];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'validar' => $this->ValidarAsistencia(),
                'consultar' => $this->ConsultarAsistencia(),
                'consultar_hoy' => $this->ConsultarAsistenciaHoy(),
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
    private function ValidarAsistencia(): array {
        try {
            if ($this->cedulaEmpleado === '' || $this->fecha === '' || $this->tipoMarcacion === '') {
                throw new \InvalidArgumentException('Datos de marcación incompletos.');
            }

            $this->LlamarConexion();
            $sqlEmpleado = "SELECT estatus FROM empleado WHERE cedula = :cedula LIMIT 1";
            $stmEmpleado = $this->LlamarConexion()->prepare($sqlEmpleado);
            $stmEmpleado->execute([':cedula' => $this->cedulaEmpleado]);
            if ((int) $stmEmpleado->fetchColumn() !== 1) {
                throw new \DomainException('El empleado no existe o no está activo.');
            }

            $sqlMarcaciones = "SELECT tipo_marcacion
                               FROM asistencia
                               WHERE cedula_empleado = :cedula
                                 AND fecha = :fecha
                               ORDER BY id_asistencia ASC";
            $stmMarcaciones = $this->LlamarConexion()->prepare($sqlMarcaciones);
            $stmMarcaciones->execute([
                ':cedula' => $this->cedulaEmpleado,
                ':fecha' => $this->fecha,
            ]);
            self::validarSecuenciaMarcacion(
                $stmMarcaciones->fetchAll(PDO::FETCH_COLUMN),
                $this->tipoMarcacion
            );

            return [
                'estado' => 1,
                'response' => ['resultado' => 200, 'mensaje' => 'La marcación puede registrarse.'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK']
            ];
        } catch (\DomainException $e) {
            return [
                'estado' => 0,
                'response' => ['resultado' => 409, 'icon' => 'warning', 'mensaje' => $e->getMessage()],
                'HTTP_STATUS' => ['codigo' => 409, 'mensaje' => 'Conflict']
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'estado' => 0,
                'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => $e->getMessage()],
                'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Datos no válidos']
            ];
        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return [
                'estado' => -1,
                'response' => ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde'],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Error interno del servidor']
            ];
        } finally {
            $this->DestruirConexion();
        }
    }

    private function ConsultarAsistencia(): array {
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
            $stm = null;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => 'OK', 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde', 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    private function ConsultarAsistenciaHoy(): array {
        $dato = [];
        $arreglo = [];
        $db = null;
        $momento = new \DateTimeImmutable('now');
        $fechaActual = $momento->format('Y-m-d');
        $fechaAnterior = $momento->modify('-1 day')->format('Y-m-d');

        try {
            $db = $this->LlamarConexion();
            $db->beginTransaction();

            $sql = "SELECT e.cedula AS cedula_empleado,
                           CONCAT(p.nombre, ' ', p.apellido) AS nombre_empleado,
                           jornada.fecha_turno,
                           jornada.hora_inicio,
                           jornada.hora_fin,
                           jornada.minuto_tolerancia,
                           jornada.sin_turno,
                           a.id_asistencia,
                           a.tipo_marcacion,
                           a.hora,
                           a.estado,
                           EXISTS (
                               SELECT 1
                               FROM permiso_laboral pl
                               WHERE pl.cedula_empleado = e.cedula
                                 AND pl.estado = 'APROBADO'
                                 AND pl.estatus = 1
                                 AND jornada.fecha_turno BETWEEN pl.fecha_inicio AND pl.fecha_fin
                           ) AS tiene_permiso
                    FROM empleado e
                    INNER JOIN persona p ON p.cedula = e.cedula
                    INNER JOIN (
                        SELECT pt.cedula_empleado, pt.fecha AS fecha_turno,
                               t.hora_inicio, t.hora_fin, t.minuto_tolerancia, 0 AS sin_turno
                        FROM planificador_turno pt
                        INNER JOIN turno t ON t.id_turno = pt.id_turno
                        WHERE pt.fecha = :fecha_turno_actual
                        UNION ALL
                        SELECT pt.cedula_empleado, pt.fecha AS fecha_turno,
                               t.hora_inicio, t.hora_fin, t.minuto_tolerancia, 0 AS sin_turno
                        FROM planificador_turno pt
                        INNER JOIN turno t ON t.id_turno = pt.id_turno
                        WHERE pt.fecha = :fecha_turno_anterior
                          AND t.hora_fin < t.hora_inicio
                          AND NOT EXISTS (
                              SELECT 1 FROM asistencia a
                              WHERE a.cedula_empleado = pt.cedula_empleado
                                AND a.fecha = pt.fecha
                                AND a.tipo_marcacion = 'SALIDA'
                          )
                          AND (
                              :hora_actual <= t.hora_fin
                              OR (
                                  EXISTS (
                                      SELECT 1 FROM asistencia a
                                      WHERE a.cedula_empleado = pt.cedula_empleado
                                        AND a.fecha = pt.fecha
                                        AND a.tipo_marcacion = 'ENTRADA'
                                  )
                              )
                          )
                        UNION ALL
                        SELECT a.cedula_empleado, a.fecha AS fecha_turno,
                               NULL AS hora_inicio, NULL AS hora_fin, NULL AS minuto_tolerancia, 1 AS sin_turno
                        FROM asistencia a
                        WHERE a.fecha = :fecha_marcacion_actual
                          AND NOT EXISTS (
                              SELECT 1
                              FROM planificador_turno pt
                              INNER JOIN turno t ON t.id_turno = pt.id_turno
                              WHERE pt.cedula_empleado = a.cedula_empleado
                                AND pt.fecha = a.fecha
                          )
                        GROUP BY a.cedula_empleado, a.fecha
                    ) jornada ON jornada.cedula_empleado = e.cedula
                    LEFT JOIN asistencia a
                      ON a.cedula_empleado = jornada.cedula_empleado
                     AND a.fecha = jornada.fecha_turno
                    WHERE e.estatus = 1
                    ORDER BY p.nombre, p.apellido, jornada.fecha_turno, a.id_asistencia";
            $stm = $db->prepare($sql);
            $stm->execute([
                ':fecha_turno_actual' => $fechaActual,
                ':fecha_turno_anterior' => $fechaAnterior,
                ':hora_actual' => $momento->format('H:i:s'),
                ':fecha_marcacion_actual' => $fechaActual,
            ]);

            $jornadas = [];
            foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $clave = $fila['cedula_empleado'] . '|' . $fila['fecha_turno'];
                if (!isset($jornadas[$clave])) {
                    $turno = $fila['sin_turno'] ? null : [
                        'hora_inicio' => $fila['hora_inicio'],
                        'hora_fin' => $fila['hora_fin'],
                        'minuto_tolerancia' => $fila['minuto_tolerancia'],
                    ];
                    $jornadas[$clave] = [
                        'cedula_empleado' => $fila['cedula_empleado'],
                        'nombre_empleado' => $fila['nombre_empleado'],
                        'fecha_turno' => $fila['fecha_turno'],
                        'hora_entrada' => null,
                        'hora_descanso_in' => null,
                        'hora_descanso_out' => null,
                        'hora_salida' => null,
                        'estado_entrada' => null,
                        'estado_descanso_in' => null,
                        'estado_descanso_out' => null,
                        'estado_salida' => null,
                        'tiene_permiso' => (int) $fila['tiene_permiso'],
                        'turno' => $turno,
                    ];
                }

                if (!$fila['id_asistencia']) {
                    continue;
                }

                $campos = [
                    'ENTRADA' => ['hora_entrada', 'estado_entrada'],
                    'DESCANSO_IN' => ['hora_descanso_in', 'estado_descanso_in'],
                    'DESCANSO_OUT' => ['hora_descanso_out', 'estado_descanso_out'],
                    'SALIDA' => ['hora_salida', 'estado_salida'],
                ];
                if (isset($campos[$fila['tipo_marcacion']])) {
                    [$campoHora, $campoEstado] = $campos[$fila['tipo_marcacion']];
                    $jornadas[$clave][$campoHora] = $fila['hora'];
                    if ($campoEstado !== null) {
                        $jornadas[$clave][$campoEstado] = $fila['estado'];
                    }
                }
            }

            foreach ($jornadas as $jornada) {
                if ($jornada['hora_entrada'] === null && $jornada['turno'] !== null
                    && $jornada['tiene_permiso'] !== 1
                    && self::debeMarcarFalta($jornada['turno'], $jornada['fecha_turno'], $momento)
                ) {
                    $jornada['estado_entrada'] = 'FALTA';
                }
                unset($jornada['turno']);
                unset($jornada['fecha_turno']);
                $arreglo[] = $jornada;
            }

            $db->commit();
            $stm = null;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => 'OK', 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\PDOException $e) {
            if ($db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde', 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    private function RegistrarAsistencia(): array {
        $dato = [];
        $db = null;
        $dato['estado'] = 0;
        $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Función de registro no implementada'];
        $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Función no implementada'];

        try {
            $db = $this->LlamarConexion();
            $db->beginTransaction();

            $sqlEmpleado = "SELECT estatus FROM empleado WHERE cedula = :cedula FOR UPDATE";
            $stmEmpleado = $db->prepare($sqlEmpleado);
            $stmEmpleado->execute([':cedula' => $this->cedulaEmpleado]);
            if ((int) $stmEmpleado->fetchColumn() !== 1) {
                throw new \DomainException('El empleado no existe o no está activo.');
            }

            $sqlMarcaciones = "SELECT tipo_marcacion
                               FROM asistencia
                               WHERE cedula_empleado = :cedula
                                 AND fecha = :fecha
                               ORDER BY id_asistencia ASC
                               FOR UPDATE";
            $stmMarcaciones = $db->prepare($sqlMarcaciones);
            $stmMarcaciones->execute([
                ':cedula' => $this->cedulaEmpleado,
                ':fecha' => $this->fecha,
            ]);
            self::validarSecuenciaMarcacion(
                $stmMarcaciones->fetchAll(PDO::FETCH_COLUMN),
                $this->tipoMarcacion
            );

            // Si la observación es un texto plano simple y no está vacía, la convertimos al nuevo formato JSON.
            $observacionJson = $this->observacion;
            if (!empty($observacionJson)) {
                // Intentamos decodificarlo para ver si ya es JSON
                $temp = json_decode($observacionJson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Si no es JSON, lo tratamos como una observación simple de texto plano
                    // y la convertimos al nuevo formato.
                    $nuevaObservacion = $this->crearNuevaObservacion($observacionJson);
                    $observacionJson = json_encode([$nuevaObservacion]);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception('Error al codificar la observación inicial a JSON.');
                    }
                } else {
                    // Ya es JSON, lo dejamos como está.
                    $observacionJson = $this->observacion;
                }
            } else {
                $observacionJson = null; // Para que la base de datos lo guarde como NULL
            }

            $sql = "INSERT INTO asistencia (id_asistencia, cedula_empleado, tipo_marcacion, fecha, hora, estado, observacion)
                    VALUES (:id_asistencia, :cedula_empleado, :tipo_marcacion, :fecha, :hora, :estado, :observacion)";

            $stm = $db->prepare($sql);
            $stm->execute([
                ':id_asistencia' => $this->idAsistencia,
                ':cedula_empleado' => $this->cedulaEmpleado,
                ':tipo_marcacion' => $this->tipoMarcacion,
                ':fecha' => $this->fecha,
                ':hora' => $this->hora,
                ':estado' => $this->estado,
                ':observacion' => $observacionJson
            ]);

            $db->commit();
            $stm = null;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Asistencia registrada correctamente'];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
        } catch (\DomainException $e) {
            if ($db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }
            $dato['estado'] = 0;
            $dato['response'] = ['resultado' => 409, 'icon' => 'warning', 'mensaje' => $e->getMessage()];
            $dato['HTTP_STATUS'] = ['codigo' => 409, 'mensaje' => 'Conflict'];
        } catch (\PDOException $e) {
            if ($db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        } catch (Exception $e) {
            if ($db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Ups, intente de nuevo más tarde'];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => 'Error interno del servidor'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    /**
     * Agrega una nueva observación al registro de asistencia.
     * Utiliza un campo JSON en la base de datos para almacenar un array de observaciones.
     */
    private function AgregarObservacion(): array {
        $dato = [];
        $dato['estado'] = 0;
        $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Función de actualización no implementada'];
        $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Función no implementada'];

        try {
            // 1. Validar el tamaño de la observación
            if (mb_strlen($this->observacion) > 2000) {
                throw new Exception('La observación no puede superar los 2000 caracteres.');
            }

            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            // 2. Obtener la observación actual del registro
            $sqlSelect = "SELECT observacion FROM asistencia WHERE id_asistencia = :id_asistencia FOR UPDATE";
            $stm = $this->LlamarConexion()->prepare($sqlSelect);
            $stm->execute([':id_asistencia' => $this->idAsistencia]);
            $observacionActual = $stm->fetchColumn();

            // 3. Decodificar el JSON actual
            $observaciones = $this->decodificarObservaciones($observacionActual);

            // 4. Crear la nueva observación
            $nuevaObservacion = $this->crearNuevaObservacion($this->observacion);
            
            // 5. Agregarla al array
            $observaciones[] = $nuevaObservacion;

            // 6. Codificar a JSON
            $nuevoJson = json_encode($observaciones);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al codificar las observaciones a JSON: ' . json_last_error_msg());
            }

            // 7. Actualizar en la base de datos
            $sqlUpdate = "UPDATE asistencia SET observacion = :observacion WHERE id_asistencia = :id_asistencia";
            $stm = $this->LlamarConexion()->prepare($sqlUpdate);
            $stm->execute([
                ':observacion' => $nuevoJson,
                ':id_asistencia' => $this->idAsistencia
            ]);

            if ($stm->rowCount() === 0) {
                throw new Exception('No se encontró el registro de asistencia para actualizar.');
            }

            $this->LlamarConexion()->commit();
            $stm = null;

            $dato['estado'] = 1;
            $dato['response'] = [
                'resultado' => 200, 
                'icon' => 'success', 
                'mensaje' => 'Observación agregada correctamente', 
                'datos' => ['observaciones' => $observaciones]
            ];
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
            $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => $e->getMessage()];
            $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error en la solicitud'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    /**
     * Elimina una observación del registro de asistencia por su ID (eliminación lógica).
     * La observación se marca como eliminada, pero no se elimina físicamente.
     */
    private function EliminarObservacion(): array {
        $dato = [];
        $dato['estado'] = 0;
        $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Función de eliminación no implementada'];
        $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Función no implementada'];

        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            // 1. Obtener la observación actual del registro
            $sqlSelect = "SELECT observacion FROM asistencia WHERE id_asistencia = :id_asistencia FOR UPDATE";
            $stm = $this->LlamarConexion()->prepare($sqlSelect);
            $stm->execute([':id_asistencia' => $this->idAsistencia]);
            $observacionActual = $stm->fetchColumn();

            // 2. Decodificar el JSON actual
            $observaciones = $this->decodificarObservaciones($observacionActual);

            // 3. Buscar la observación por ID y marcarla como eliminada
            $encontrado = false;
            foreach ($observaciones as &$obs) {
                if (isset($obs['id']) && $obs['id'] === $this->observacion) {
                    // Eliminación lógica: marcar como eliminada
                    $obs['eliminada'] = true;
                    $obs['eliminada_por'] = $_SESSION['user']['cedula'] ?? 'Sistema';
                    $obs['eliminada_en'] = date('Y-m-d H:i:s');
                    $encontrado = true;
                    break;
                }
            }

            if (!$encontrado) {
                throw new Exception('No se encontró la observación con el ID especificado.');
            }

            // 4. Codificar a JSON
            $nuevoJson = json_encode($observaciones);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al codificar las observaciones a JSON: ' . json_last_error_msg());
            }

            // 5. Actualizar en la base de datos
            $sqlUpdate = "UPDATE asistencia SET observacion = :observacion WHERE id_asistencia = :id_asistencia";
            $stm = $this->LlamarConexion()->prepare($sqlUpdate);
            $stm->execute([
                ':observacion' => $nuevoJson,
                ':id_asistencia' => $this->idAsistencia
            ]);

            if ($stm->rowCount() === 0) {
                throw new Exception('No se encontró el registro de asistencia para actualizar.');
            }

            $this->LlamarConexion()->commit();
            $stm = null;

            $dato['estado'] = 1;
            $dato['response'] = [
                'resultado' => 200, 
                'icon' => 'success', 
                'mensaje' => 'Observación eliminada correctamente', 
                'datos' => ['observaciones' => $observaciones]
            ];
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
            $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => $e->getMessage()];
            $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error en la solicitud'];
        }

        $this->DestruirConexion();
        return $dato;
    }

    //FIN DE OPERACIONES A LA BASE DE DATOS

    /**
     * Calcula la puntualidad de una entrada usando el turno asignado.
     */
    public function calcularEstadoAsistencia(
        string $tipoMarcacion,
        string $horaActual,
        ?string $horaInicio = null,
        ?int $minutoTolerancia = null,
        ?string $horaFin = null,
        ?string $fechaTurno = null,
        ?string $fechaMarcacion = null
    ): string {
        $fechaTurno = $fechaTurno ?? date('Y-m-d');
        $fechaMarcacion = $fechaMarcacion ?? $fechaTurno;
        $tolerancia = max(0, $minutoTolerancia ?? 15);

        if ($tipoMarcacion === 'ENTRADA' && $horaInicio !== null) {
            $horaRegistro = new \DateTimeImmutable($fechaMarcacion . ' ' . $horaActual);
            $horaInicioTurno = new \DateTimeImmutable($fechaTurno . ' ' . $horaInicio);

            if ($horaFin !== null && $horaFin < $horaInicio && $fechaMarcacion === $fechaTurno && $horaActual < $horaInicio) {
                return 'A_TIEMPO';
            }

            $limiteATiempo = $horaInicioTurno->add(new \DateInterval('PT' . $tolerancia . 'M'));
            return $horaRegistro <= $limiteATiempo ? 'A_TIEMPO' : 'TARDE';
        }

        if ($tipoMarcacion === 'SALIDA' && $horaFin !== null) {
            $horaFinTurno = new \DateTimeImmutable($fechaTurno . ' ' . $horaFin);
            if ($horaInicio !== null && $horaFin <= $horaInicio) {
                $horaFinTurno = $horaFinTurno->modify('+1 day');
            }

            $horaRegistro = new \DateTimeImmutable($fechaMarcacion . ' ' . $horaActual);
            $salidaPermitidaDesde = $horaFinTurno->sub(new \DateInterval('PT' . $tolerancia . 'M'));
            return $horaRegistro >= $salidaPermitidaDesde ? 'A_TIEMPO' : 'SALIDA_ANTICIPADA';
        }

        return 'A_TIEMPO';
    }

    public function calcularEstadoMarcacion(
        string $tipoMarcacion,
        string $horaActual,
        ?array $turno,
        string $fechaTurno,
        \DateTimeImmutable $momento
    ): string {
        if ($turno === null) {
            return 'DIA_SIN_TURNO';
        }

        return $this->calcularEstadoAsistencia(
            $tipoMarcacion,
            $horaActual,
            $turno['hora_inicio'] ?? null,
            isset($turno['minuto_tolerancia']) ? (int) $turno['minuto_tolerancia'] : null,
            $turno['hora_fin'] ?? null,
            $fechaTurno,
            $momento->format('Y-m-d')
        );
    }

    public static function debeMarcarFalta(array $turno, string $fechaTurno, \DateTimeImmutable $momento): bool {
        if (empty($turno['hora_inicio']) || empty($turno['hora_fin'])) {
            return false;
        }

        $inicio = new \DateTimeImmutable($fechaTurno . ' ' . $turno['hora_inicio']);
        $fin = new \DateTimeImmutable($fechaTurno . ' ' . $turno['hora_fin']);
        if ($fin <= $inicio) {
            $fin = $fin->modify('+1 day');
        }

        $duracionSegundos = $fin->getTimestamp() - $inicio->getTimestamp();
        $mitad = $inicio->modify('+' . (int) floor($duracionSegundos / 2) . ' seconds');

        return $momento >= $mitad;
    }

    public static function validarSecuenciaMarcacion(array $marcaciones, string $siguiente): void {
        $estado = [
            'entrada' => false,
            'descanso_inicio' => false,
            'descanso_fin' => false,
            'salida' => false,
        ];

        foreach ($marcaciones as $marcacion) {
            self::aplicarMarcacionAEstado($estado, (string) $marcacion, false);
        }
        self::aplicarMarcacionAEstado($estado, $siguiente, true);
    }

    private static function aplicarMarcacionAEstado(array &$estado, string $tipo, bool $esNueva): void {
        $prefijo = $esNueva ? '' : 'Las marcaciones existentes tienen una secuencia inválida. ';

        if ($estado['salida']) {
            throw new \DomainException($prefijo . 'No se permiten marcaciones después de la salida.');
        }

        switch ($tipo) {
            case 'ENTRADA':
                if ($estado['entrada'] || $estado['descanso_inicio'] || $estado['descanso_fin']) {
                    throw new \DomainException($prefijo . 'La entrada solo se puede marcar una vez y debe ser la primera.');
                }
                $estado['entrada'] = true;
                return;

            case 'DESCANSO_IN':
                if (!$estado['entrada']) {
                    throw new \DomainException($prefijo . 'Debe marcar la entrada antes de iniciar el descanso.');
                }
                if ($estado['descanso_inicio'] || $estado['descanso_fin']) {
                    throw new \DomainException($prefijo . 'Solo se permite iniciar un descanso por turno.');
                }
                $estado['descanso_inicio'] = true;
                return;

            case 'DESCANSO_OUT':
                if (!$estado['descanso_inicio']) {
                    throw new \DomainException($prefijo . 'Debe iniciar el descanso antes de terminarlo.');
                }
                if ($estado['descanso_fin']) {
                    throw new \DomainException($prefijo . 'El descanso solo se puede terminar una vez.');
                }
                $estado['descanso_fin'] = true;
                return;

            case 'SALIDA':
                if (!$estado['entrada']) {
                    throw new \DomainException($prefijo . 'Debe marcar la entrada antes de la salida.');
                }
                $estado['salida'] = true;
                return;

            default:
                throw new \DomainException('Tipo de marcación inválido.');
        }
    }

    // --- NUEVOS MÉTODOS PRIVADOS PARA MANEJO DE JSON ---

    /**
     * Decodifica el campo de observación de la base de datos a un array.
     * Si el JSON es inválido o está vacío, devuelve un array vacío.
     */
    private function decodificarObservaciones(?string $json): array {
        if (empty($json)) {
            return [];
        }

        $datos = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($datos)) {
            return $datos;
        }

        // Compatibilidad hacia atrás con texto plano legado
        $lineas = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $json)));
        $observaciones = [];
        foreach ($lineas as $idx => $linea) {
            $textoLimpio = ltrim($linea, '- ');
            if (!empty($textoLimpio)) {
                $observaciones[] = [
                    'id' => 'LEGACY_' . ($idx + 1),
                    'texto' => $textoLimpio,
                    'autor' => 'Sistema',
                    'fecha' => date('Y-m-d H:i:s'),
                    'eliminada' => false
                ];
            }
        }
        return $observaciones;
    }

    /**
     * Crea una nueva observación con el formato estándar.
     */
    private function crearNuevaObservacion(string $texto): array {
        return [
            'id' => Helper::generarId('OBS'),
            'texto' => trim($texto),
            'autor' => $_SESSION['user']['cedula'] ?? 'Sistema',
            'fecha' => date('Y-m-d H:i:s'),
            'eliminada' => false
        ];
    }

}