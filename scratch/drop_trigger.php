<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getConnection('security');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("DROP TRIGGER IF EXISTS trg_imagen_principal_unica");
    echo "SUCCESS: Trigger dropped or didn't exist.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
