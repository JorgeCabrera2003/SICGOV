<?php
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable('.');
$dotenv->safeLoad();

try {
    $db = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME_SYSTEM']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
    
    // Verificar si el usuario actual es cliente
    $cedula = "V00000000"; // El ID del admin en el screenshot
    $stm = $db->prepare("SELECT COUNT(*) FROM cliente WHERE cedula = ?");
    $stm->execute([$cedula]);
    if ($stm->fetchColumn() == 0) {
        echo "El usuario $cedula no existe en la tabla cliente. Intentando registrarlo...\n";
        // Registrar como cliente (asumiendo que los campos son mínimos)
        $db->prepare("INSERT INTO cliente (cedula) VALUES (?)")->execute([$cedula]);
        echo "Usuario registrado como cliente con éxito.";
    } else {
        echo "El usuario ya es cliente.";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
