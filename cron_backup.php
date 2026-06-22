<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("Acceso denegado. Este script solo puede ejecutarse desde la línea de comandos.");
}

define('CRON_BASE_PATH', __DIR__);

define('BACKUP_FRECUENCIA', 'semanal');

define('BACKUP_MAX_RETENER', 8);

define('BACKUP_STORAGE_DIR', CRON_BASE_PATH . '/storage/backups');

define('BACKUP_LOG_FILE', CRON_BASE_PATH . '/logs/cron_backup.log');

$envFile = CRON_BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }
} else {
    cronLog("ERROR CRÍTICO: Archivo .env no encontrado en: $envFile");
    exit(1);
}

$autoload = CRON_BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

if (!defined('BASE_PATH')) define('BASE_PATH', CRON_BASE_PATH);
if (!defined('DS'))        define('DS', DIRECTORY_SEPARATOR);

$helperFile = CRON_BASE_PATH . '/app/Helpers/Helper.php';
if (file_exists($helperFile)) {
    require_once $helperFile;
}

$backupHelperFile = CRON_BASE_PATH . '/app/Helpers/BackupHelper.php';
if (!file_exists($backupHelperFile)) {
    cronLog("ERROR CRÍTICO: BackupHelper.php no encontrado en: $backupHelperFile");
    exit(1);
}
require_once $backupHelperFile;

$databasesParaRespaldar = [
    $_ENV['DB_NAME_SYSTEM'] ?? 'goobv-sistema',
    $_ENV['DB_NAME_USER']   ?? 'goobv-usuarios',
];

cronLog("SICGOV — Respaldo Automático Iniciado");
cronLog("Frecuencia configurada: " . BACKUP_FRECUENCIA);
cronLog("Fecha/Hora: " . date('Y-m-d H:i:s'));
$exitCode   = 0;
$resumenTotal = [];

foreach ($databasesParaRespaldar as $db) {
    cronLog("Iniciando respaldo de: '$db'");

    $result = \App\Helpers\BackupHelper::generarRespaldo($db, BACKUP_STORAGE_DIR);

    if ($result['success']) {
        $tamaño = file_exists($result['file']) ? formatearBytes(filesize($result['file'])) : 'N/A';
        cronLog("  Respaldo exitoso: {$result['filename']} ({$tamaño})");
        $resumenTotal[] = ['db' => $db, 'status' => 'OK', 'file' => $result['filename']];

        if (BACKUP_MAX_RETENER > 0) {
            rotarRespaldos($db, BACKUP_STORAGE_DIR, BACKUP_MAX_RETENER);
        }
    } else {
        cronLog("  Error en '$db': {$result['message']}");
        $resumenTotal[] = ['db' => $db, 'status' => 'ERROR', 'message' => $result['message']];
        $exitCode = 1;
    }
}

cronLog("RESUMEN:");
foreach ($resumenTotal as $r) {
    $estado = $r['status'] === 'OK'
        ? "{$r['db']} → {$r['file']}"
        : "{$r['db']} → {$r['message']}";
    cronLog("  $estado");
}
cronLog("Script finalizado con código: $exitCode");
cronLog("════════════════════════════════════════\n");

exit($exitCode);

function cronLog(string $mensaje): void
{
    $linea = "[" . date('Y-m-d H:i:s') . "] $mensaje\n";
    echo $linea;

    $logDir = dirname(BACKUP_LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0750, true);
    }
    file_put_contents(BACKUP_LOG_FILE, $linea, FILE_APPEND | LOCK_EX);
}

function rotarRespaldos(string $database, string $dir, int $maxRetener): void
{
    $patron = $dir . DIRECTORY_SEPARATOR . 'backup_' . $database . '_*.sql';
    $files  = glob($patron);

    if ($files === false || count($files) <= $maxRetener) {
        return;
    }

    usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

    $aEliminar = array_slice($files, $maxRetener);
    foreach ($aEliminar as $viejo) {
        if (@unlink($viejo)) {
            cronLog("  Respaldo antiguo eliminado: " . basename($viejo));
        } else {
            cronLog("  No se pudo eliminar: " . basename($viejo));
        }
    }
}

function formatearBytes(int $bytes): string
{
    if ($bytes >= 1_073_741_824) return number_format($bytes / 1_073_741_824, 2) . ' GB';
    if ($bytes >= 1_048_576)     return number_format($bytes / 1_048_576, 2)     . ' MB';
    if ($bytes >= 1_024)         return number_format($bytes / 1_024, 2)         . ' KB';
    return $bytes . ' B';
}
