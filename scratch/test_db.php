<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();

    echo "=== PROBANDO CONEXIONES A BD ===\n";

    echo "Intentando conectar a base de datos de Seguridad...\n";
    $securityDb = Database::getConnection('security');
    echo "¡Conexión de Seguridad exitosa!\n";

    echo "Intentando conectar a base de datos de Negocio (business)...\n";
    $businessDb = Database::getConnection('business');
    echo "¡Conexión de Negocio exitosa!\n";

    echo "\n=== INFORMACIÓN DE USUARIOS ===\n";
    $stmt = $securityDb->query("SELECT cedula, username, id_rol, estatus FROM usuario LIMIT 5");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($usuarios)) {
        print_r($usuarios);
    } else {
        echo "No hay usuarios registrados.\n";
    }

    echo "\n=== REGISTROS DE BITÁCORA ===\n";
    try {
        $stmtB = $securityDb->query("SELECT id_bitacora, modulo, accion, fecha FROM bitacora ORDER BY fecha DESC LIMIT 5");
        $bitacora = $stmtB->fetchAll(PDO::FETCH_ASSOC);
        print_r($bitacora);
    } catch (Exception $e) {
        echo "Error leyendo bitacora: " . $e->getMessage() . "\n";
    }

    echo "\n=== IMÁGENES GUARDADAS ===\n";
    try {
        $stmtI = $securityDb->query("SELECT id_imagen, entidad_tipo, entidad_id, direccion, es_principal FROM imagen LIMIT 5");
        $imagenes = $stmtI->fetchAll(PDO::FETCH_ASSOC);
        print_r($imagenes);
    } catch (Exception $e) {
        echo "Error leyendo imagenes: " . $e->getMessage() . "\n";
    }

    echo "\n=== INFORMACIÓN DE PERSONAS ===\n";
    try {
        $stmtP = $businessDb->query("SELECT cedula, nombre, apellido, correo, telefono FROM persona LIMIT 5");
        $personas = $stmtP->fetchAll(PDO::FETCH_ASSOC);
        print_r($personas);
    } catch (Exception $e) {
        echo "Error leyendo personas: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
