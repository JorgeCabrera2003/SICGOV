<?php
namespace App\Core;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database {
    private static $instances = [];

    public static function getRawConnection() {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();

    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';

    return new \PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    ]);
}

    public static function getConnection($type = 'business') {
        if (!isset(self::$instances[$type])) {
            try {
                // Cargamos variables de entorno (.env)
                $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
                $dotenv->safeLoad();

                $dbName = ($type === 'security') ? $_ENV['DB_NAME_USER'] : $_ENV['DB_NAME_SYSTEM'];
                
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $user = $_ENV['DB_USER'] ?? 'root';
                $pass = $_ENV['DB_PASS'] ?? '';

                $dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$instances[$type] = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                die(" Error de Conexión ({$type}): " . $e->getMessage());
            }
        }
        return self::$instances[$type];
    }
}