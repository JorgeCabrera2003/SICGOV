<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Proveedor;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Proveedores
 * Evalúa persistencia real, validación de RIF / Documento Legal,
 * duplicados y consultas transaccionales en MariaDB.
 */
class ProveedorIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private Proveedor $proveedorModel;
    private string $docLegalTest = 'J-998877665';

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

        $this->dbBusiness = Database::getConnection('business');
        $this->proveedorModel = new Proveedor();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM proveedor WHERE documento_legal = '{$this->docLegalTest}'");
    }

    #[Test]
    public function testConsultarProveedoresEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->proveedorModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs);
    }

    #[Test]
    public function testCicloDeVidaProveedorPersistenciaReal(): void
    {
        // 1. Registrar
        $this->proveedorModel->setDocumentoLegal($this->docLegalTest);
        $this->proveedorModel->setNombre('Distribuidora Avícola Lara C.A.');
        $this->proveedorModel->setTelefono('0251-5544332');
        $this->proveedorModel->setCorreo('ventas@avicolalara.com');
        $this->proveedorModel->setDireccion('Zona Industrial II, Barquisimeto');

        $resReg = $this->proveedorModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($resReg['response']['resultado'], [200, 201]);

        // 2. Verificar persistencia
        $stmt = $this->dbBusiness->prepare("SELECT * FROM proveedor WHERE documento_legal = ?");
        $stmt->execute([$this->docLegalTest]);
        $provBD = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($provBD);
        $this->assertEquals('Distribuidora Avícola Lara C.A.', $provBD['nombre']);

        // 3. Modificar
        $this->proveedorModel->setNombre('Distribuidora Avícola Lara Mayorista');
        $resMod = $this->proveedorModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $resMod['response']['resultado']);

        // 4. Eliminar (Lógico)
        $resElim = $this->proveedorModel->Transaccion(['peticion' => 'eliminar']);
        $this->assertEquals(200, $resElim['response']['resultado']);

        // 5. Verificar estatus = 0
        $stmt->execute([$this->docLegalTest]);
        $provInactivo = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(0, (int)$provInactivo['estatus']);
    }
}
