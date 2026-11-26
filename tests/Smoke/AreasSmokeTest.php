<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\System\Areas;

/**
 * Pruebas de Humo: Módulo Áreas
 *
 * Reglas de oro:
 * 1. Ultra-rápidas (< 500 ms).
 * 2. Prueban la ruta crítica de las áreas del restaurante contra la base de datos de pruebas.
 * 3. Si falla, la zonificación y configuración de áreas está inoperativa.
 */
class AreasSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
    }

    /**
     * Comprueba que el módulo de Áreas pueda consultar el catálogo de áreas.
     */
    public function test_humo_areas_catalogo_activo(): void
    {
        $areasModel = new Areas();
        $resultado = $areasModel->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resultado['estado'] ?? 0, 'La consulta de áreas debe ser exitosa.');
        $this->assertEquals(200, $resultado['response']['resultado'] ?? 0);
        $this->assertIsArray($resultado['response']['datos'] ?? null);
    }
}
