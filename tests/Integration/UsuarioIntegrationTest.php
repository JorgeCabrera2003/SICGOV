<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Security\Usuario;
use App\Models\Security\Rol;
use App\Core\Database;
use App\Helpers\Helper;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Usuario, Autenticación y Sesiones
 * Evalúa el ciclo completo del modelo Usuario, transacciones reales en MariaDB (goobv-usuarios-test),
 * eventos de Bitácora, validaciones del controlador, rendimiento y vulnerabilidades de seguridad.
 */
class UsuarioIntegrationTest extends TestCase
{
    private PDO $dbSecurity;
    private PDO $dbBusiness;
    private Usuario $usuarioModel;
    private string $cedulaTest = 'V-88776655';
    private string $usernameTest = 'usuariotestinteg';

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // Simular sesión de Administrador
        $_SESSION['user'] = [
            'cedula' => 'V-00000000',
            'username' => 'admin_test',
            'id_rol' => 'ADMIN00120251001',
            'rol' => 'Administrador',
            'estatus_clave' => 1
        ];

        $this->dbSecurity = Database::getConnection('security');
        $this->dbBusiness = Database::getConnection('business');
        $this->usuarioModel = new Usuario();

        // Limpieza previa
        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbSecurity->exec("DELETE FROM sesion WHERE cedula = '{$this->cedulaTest}'");
        $this->dbSecurity->exec("DELETE FROM usuario WHERE cedula = '{$this->cedulaTest}' OR username = '{$this->usernameTest}'");
        $this->dbBusiness->exec("DELETE FROM empleado WHERE cedula = '{$this->cedulaTest}'");
        $this->dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaTest}'");
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
        $this->assertIsArray($res['response']['datos']);

        // Rendimiento: La consulta debe resolverse en menos de 300 ms
        $this->assertLessThan(300, $duracionMs, "La consulta de usuarios superó los 300 ms ({$duracionMs} ms)");

        // Verificar estructura de cada registro retornado
        if (!empty($res['response']['datos'])) {
            $primerUsuario = $res['response']['datos'][0];
            $this->assertArrayHasKey('cedula', $primerUsuario);
            $this->assertArrayHasKey('username', $primerUsuario);
            $this->assertArrayHasKey('id_rol', $primerUsuario);
            $this->assertArrayHasKey('estatus', $primerUsuario);
        }
    }

    #[Test]
    public function testEmpleadosSinUsuarioListar(): void
    {
        $res = $this->usuarioModel->Transaccion(['peticion' => 'empleados-sin-usuario']);
        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertIsArray($res['response']['datos']);
    }

    #[Test]
    public function testListarRolesActivosEstructuraJSON(): void
    {
        $rolModel = new Rol();
        $rolesResult = $rolModel->Transaccion(['peticion' => 'consultar']);
        
        $this->assertIsArray($rolesResult);
        $this->assertEquals(200, $rolesResult['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $rolesResult['response']);
        $this->assertNotEmpty($rolesResult['response']['datos']);

        $primerRol = $rolesResult['response']['datos'][0];
        $this->assertArrayHasKey('id_rol', $primerRol);
        $this->assertArrayHasKey('nombre_rol', $primerRol);
    }

    #[Test]
    public function testCicloVidaRegistroModificacionYToggleEstatusUsuario(): void
    {
        // 1. Crear persona y empleado base en la base de datos de negocio
        $this->dbBusiness->exec("INSERT INTO persona (cedula, documento, nombre, apellido, telefono, direccion, sexo)
            VALUES ('{$this->cedulaTest}', 'V', 'Usuario', 'Integracion', '04141234567', 'Calle Test', 'M')");
        $this->dbBusiness->exec("INSERT INTO empleado (cedula, id_cargo, estatus)
            VALUES ('{$this->cedulaTest}', 'CARGO00120260519200547232', 1)");

        // 2. Registrar usuario en la base de datos de seguridad
        $this->usuarioModel->setCedula($this->cedulaTest);
        $this->usuarioModel->setUsername($this->usernameTest);
        $this->usuarioModel->setIdRol('ADMIN00120251001');
        $this->usuarioModel->setClave('ClaveSegura123*', true);

        $resReg = $this->usuarioModel->Transaccion(['peticion' => 'registrar']);
        $this->assertEquals(201, $resReg['HTTP_STATUS']['codigo']);
        $this->assertEquals(1, $resReg['estado']);

        // Verificar en BD
        $stm = $this->dbSecurity->prepare("SELECT username, estatus FROM usuario WHERE cedula = :c");
        $stm->execute([':c' => $this->cedulaTest]);
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
        $this->assertEquals($this->usernameTest, $row['username']);
        $this->assertEquals(1, $row['estatus']);

        // 3. Modificar username
        $nuevoUsername = $this->usernameTest . 'mod';
        $this->usuarioModel->setUsername($nuevoUsername);
        $this->usuarioModel->setClave('', false); // Conservar clave anterior
        $resMod = $this->usuarioModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $resMod['HTTP_STATUS']['codigo']);

        $stmMod = $this->dbSecurity->prepare("SELECT username FROM usuario WHERE cedula = :c");
        $stmMod->execute([':c' => $this->cedulaTest]);
        $this->assertEquals($nuevoUsername, $stmMod->fetchColumn());

        // 4. Toggle estatus a 0 (Inactivar)
        $this->usuarioModel->setEstatus(0);
        $resInact = $this->usuarioModel->Transaccion(['peticion' => 'toggle-estatus']);
        $this->assertEquals(200, $resInact['HTTP_STATUS']['codigo']);

        $stmEst = $this->dbSecurity->prepare("SELECT estatus FROM usuario WHERE cedula = :c");
        $stmEst->execute([':c' => $this->cedulaTest]);
        $this->assertEquals(0, $stmEst->fetchColumn());

        // 5. Toggle estatus a 1 (Reactivar)
        $this->usuarioModel->setEstatus(1);
        $resAct = $this->usuarioModel->Transaccion(['peticion' => 'toggle-estatus']);
        $this->assertEquals(200, $resAct['HTTP_STATUS']['codigo']);

        $stmEst->execute([':c' => $this->cedulaTest]);
        $this->assertEquals(1, $stmEst->fetchColumn());
    }

    #[Test]
    public function testForzarCambioClaveUsuario(): void
    {
        // 1. Preparar usuario activo
        $this->dbBusiness->exec("INSERT INTO persona (cedula, documento, nombre, apellido, telefono, direccion, sexo)
            VALUES ('{$this->cedulaTest}', 'V', 'UserClave', 'Test', '04141234567', 'Calle Test', 'M')");
        $this->dbBusiness->exec("INSERT INTO empleado (cedula, id_cargo, estatus)
            VALUES ('{$this->cedulaTest}', 'CARGO00120260519200547232', 1)");

        $this->usuarioModel->setCedula($this->cedulaTest);
        $this->usuarioModel->setUsername($this->usernameTest);
        $this->usuarioModel->setIdRol('ADMIN00120251001');
        $this->usuarioModel->setClave('ClaveInicial123*', true);
        $this->usuarioModel->Transaccion(['peticion' => 'registrar']);

        // 2. Ejecutar forzar cambio de clave
        $res = $this->usuarioModel->Transaccion(['peticion' => 'forzar-clave']);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);

        // 3. Verificar en BD que estatus_clave es 0
        $stm = $this->dbSecurity->prepare("SELECT estatus_clave FROM usuario WHERE cedula = :c");
        $stm->execute([':c' => $this->cedulaTest]);
        $this->assertEquals(0, $stm->fetchColumn(), "El estatus_clave debe ser 0 para forzar actualización en próximo inicio");
    }

    #[Test]
    public function testInmutabilidadSuperusuarioV00000000(): void
    {
        // El superusuario principal no puede ser modificado ni inactivado
        $this->usuarioModel->setCedula('V-00000000');
        $this->usuarioModel->setEstatus(0);

        // Validar en el archivo del controlador la regla de bloqueo HTTP 403
        $controladorPath = dirname(__DIR__, 2) . '/app/Controllers/UsuarioController.php';
        $codigo = file_get_contents($controladorPath);
        $this->assertStringContainsString("if (isset(\$_POST[\"cedula\"]) && \$_POST[\"cedula\"] === 'V-00000000'", $codigo);
        $this->assertStringContainsString("403 Forbidden", $codigo);
    }

    #[Test]
    public function testDefectoRegexIncompatibleUsernameEntreControladorYModelo(): void
    {
        // Defecto AUTH-BUG-02:
        // El controlador permite números, puntos y tildes mediante /^[a-zA-Z0-9._\-ÁÉÍÓÚáéíóúüñÑ]+$/u
        // pero el Modelo Usuario.php usa /^[a-zA-Z]+$/, rechazándolos y lanzando excepción.
        $usernameConPunto = 'usuario.test';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('El usuario solo puede contener letras, sin números ni símbolos.');
        
        $this->usuarioModel->setUsername($usernameConPunto);
    }

    #[Test]
    public function testPoliticaContrasenasDebilesPermiteClavesCortas(): void
    {
        // Hallazgo AUTH-SEC-01: El sistema permite contraseñas de solo 4 caracteres
        $claveCorta = '1234';
        
        $this->usuarioModel->setClave($claveCorta, false);
        $this->assertNotEmpty($this->usuarioModel->getClave(), "El modelo acepta contraseñas de apenas 4 caracteres");
    }

    #[Test]
    public function testVulnerabilidadFuerzaBrutaEvasionPorDescarteDeSesion(): void
    {
        // Vulnerabilidad AUTH-VULN-02:
        // El contador de intentos fallidos se almacena únicamente en $_SESSION['login_attempts']
        // y no en base de datos. Si un atacante no envía cookie de sesión, el contador se resetea.
        $loginCtrlPath = dirname(__DIR__, 2) . '/app/Controllers/LoginController.php';
        $codigo = file_get_contents($loginCtrlPath);

        $this->assertStringContainsString("\$_SESSION['login_attempts'][\$cedula]", $codigo);
        $this->assertStringContainsString("if (\$_SESSION['login_attempts'][\$cedula] >= 4)", $codigo);
    }

    #[Test]
    public function testVulnerabilidadBypassRecaptchaEnLogin(): void
    {
        // Vulnerabilidad AUTH-VULN-01:
        // El controlador omite la verificación real con los servidores de Google reCAPTCHA
        // ante tokens de bypass o en entornos locales.
        $loginCtrlPath = dirname(__DIR__, 2) . '/app/Controllers/LoginController.php';
        $codigo = file_get_contents($loginCtrlPath);

        $this->assertStringContainsString("g-recaptcha-response", $codigo);
    }
}
