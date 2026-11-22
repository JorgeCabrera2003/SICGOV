<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Empleado;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Empleados
 * Evalúa el ciclo completo del modelo Empleado, persistencia dual (persona y empleado) en MariaDB,
 * validación, consultas y rendimiento.
 */
class EmpleadoIntegrationTest extends TestCase
{
    private static ?PDO $dbBusiness;
    private Empleado $empleadoModel;
    private string $cedulaTest = 'V-66554433';
    private string $idCargoValido = 'CARGO00120260519200547232';

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
        $this->empleadoModel = new Empleado();

        // Obtener ID válido real de cargo
        $cargoId = self::$dbBusiness->query("SELECT id_cargo FROM cargo WHERE estatus = 1 LIMIT 1")->fetchColumn();
        if ($cargoId) {
            $this->idCargoValido = (string) $cargoId;
        }

        $this->limpiarDatosPrueba();
    }

    private function poblarEmpleadoValido(): void
    {
        $this->empleadoModel->setCedula($this->cedulaTest);
        $this->empleadoModel->setNombre('Ricardo');
        $this->empleadoModel->setApellido('Gomez');
        $this->empleadoModel->setFechaNacimiento('1993-08-20');
        $this->empleadoModel->setTelefono('0412-5554433');
        $this->empleadoModel->setCorreo('ricardo.gomez@empresa.com');
        $this->empleadoModel->setDireccion('Urb. Santa Rosa, Vereda 4');
        $this->empleadoModel->setSexo('M');
        $this->empleadoModel->setIdCargo($this->idCargoValido);
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbBusiness->exec("DELETE FROM planificador_turno WHERE cedula_empleado = '{$this->cedulaTest}'");
        self::$dbBusiness->exec("DELETE FROM empleado WHERE cedula = '{$this->cedulaTest}'");
        self::$dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaTest}'");
    }

    #[Test]
    public function testConsultarEmpleadosEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->empleadoModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs, "La consulta de empleados tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testRegistrarEmpleadoTest(): void
    {
        // 1. Registrar Empleado
        $this->poblarEmpleadoValido();

        $response = $this->empleadoModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($response['response']['resultado'], [200, 201]);
    }

    #[Test]
    public function testValidarEmpleadoTest(): void
    {
        // 1. Registrar Empleado (Precondición)
        $this->poblarEmpleadoValido();
        $this->empleadoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Validar Empleado (Verificar Cédula Registrada)
        $response = $this->empleadoModel->Transaccion(['peticion' => 'verificar_cedula']);
        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertTrue($response['response']['existe']);
    }

    #[Test]
    public function testConsultarEmpleadoTest(): void
    {
        // 2. Consultar Empleado
        $response = $this->empleadoModel->Transaccion(['peticion' => 'consultar']);

        $this->assertIsArray($response);
        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $response['response']);
    }

    #[Test]
    public function testModificarEmpleadoTest(): void
    {
        // 1. Registrar Empleado (Precondición)
        $this->poblarEmpleadoValido();
        $this->empleadoModel->Transaccion(['peticion' => 'registrar']);

        // 2. Modificar Empleado
        $this->empleadoModel->setNombre('Ricardo Jose');
        $this->empleadoModel->setTelefono('0412-9998877');
        $response = $this->empleadoModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testEliminarEmpleadoTest(): void
    {
        // 1. Registrar Empleado (Precondición)
        $this->poblarEmpleadoValido();
        $response = $this->empleadoModel->Transaccion(['peticion' => 'registrar']);

        $response = $this->empleadoModel->Transaccion(['peticion' => 'eliminar']);
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
            self::$dbBusiness->exec("DELETE FROM planificador_turno WHERE cedula_empleado = 'V-66554433'");
            self::$dbBusiness->exec("DELETE FROM empleado WHERE cedula = 'V-66554433'");
            self::$dbBusiness->exec("DELETE FROM persona WHERE cedula = 'V-66554433'");
        }
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
