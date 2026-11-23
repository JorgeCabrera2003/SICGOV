<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Proveedor;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Proveedors e Inventario
 * Evalúa el ciclo completo del modelo Proveedor, persistencia en MariaDB,
 * validación de stocks, unidades de medida y rendimiento.
 */
class ProveedorIntegrationTest extends TestCase
{
    private static ?PDO $dbBusiness;
    private Proveedor $proveedorModel;
    private string $idProveedorTest = 'J-39851746';


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
        $this->proveedorModel = new Proveedor();

        $this->limpiarDatosPrueba();
    }

    private function poblarProveedorValido(): void
    {
        $this->proveedorModel->setDocumentoLegal($this->idProveedorTest);
        $this->proveedorModel->setNombre('Distribuidora El Tigrito');
        $this->proveedorModel->setDireccion("Av. 3 Calles 3 y 4 de la Pedro León");
        $this->proveedorModel->setTelefono("0424-1314514");
        $this->proveedorModel->setCorreo("distrotigrito@gmail.com");
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbBusiness->exec("DELETE FROM proveedor WHERE documento_legal = '{$this->idProveedorTest}'");
    }

    #[Test]
    public function testRendimientoConsultarProveedoresEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->proveedorModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs, "La consulta de insumos tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testRegistrarProveedorTest(): void
    {
        // 1. Registrar Proveedor
        $this->poblarProveedorValido();

        $response = $this->proveedorModel->Transaccion(['peticion' => 'registrar']);

        // 2. Verificar que existe el registro en la BD
        $validar = $this->proveedorModel->Transaccion(['peticion' => 'validar']);

        $this->assertContains($response['response']['resultado'], [200, 201]);
        $this->assertEquals(1, $validar['bool']);
    }

    #[Test]
    public function testValidarProveedorTest(): void
    {
        // 1. Registrar Proveedor (Precondición)
        $this->poblarProveedorValido();
        $this->proveedorModel->Transaccion(['peticion' => 'registrar']);

        // 2. Validar Proveedor
        $response = $this->proveedorModel->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(1, $response['bool']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testConsultarProveedorTest(): void
    {
        // 2. Consultar Proveedor
        $response = $this->proveedorModel->Transaccion(['peticion' => 'consultar']);

        $this->assertIsArray($response);
        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $response['response']);
    }

    #[Test]
    public function testModificarProveedorTest(): void
    {
        // 1. Registrar Proveedor (Precondición)
        $this->proveedorModel->Transaccion(['peticion' => 'registrar']);

        // 2. Modificar Proveedor
        $this->proveedorModel->setTelefono("0424-1314531");
        $this->proveedorModel->setCorreo("distrotigrito2026@gmail.com");
        $response = $this->proveedorModel->Transaccion(['peticion' => 'modificar']);
        
        //3 Comprobar Modificación
        $modificacion = $this->proveedorModel->Transaccion(['peticion' => 'validar']);
        
        $this->assertEquals(200, $response['response']['resultado']);
        $this->assertEquals("0424-1314531", $modificacion['response']['registro']['telefono']);
        $this->assertEquals("distrotigrito2026@gmail.com", $modificacion['response']['registro']['correo']);
    }

    #[Test]
    public function testEliminarProveedorTest(): void
    {
        // 1. Eliminar Proveedor (Precondición)
        $this->poblarProveedorValido();
        $this->proveedorModel->Transaccion(['peticion' => 'registrar']);

        $response = $this->proveedorModel->Transaccion(['peticion' => 'eliminar']);
        
        $validacion = $this->proveedorModel->Transaccion(['peticion' => 'validar']);


        $this->assertEquals(200, $response['response']['resultado']);
        $this->assertEquals(0, $validacion['response']['registro']['estatus']);
    }

        public function testEliminarNoExisteProveedorTest(): void
    {
        // 1. Eliminar Proveedor

        $response = $this->proveedorModel->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(404, $response['response']['resultado']);
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Limpieza final global (por si acaso)
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
