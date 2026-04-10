<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Database\Seeders\SecuritySeeder;
use App\Database\Seeders\BusinessSeeder;

class GoodVibesInstallerService
{
    private $dbSecurity;
    private $dbBusiness;
    private $rawDb;

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
        
        $this->success("      ✓ Bases de datos creadas desde cero.");
    }

    private function runMigrations()
    {
        $this->info("\n[2/4] Ejecutando Migraciones (SQL)...");
        
        $rutaMigracionUsuarios = __DIR__ . '/migrations/goobv-usuarios.sql';
        $rutaMigracionSistema = __DIR__ . '/migrations/goobv-sistema.sql';

        $this->executeSQLFile($this->dbSecurity, $rutaMigracionUsuarios, "Usuarios");
        $this->executeSQLFile($this->dbBusiness, $rutaMigracionSistema, "Sistema");
    }

    private function runSecuritySeeder()
    {
        $this->info("\n[3/4] Ejecutando Security Seeder...");
        $securitySeeder = new SecuritySeeder($this->dbSecurity);
        $securitySeeder->run();
        $this->success("      ✓ Datos de seguridad inyectados.");
    }

    private function runBusinessSeeder()
    {
        $this->info("\n[4/4] Ejecutando Business Seeder (Faker)...");
        $this->dbBusiness->beginTransaction(); // Optimización: Transacciones para inserciones masivas
        try {
            $businessSeeder = new BusinessSeeder($this->dbBusiness);
            $businessSeeder->run();
            $this->dbBusiness->commit();
            $this->success("      ✓ Datos de negocio inyectados.");
        } catch (Exception $e) {
            $this->dbBusiness->rollBack();
            throw $e;
        }
    }

    private function executeSQLFile($db, $archivo, $nombreModulo)
    {
        if (!file_exists($archivo)) {
            throw new Exception("Archivo SQL no encontrado: $archivo");
        }
        $sql = file_get_contents($archivo);
        $db->exec($sql);
        $this->success("      ✓ Estructura de $nombreModulo cargada.");
    }

    // ==========================================
    // UTILIDADES DE CONSOLA (UI/UX)
    // ==========================================

    private function confirm(string $question): bool
    {
        // Color amarillo para las preguntas
        echo "\033[33m? \033[0m" . $question . " \033[90m(y/n) [y]\033[0m: ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);

        $line = strtolower($line);
        if ($line === 'n' || $line === 'no') {
            return false;
        }
        return true; // Por defecto es sí
    }

    private function info(string $text)
    {
        echo "\033[36mℹ " . $text . "\033[0m\n"; // Cyan
    }

    private function success(string $text)
    {
        echo "\033[32m" . $text . "\033[0m\n"; // Verde
    }

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
        echo " ✨ INSTALACIÓN COMPLETADA CON ÉXITO ✨\n";
        echo "===========================================================\n";
        echo "\033[0m";
        echo "\033[36m Usuario Admin:\033[0m V00000000\n";
        echo "\033[36m Clave:\033[0m 1234\n\n";
    }
}

// Ejecutar el servicio
$installer = new GoodVibesInstallerService();
$installer->run();