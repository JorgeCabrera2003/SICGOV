<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Estadistica;

class EstadisticaTest extends TestCase
{
    #[Test]
    public function testObtenerDatosDashboardEstructuraCompleta(): void
    {
        $estadistica = new Estadistica();
        $datos = $estadistica->ObtenerDatosDashboard([]);

        $this->assertIsArray($datos);
        
        // Verificar claves de primer nivel del dashboard
        $this->assertArrayHasKey('kpis', $datos);
        $this->assertArrayHasKey('catalogs', $datos);
        $this->assertArrayHasKey('reservacionesEstado', $datos);
        $this->assertArrayHasKey('reservacionesMes', $datos);
        $this->assertArrayHasKey('productosCategoria', $datos);
        $this->assertArrayHasKey('asistenciasEstado', $datos);
        $this->assertArrayHasKey('bitacoraActividad', $datos);
        $this->assertArrayHasKey('topProductos', $datos);
        $this->assertArrayHasKey('metodosPago', $datos);
        $this->assertArrayHasKey('mesasPopularidad', $datos);
        $this->assertArrayHasKey('ingredientesAlerta', $datos);

        // Verificar subestructuras de KPIs
        $this->assertArrayHasKey('totalReservaciones', $datos['kpis']);
        $this->assertArrayHasKey('totalClientes', $datos['kpis']);
        $this->assertArrayHasKey('totalProductos', $datos['kpis']);
        $this->assertArrayHasKey('gananciasTotales', $datos['kpis']);
        $this->assertArrayHasKey('totalPedidos', $datos['kpis']);

        // Verificar subestructuras de catálogos
        $this->assertArrayHasKey('mesas', $datos['catalogs']);
        $this->assertArrayHasKey('empleados', $datos['catalogs']);
        $this->assertArrayHasKey('categorias', $datos['catalogs']);
        $this->assertArrayHasKey('metodosPago', $datos['catalogs']);
    }

    #[Test]
    public function testObtenerDatosDashboardConFiltroFechas(): void
    {
        $estadistica = new Estadistica();
        $filtros = [
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-12-31',
            'pedido_tipo' => 'POS'
        ];

        $datos = $estadistica->ObtenerDatosDashboard($filtros);

        $this->assertIsArray($datos);
        $this->assertArrayHasKey('kpis', $datos);
        $this->assertArrayHasKey('gananciasTotales', $datos['kpis']);
        $this->assertArrayHasKey('totalPedidos', $datos['kpis']);
    }

    #[Test]
    public function testObtenerDatosDashboardConFiltrosIncompatiblesRetornaEstructuraSegura(): void
    {
        $estadistica = new Estadistica();
        // Fechas invertidas o datos maliciosos
        $filtros = [
            'fecha_desde' => '2030-01-01',
            'fecha_hasta' => '2020-01-01',
            'bitacora_modulo' => "MODULO_FANTASMA' OR 1=1--"
        ];

        $datos = $estadistica->ObtenerDatosDashboard($filtros);
        $this->assertIsArray($datos);
        $this->assertArrayHasKey('kpis', $datos);
        $this->assertArrayHasKey('topProductos', $datos);
    }
}
