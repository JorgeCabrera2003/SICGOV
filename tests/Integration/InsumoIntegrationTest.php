<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Insumo;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Insumos e Inventario
 * Evalúa el ciclo completo del modelo Insumo, persistencia en MariaDB,
 * validación de stocks, unidades de medida y rendimiento.
 */
class InsumoIntegrationTest extends TestCase
{
    private static ?PDO $dbBusiness;
    private Insumo $insumoModel;
    private string $idInsumoTest = 'INSU0012026010112';
    private string $idCatTest = 'CATI0012026010112';
    private string $idUnidTest = 'UNID0012026010112';


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
        $this->insumoModel = new Insumo();

        // Obtener IDs válidos reales de categoria_insumo y unidad_medida
        $this->idCatTest = (string) self::$dbBusiness->query("SELECT id_categoria FROM categoria_insumo LIMIT 1")->fetchColumn();
        $this->idUnidTest = (string) self::$dbBusiness->query("SELECT id_unidad FROM unidad_medida LIMIT 1")->fetchColumn();

        $this->limpiarDatosPrueba();
    }

    private function poblarInsumoValido(): void
    {
        $this->insumoModel->setId($this->idInsumoTest);
        $this->insumoModel->setNombre('Lomo de Cerdo Test');
        $this->insumoModel->setIdCategoria($this->idCatTest);
        $this->insumoModel->setIdUnidadMedida($this->idUnidTest);
        $this->insumoModel->setPrecioUnitario(8.50);
        $this->insumoModel->setStockActual(25.0);
        $this->insumoModel->setStockMinimo(5.0);
        $this->insumoModel->setStockMaximo(100.0);
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbBusiness->exec("DELETE FROM movimiento_insumo WHERE id_insumo = '{$this->idInsumoTest}'");
        self::$dbBusiness->exec("DELETE FROM insumo WHERE id_insumo = '{$this->idInsumoTest}' OR nombre_insumo = 'Lomo de Cerdo Test'");
    }

    #[Test]
    public function testConsultarInsumosEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->insumoModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs, "La consulta de insumos tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testRegistrarInsumoTest(): void
    {
        // 1. Registrar Insumo
        $this->poblarInsumoValido();

        $response = $this->insumoModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($response['response']['resultado'], [200, 201]);
    }

    #[Test]
    public function testValidarInsumoTest(): void
    {
        // 1. Registrar Insumo (Precondición)
        $this->poblarInsumoValido();
        $this->insumoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Validar Insumo
        $response = $this->insumoModel->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(1, $response['bool']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testConsultarInsumoTest(): void
    {
        // 2. Consultar Insumo
        $response = $this->insumoModel->Transaccion(['peticion' => 'consultar']);

        $this->assertIsArray($response);
        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $response['response']);
    }

    #[Test]
    public function testModificarInsumoTest(): void
    {
        // 1. Registrar Insumo (Precondición)
        $this->insumoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Modificar Insumo
        $this->insumoModel->setPrecioUnitario(9.75);
        $this->insumoModel->setStockActual(30.0);
        $response = $this->insumoModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testEliminarInsumoTest(): void
    {
        // 1. Registrar Insumo (Precondición)
        $this->poblarInsumoValido();
        $response = $this->insumoModel->Transaccion(['peticion' => 'registrar']);

        $response = $this->insumoModel->Transaccion(['peticion' => 'eliminar']);
        $this->assertEquals(200, $response['response']['resultado']);
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
            self::$dbBusiness->exec("DELETE FROM movimiento_insumo WHERE id_insumo = 'INSU0012026010112'");
            self::$dbBusiness->exec("DELETE FROM insumo WHERE id_insumo = 'INSU0012026010112' OR nombre_insumo = 'Lomo de Cerdo Test'");
        }
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
