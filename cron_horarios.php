<?php

use App\Core\Database;
use App\Helpers\NotificacionHelper;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Acceso denegado. Este script solo puede ejecutarse desde la línea de comandos.\n");
}

define('BASE_PATH', __DIR__);
define('DS', DIRECTORY_SEPARATOR);

require_once BASE_PATH . '/vendor/autoload.php';

$exitCode = 0;

try {
    $dbSistema = Database::getConnection('business');
    $dbSeguridad = Database::getConnection('security');

    $sqlHorarios = "SELECT e.cedula, p.nombre, p.apellido, ult.fecha_fin,
                           DATEDIFF(ult.fecha_fin, CURDATE()) AS dias_restantes
                    FROM (
                        SELECT cedula_empleado, MAX(fecha) AS fecha_fin
                        FROM planificador_turno
                        GROUP BY cedula_empleado
                    ) AS ult
                    INNER JOIN empleado e ON e.cedula = ult.cedula_empleado
                    INNER JOIN persona p ON p.cedula = e.cedula
                    WHERE e.estatus = 1
                      AND ult.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
                    ORDER BY ult.fecha_fin ASC, p.apellido ASC, p.nombre ASC";
    $horariosPorVencer = $dbSistema->query($sqlHorarios)->fetchAll();

    if ($horariosPorVencer === []) {
        horarioCronLog('No hay horarios próximos a vencer.');
        exit(0);
    }

    $sqlDestinatarios = "SELECT DISTINCT u.cedula
                         FROM usuario u
                         INNER JOIN permiso pm ON pm.id_rol = u.id_rol
                         INNER JOIN modulo m ON m.id_modulo = pm.id_modulo
                         WHERE u.estatus = 1
                           AND pm.estatus = 1
                           AND LOWER(m.nombre) = 'horario'
                           AND LOWER(pm.accion) = 'modificar'
                         ORDER BY u.cedula";
    $destinatarios = $dbSeguridad->query($sqlDestinatarios)->fetchAll();

    if ($destinatarios === []) {
        throw new RuntimeException('Hay horarios próximos a vencer, pero no se encontraron usuarios activos con permiso para modificarlos.');
    }

    $notificacionesProcesadas = 0;
    foreach ($destinatarios as $destinatario) {
        if (!NotificacionHelper::registrarAlertasVencimientoHorario($destinatario['cedula'], $horariosPorVencer)) {
            throw new RuntimeException(
                "No se pudieron registrar las alertas de horarios próximos a vencer para {$destinatario['cedula']}."
            );
        }
        $notificacionesProcesadas++;
    }

    horarioCronLog(
        'Revisión completada. Empleados próximos a vencer: ' . count($horariosPorVencer)
        . '; destinatarios procesados: ' . $notificacionesProcesadas . '.'
    );
} catch (Throwable $exception) {
    horarioCronLog('ERROR: ' . $exception->getMessage());
    $exitCode = 1;
}

exit($exitCode);

function horarioCronLog(string $mensaje): void
{
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;
    echo $linea;

    $directorio = BASE_PATH . '/logs';
    if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
        fwrite(STDERR, "No se pudo crear el directorio de logs.\n");
        return;
    }
    if (file_put_contents($directorio . '/cron_horarios.log', $linea, FILE_APPEND | LOCK_EX) === false) {
        fwrite(STDERR, "No se pudo escribir logs/cron_horarios.log.\n");
    }
}
