<?php
namespace App\Core;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database
{
    private static $instances = [];
    private $pdo;

    public static function getRawConnection()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        return new \PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]);
    }

    public static function getConnection($type = 'business')
    {
        if (!isset(self::$instances[$type])) {
            try {
                $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
                $dotenv->safeLoad();

                $dbName = ($type === 'security') ? $_ENV['DB_NAME_USER'] : $_ENV['DB_NAME_SYSTEM'];

                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $port = $_ENV['DB_PORT'] ?? '3306';
                $user = $_ENV['DB_USER'] ?? 'root';
                $pass = $_ENV['DB_PASS'] ?? '';

                $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                self::$instances[$type] = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                die(" Error de Conexión ({$type}): " . $e->getMessage());
            }
        }
        return self::$instances[$type];
    }

    public static function getSystemDb()
    {
        return $_ENV['DB_NAME_SYSTEM'] ?? 'goobv-sistema';
    }

    public static function getSecurityDb()
    {
        return $_ENV['DB_NAME_USER'] ?? 'goobv-usuarios';
    }

    /**
     * Llama a la Conexión a la Base de Datos de no existir el objeto,
     * se instancia uno al ser llamado por primera vez
     *
     * @param string $nombreBD Indica a que Base de Datos se realizará la conexión
     * @param PDO $pdo Referencia del Objeto que tiene la conexión (para agruptar transacciones bajo un mismo objeto PDO)
     * 
     * @return PDO $this->pdo 
     */
    public function LlamarConexion($nombreBD = 'business', ?PDO &$pdo = NULL)
    {
        if ($pdo != NULL) {
            $this->pdo = $pdo;
        }
        if ($this->pdo == NULL) {
            $this->pdo = Database::getConnection($nombreBD);
        }
        return $this->pdo;
    }

    /**
     * Destruye una Conexión a la Base de Datos
     *
     * @param string $bool Booleano que indica realizar la destrucción o no (En caso de que se esté trabajando con Transacciones SQL)
     */
    public function DestruirConexion($bool = true)
    {
        if ($bool) {
            $this->pdo = NULL;
        }
    }

}