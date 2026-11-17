<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Security\Usuario;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Usuarios y Seguridad
 * Evalúa el ciclo completo del modelo Usuario, persistencia en MariaDB,
 * validación, consultas transaccionales y rendimiento.
 */
class UsuarioIntegrationTest extends TestCase
{
    private static ?PDO $dbSecurity;
    private static ?PDO $dbBusiness;
    private Usuario $usuarioModel;
    private string $cedulaTest = 'V-88776655';
    private string $usernameTest = 'usuariotestinteg';
    private string $idRolTest = 'ADMIN00120251001';
    private string $idCargoTest = 'CARGO00120260519200547232';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$dbSecurity = Database::getConnection('security');
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

        self::$dbSecurity = Database::getConnection('security');
        self::$dbBusiness = Database::getConnection('business');
        $this->usuarioModel = new Usuario();

        // Obtener rol y cargo válidos activos
        $rolId = self::$dbSecurity->query("SELECT id_rol FROM rol WHERE estatus = 1 LIMIT 1")->fetchColumn();
        if ($rolId) {
            $this->idRolTest = (string) $rolId;
        }
        $cargoId = self::$dbBusiness->query("SELECT id_cargo FROM cargo WHERE estatus = 1 LIMIT 1")->fetchColumn();
        if ($cargoId) {
            $this->idCargoTest = (string) $cargoId;
        }

        $this->limpiarDatosPrueba();
    }

    private function poblarUsuarioValido(): void
    {
        // Asegurar existencia de persona y empleado en la base de datos de negocio
        self::$dbBusiness->exec("INSERT INTO persona (cedula, documento, nombre, apellido, telefono, direccion, sexo)
            VALUES ('{$this->cedulaTest}', 'V', 'Usuario', 'Integracion', '04141234567', 'Calle Test', 'M')
            ON DUPLICATE KEY UPDATE nombre = 'Usuario'");
        self::$dbBusiness->exec("INSERT INTO empleado (cedula, id_cargo, estatus)
            VALUES ('{$this->cedulaTest}', '{$this->idCargoTest}', 1)
            ON DUPLICATE KEY UPDATE estatus = 1");

        $this->usuarioModel->setCedula($this->cedulaTest);
        $this->usuarioModel->setUsername($this->usernameTest);
        $this->usuarioModel->setIdRol($this->idRolTest);
        $this->usuarioModel->setClave('ClaveSegura123*', true);
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbSecurity->exec("DELETE FROM sesion WHERE cedula = '{$this->cedulaTest}'");
        self::$dbSecurity->exec("DELETE FROM usuario WHERE cedula = '{$this->cedulaTest}' OR username LIKE '{$this->usernameTest}%'");
        self::$dbBusiness->exec("DELETE FROM empleado WHERE cedula = '{$this->cedulaTest}'");
        self::$dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaTest}'");
    }

    #[Test]
    public function testConsultarUsuariosEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->usuarioModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs, "La consulta de usuarios tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testRegistrarUsuarioTest(): void
    {
        // 1. Registrar Usuario
        $this->poblarUsuarioValido();

        $response = $this->usuarioModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($response['response']['resultado'], [200, 201]);
    }

    #[Test]
    public function testValidarUsuarioTest(): void
    {
        // 1. Registrar Usuario (Precondición)
        $this->poblarUsuarioValido();
        $this->usuarioModel->Transaccion(['peticion' => 'registrar']);

        // 2. Validar Usuario
        $response = $this->usuarioModel->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(1, $response['bool']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testConsultarUsuarioTest(): void
    {
        // 2. Consultar Usuario
        $response = $this->usuarioModel->Transaccion(['peticion' => 'consultar']);

        $this->assertIsArray($response);
        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $response['response']);
    }

    #[Test]
    public function testModificarUsuarioTest(): void
    {
        // 1. Registrar Usuario (Precondición)
        $this->poblarUsuarioValido();
        $this->usuarioModel->Transaccion(['peticion' => 'registrar']);

        // 2. Modificar Usuario
        $nuevoUsername = $this->usernameTest . 'mod';
        $this->usuarioModel->setUsername($nuevoUsername);
        $this->usuarioModel->setClave('', false);
        $response = $this->usuarioModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $response['response']['resultado']);
    }

    #[Test]
    public function testEliminarUsuarioTest(): void
    {
        // 1. Registrar Usuario (Precondición)
        $this->poblarUsuarioValido();
        $this->usuarioModel->Transaccion(['peticion' => 'registrar']);

        // 2. Eliminar / Inactivar Usuario
        $this->usuarioModel->setEstatus(0);
        $response = $this->usuarioModel->Transaccion(['peticion' => 'toggle-estatus']);
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
        if (self::$dbSecurity !== NULL) {
            self::$dbSecurity->exec("DELETE FROM sesion WHERE cedula = 'V-88776655'");
            self::$dbSecurity->exec("DELETE FROM usuario WHERE cedula = 'V-88776655' OR username LIKE 'usuariotestinteg%'");
        }
        if (self::$dbBusiness !== NULL) {
            self::$dbBusiness->exec("DELETE FROM empleado WHERE cedula = 'V-88776655'");
            self::$dbBusiness->exec("DELETE FROM persona WHERE cedula = 'V-88776655'");
        }
        self::$dbSecurity = NULL;
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
