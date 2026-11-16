<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Areas;
use App\Models\System\Mesas;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Áreas y Mesas
 * Evalúa creación de áreas, distribución de mesas, capacidad de comensales,
 * cambios de estado (DISPONIBLE, OCUPADA) y persistencia relacional en MariaDB.
 */
class MesaAreaIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private Areas $areaModel;
    private Mesas $mesaModel;
    private string $idAreaTest = 'AREA0012026010112';
    private string $idMesaTest = 'MESA0012026010112';

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
        $this->areaModel = new Areas();
        $this->mesaModel = new Mesas();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM mesa WHERE id_mesa = '{$this->idMesaTest}'");
        $this->dbBusiness->exec("DELETE FROM area_mesa WHERE id_area = '{$this->idAreaTest}'");
    }

    #[Test]
    public function testConsultarAreasYMesasVelocidad(): void
    {
        $tInicio = microtime(true);
        $resAreas = $this->areaModel->Transaccion(['peticion' => 'consultar']);
        $resMesas = $this->mesaModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertEquals(200, $resAreas['HTTP_STATUS']['codigo']);
        $this->assertEquals(200, $resMesas['HTTP_STATUS']['codigo']);
        $this->assertLessThan(400, $duracionMs);
    }

    #[Test]
    public function testFlujoIntegradoAreaYMesasPersistencia(): void
    {
        // 1. Crear Área
        $this->areaModel->setIdArea($this->idAreaTest);
        $this->areaModel->setNombre('Terraza Gourmet Test');
        $this->areaModel->setEstatus(1);

        $resArea = $this->areaModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($resArea['response']['resultado'], [200, 201]);

        // 2. Crear Mesa vinculada al área
        $this->mesaModel->setIdMesa($this->idMesaTest);
        $this->mesaModel->setIdArea($this->idAreaTest);
        $this->mesaModel->setNumeroMesa(99);
        $this->mesaModel->setCapacidad(6);
        $this->mesaModel->setEstado('DISPONIBLE');
        $this->mesaModel->setEstatus(1);

        $resMesa = $this->mesaModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($resMesa['response']['resultado'], [200, 201]);

        // 3. Validar en Base de Datos
        $stmt = $this->dbBusiness->prepare("SELECT * FROM mesa WHERE id_mesa = ?");
        $stmt->execute([$this->idMesaTest]);
        $mesaBD = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($mesaBD);
        $this->assertEquals(6, (int)$mesaBD['capacidad']);
        $this->assertEquals('DISPONIBLE', $mesaBD['estado']);

        // 4. Modificar Estado a Ocupada
        $this->mesaModel->setEstado('OCUPADA');
        $resMod = $this->mesaModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $resMod['response']['resultado']);

        $stmt->execute([$this->idMesaTest]);
        $mesaModBD = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('OCUPADA', $mesaModBD['estado']);
    }
}
