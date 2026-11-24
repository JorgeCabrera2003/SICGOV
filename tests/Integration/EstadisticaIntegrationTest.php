<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Estadistica;

/**
 * Suite de Pruebas de Integración para el Módulo de Estadísticas y Dashboard
 * Evalúa consultas agregadas de ventas, pedidos, reservaciones y rendimiento en MariaDB.
 */
class EstadisticaIntegrationTest extends TestCase
{
    private Estadistica $estadisticaModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->estadisticaModel = new Estadistica();
    }

    #[Test]
    public function testObtenerDatosDashboardEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->estadisticaModel->ObtenerDatosDashboard([]);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertArrayHasKey('catalogs', $res);
        $this->assertArrayHasKey('kpis', $res);
        $this->assertLessThan(600, $duracionMs, "La consulta analítica tardó {$duracionMs}ms (límite: 600ms)");
    }

    #[Test]
    public function testObtenerDatosDashboardConFiltroRangoFechas(): void
    {
        $filtros = [
            'fecha_desde' => date('Y-m-d', strtotime('-30 days')),
            'fecha_hasta' => date('Y-m-d')
        ];

        $res = $this->estadisticaModel->ObtenerDatosDashboard($filtros);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('kpis', $res);
    }
}
