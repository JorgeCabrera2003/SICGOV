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
        return [
            'estado' => 0,
            'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Función de validación no implementada'],
            'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Función no implementada']
        ];
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

        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "SELECT v.cedula AS cedula_empleado,
                           CONCAT(v.nombre, ' ', v.apellido) AS nombre_empleado,
                           MAX(CASE WHEN a.tipo_marcacion = 'ENTRADA' THEN a.hora END) AS hora_entrada,
                           MAX(CASE WHEN a.tipo_marcacion = 'DESCANSO_IN' THEN a.hora END) AS hora_descanso_in,
                           MAX(CASE WHEN a.tipo_marcacion = 'DESCANSO_OUT' THEN a.hora END) AS hora_descanso_out,
                           MAX(CASE WHEN a.tipo_marcacion = 'SALIDA' THEN a.hora END) AS hora_salida
                    FROM vw_directorio_empleados v
                    LEFT JOIN asistencia a ON a.cedula_empleado = v.cedula AND a.fecha = CURDATE()
                    GROUP BY v.cedula, v.nombre, v.apellido
                    ORDER BY v.nombre, v.apellido";
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

    private function RegistrarAsistencia(): array {
        $dato = [];
        $dato['estado'] = 0;
        $dato['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Función de registro no implementada'];
        $dato['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Función no implementada'];

        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

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

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute([
                ':id_asistencia' => $this->idAsistencia,
                ':cedula_empleado' => $this->cedulaEmpleado,
                ':tipo_marcacion' => $this->tipoMarcacion,
                ':fecha' => $this->fecha,
                ':hora' => $this->hora,
                ':estado' => $this->estado,
                ':observacion' => $observacionJson
            ]);

            $this->LlamarConexion()->commit();
            $stm = null;

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
     * Calcula el estado de la asistencia según el tipo de marcación y la hora actual.
     * Este método se mantiene igual.
     */
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
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Registrar el error pero devolver un array vacío para no interrumpir el flujo
            Helper::ErrorLog("Error al decodificar JSON de observaciones: " . json_last_error_msg() . " - JSON: " . $json);
            return [];
        }

        if (!is_array($datos)) {
            return [];
        }

        return $datos;
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