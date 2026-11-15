<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Insumo;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Insumos e Inventario
 * Evalúa el ciclo completo del modelo Insumo, persistencia en MariaDB,
 * validación de stocks, unidades de medida y rendimiento.
 */
class InsumoIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private Insumo $insumoModel;
    private string $idInsumoTest = 'INSU0012026010112';
    private string $idCatTest = 'CATI0012026010112';
    private string $idUnidTest = 'UNID0012026010112';

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
        $this->insumoModel = new Insumo();

        // Obtener IDs válidos reales de categoria_insumo y unidad_medida
        $this->idCatTest = (string)$this->dbBusiness->query("SELECT id_categoria FROM categoria_insumo LIMIT 1")->fetchColumn();
        $this->idUnidTest = (string)$this->dbBusiness->query("SELECT id_unidad FROM unidad_medida LIMIT 1")->fetchColumn();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM movimiento_insumo WHERE id_insumo = '{$this->idInsumoTest}'");
        $this->dbBusiness->exec("DELETE FROM insumo WHERE id_insumo = '{$this->idInsumoTest}' OR nombre_insumo = 'Lomo de Cerdo Test'");
    }

    #[Test]
    public function testConsultarInsumosEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->insumoModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs, "La consulta de insumos tardó {$duracionMs}ms (límite: 400ms)");
    }

    #[Test]
    public function testCicloDeVidaInsumoPersistenciaReal(): void
    {
        // 1. Registrar Insumo
        $this->insumoModel->setId($this->idInsumoTest);
        $this->insumoModel->setNombre('Lomo de Cerdo Test');
        $this->insumoModel->setIdCategoria($this->idCatTest);
        $this->insumoModel->setIdUnidadMedida($this->idUnidTest);
        $this->insumoModel->setPrecioUnitario(8.50);
        $this->insumoModel->setStockActual(25.0);
        $this->insumoModel->setStockMinimo(5.0);
        $this->insumoModel->setStockMaximo(100.0);

        $resReg = $this->insumoModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($resReg['response']['resultado'], [200, 201]);

        // 2. Validar persistencia en BD
        $stmt = $this->dbBusiness->prepare("SELECT * FROM insumo WHERE id_insumo = ?");
        $stmt->execute([$this->idInsumoTest]);
        $insumoBD = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($insumoBD);
        $this->assertEquals('Lomo de Cerdo Test', $insumoBD['nombre_insumo']);
        $this->assertEquals(25.0, (float)$insumoBD['stock_actual']);

        // 3. Modificar Insumo
        $this->insumoModel->setPrecioUnitario(9.75);
        $this->insumoModel->setStockActual(30.0);
        $resMod = $this->insumoModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $resMod['response']['resultado']);

        // 4. Eliminar Insumo (Lógico)
        $resElim = $this->insumoModel->Transaccion(['peticion' => 'eliminar']);
        $this->assertEquals(200, $resElim['response']['resultado']);

        // 5. Verificar que estatus pasó a 0
        $stmt->execute([$this->idInsumoTest]);
        $insumoInactivo = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(0, (int)$insumoInactivo['estatus']);
    }

    #[Test]
    public function testRechazoInsumoConPeticionInvalida(): void
    {
        $res = $this->insumoModel->Transaccion(['peticion' => 'comando_inexistente']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
