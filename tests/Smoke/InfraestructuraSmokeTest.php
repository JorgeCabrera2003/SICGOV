<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use PDO;

/**
 * Pruebas de Humo: Salud de la Infraestructura (Health Checks)
 *
 * Reglas de oro:
 * 1. Ejecución ultra rápida (< 500ms).
 * 2. Comprobación directa de los componentes vitales del entorno.
 * 3. Fallo inmediato si la infraestructura no está operativa.
 */
class InfraestructuraSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Aseguramos que la configuración apunte al entorno de pruebas
        Database::resetConnections();
    }

    /**
     * Verifica que las variables de entorno críticas estén presentes.
     */
    public function test_variables_de_entorno_criticas_cargadas(): void
    {
        $variablesObligatorias = [
            'DB_HOST',
            'DB_NAME_SYSTEM',
            'DB_NAME_USER',
            'DB_USER',
        ];

        foreach ($variablesObligatorias as $variable) {
            $this->assertNotEmpty(
                $_ENV[$variable] ?? null,
                "La variable de entorno crítica '{$variable}' no está definida o está vacía."
            );
        }

        // Verificamos que esté apuntando a las bases de datos de test
        $this->assertStringContainsString('test', $_ENV['DB_NAME_SYSTEM'], 'La DB de sistema debe ser de prueba.');
        $this->assertStringContainsString('test', $_ENV['DB_NAME_USER'], 'La DB de seguridad debe ser de prueba.');
    }

    /**
     * Verifica que las carpetas vitales del sistema existan y tengan permisos de escritura.
     */
    public function test_directorios_esenciales_existen_y_tienen_permisos(): void
    {
        $directorios = [
            'logs' => BASE_PATH . '/logs',
            'config' => BASE_PATH . '/config',
            'public' => BASE_PATH . '/public',
        ];

        foreach ($directorios as $nombre => $ruta) {
            $this->assertDirectoryExists($ruta, "El directorio esencial '{$nombre}' ({$ruta}) no existe.");
        }

        // El directorio de logs debe ser escribible para bitácoras y errores
        $this->assertDirectoryIsWritable(
            $directorios['logs'],
            "El directorio de logs no tiene permisos de escritura."
        );
    }

    /**
     * Health check: Conexión y respuesta inmediata de la base de datos de negocio (goobv-sistema).
     */
    public function test_conexion_base_datos_sistema_activa(): void
    {
        $db = Database::getConnection('business');

        $this->assertInstanceOf(PDO::class, $db, 'La conexión a negocio no devolvió una instancia PDO.');

        $resultado = $db->query("SELECT 1 AS alive")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, $resultado['alive'] ?? 0, 'La base de datos de negocio no respondió a SELECT 1.');
    }

    /**
     * Health check: Conexión y respuesta inmediata de la base de datos de seguridad (goobv-usuarios).
     */
    public function test_conexion_base_datos_seguridad_activa(): void
    {
        $db = Database::getConnection('security');

        $this->assertInstanceOf(PDO::class, $db, 'La conexión a seguridad no devolvió una instancia PDO.');

        $resultado = $db->query("SELECT 1 AS alive")->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, $resultado['alive'] ?? 0, 'La base de datos de seguridad no respondió a SELECT 1.');
    }

    /**
     * Verifica la existencia de las tablas núcleo en ambas bases de datos.
     */
    public function test_tablas_criticas_existen_en_ambas_bases_de_datos(): void
    {
        $dbSystem = Database::getConnection('business');
        $dbSecurity = Database::getConnection('security');

        $tablasSistemaEsperadas = ['persona', 'producto', 'mesa', 'categoria_producto'];
        $tablasSecurityEsperadas = ['usuario', 'rol', 'modulo', 'permiso', 'bitacora'];

        // Obtener tablas de sistema
        $tablasSistema = $dbSystem->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tablasSistemaEsperadas as $tabla) {
            $this->assertContains(
                $tabla,
                $tablasSistema,
                "Falta la tabla crítica '{$tabla}' en la base de datos del sistema."
            );
        }

        // Obtener tablas de seguridad
        $tablasSecurity = $dbSecurity->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tablasSecurityEsperadas as $tabla) {
            $this->assertContains(
                $tabla,
                $tablasSecurity,
                "Falta la tabla crítica '{$tabla}' en la base de datos de seguridad."
            );
        }
    }
}

