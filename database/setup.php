<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Database\Seeders\SecuritySeeder;
use App\Database\Seeders\BusinessSeeder;

/**
 * GoodVibes Core-Deployer 
 *
 * Este servicio orquesta la creación de las bases de datos, la ejecución
 * de migraciones y la carga de datos iniciales para los módulos de seguridad
 * y negocio.
 */
class GoodVibesInstallerService
{
    private $dbSecurity;
    private $dbBusiness;
    private $rawDb;

    /**
     * Ejecuta el flujo completo de instalación.
     *
     * @return void
     */
    public function run()
    {
        $this->clearScreen();
        $this->printHeader();

        try {
            // 1. Cargar Variables de Entorno
            $this->info("Cargando variables de entorno...");
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->safeLoad();
            $this->success("Variables cargadas correctamente.\n");

            // 2. Opciones Dinámicas
            $resetDB   = $this->confirm("¿Deseas RESETEAR (Eliminar y Crear) las bases de datos?");
            $runMig    = $this->confirm("¿Deseas ejecutar las MIGRACIONES (Estructura SQL)?");
            $runSecSeed = $this->confirm("¿Deseas ejecutar el SEEDER DE SEGURIDAD (Roles y Admin)?");
            $runBizSeed = $this->confirm("¿Deseas ejecutar el SEEDER DE NEGOCIO (Faker: Mesas, Productos, etc.)?");

            echo "\n-----------------------------------------------------------\n";

            // 3. Ejecución Condicional
            if ($resetDB) {
                $this->resetDatabases();
            }

            // Inicializar conexiones después de asegurar que las DBs existen
            $this->dbSecurity = Database::getConnection('security');
            $this->dbBusiness = Database::getConnection('business');

            if ($runMig) {
                $this->runMigrations();
            }

            if ($runSecSeed) {
                $this->runSecuritySeeder();
            }

            if ($runBizSeed) {
                $this->runBusinessSeeder();
            }

            $this->printFooter();

        } catch (Exception $e) {
            $this->error("\n[X] ERROR CRÍTICO DURANTE LA INSTALACIÓN:");
            echo "Mensaje: " . $e->getMessage() . "\n";
            echo "Archivo: " . $e->getFile() . " (Línea " . $e->getLine() . ")\n";
            exit(1);
        }
    }

    // ==========================================
    // LÓGICA CORE DEL SERVICIO
    // ==========================================

    /**
     * Elimina y crea las bases de datos configuradas en el servidor.
     *
     * @return void
     */
    private function resetDatabases()
    {
        $this->info("[1/4] Reseteando bases de datos en el servidor...");
        $this->rawDb = Database::getRawConnection();
        
        $dbUsers = $_ENV['DB_NAME_USER'] ?? 'goobv-usuarios';
        $dbSystem = $_ENV['DB_NAME_SYSTEM'] ?? 'goobv-sistema';

        $this->rawDb->exec("DROP DATABASE IF EXISTS `$dbUsers`;");
        $this->rawDb->exec("DROP DATABASE IF EXISTS `$dbSystem`;");
        $this->rawDb->exec("CREATE DATABASE `$dbUsers` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
        $this->rawDb->exec("CREATE DATABASE `$dbSystem` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
        
        $this->success("      Bases de datos creadas desde cero.");
    }

    /**
     * Ejecuta los scripts de migración SQL para ambas bases de datos.
     *
     * @return void
     */
    private function runMigrations()
    {
        $this->info("\n[2/4] Ejecutando Migraciones (SQL)...");
        
        $rutaMigracionSistema = __DIR__ . '/migrations/goobv-sistema.sql';
        $rutaMigracionUsuarios = __DIR__ . '/migrations/goobv-usuarios.sql';

        $this->executeSQLFile($this->dbBusiness, $rutaMigracionSistema, "Sistema");
        $this->executeSQLFile($this->dbSecurity, $rutaMigracionUsuarios, "Usuarios");
    }

    /**
     * Ejecuta el seeder de seguridad para roles y usuario administrador.
     *
     * @return void
     */
    private function runSecuritySeeder()
    {
        $this->info("\n[3/4] Ejecutando Security Seeder...");
        $securitySeeder = new SecuritySeeder($this->dbSecurity);
        $securitySeeder->run();
        $this->success("      Datos de seguridad inyectados.");
    }

    /**
     * Ejecuta el seeder de negocio para datos iniciales de la base de datos.
     *
     * @return void
     */
    private function runBusinessSeeder()
    {
        $this->info("\n[4/4] Ejecutando Business Seeder (Faker)...");
        $this->dbBusiness->beginTransaction(); // Optimización: Transacciones para inserciones masivas
        try {
            $businessSeeder = new BusinessSeeder($this->dbBusiness);
            $businessSeeder->run();
            $this->dbBusiness->commit();
            $this->success("      Datos de negocio inyectados.");
        } catch (Exception $e) {
            $this->dbBusiness->rollBack();
            throw $e;
        }
    }

    /**
     * Ejecuta las sentencias SQL contenidas en un archivo de migración.
     *
     * @param \PDO $db Conexión de base de datos.
     * @param string $archivo Ruta del archivo SQL.
     * @param string $nombreModulo Nombre del módulo para mensajes.
     *
     * @return void
     * @throws Exception Si no existe el archivo o falla la ejecución de una sentencia.
     */
    private function executeSQLFile($db, $archivo, $nombreModulo)
    {
        if (!file_exists($archivo)) {
            throw new Exception("Archivo SQL no encontrado: $archivo");
        }

        $sql = file_get_contents($archivo);
        
        // --- DINAMISMO: Reemplazar Placeholders por nombres reales de .env ---
        $dbUsers = $_ENV['DB_NAME_USER'] ?? 'goobv-usuarios';
        $dbSystem = $_ENV['DB_NAME_SYSTEM'] ?? 'goobv-sistema';
        $sql = str_replace(['{{DB_SYSTEM}}', '{{DB_SECURITY}}'], [$dbSystem, $dbUsers], $sql);

        $statements = $this->splitSQLStatements($sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }

            $result = $db->exec($statement);
            if ($result === false) {
                $errorInfo = $db->errorInfo();
                throw new Exception("Error ejecutando migración ($nombreModulo): " . ($errorInfo[2] ?? 'Unknown error'));
            }
        }

        $this->success("      Estructura de $nombreModulo cargada.");
    }

    /**
     * Divide un archivo SQL en sentencias individuales respetando delimitadores.
     *
     * @param string $sql Contenido completo del archivo SQL.
     * @return array Lista de sentencias SQL preparadas para ejecución.
     */
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

    // ==========================================
    // UTILIDADES DE CONSOLA (UI/UX)
    // ==========================================

    /**
     * Pregunta al usuario con un mensaje y devuelve un booleano.
     *
     * @param string $question Texto de la pregunta.
     * @return bool Respuesta del usuario (por defecto true).
     */
    private function confirm(string $question): bool
    {
        echo "\033[33m" . $question . " \033[90m(y/n) [y]\033[0m: ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);

        $line = strtolower($line);
        if ($line === 'n' || $line === 'no') {
            return false;
        }

        return true;
    }

    /**
     * Muestra un mensaje informativo en la consola.
     *
     * @param string $text Mensaje a imprimir.
     * @return void
     */
    private function info(string $text)
    {
        echo "\033[36m" . $text . "\033[0m\n"; // Cyan
    }

    /**
     * Muestra un mensaje de éxito en la consola.
     *
     * @param string $text Mensaje a imprimir.
     * @return void
     */
    private function success(string $text)
    {
        echo "\033[32m" . $text . "\033[0m\n"; // Verde
    }

    /**
     * Muestra un mensaje de error en la consola.
     *
     * @param string $text Mensaje a imprimir.
     * @return void
     */
    private function error(string $text)
    {
        echo "\033[31m" . $text . "\033[0m\n"; // Rojo
    }

    private function clearScreen()
    {
        echo "\033[2J\033[;H"; // Limpia la terminal
    }

    private function printHeader()
    {
        echo "\033[1;32m";
        echo "===========================================================\n";
        echo "      GoodVibes Core-Deployer \n";
        echo "===========================================================\n";
        echo "\033[0m\n";
    }

    private function printFooter()
    {
        echo "\033[1;32m";
        echo "\n===========================================================\n";
        echo " INSTALACIÓN COMPLETADA CON ÉXITO\n";
        echo "===========================================================\n";
        echo "\033[0m";
        echo "\033[36m Usuario Admin:\033[0m V00000000\n";
        echo "\033[36m Clave:\033[0m 1234\n\n";
    }
}

// Ejecutar el servicio
$installer = new GoodVibesInstallerService();
$installer->run();