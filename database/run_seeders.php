<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Database\Seeders\SecuritySeeder;
use App\Database\Seeders\BusinessSeeder;
use App\Database\Seeders\PermisosSeeder;

echo "=== Ejecutando Seeders de SICGOV ===\n";

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$dbSecurity = Database::getConnection('security');
$dbBusiness = Database::getConnection('business');

echo "[1/3] Ejecutando SecuritySeeder...\n";
$securitySeeder = new SecuritySeeder($dbSecurity);
$securitySeeder->run();

echo "[2/3] Ejecutando PermisosSeeder...\n";
$permisosSeeder = new PermisosSeeder($dbSecurity);
$permisosSeeder->run();

echo "[3/3] Ejecutando BusinessSeeder...\n";
$dbBusiness->beginTransaction();
try {
    $businessSeeder = new BusinessSeeder($dbBusiness);
    $businessSeeder->run();
    $dbBusiness->commit();
    echo "✔ Datos de negocio y Faker inyectados correctamente.\n";
} catch (\Throwable $e) {
    $dbBusiness->rollBack();
    echo "❌ Error en BusinessSeeder: " . $e->getMessage() . "\n";
}

echo "=== Seeders completados exitosamente ===\n";
