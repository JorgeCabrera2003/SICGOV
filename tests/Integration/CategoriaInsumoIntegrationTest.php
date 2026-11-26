<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\CategoriaInsumo;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Categoría de Insumo
 * Evalúa el ciclo completo del modelo Insumo, persistencia en MariaDB,
 * validación de stocks, unidades de medida y rendimiento.
 */
class CategoriaInsumoIntegrationTest extends TestCase
{
    private static ?PDO $dbBusiness;
    private CategoriaInsumo $categoriaInsumoModel;
    private string $idCatTest = 'CATI0012026010112';


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$dbBusiness = Database::getConnection('business');
    }

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

        self::$dbBusiness = Database::getConnection('business');
        $this->categoriaInsumoModel = new CategoriaInsumo();

        $this->limpiarDatosPrueba();
    }

    private function poblarInsumoValido(): void
    {
        $this->categoriaInsumoModel->setId($this->idCatTest);
        $this->categoriaInsumoModel->setNombre('Panes');
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbBusiness->exec("DELETE FROM categoria_insumo WHERE id_categoria = '{$this->idCatTest}'");
    }

    #[Test]
    public function testConsultarRendimientoInsumosEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->categoriaInsumoModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs, "La consulta de insumos tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testRegistrarCategoriaInsumoTest(): void
    {
        // 1. Registrar Insumo
        $this->poblarInsumoValido();

        $response = $this->categoriaInsumoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Verificar que existe el registro en la BD
        $validar = $this->categoriaInsumoModel->Transaccion(['peticion' => 'validar']);

        $this->assertContains($response['response']['resultado'], [200, 201]);
        $this->assertEquals(1, $validar['bool']);
    }

    #[Test]
    public function testValidarCategoriaInsumoTest(): void
    {
        // 1. Registrar Insumo (Precondición)
        $this->poblarInsumoValido();
        $this->categoriaInsumoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Validar Insumo
        $response = $this->categoriaInsumoModel->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(1, $response['bool']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testConsultarCategoriaInsumoTest(): void
    {
        // 2. Consultar Insumo
        $response = $this->categoriaInsumoModel->Transaccion(['peticion' => 'consultar']);

        $this->assertIsArray($response);
        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $response['response']);
    }

    #[Test]
    public function testModificarCategoriaInsumoTest(): void
    {
        // 1. Registrar Insumo (Precondición)
        $this->poblarInsumoValido();
        $this->categoriaInsumoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Modificar Insumo
        $this->categoriaInsumoModel->setNombre("Embutidos");
        $response = $this->categoriaInsumoModel->Transaccion(['peticion' => 'modificar']);

        $modificacion = $this->categoriaInsumoModel->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(200, $response['response']['resultado']);
        $this->assertEquals("Embutidos", $modificacion['response']['registro']['nombre']);
    }

    #[Test]
    public function testEliminarCategoriaInsumoTest(): void
    {
        // 1. Eliminar Insumo (Precondición)
        $this->poblarInsumoValido();
        $this->categoriaInsumoModel->Transaccion(['peticion' => 'registrar']);

        $response = $this->categoriaInsumoModel->Transaccion(['peticion' => 'eliminar']);
        $validacion = $this->categoriaInsumoModel->Transaccion(['peticion' => 'validar']);


        $this->assertEquals(200, $response['response']['resultado']);
        $this->assertEquals(0, $validacion['response']['registro']['estatus']);
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Limpieza final global (por si acaso)
        if (self::$dbBusiness !== NULL) {
            self::$dbBusiness->exec("DELETE FROM categoria_insumo WHERE id_categoria = 'CATI0012026010112' ");
        }
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
