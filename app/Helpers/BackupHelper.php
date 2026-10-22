<?php

namespace App\Helpers;

class BackupHelper
{

    private const BACKUP_DIR = BASE_PATH . '/storage/backups';

    private const ALLOWED_DBS = ['good_vibes', 'goobv-sistema', 'goobv-usuarios'];

    public static function getAllowedDbs(): array
    {
        $dbs = self::ALLOWED_DBS;
        if (!empty($_ENV['DB_NAME_SYSTEM'])) {
            $dbs[] = $_ENV['DB_NAME_SYSTEM'];
        }
        if (!empty($_ENV['DB_NAME_USER'])) {
            $dbs[] = $_ENV['DB_NAME_USER'];
        }
        return array_values(array_unique(array_filter($dbs)));
    }

    public static function generarRespaldo(string $database, ?string $destDir = null): array
    {
        if ($database === 'todas') {
            $dbsToBackup = [
                $_ENV['DB_NAME_SYSTEM'] ?? 'goobv-sistema',
                $_ENV['DB_NAME_USER']   ?? 'goobv-usuarios',
            ];
            $dbsToBackup = array_unique($dbsToBackup);
            $generados = [];
            $ultimoArchivo = null;
            foreach ($dbsToBackup as $db) {
                $res = self::generarRespaldo($db, $destDir);
                if (!$res['success']) {
                    return $res;
                }
                $generados[] = $res['filename'];
                $ultimoArchivo = $res['file'];
            }

            return [
                'success'  => true,
                'file'     => $ultimoArchivo,
                'filename' => implode(', ', $generados),
                'message'  => "Respaldos generados exitosamente: " . implode(' y ', $generados)
            ];
        }

        if (!in_array($database, self::getAllowedDbs(), true)) {
            return self::error("Base de datos no permitida: '$database'.");
        }

        $mysqldumpBin = self::detectarMysqldump();
        if ($mysqldumpBin === null) {
            return self::error(
                "El ejecutable 'mysqldump' no fue encontrado en el servidor. " .
                "Verifique que MySQL esté instalado o agregue su ruta a la variable de entorno PATH."
            );
        }

        $destDir = rtrim($destDir ?? self::BACKUP_DIR, '/\\');

        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0750, true)) {
                return self::error("No se pudo crear el directorio de respaldos: '$destDir'.");
            }
        }

        if (!is_writable($destDir)) {
            return self::error("El directorio de respaldos no tiene permisos de escritura: '$destDir'.");
        }

        if (!isset($_ENV['DB_USER']) && class_exists(\Dotenv\Dotenv::class)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
            $dotenv->safeLoad();
        }

        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: '';
        $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

        if (empty($user)) {
            return self::error("Las credenciales de base de datos no están definidas en el entorno (.env).");
        }

        $timestamp = date('Y-m-d_His');
        $filename  = "backup_{$database}_{$timestamp}.sql";
        $filepath  = $destDir . DIRECTORY_SEPARATOR . $filename;

        $errFile = tempnam(sys_get_temp_dir(), 'backup_err_');
        $passArg = ($pass !== '') ? ' --password=' . escapeshellarg($pass) : '';
        $binEscaped = (str_starts_with($mysqldumpBin, 'docker') || str_starts_with($mysqldumpBin, 'podman'))
            ? $mysqldumpBin
            : '"' . $mysqldumpBin . '"';

        $command = sprintf(
            '%s --host=%s --user=%s%s --single-transaction --routines --events -- %s > %s 2> %s',
            $binEscaped,
            escapeshellarg($host),
            escapeshellarg($user),
            $passArg,
            escapeshellarg($database),
            escapeshellarg($filepath),
            escapeshellarg($errFile)
        );

        try {
            $output   = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                $errorMsg = file_exists($errFile) ? file_get_contents($errFile) : '';
                @unlink($errFile);
                if (file_exists($filepath)) {
                    @unlink($filepath);
                }
                return self::error("mysqldump falló (código $exitCode): $errorMsg");
            }
            @unlink($errFile);

            if (!file_exists($filepath) || filesize($filepath) === 0) {
                return self::error("El respaldo se ejecutó pero el archivo resultante está vacío o no existe.");
            }

            return [
                'success'  => true,
                'file'     => $filepath,
                'filename' => $filename,
                'message'  => "Respaldo de '$database' generado exitosamente."
            ];

        } catch (\Throwable $e) {
            Helper::ErrorLog("[BackupHelper] Excepción inesperada: " . $e->getMessage());
            return self::error("Error inesperado al generar el respaldo: " . $e->getMessage());
        }
    }

    public static function forzarDescarga(string $filepath, string $filename): void
    {
        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'El archivo de respaldo no fue encontrado.']);
            exit;
        }

        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Expires: 0');

        readfile($filepath);
        exit;
    }

    public static function listarRespaldos(): array
    {
        $dir = self::BACKUP_DIR;
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . DIRECTORY_SEPARATOR . 'backup_*.sql');
        if ($files === false) {
            return [];
        }

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        $result = [];
        foreach ($files as $file) {
            $result[] = [
                'filename'  => basename($file),
                'filepath'  => $file,
                'size'      => self::formatearBytes(filesize($file)),
                'size_raw'  => filesize($file),
                'fecha'     => date('d/m/Y h:i:s A', filemtime($file)),
                'timestamp' => filemtime($file),
            ];
        }

        return $result;
    }

    public static function subirRespaldo(array $file): array
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errorCodes = [
                UPLOAD_ERR_INI_SIZE   => "El archivo excede el tamaño máximo permitido por el servidor.",
                UPLOAD_ERR_FORM_SIZE  => "El archivo excede el límite del formulario.",
                UPLOAD_ERR_PARTIAL    => "El archivo solo fue subido parcialmente.",
                UPLOAD_ERR_NO_FILE    => "No se seleccionó ningún archivo.",
                UPLOAD_ERR_NO_TMP_DIR => "Falta la carpeta temporal en el servidor.",
                UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en el disco.",
            ];
            return self::error($errorCodes[$file['error'] ?? -1] ?? "Error al subir el archivo.");
        }

        $destDir = self::BACKUP_DIR;
        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0750, true)) {
                return self::error("No se pudo crear el directorio de respaldos.");
            }
        }

        $origName = basename($file['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if ($ext !== 'sql') {
            return self::error("Solo se permiten archivos con extensión .sql");
        }

        $cleanBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
        if (!str_starts_with($cleanBase, 'backup_')) {
            $cleanBase = 'backup_' . $cleanBase;
        }
        $timestamp = date('Y-m-d_His');
        $filename = "{$cleanBase}_{$timestamp}.sql";
        $targetPath = $destDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return self::error("No se pudo guardar el archivo en el almacén de respaldos.");
        }

        return [
            'success'  => true,
            'filename' => $filename,
            'message'  => "Archivo '$origName' subido exitosamente."
        ];
    }

    public static function eliminarRespaldo(string $filename): array
    {
        if (!preg_match('/^backup_[\w\-]+\.sql$/', $filename)) {
            return self::error("Nombre de archivo inválido.");
        }

        $filepath = self::BACKUP_DIR . DIRECTORY_SEPARATOR . $filename;

        $realDir  = realpath(self::BACKUP_DIR);
        $realFile = realpath(dirname($filepath)) . DIRECTORY_SEPARATOR . basename($filepath);

        if ($realDir === false || strpos($realFile, $realDir) !== 0) {
            return self::error("Ruta de archivo no autorizada.");
        }

        if (!file_exists($filepath)) {
            return self::error("El archivo no existe: '$filename'.");
        }

        if (@unlink($filepath)) {
            return ['success' => true, 'message' => "Respaldo '$filename' eliminado correctamente."];
        }

        return self::error("No se pudo eliminar el archivo. Verifique los permisos del servidor.");
    }

    public static function restaurarRespaldo(string $filename): array
    {
        $database = null;
        $allowed = self::getAllowedDbs();

        foreach ($allowed as $dbName) {
            if (str_starts_with($filename, "backup_{$dbName}_") || str_starts_with($filename, "backup_{$dbName}.")) {
                $database = $dbName;
                break;
            }
        }

        if ($database === null) {
            if (str_contains($filename, 'sistema')) {
                $database = $_ENV['DB_NAME_SYSTEM'] ?? 'goobv-sistema';
            } elseif (str_contains($filename, 'usuario')) {
                $database = $_ENV['DB_NAME_USER'] ?? 'goobv-usuarios';
            }
        }

        if ($database === null) {
            return self::error("Nombre de archivo inválido o base de datos no reconocida para restauración.");
        }

        $filepath = self::BACKUP_DIR . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filepath)) {
            return self::error("El archivo no existe: '$filename'.");
        }

        $mysqlBin = self::detectarMysql();
        if ($mysqlBin === null) {
            return self::error("El ejecutable 'mysql' no fue encontrado. Verifique la instalación de MySQL.");
        }

        if (!isset($_ENV['DB_USER']) && class_exists(\Dotenv\Dotenv::class)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
            $dotenv->safeLoad();
        }

        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: '';
        $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

        if (empty($user)) {
            return self::error("Credenciales de base de datos no definidas.");
        }

        $passArg = ($pass !== '') ? ' --password=' . escapeshellarg($pass) : '';
        $binEscaped = (str_starts_with($mysqlBin, 'docker') || str_starts_with($mysqlBin, 'podman'))
            ? $mysqlBin
            : '"' . $mysqlBin . '"';

        $command = sprintf(
            '%s --host=%s --user=%s%s %s < %s 2>&1',
            $binEscaped,
            escapeshellarg($host),
            escapeshellarg($user),
            $passArg,
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        try {
            $output   = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                $errorMsg = implode(' | ', $output);
                return self::error("Restauración falló (código $exitCode): $errorMsg");
            }

            return ['success' => true, 'message' => "La base de datos '$database' ha sido restaurada con éxito."];
        } catch (\Throwable $e) {
            Helper::ErrorLog("[BackupHelper] Excepción al restaurar: " . $e->getMessage());
            return self::error("Error inesperado al restaurar: " . $e->getMessage());
        }
    }

    private static function detectarMysqldump(): ?string
    {
        $candidatos = [
            'mysqldump',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\current\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/lampp/bin/mysqldump',
            '/Applications/MAMP/Library/bin/mysqldump',
            'podman exec codex_mysql_dev mysqldump',
            'docker exec codex_mysql_dev mysqldump',
        ];

        foreach ($candidatos as $bin) {
            if (file_exists($bin)) {
                return $bin;
            }
            $output   = [];
            $exitCode = 0;
            @exec($bin . ' --version 2>&1', $output, $exitCode);
            if ($exitCode === 0) {
                return $bin;
            }
        }

        return null;
    }

    private static function detectarMysql(): ?string
    {
        $candidatos = [
            'mysql',
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysql.exe',
            'C:\\laragon\\bin\\mysql\\current\\bin\\mysql.exe',
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            '/opt/lampp/bin/mysql',
            '/Applications/MAMP/Library/bin/mysql',
            'podman exec -i codex_mysql_dev mysql',
            'docker exec -i codex_mysql_dev mysql',
        ];

        foreach ($candidatos as $bin) {
            if (file_exists($bin)) {
                return $bin;
            }
            $output   = [];
            $exitCode = 0;
            @exec($bin . ' --version 2>&1', $output, $exitCode);
            if ($exitCode === 0) {
                return $bin;
            }
        }
        return null;
    }

    private static function formatearBytes(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) return number_format($bytes / 1_073_741_824, 2) . ' GB';
        if ($bytes >= 1_048_576)     return number_format($bytes / 1_048_576, 2)     . ' MB';
        if ($bytes >= 1_024)         return number_format($bytes / 1_024, 2)         . ' KB';
        return $bytes . ' B';
    }

    private static function error(string $message): array
    {
        Helper::ErrorLog("[BackupHelper] $message");
        return ['success' => false, 'file' => null, 'filename' => null, 'message' => $message];
    }
}
