<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Seeders\SecuritySeeder;
use App\Database\Seeders\BusinessSeeder;
use App\Database\Seeders\PermisosSeeder;
use Dotenv\Dotenv;

class TestDatabaseInstaller
{
    private PDO $rawDb;
    private PDO $dbBusiness;
    private PDO $dbSecurity;
    private string $dbSystem;
    private string $dbUsers;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->safeLoad();

        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        $this->dbSystem = 'goobv-sistema-test';
        $this->dbUsers = 'goobv-usuarios-test';

        $this->rawDb = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public function run(): void
    {
        echo "=== Preparando Bases de Datos de Pruebas ===\n";

        // 1. Resetear bases de datos de test
        echo "[1/4] Creando bases de datos '{$this->dbSystem}' y '{$this->dbUsers}'...\n";
        $this->rawDb->exec("DROP DATABASE IF EXISTS `{$this->dbUsers}`;");
        $this->rawDb->exec("DROP DATABASE IF EXISTS `{$this->dbSystem}`;");
        $this->rawDb->exec("CREATE DATABASE `{$this->dbUsers}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
        $this->rawDb->exec("CREATE DATABASE `{$this->dbSystem}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

        // 2. Conectar a las bases de datos de prueba
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->dbBusiness = new PDO("mysql:host=$host;port=$port;dbname={$this->dbSystem};charset=utf8mb4", $user, $pass, $options);
        $this->dbSecurity = new PDO("mysql:host=$host;port=$port;dbname={$this->dbUsers};charset=utf8mb4", $user, $pass, $options);

        // 3. Ejecutar Migraciones SQL
        echo "[2/4] Ejecutando migraciones SQL...\n";
        $this->executeSQLFile($this->dbBusiness, __DIR__ . '/migrations/goobv-sistema.sql', "Sistema");
        $this->executeSQLFile($this->dbSecurity, __DIR__ . '/migrations/goobv-usuarios.sql', "Usuarios");

        // 4. Ejecutar Seeders
        echo "[3/4] Ejecutando Seeder de Negocio...\n";
        $this->dbBusiness->beginTransaction();
        try {
            $businessSeeder = new BusinessSeeder($this->dbBusiness);
            $businessSeeder->run();
            $this->dbBusiness->commit();
        } catch (\Throwable $e) {
            $this->dbBusiness->rollBack();
            throw $e;
        }

        echo "[4/4] Ejecutando Seeders de Seguridad y Permisos...\n";
        $securitySeeder = new SecuritySeeder($this->dbSecurity);
        $securitySeeder->run();

        $permisosSeeder = new PermisosSeeder($this->dbSecurity);
        $permisosSeeder->run();

        echo "=== Bases de datos de prueba listas con exito ===\n";
    }

    private function executeSQLFile(PDO $db, string $archivo, string $nombreModulo): void
    {
        if (!file_exists($archivo)) {
            throw new Exception("Archivo SQL no encontrado: $archivo");
        }

        $sql = file_get_contents($archivo);
        $sql = str_replace(['{{DB_SYSTEM}}', '{{DB_SECURITY}}'], [$this->dbSystem, $this->dbUsers], $sql);

        $statements = $this->splitSQLStatements($sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }

            $db->exec($statement);
        }
    }

    private function splitSQLStatements(string $sql): array
    {
        $delimiter = ';';
        $statements = [];
        $current = '';

        $lines = preg_split('/\r\n|\n|\r/', $sql);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                $current .= $line . "\n";
                continue;
            }

            if (stripos($trimmed, 'DELIMITER ') === 0) {
                $delimiter = trim(substr($trimmed, strlen('DELIMITER ')));
                continue;
            }

            $current .= $line . "\n";

            if ($delimiter !== '' && str_ends_with(rtrim($current), $delimiter)) {
                $statements[] = substr(trim($current), 0, -strlen($delimiter));
                $current = '';
            }
        }

        if (trim($current) !== '') {
            $statements[] = trim($current);
        }

        return $statements;
    }
}

if (php_sapi_name() === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $installer = new TestDatabaseInstaller();
    $installer->run();
}
