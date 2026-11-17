<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\CategoriaProducto;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Categorías del Menú
 * Evalúa el ciclo completo del modelo CategoriaProducto, persistencia en MariaDB,
 * validación, consultas transaccionales y rendimiento.
 */
class CategoriaProductoIntegrationTest extends TestCase
{
    private static ?PDO $dbBusiness;
    private CategoriaProducto $categoriaModel;
    private string $idCategoriaTest = 'CAT202611122000001111';
    private string $nombreCatTest = 'Postres Exquisitos';

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
        $this->categoriaModel = new CategoriaProducto();

        $this->limpiarDatosPrueba();
    }

    private function poblarCategoriaValida(): void
    {
        $this->categoriaModel->setIdCategoria($this->idCategoriaTest);
        $this->categoriaModel->setNombreCategoria($this->nombreCatTest);
        $this->categoriaModel->setEstatus(1);
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbBusiness->exec("DELETE FROM producto WHERE id_categoria = '{$this->idCategoriaTest}' OR id_categoria IN (SELECT id_categoria FROM categoria_producto WHERE nombre_categoria LIKE 'Postres Exquisitos%')");
        self::$dbBusiness->exec("DELETE FROM categoria_producto WHERE id_categoria = '{$this->idCategoriaTest}' OR nombre_categoria LIKE 'Postres Exquisitos%'");
    }

    #[Test]
    public function testConsultarCategoriasEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->categoriaModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertLessThan(400, $duracionMs, "La consulta de categorías tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testRegistrarCategoriaTest(): void
    {
        // 1. Registrar Categoría
        $this->poblarCategoriaValida();

        $response = $this->categoriaModel->Transaccion(['peticion' => 'guardar']);
        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['id']);
    }

    #[Test]
    public function testValidarCategoriaTest(): void
    {
        // 1. Registrar Categoría (Precondición)
        $this->poblarCategoriaValida();
        $responseReg = $this->categoriaModel->Transaccion(['peticion' => 'guardar']);
        $this->assertTrue($responseReg['success']);
        $idCat = $responseReg['id'];

        // 2. Validar Categoría (Búsqueda por ID)
        $this->categoriaModel->setIdCategoria($idCat);
        $response = $this->categoriaModel->Transaccion(['peticion' => 'buscar']);
        $this->assertIsArray($response);
        $this->assertEquals($idCat, $response['id_categoria']);
        $this->assertEquals($this->nombreCatTest, $response['nombre_categoria']);
    }

    #[Test]
    public function testConsultarCategoriaTest(): void
    {
        // 2. Consultar Categoría
        $response = $this->categoriaModel->Transaccion(['peticion' => 'consultar']);

        $this->assertIsArray($response);
    }

    #[Test]
    public function testModificarCategoriaTest(): void
    {
        // 1. Registrar Categoría (Precondición)
        $this->poblarCategoriaValida();
        $responseReg = $this->categoriaModel->Transaccion(['peticion' => 'guardar']);
        $this->assertTrue($responseReg['success']);
        $idCat = $responseReg['id'];

        // 2. Modificar Categoría
        $this->categoriaModel->setIdCategoria($idCat);
        $this->categoriaModel->setNombreCategoria('Postres Exquisitos Gourmet');
        $this->categoriaModel->setEstatus(1);
        $response = $this->categoriaModel->Transaccion(['peticion' => 'actualizar']);
        $this->assertTrue($response['success']);
    }

    #[Test]
    public function testEliminarCategoriaTest(): void
    {
        // 1. Registrar Categoría (Precondición)
        $this->poblarCategoriaValida();
        $responseReg = $this->categoriaModel->Transaccion(['peticion' => 'guardar']);
        $this->assertTrue($responseReg['success']);
        $idCat = $responseReg['id'];

        // 2. Eliminar Categoría
        $this->categoriaModel->setIdCategoria($idCat);
        $response = $this->categoriaModel->Transaccion(['peticion' => 'eliminar']);
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
            self::$dbBusiness->exec("DELETE FROM producto WHERE id_categoria = 'CAT202611122000001111' OR id_categoria IN (SELECT id_categoria FROM categoria_producto WHERE nombre_categoria LIKE 'Postres Exquisitos%')");
            self::$dbBusiness->exec("DELETE FROM categoria_producto WHERE id_categoria = 'CAT202611122000001111' OR nombre_categoria LIKE 'Postres Exquisitos%'");
        }
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
