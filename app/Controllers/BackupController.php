<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\BackupHelper;

$type    = $_REQUEST['type']    ?? 'backups';
$peticion = $_POST['peticion']  ?? ($_GET['action'] ?? '');

if ($type === 'backups') {

    Helper::verificarSesion();

    if ($peticion === 'generar-respaldo') {

        $database = trim($_POST['database'] ?? '');
        if (empty($database)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'icon'    => 'warning',
                'mensaje' => 'Debe seleccionar una base de datos.'
            ]);
            exit;
        }

        $result = BackupHelper::generarRespaldo($database);

        if (!$result['success']) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'icon'    => 'error',
                'mensaje' => $result['message']
            ]);
            exit;
        }

        Helper::Bitacora(
            'RESPALDO_MANUAL',
            'Backup',
            "Respaldo manual generado: {$result['filename']} — BD: {$database}"
        );

        header('Content-Type: application/json');
        echo json_encode([
            'success'  => true,
            'icon'     => 'success',
            'mensaje'  => "Respaldo generado exitosamente. La descarga iniciará en breve.",
            'filename' => $result['filename'],
            'download_url' => BASE_URL . '?page=Backup&type=backups&action=descargar-respaldo&file=' . urlencode($result['filename'])
        ]);
        exit;
    }

    if (($peticion === 'descargar-respaldo') || (($_GET['action'] ?? '') === 'descargar-respaldo')) {
        $filename = basename($_GET['file'] ?? '');

        if (empty($filename) || !preg_match('/^backup_[\w\-]+\.sql$/', $filename)) {
            http_response_code(400);
            echo "Archivo inválido.";
            exit;
        }

        $filepath = BASE_PATH . '/storage/backups/' . $filename;
        BackupHelper::forzarDescarga($filepath, $filename);

    }

    if ($peticion === 'listar-respaldos') {
        header('Content-Type: application/json');
        $lista = BackupHelper::listarRespaldos();
        echo json_encode(['success' => true, 'backups' => $lista]);
        exit;
    }

    if ($peticion === 'eliminar-respaldo') {
        $filename = basename($_POST['filename'] ?? '');

        if (empty($filename)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'icon' => 'warning', 'mensaje' => 'Nombre de archivo requerido.']);
            exit;
        }

        $result = BackupHelper::eliminarRespaldo($filename);

        if ($result['success']) {
            Helper::Bitacora('RESPALDO_ELIMINADO', 'Backup', "Respaldo eliminado: {$filename}");
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result['success'],
            'icon'    => $result['success'] ? 'success' : 'error',
            'mensaje' => $result['message']
        ]);
        exit;
    }

    if ($peticion === 'restaurar-respaldo') {
        $filename = basename($_POST['filename'] ?? '');

        if (empty($filename)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'icon' => 'warning', 'mensaje' => 'Nombre de archivo requerido.']);
            exit;
        }

        $result = BackupHelper::restaurarRespaldo($filename);

        if ($result['success']) {
            Helper::Bitacora('RESPALDO_RESTAURADO', 'Backup', "Base de datos restaurada desde: {$filename}");
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result['success'],
            'icon'    => $result['success'] ? 'success' : 'error',
            'mensaje' => $result['message']
        ]);
        exit;
    }

    if ($peticion === 'get-config-backup') {
        $configFile = BASE_PATH . '/config/backup.php';
        $config = file_exists($configFile) ? (require $configFile) : [];
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'config' => $config]);
        exit;
    }

    if ($peticion === 'guardar-config-backup') {

        $frecuencia = $_POST['frecuencia'] ?? '';
        $hora       = (int) ($_POST['hora']       ?? 3);
        $minuto     = (int) ($_POST['minuto']      ?? 0);
        $diaSemana  = (int) ($_POST['dia_semana']  ?? 0);
        $diaMes     = (int) ($_POST['dia_mes']     ?? 1);

        $frecuenciasValidas = ['diario', 'semanal', 'mensual'];
        if (!in_array($frecuencia, $frecuenciasValidas, true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'icon' => 'warning', 'mensaje' => 'Frecuencia inválida.']);
            exit;
        }
        if ($hora < 0 || $hora > 23 || $minuto < 0 || $minuto > 59) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'icon' => 'warning', 'mensaje' => 'Hora o minuto fuera de rango.']);
            exit;
        }
        if ($diaSemana < 0 || $diaSemana > 6) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'icon' => 'warning', 'mensaje' => 'Día de semana inválido.']);
            exit;
        }
        if ($diaMes < 1 || $diaMes > 31) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'icon' => 'warning', 'mensaje' => 'Día del mes inválido.']);
            exit;
        }

        $configFile = BASE_PATH . '/config/backup.php';

        $contenido = "<?php\n";
        $contenido .= "/**\n * config/backup.php — Generado automáticamente por SICGOV.\n * Última actualización: " . date('Y-m-d H:i:s') . "\n */\n";
        $contenido .= "return [\n";
        $contenido .= "    'frecuencia'  => " . var_export($frecuencia, true) . ",\n";
        $contenido .= "    'hora'        => " . $hora . ",\n";
        $contenido .= "    'minuto'      => " . $minuto . ",\n";
        $contenido .= "    'dia_semana'  => " . $diaSemana . ",\n";
        $contenido .= "    'dia_mes'     => " . $diaMes . ",\n";
        $contenido .= "    'actualizado' => " . var_export(date('Y-m-d H:i:s'), true) . ",\n";
        $contenido .= "];\n";

        $tmp = $configFile . '.tmp.' . getmypid();
        if (file_put_contents($tmp, $contenido, LOCK_EX) === false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'icon' => 'error', 'mensaje' => 'No se pudo escribir el archivo de configuración. Verifique permisos.']);
            exit;
        }
        rename($tmp, $configFile);

        $cronExpr = match($frecuencia) {
            'diario'   => "$minuto $hora * * *",
            'semanal'  => "$minuto $hora * * $diaSemana",
            'mensual'  => "$minuto $hora $diaMes * *",
            default    => "$minuto $hora * * *"
        };

        try {
            $scriptPath = realpath(BASE_PATH . '/cron_backup.php');
            if ($scriptPath) {
                $cronLine = "$cronExpr php $scriptPath";
                $tmp = tempnam(sys_get_temp_dir(), 'cron');
                exec("crontab -l > $tmp 2>/dev/null");

                $lines = file_exists($tmp) ? file($tmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

                $newLines = array_filter($lines, fn($line) => !str_contains($line, $scriptPath));
                $newLines[] = $cronLine;

                file_put_contents($tmp, implode("\n", $newLines) . "\n");
                exec("crontab $tmp");
                @unlink($tmp);
            }
        } catch (\Throwable $e) {
            Helper::ErrorLog("[Crontab Sync Error] " . $e->getMessage());

        }

        Helper::Bitacora(
            'CONFIG_BACKUP',
            'Backup',
            "Programación actualizada: $frecuencia · $cronExpr (Sincronizado con OS)"
        );

        header('Content-Type: application/json');
        echo json_encode([
            'success'    => true,
            'icon'       => 'success',
            'mensaje'    => 'Configuración guardada correctamente.',
            'cron_expr'  => $cronExpr,
            'frecuencia' => $frecuencia,
        ]);
        exit;
    }

    Helper::cargarVista(
        'backup/index',
        'Centro de Respaldos — SICGOV',
        [
            'extra_js' => [BASE_URL . '/assets/js/Controllers/BackupController.js']
        ]
    );
}
