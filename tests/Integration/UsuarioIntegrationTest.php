<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Security\Usuario;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Usuarios, Autenticación y Seguridad
 * Evalúa el ciclo completo del modelo Usuario, persistencia en MariaDB,
 * validación, consultas, inicio de sesión, recuperación de contraseña y rendimiento.
 */
class UsuarioIntegrationTest extends TestCase
{
    private static ?PDO $dbSecurity;
    private static ?PDO $dbBusiness;
    private Usuario $usuarioModel;
    private string $cedulaTest = 'V-88776655';
    private string $usernameTest = 'usuariotestinteg';
    private string $correoTest = 'usuario.integ@empresa.com';
    private string $claveTest = 'ClaveSegura123*';
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
        self::$dbBusiness->exec("INSERT INTO persona (cedula, documento, nombre, apellido, fecha_nacimiento, telefono, correo, direccion, sexo)
            VALUES ('{$this->cedulaTest}', 'V', 'Usuario', 'Integracion', '1990-01-01', '04141234567', '{$this->correoTest}', 'Calle Test', 'M')
            ON DUPLICATE KEY UPDATE correo = '{$this->correoTest}'");
        self::$dbBusiness->exec("INSERT INTO empleado (cedula, id_cargo, estatus)
            VALUES ('{$this->cedulaTest}', '{$this->idCargoTest}', 1)
            ON DUPLICATE KEY UPDATE estatus = 1");

        $this->usuarioModel->setCedula($this->cedulaTest);
        $this->usuarioModel->setUsername($this->usernameTest);
        $this->usuarioModel->setCorreo($this->correoTest);
        $this->usuarioModel->setIdRol($this->idRolTest);
        $this->usuarioModel->setClave($this->claveTest, true);
    }

    private function limpiarDatosPrueba(): void
    {
        self::$dbSecurity->exec("DELETE FROM sesion WHERE cedula = '{$this->cedulaTest}'");
        self::$dbSecurity->exec("DELETE FROM usuario WHERE cedula = '{$this->cedulaTest}' OR username LIKE '{$this->usernameTest}%'");
        self::$dbBusiness->exec("DELETE FROM empleado WHERE cedula = '{$this->cedulaTest}'");
        self::$dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaTest}' OR correo = '{$this->correoTest}'");
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

    #[Test]
    public function testIniciarSesionExitosaTest(): void
    {
        // 1. Preparar Usuario Activo (Precondición)
        $this->poblarUsuarioValido();
        $this->usuarioModel->Transaccion(['peticion' => 'registrar']);

        // 2. Iniciar Sesión con credenciales correctas
        $this->usuarioModel->setCedula($this->cedulaTest);
        $this->usuarioModel->setClave($this->claveTest, false);

        $response = $this->usuarioModel->Transaccion(['peticion' => 'sesion']);

        $this->assertEquals(200, $response['HTTP_STATUS']['codigo']);
        $this->assertEquals(200, $response['response']['resultado']);
        $this->assertTrue($response['response']['verificacion']);
    }

    #[Test]
    public function testIniciarSesionCredencialesInvalidasTest(): void
    {
        // 1. Preparar Usuario Activo (Precondición)
        $this->poblarUsuarioValido();
        $this->usuarioModel->Transaccion(['peticion' => 'registrar']);

        // 2. Intentar Iniciar Sesión con contraseña errónea
        $this->usuarioModel->setCedula($this->cedulaTest);
        $this->usuarioModel->setClave('ClaveIncorrecta999*', false);

        $response = $this->usuarioModel->Transaccion(['peticion' => 'sesion']);

        $this->assertEquals(401, $response['HTTP_STATUS']['codigo']);
        $this->assertEquals(401, $response['response']['resultado']);
        $this->assertFalse($response['response']['verificacion']);
    }

    #[Test]
    public function testGuardarCodigoRecuperacionTest(): void
    {
        // 1. Preparar Usuario Activo (Precondición)
        $this->poblarUsuarioValido();
        $this->usuarioModel->Transaccion(['peticion' => 'registrar']);

        // 2. Guardar código de recuperación
        $codigoPrueba = '654321';
        $this->usuarioModel->setCedula($this->cedulaTest);
        $response = $this->usuarioModel->Transaccion([
            'peticion' => 'guardar-codigo',
            'codigo'   => $codigoPrueba
        ]);

        $this->assertEquals(1, $response['estado']);
        $this->assertEquals(200, $response['response']['resultado']);

        // 3. Verificar persistencia de token en MariaDB
        $stm = self::$dbSecurity->prepare("SELECT token_recuperacion FROM usuario WHERE cedula = :c");
        $stm->execute([':c' => $this->cedulaTest]);
        $this->assertEquals($codigoPrueba, $stm->fetchColumn());
    }

    #[Test]
    public function testValidarCodigoRecuperacionTest(): void
    {
        // 1. Preparar Usuario y Código (Precondición)
        $this->poblarUsuarioValido();
        $this->usuarioModel->Transaccion(['peticion' => 'registrar']);

        $codigoPrueba = '654321';
        $this->usuarioModel->setCedula($this->cedulaTest);
        $this->usuarioModel->Transaccion([
            'peticion' => 'guardar-codigo',
            'codigo'   => $codigoPrueba
        ]);

        // 2. Validar código correcto
        $resValido = $this->usuarioModel->Transaccion([
            'peticion' => 'validar-codigo',
            'codigo'   => $codigoPrueba
        ]);
        $this->assertEquals(1, $resValido['bool']);

        // 3. Validar código incorrecto
        $resInvalido = $this->usuarioModel->Transaccion([
            'peticion' => 'validar-codigo',
            'codigo'   => '000000'
        ]);
        $this->assertEquals(0, $resInvalido['bool']);
    }

    #[Test]
    public function testRestablecerClaveTest(): void
    {
        // 1. Preparar Usuario con código (Precondición)
        $this->poblarUsuarioValido();
        $this->usuarioModel->Transaccion(['peticion' => 'registrar']);

        $codigoPrueba = '654321';
        $this->usuarioModel->setCedula($this->cedulaTest);
        $this->usuarioModel->Transaccion([
            'peticion' => 'guardar-codigo',
            'codigo'   => $codigoPrueba
        ]);

        // 2. Restablecer contraseña
        $nuevaClave = 'NuevaClaveSegura2026*';
        $this->usuarioModel->setClave($nuevaClave, true);
        $resClave = $this->usuarioModel->Transaccion(['peticion' => 'actualizar-clave']);
        $this->assertEquals(1, $resClave['estado']);
        $this->assertEquals(200, $resClave['response']['resultado']);

        // 3. Limpiar código de recuperación
        $resLimpiar = $this->usuarioModel->Transaccion(['peticion' => 'limpiar-codigo']);
        $this->assertEquals(1, $resLimpiar['estado']);

        $stm = self::$dbSecurity->prepare("SELECT token_recuperacion FROM usuario WHERE cedula = :c");
        $stm->execute([':c' => $this->cedulaTest]);
        $this->assertNull($stm->fetchColumn(), "El token debe quedar en NULL tras restablecer");

        // 4. Probar que el inicio de sesión tiene éxito con la nueva contraseña
        $this->usuarioModel->setClave($nuevaClave, false);
        $resLoginNuevo = $this->usuarioModel->Transaccion(['peticion' => 'sesion']);
        $this->assertEquals(200, $resLoginNuevo['response']['resultado']);
        $this->assertTrue($resLoginNuevo['response']['verificacion']);

        // 5. Probar que la contraseña anterior ya no funciona
        $this->usuarioModel->setClave($this->claveTest, false);
        $resLoginViejo = $this->usuarioModel->Transaccion(['peticion' => 'sesion']);
        $this->assertEquals(401, $resLoginViejo['response']['resultado']);
        $this->assertFalse($resLoginViejo['response']['verificacion']);
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
            self::$dbBusiness->exec("DELETE FROM persona WHERE cedula = 'V-88776655' OR correo = 'usuario.integ@empresa.com'");
        }
        self::$dbSecurity = NULL;
        self::$dbBusiness = NULL;
        parent::tearDownAfterClass();
    }
}
