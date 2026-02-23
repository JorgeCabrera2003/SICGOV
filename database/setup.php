<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Database\Seeders\SecuritySeeder;
use App\Database\Seeders\BusinessSeeder;

// $nombre = readline("Por favor, introduce tu nombre: ");
// echo "Hola, $nombre!";

echo "\n INICIANDO INSTALACIÓN LIMPIA DEL SISTEMA GOOD VIBES...\n";
echo "-----------------------------------------------------------\n";

try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();

    echo "[0/4] Reseteando bases de datos en el servidor...\n";
    $raw = Database::getRawConnection();
    
    $dbUsers = $_ENV['DB_NAME_USER'] ?? 'goobv-usuarios';
    $dbSystem = $_ENV['DB_NAME_SYSTEM'] ?? 'goobv-sistema';

    $raw->exec("DROP DATABASE IF EXISTS `$dbUsers`;");
    $raw->exec("DROP DATABASE IF EXISTS `$dbSystem`;");
    $raw->exec("CREATE DATABASE `$dbUsers` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $raw->exec("CREATE DATABASE `$dbSystem` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    
    echo " Bases de datos creadas desde cero.\n";

    echo "[1/4] Cargando tablas y datos base (SQL)...\n";
    
    $dbSecurity = Database::getConnection('security');
    $dbBusiness = Database::getConnection('business');

    function ejecutarSQL($db, $archivo) {
        if (!file_exists($archivo)) {
            throw new Exception(" Archivo no encontrado: $archivo");
        }
        $sql = file_get_contents($archivo);
        $db->exec($sql);
    }

    $rutaMigracionUsuarios = __DIR__ . '/migrations/goobv-usuarios.sql';
    $rutaMigracionSistema = __DIR__ . '/migrations/goobv-sistema.sql';

    ejecutarSQL($dbSecurity, $rutaMigracionUsuarios);
    echo "       Estructura de Usuarios cargada.\n";

    ejecutarSQL($dbBusiness, $rutaMigracionSistema);
    echo "       Estructura del Sistema cargada.\n";

    $bool = readline("¿Deseas cargar datos en seguridad? (s/n): ");
    if (strtolower($bool) == 's') {
        echo "[2/4] Ejecutando SecuritySeeder...\n";
        $securitySeeder = new SecuritySeeder($dbSecurity);
        $securitySeeder->run();
    }
    $bool = readline("¿Confirma que desea cargar datos de negocio?. (s/n): ");
    if (strtolower($bool) == 's') {
        echo "[3/4] Generando datos de prueba (Faker)...\n";
        $businessSeeder = new BusinessSeeder($dbBusiness);
        $businessSeeder->run();
    }

    echo "-----------------------------------------------------------\n";
    echo " INSTALACIÓN COMPLETADA CON ÉXITO.\n";
    echo " Usuario: v-00000000 | Clave: 1234\n";

} catch (Exception $e) {
    echo "\n ERROR DURANTE LA INSTALACIÓN:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . " en " . $e->getFile() . "\n";
    exit(1);
}