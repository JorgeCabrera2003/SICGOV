<?php
require 'vendor/autoload.php';
use App\Core\Database;
try {
    $db = Database::getConnection('business');
    $metodos = $db->query('SELECT * FROM metodo_pago')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($metodos, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
