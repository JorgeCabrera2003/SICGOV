<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Security\Rol;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Rol 
 * Evalúa el ciclo completo del modelo Insumo, persistencia en MariaDB,
 * validación de stocks, unidades de medida y rendimiento.
 */
class RolIntegrationTest extends TestCase
{
    private static ?PDO $dbBusiness;
    private Rol $rolModel;
    private string $idRolTest = 'ROL00012026010112';


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

        self::$dbBusiness = Database::getConnection('security');
        $this->rolModel = new Rol();

        $this->limpiarDatosPrueba();
    }

    private function poblarRolValido(): void
    {
        $this->rolModel->setId($this->idRolTest);
        $this->rolModel->setNombre('Rol Prueba');
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbBusiness->exec("DELETE FROM rol WHERE id_rol = '{$this->idRolTest}'");
    }
/*
    #[Test]
    public function testConsultarRendimientoInsumosEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->rolModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs, "La consulta de insumos tardó {$duracionMs}ms (límite: 400ms)");
    }
     */
    #[Test]
    public function testRegistrarRolTest(): void
    {
        // 1. Registrar Insumo
        $this->poblarRolValido();

        $response = $this->rolModel->Transaccion(['peticion' => 'registrar']);

        // 2. Verificar que existe el registro en la BD
        $validar = $this->rolModel->Transaccion(['peticion' => 'validar']);

        $this->assertContains($response['response']['resultado'], [200, 201]);
        $this->assertEquals(1, $validar['bool']);
    }

    #[Test]
    public function testValidarRolTest(): void
    {
        // 1. Registrar Insumo (Precondición)
        $this->poblarRolValido();
        $this->rolModel->Transaccion(['peticion' => 'registrar']);

        // 2. Validar Insumo
        $response = $this->rolModel->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(1, $response['bool']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testConsultarRolTest(): void
    {
        // 2. Consultar Insumo
        $response = $this->rolModel->Transaccion(['peticion' => 'consultar']);

        $this->assertIsArray($response);
        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $response['response']);
    }

    #[Test]
    public function testModificarRolTest(): void
    {
        // 1. Registrar Insumo (Precondición)
        $this->poblarRolValido();
        $this->rolModel->Transaccion(['peticion' => 'registrar']);

        // 2. Modificar Insumo
        $this->rolModel->setNombre("Rol Unitario");
        $response = $this->rolModel->Transaccion(['peticion' => 'modificar']);

        $modificacion = $this->rolModel->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(200, $response['response']['resultado']);
        $this->assertEquals("Rol Unitario", $modificacion['response']['registro']['nombre_rol']);
    }

    #[Test]
    public function testEliminarRolTest(): void
    {
        // 1. Eliminar Insumo (Precondición)
        $this->poblarRolValido();
        $this->rolModel->Transaccion(['peticion' => 'registrar']);

        $response = $this->rolModel->Transaccion(['peticion' => 'eliminar']);
        $validacion = $this->rolModel->Transaccion(['peticion' => 'validar']);


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
            self::$dbBusiness->exec("DELETE FROM rol WHERE id_rol = 'ROL00012026010112' ");
        }
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
