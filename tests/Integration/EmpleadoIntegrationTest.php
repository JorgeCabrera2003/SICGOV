<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Empleado;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Empleados
 * Evalúa el ciclo completo del modelo Empleado, vinculación con cargos, persistencia dual (persona y empleado),
 * eventos de Bitácora, vulnerabilidad de bypass de cédula y omisión de validación de edad legal.
 */
class EmpleadoIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private PDO $dbSecurity;
    private Empleado $empleadoModel;
    private string $cedulaTest = 'V-66554433';
    private string $idCargoValido = 'CARGO00120260519200547232'; // Mesero

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

        $this->dbBusiness = Database::getConnection('business');
        $this->dbSecurity = Database::getConnection('security');
        $this->empleadoModel = new Empleado();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM empleado WHERE cedula = '{$this->cedulaTest}' OR cedula = 'INVALIDA-HACK-999'");
        $this->dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaTest}' OR cedula = 'INVALIDA-HACK-999'");
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
        $this->assertIsArray($res['response']['datos']);

        // Velocidad: Resuelve en menos de 250 ms
        $this->assertLessThan(250, $duracionMs, "La consulta de empleados demoró más de 250 ms ({$duracionMs} ms)");

        if (!empty($res['response']['datos'])) {
            $emp = $res['response']['datos'][0];
            $this->assertArrayHasKey('cedula', $emp);
            $this->assertArrayHasKey('nombre', $emp);
            $this->assertArrayHasKey('apellido', $emp);
            $this->assertArrayHasKey('id_cargo', $emp);
            $this->assertArrayHasKey('cargo', $emp);
            $this->assertArrayHasKey('estatus', $emp);
        }
    }

    #[Test]
    public function testCicloVidaRegistroModificacionYEliminacionEmpleado(): void
    {
        // 1. Asignar datos válidos
        $this->empleadoModel->setCedula($this->cedulaTest);
        $this->empleadoModel->setNombre('Ricardo');
        $this->empleadoModel->setApellido('Gomez');
        $this->empleadoModel->setFechaNacimiento('1993-08-20');
        $this->empleadoModel->setTelefono('0412-5554433');
        $this->empleadoModel->setCorreo('ricardo.gomez@empresa.com');
        $this->empleadoModel->setDireccion('Urb. Santa Rosa, Vereda 4');
        $this->empleadoModel->setSexo('M');
        $this->empleadoModel->setIdCargo($this->idCargoValido);

        // 2. Registro
        $resReg = $this->empleadoModel->Transaccion(['peticion' => 'registrar']);
        $this->assertEquals(200, $resReg['HTTP_STATUS']['codigo']);
        $this->assertEquals(1, $resReg['estado']);

        // Comprobar persistencia dual en MariaDB
        $stmEmp = $this->dbBusiness->prepare("SELECT id_cargo, estatus FROM empleado WHERE cedula = :c");
        $stmEmp->execute([':c' => $this->cedulaTest]);
        $rowEmp = $stmEmp->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals($this->idCargoValido, $rowEmp['id_cargo']);
        $this->assertEquals(1, $rowEmp['estatus']);

        // 3. Modificación (Cambio de Cargo a Cocinero)
        $idCargoCocinero = 'CARGO00220260519200547232';
        $this->empleadoModel->setIdCargo($idCargoCocinero);
        $this->empleadoModel->setTelefono('0412-9998877');
        $resMod = $this->empleadoModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $resMod['HTTP_STATUS']['codigo']);

        $stmEmp->execute([':c' => $this->cedulaTest]);
        $this->assertEquals($idCargoCocinero, $stmEmp->fetch(PDO::FETCH_ASSOC)['id_cargo']);

        // 4. Eliminación Lógica (Soft-Delete)
        $resElim = $this->empleadoModel->Transaccion(['peticion' => 'eliminar']);
        $this->assertEquals(200, $resElim['HTTP_STATUS']['codigo']);

        $stmEst = $this->dbBusiness->prepare("SELECT estatus FROM empleado WHERE cedula = :c");
        $stmEst->execute([':c' => $this->cedulaTest]);
        $this->assertEquals(0, $stmEst->fetchColumn(), "El empleado debe tener estatus 0 (Papelera)");
    }

    #[Test]
    public function testValidarCargoExistenteEInexistente(): void
    {
        $this->assertTrue($this->empleadoModel->validarCargo($this->idCargoValido));
        $this->assertFalse($this->empleadoModel->validarCargo('CARGO_FANTASMA_INEXISTENTE_999'));
    }

    #[Test]
    public function testVulnerabilidadBypassFormatoCedulaEnSetCedula(): void
    {
        // Vulnerabilidad EMP-VULN-01:
        // En EmpleadoController.php línea 45 se invoca set_cedula() en lugar de setCedula().
        // El método set_cedula() asigna el valor directo a la propiedad privada sin llamar
        // a RegexHelper::ValidarFormatos('Cedula'), permitiendo cadenas totalmente corruptas.
        $cedulaInvalida = 'INVALIDA-HACK-999';

        $this->empleadoModel->set_cedula($cedulaInvalida);
        $this->assertEquals($cedulaInvalida, $this->empleadoModel->getCedula(), "Vulnerabilidad confirmada: set_cedula() salta la validación regex");
    }

    #[Test]
    public function testDefectoAdmisionEmpleadoInfanteSinValidarEdadMinimaLegal(): void
    {
        // Corrección de Defecto EMP-BIZ-01:
        // Ahora Persona.php exige que la persona sea mayor o igual a 18 años.
        // Se valida que registrar un infante de 4 años lance la excepción correspondiente.
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Debe ser mayor o igual a 18 años.');

        $fechaInfante = '2022-06-15'; // 4 años de edad
        $this->empleadoModel->setFechaNacimiento($fechaInfante);
    }

    #[Test]
    public function testDefectoEliminacionCiegaRetorna200OkYBitacoraFalsa(): void
    {
        // Defecto EMP-INT-01:
        // Empleado.php:324-337 ejecuta UPDATE empleado SET estatus = 0 WHERE cedula = :cedula
        // sin validar rowCount(). Si la cédula no existe, emite resultado 200 y mensaje de éxito falso.
        $this->empleadoModel->setCedula('V-99999990');
        $res = $this->empleadoModel->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertEquals(1, $res['estado']);
        $this->assertEquals('Empleado eliminado exitosamente.', $res['response']['mensaje']);
    }
}
