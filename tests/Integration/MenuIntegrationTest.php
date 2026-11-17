<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Menu;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Menú y Catálogo de Productos
 * Evalúa el ciclo completo del modelo Menu (Producto), persistencia en MariaDB,
 * relación con categorías e insumos, consultas transaccionales y rendimiento.
 */
class MenuIntegrationTest extends TestCase
{
    private static ?PDO $dbBusiness;
    private Menu $menuModel;
    private string $idProductoTest = 'PROD202611122000001111';
    private string $idCatTest = 'CATPROD00320260519200547232';
    private string $idInsumoTest = 'INSU0012026010112';
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
        $this->menuModel = new Menu();

        // Obtener IDs válidos reales de categoria_producto, insumo y unidad_medida
        $catId = self::$dbBusiness->query("SELECT id_categoria FROM categoria_producto WHERE estatus = 1 LIMIT 1")->fetchColumn();
        if ($catId) {
            $this->idCatTest = (string) $catId;
        }

        $insId = self::$dbBusiness->query("SELECT id_insumo FROM insumo WHERE estatus = 1 LIMIT 1")->fetchColumn();
        if ($insId) {
            $this->idInsumoTest = (string) $insId;
        }

        $unidId = self::$dbBusiness->query("SELECT id_unidad FROM unidad_medida LIMIT 1")->fetchColumn();
        if ($unidId) {
            $this->idUnidTest = (string) $unidId;
        }

        $this->limpiarDatosPrueba();
    }

    private function poblarMenuValido(): void
    {
        $this->menuModel->setIdProducto($this->idProductoTest);
        $this->menuModel->setNombreProducto('Producto Test Integ Cocktail');
        $this->menuModel->setDescripcion('Cóctel refrescante de prueba');
        $this->menuModel->setPrecio(8.50);
        $this->menuModel->setIdCategoria($this->idCatTest);
        $this->menuModel->setTipoProducto('BARRA');
        $this->menuModel->setImagen('default.png');
        $this->menuModel->setInsumosPrincipales(json_encode([
            ['id' => $this->idInsumoTest, 'cantidad' => 1, 'unidad' => $this->idUnidTest]
        ]));
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbBusiness->exec("DELETE FROM preparacion WHERE id_producto = '{$this->idProductoTest}' OR id_producto IN (SELECT id_producto FROM producto WHERE nombre_producto LIKE 'Producto Test Integ%')");
        self::$dbBusiness->exec("DELETE FROM producto WHERE id_producto = '{$this->idProductoTest}' OR nombre_producto LIKE 'Producto Test Integ%'");
    }

    #[Test]
    public function testConsultarMenusEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->menuModel->Transaccion(['peticion' => 'listar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertLessThan(400, $duracionMs, "La consulta de menú tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testRegistrarMenuTest(): void
    {
        // 1. Registrar Menú
        $this->poblarMenuValido();

        $response = $this->menuModel->Transaccion(['peticion' => 'registrar']);
        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['id']);
    }

    #[Test]
    public function testValidarMenuTest(): void
    {
        // 1. Registrar Menú (Precondición)
        $this->poblarMenuValido();
        $responseReg = $this->menuModel->Transaccion(['peticion' => 'registrar']);
        $this->assertTrue($responseReg['success']);
        $idProd = $responseReg['id'];

        // 2. Validar Menú (Búsqueda por ID)
        $this->menuModel->setIdProducto($idProd);
        $response = $this->menuModel->Transaccion(['peticion' => 'buscar']);
        $this->assertIsArray($response);
        $this->assertEquals($idProd, $response['id_producto']);
        $this->assertEquals('Producto Test Integ Cocktail', $response['nombre_producto']);
    }

    #[Test]
    public function testConsultarMenuTest(): void
    {
        // 2. Consultar Menú
        $response = $this->menuModel->Transaccion(['peticion' => 'listar']);

        $this->assertIsArray($response);
    }

    #[Test]
    public function testModificarMenuTest(): void
    {
        // 1. Registrar Menú (Precondición)
        $this->poblarMenuValido();
        $responseReg = $this->menuModel->Transaccion(['peticion' => 'registrar']);
        $this->assertTrue($responseReg['success']);
        $idProd = $responseReg['id'];

        // 2. Modificar Menú
        $this->menuModel->setIdProducto($idProd);
        $this->menuModel->setNombreProducto('Producto Test Integ Modificado');
        $this->menuModel->setPrecio(11.50);
        $response = $this->menuModel->Transaccion(['peticion' => 'modificar']);
        $this->assertTrue($response['success']);
    }

    #[Test]
    public function testEliminarMenuTest(): void
    {
        // 1. Registrar Menú (Precondición)
        $this->poblarMenuValido();
        $responseReg = $this->menuModel->Transaccion(['peticion' => 'registrar']);
        $this->assertTrue($responseReg['success']);
        $idProd = $responseReg['id'];

        // 2. Eliminar Menú
        $this->menuModel->setIdProducto($idProd);
        $response = $this->menuModel->Transaccion(['peticion' => 'eliminar']);
        $this->assertTrue($response['success']);
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
            self::$dbBusiness->exec("DELETE FROM preparacion WHERE id_producto = 'PROD202611122000001111' OR id_producto IN (SELECT id_producto FROM producto WHERE nombre_producto LIKE 'Producto Test Integ%')");
            self::$dbBusiness->exec("DELETE FROM producto WHERE id_producto = 'PROD202611122000001111' OR nombre_producto LIKE 'Producto Test Integ%'");
        }
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
