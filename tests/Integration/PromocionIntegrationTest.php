<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Promocion;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Promociones
 * Evalúa persistencia real de descuentos, vigencias temporales y consultas en MariaDB.
 */
class PromocionIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private Promocion $promocionModel;
    private string $idPromoTest = 'PROM0012026010112';

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
        $this->promocionModel = new Promocion();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM planificador_promocion WHERE id_promocion = '{$this->idPromoTest}'");
        $this->dbBusiness->exec("DELETE FROM promocion WHERE id_promocion = '{$this->idPromoTest}'");
    }

    #[Test]
    public function testConsultarPromocionesEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->promocionModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertLessThan(400, $duracionMs);
    }

    #[Test]
    public function testCicloDeVidaPromocionPersistenciaReal(): void
    {
        // 1. Registrar
        $this->promocionModel->setIdPromocion($this->idPromoTest);
        $this->promocionModel->setNombre('Happy Hour 2x1 Tapas Test');
        $this->promocionModel->setTipoDescuento('PORCENTAJE');
        $this->promocionModel->setValorDescuento('20');
        $this->promocionModel->setDescripcion('Descuento especial de tarde para clientes');
        $this->promocionModel->setFechaInicio(date('Y-m-d'));
        $this->promocionModel->setFechaFin(date('Y-m-d', strtotime('+30 days')));
        $this->promocionModel->setHoraInicio('17:00:00');
        $this->promocionModel->setHoraFin('20:00:00');

        $resReg = $this->promocionModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($resReg['response']['resultado'], [200, 201]);

        // 2. Validar persistencia
        $stmt = $this->dbBusiness->prepare("SELECT * FROM promocion WHERE id_promocion = ?");
        $stmt->execute([$this->idPromoTest]);
        $promoBD = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($promoBD);
        $this->assertEquals('Happy Hour 2x1 Tapas Test', $promoBD['nombre']);
    }
}
