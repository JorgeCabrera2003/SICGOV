<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Cliente;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Clientes
 * Evalúa el ciclo completo del modelo Cliente, persistencia dual (persona y cliente) en MariaDB,
 * validación, consultas y rendimiento.
 */
class ClienteIntegrationTest extends TestCase
{
    private static ?PDO $dbBusiness;
    private Cliente $clienteModel;
    private string $cedulaTest = 'V-77665544';

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
        $this->clienteModel = new Cliente();

        $this->limpiarDatosPrueba();
    }

    private function poblarClienteValido(): void
    {
        $this->clienteModel->setCedula($this->cedulaTest);
        $this->clienteModel->setNombre('Carlos');
        $this->clienteModel->setApellido('Perez');
        $this->clienteModel->setFechaNacimiento('1990-05-15');
        $this->clienteModel->setTelefono('0414-1234567');
        $this->clienteModel->setCorreo('carlosperez@test.com');
        $this->clienteModel->setDireccion('Av. Los Jabillos, Casa 12');
        $this->clienteModel->setSexo('M');
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbBusiness->exec("DELETE FROM detalle_pedido WHERE id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = '{$this->cedulaTest}')");
        self::$dbBusiness->exec("DELETE FROM pago WHERE id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = '{$this->cedulaTest}')");
        self::$dbBusiness->exec("DELETE FROM pedido WHERE cedula_cliente = '{$this->cedulaTest}'");
        self::$dbBusiness->exec("DELETE FROM cliente WHERE cedula = '{$this->cedulaTest}'");
        self::$dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaTest}'");
    }

    #[Test]
    public function testConsultarClientesEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->clienteModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs, "La consulta de clientes tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testRegistrarClienteTest(): void
    {
        // 1. Registrar Cliente
        $this->poblarClienteValido();

        $response = $this->clienteModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($response['response']['resultado'], [200, 201]);
    }

    #[Test]
    public function testValidarClienteTest(): void
    {
        // 1. Registrar Cliente (Precondición)
        $this->poblarClienteValido();
        $this->clienteModel->Transaccion(['peticion' => 'registrar']);

        // 2. Validar Cliente
        $response = $this->clienteModel->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(1, $response['bool']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testConsultarClienteTest(): void
    {
        // 2. Consultar Cliente
        $response = $this->clienteModel->Transaccion(['peticion' => 'consultar']);

        $this->assertIsArray($response);
        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $response['response']);
    }

    #[Test]
    public function testModificarClienteTest(): void
    {
        // 1. Registrar Cliente (Precondición)
        $this->poblarClienteValido();
        $this->clienteModel->Transaccion(['peticion' => 'registrar']);

        // 2. Modificar Cliente
        $this->clienteModel->setNombre('Carlos Alberto');
        $this->clienteModel->setTelefono('0424-9876543');
        $response = $this->clienteModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testEliminarClienteTest(): void
    {
        // 1. Registrar Cliente (Precondición)
        $this->poblarClienteValido();
        $response = $this->clienteModel->Transaccion(['peticion' => 'registrar']);

        $response = $this->clienteModel->Transaccion(['peticion' => 'eliminar']);
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
            self::$dbBusiness->exec("DELETE FROM detalle_pedido WHERE id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = 'V-77665544')");
            self::$dbBusiness->exec("DELETE FROM pago WHERE id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = 'V-77665544')");
            self::$dbBusiness->exec("DELETE FROM pedido WHERE cedula_cliente = 'V-77665544'");
            self::$dbBusiness->exec("DELETE FROM cliente WHERE cedula = 'V-77665544'");
            self::$dbBusiness->exec("DELETE FROM persona WHERE cedula = 'V-77665544'");
        }
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
