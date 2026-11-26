<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\System\Promocion;

/**
 * Pruebas de Humo: Módulo Promociones
 *
 * Reglas de oro:
 * 1. Ultra-rápidas (< 500 ms).
 * 2. Prueban la ruta crítica del módulo de Promociones contra la base de datos de pruebas.
 * 3. Si falla, el catálogo de promociones está inoperativo.
 */
class PromocionSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
    }

    /**
     * Comprueba que el módulo de Promociones pueda consultar ofertas activas con sus productos.
     */
    public function test_humo_promocion_consulta_ofertas_activa(): void
    {
        $promocionModel = new Promocion();
        $resultado = $promocionModel->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resultado['estado'] ?? 0, 'La consulta de promociones debe responder con éxito.');
        $this->assertEquals(200, $resultado['response']['resultado'] ?? 0);
        $this->assertIsArray($resultado['response']['datos'] ?? null);
    }
}
