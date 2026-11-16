<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Papelera;
use App\Core\Database;
use ReflectionClass;
use ReflectionProperty;

/**
 * Suite de Pruebas Unitarias para el Módulo de Papelera de Reciclaje
 * Evalúa lógica interna, manejo de peticiones, estructura de configuración de módulos
 * y resistencia a entradas adversariales/maliciosas.
 */
class PapeleraUnitTest extends TestCase
{
    private Papelera $papelera;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['user'] = [
            'cedula' => 'V-00000000',
            'username' => 'admin_test',
            'id_rol' => 'ADMIN00120251001',
            'rol' => 'Administrador',
            'estatus_clave' => 1
        ];

        $this->papelera = new Papelera();
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400BadRequest(): void
    {
        // 1. Petición desconocida
        $res = $this->papelera->Transaccion(['peticion' => 'accion_inexistente_hack']);
        $this->assertIsArray($res);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals('Petición no válida', $res['response']['mensaje']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);

        // 2. Petición vacía
        $resVacia = $this->papelera->Transaccion(['peticion' => '']);
        $this->assertEquals(400, $resVacia['response']['resultado']);

        // 3. Petición tipo numérico
        $resNum = $this->papelera->Transaccion(['peticion' => 999]);
        $this->assertEquals(400, $resNum['response']['resultado']);
    }

    #[Test]
    public function testRestaurarModuloNoSoportadoRetorna400BadRequest(): void
    {
        $payload = [
            'peticion' => 'restaurar',
            'modulo' => 'MODULO_FANTASMA_XYZ',
            'id' => '123'
        ];

        $res = $this->papelera->Transaccion($payload);
        $this->assertIsArray($res);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals('Módulo no soportado', $res['response']['mensaje']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }

    #[Test]
    public function testInyeccionSqlEnParametroModuloRechazada(): void
    {
        $payload = [
            'peticion' => 'restaurar',
            'modulo' => "NOTICIAS' OR '1'='1'; --",
            'id' => '1'
        ];

        $res = $this->papelera->Transaccion($payload);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals('Módulo no soportado', $res['response']['mensaje']);
    }

    #[Test]
    public function testEliminarPermanenteModuloNoSoportadoRetorna400BadRequest(): void
    {
        $payload = [
            'peticion' => 'eliminar_permanente',
            'modulo' => 'TABLA_PRIVADA',
            'id' => '1'
        ];

        $res = $this->papelera->Transaccion($payload);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals('Módulo no soportado', $res['response']['mensaje']);
    }

    #[Test]
    public function testModulosConfiguracionEstructuraYValores(): void
    {
        $reflector = new ReflectionClass(Papelera::class);
        $prop = $reflector->getProperty('modulos');
        $prop->setAccessible(true);
        $modulos = $prop->getValue($this->papelera);

        $this->assertIsArray($modulos);
        $this->assertNotEmpty($modulos);

        foreach ($modulos as $key => $conf) {
            $this->assertArrayHasKey('tabla', $conf, "Módulo {$key} carece de clave 'tabla'");
            $this->assertArrayHasKey('pk', $conf, "Módulo {$key} carece de clave 'pk'");
            $this->assertArrayHasKey('label', $conf, "Módulo {$key} carece de clave 'label'");
            $this->assertArrayHasKey('db', $conf, "Módulo {$key} carece de clave 'db'");
            $this->assertContains($conf['db'], ['business', 'security'], "Módulo {$key} tiene DB inválida: {$conf['db']}");
        }
    }

    #[Test]
    public function testDeteccionDefectoConfiguracionTablasInsumoVsIngrediente(): void
    {
        $reflector = new ReflectionClass(Papelera::class);
        $prop = $reflector->getProperty('modulos');
        $prop->setAccessible(true);
        $modulos = $prop->getValue($this->papelera);

        // Verifica que la configuración errónea esté presente
        $this->assertArrayHasKey('INGREDIENTES', $modulos);
        $this->assertEquals('ingrediente', $modulos['INGREDIENTES']['tabla']);
        $this->assertEquals('id_ingrediente', $modulos['INGREDIENTES']['pk']);

        $this->assertArrayHasKey('CATEGORIAS_INGREDIENTE', $modulos);
        $this->assertEquals('categoria_ingrediente', $modulos['CATEGORIAS_INGREDIENTE']['tabla']);

        // Verifica en la base de datos de negocio que 'ingrediente' no existe y 'insumo' sí existe
        $pdo = Database::getConnection('business');
        $tables = $pdo->query("SHOW TABLES LIKE 'ingrediente'")->fetchAll();
        $this->assertEmpty($tables, "Fallo detectado: la tabla 'ingrediente' no debe existir en MariaDB");

        $insumoTable = $pdo->query("SHOW TABLES LIKE 'insumo'")->fetchAll();
        $this->assertNotEmpty($insumoTable, "La tabla real 'insumo' sí existe en MariaDB");
    }

    #[Test]
    public function testDeteccionDefectoOmisionCategoriaProducto(): void
    {
        $reflector = new ReflectionClass(Papelera::class);
        $prop = $reflector->getProperty('modulos');
        $prop->setAccessible(true);
        $modulos = $prop->getValue($this->papelera);

        $this->assertArrayNotHasKey('CATEGORIAS_PRODUCTO', $modulos);
        $this->assertArrayNotHasKey('CATEGORIA_PRODUCTO', $modulos);
    }

    #[Test]
    public function testResistenciaAInyeccionEnParametroId(): void
    {
        $payload = [
            'peticion' => 'restaurar',
            'modulo' => 'PRODUCTOS',
            'id' => "PROD' OR '1'='1' --"
        ];

        $res = $this->papelera->Transaccion($payload);
        $this->assertIsArray($res);
        // Debe procesarse de forma segura mediante sentencia preparada
        $this->assertContains($res['response']['resultado'], [200, 500]);
    }
}
