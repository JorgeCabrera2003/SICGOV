<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Define realpath for app
define('BASE_PATH', realpath(__DIR__ . '/..'));

try {
    $db = \App\Core\Database::getConnection('business');
    
    echo "--- 10 Personas ---\n";
    $stmt = $db->query('SELECT cedula, nombre, apellido FROM persona LIMIT 10');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- Empleados ---\n";
    $stmt = $db->query('SELECT e.cedula, p.nombre, p.apellido FROM empleado e INNER JOIN persona p ON e.cedula = p.cedula');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
