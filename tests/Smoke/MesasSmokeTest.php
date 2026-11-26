<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\System\Mesas;

/**
 * Pruebas de Humo: Módulo Mesas
 *
 * Reglas de oro:
 * 1. Ultra-rápidas (< 500 ms).
 * 2. Prueban la ruta crítica del módulo de Mesas contra la base de datos de pruebas.
 * 3. Si falla, la gestión y asignación de mesas está inoperativa.
 */
class MesasSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
    }

    /**
     * Comprueba que el módulo de Mesas tenga acceso al catálogo de mesas y sus áreas asignadas.
     */
    public function test_humo_mesas_catalogo_y_areas_disponibles(): void
    {
        $mesasModel = new Mesas();
        $resultado = $mesasModel->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resultado['estado'] ?? 0, 'La consulta de mesas debe ser exitosa.');
        $this->assertEquals(200, $resultado['response']['resultado'] ?? 0);
        $this->assertNotEmpty($resultado['response']['datos'], 'Deben existir mesas sembradas en la base de datos de pruebas.');
    }
}
