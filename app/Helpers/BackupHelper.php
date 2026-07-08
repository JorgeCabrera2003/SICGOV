<?php

namespace App\Helpers;

class BackupHelper
{

    private const BACKUP_DIR = BASE_PATH . '/storage/backups';

    private const ALLOWED_DBS = ['goobv-sistema', 'goobv-usuarios'];

    public static function generarRespaldo(string $database, ?string $destDir = null): array
    {

        if (!in_array($database, self::ALLOWED_DBS, true)) {
            return self::error("Base de datos no permitida: '$database'.");
        }

        $mysqldumpBin = self::detectarMysqldump();
        if ($mysqldumpBin === null) {
            return self::error(
                "El ejecutable 'mysqldump' no fue encontrado en el servidor. " .
                "Instale mysql-client o revise la variable PATH."
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
        $command = sprintf(
            '%s --host=%s --user=%s --password=%s --single-transaction --routines --events --set-gtid-purged=OFF -- %s > %s 2> %s',
            $mysqldumpBin,
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($database),
            escapeshellarg($filepath),
            escapeshellarg($errFile)
        );

        try {
            $output   = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                $errorMsg = file_get_contents($errFile);
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
        foreach (self::ALLOWED_DBS as $dbName) {
            if (strpos($filename, "backup_{$dbName}_") === 0) {
                $database = $dbName;
                break;
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
            return self::error("El ejecutable 'mysql' no fue encontrado. Instale mysql-client.");
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

        $command = sprintf(
            '%s --host=%s --user=%s --password=%s %s < %s 2>&1',
            $mysqlBin,
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
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
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/lampp/bin/mysqldump',               
            '/Applications/MAMP/Library/bin/mysqldump', 
            'podman exec codex_mysql_dev mysqldump',  
            'docker exec codex_mysql_dev mysqldump',  
        ];

        foreach ($candidatos as $bin) {
            $output   = [];
            $exitCode = 0;
            exec($bin . ' --version 2>&1', $output, $exitCode);
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
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            '/opt/lampp/bin/mysql',
            '/Applications/MAMP/Library/bin/mysql',
            'podman exec -i codex_mysql_dev mysql',
            'docker exec -i codex_mysql_dev mysql',
        ];

        foreach ($candidatos as $bin) {
            $output   = [];
            $exitCode = 0;
            exec($bin . ' --version 2>&1', $output, $exitCode);
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
