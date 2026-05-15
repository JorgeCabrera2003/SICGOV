<?php
require_once 'app/Config/Config.php';
require_once 'app/Core/Database.php';

try {
    $db = \App\Core\Database::getConnection('business');
    $stmt = $db->query('SELECT id_categoria FROM categoria_producto LIMIT 5');
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
} catch (Exception $e) {
    echo $e->getMessage();
}
