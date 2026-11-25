<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Cargo;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Categoría de Cargo
 * Evalúa el ciclo completo del modelo Cargo, persistencia en MariaDB,
 * validación de stocks, unidades de medida y rendimiento.
 */
class CargoIntegrationTest extends TestCase
{
    private static ?PDO $dbBusiness;
    private Cargo $cargoModel;
    private string $idTest = 'CATI0012026010112';


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
        $this->cargoModel = new Cargo();

        $this->limpiarDatosPrueba();
    }

    private function poblarCargoValido(): void
    {
        $this->cargoModel->setId($this->idTest);
        $this->cargoModel->setNombre('Mesero');
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbBusiness->exec("DELETE FROM cargo WHERE id_cargo = '{$this->idTest}'");
    }

    #[Test]
    public function testConsultarRendimientoCargosEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->cargoModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs, "La consulta de insumos tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testRegistrarCargoTest(): void
    {
        // 1. Registrar Cargo
        $this->poblarCargoValido();

        $response = $this->cargoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Verificar que existe el registro en la BD
        $validar = $this->cargoModel->Transaccion(['peticion' => 'validar']);

        $this->assertContains($response['response']['resultado'], [200, 201]);
        $this->assertEquals(1, $validar['bool']);
    }

    #[Test]
    public function testValidarCargoTest(): void
    {
        // 1. Registrar Cargo (Precondición)
        $this->poblarCargoValido();
        $this->cargoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Validar Cargo
        $response = $this->cargoModel->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(1, $response['bool']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testConsultarCargoTest(): void
    {
        // 2. Consultar Cargo
        $response = $this->cargoModel->Transaccion(['peticion' => 'consultar']);

        $this->assertIsArray($response);
        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $response['response']);
    }

    #[Test]
    public function testModificarCargoTest(): void
    {
        // 1. Registrar Cargo (Precondición)
        $this->poblarCargoValido();
        $this->cargoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Modificar Cargo
        $this->cargoModel->setNombre("Limpiador");
        $response = $this->cargoModel->Transaccion(['peticion' => 'modificar']);

        $modificacion = $this->cargoModel->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(200, $response['response']['resultado']);
        $this->assertEquals("Limpiador", $modificacion['response']['registro']['nombre_cargo']);
    }

    #[Test]
    public function testEliminarCargoTest(): void
    {
        // 1. Eliminar Cargo (Precondición)
        $this->poblarCargoValido();
        $this->cargoModel->Transaccion(['peticion' => 'registrar']);

        $response = $this->cargoModel->Transaccion(['peticion' => 'eliminar']);
        $validacion = $this->cargoModel->Transaccion(['peticion' => 'validar']);


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
            self::$dbBusiness->exec("DELETE FROM cargo WHERE id_cargo = 'CATI0012026010112' ");
        }
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
