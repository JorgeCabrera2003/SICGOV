<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\System\Pedido;

/**
 * Pruebas de Humo: Módulo Pedidos
 *
 * Reglas de oro:
 * 1. Ultra-rápidas (< 500 ms).
 * 2. Prueban la ruta crítica del módulo de Pedidos contra la base de datos de pruebas.
 * 3. Si falla, el subsistema de comandas/pedidos está inoperativo.
 */
class PedidoSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
    }

    /**
     * Comprueba que el módulo de Pedidos pueda listar pedidos y verificar relaciones de datos.
     */
    public function test_humo_pedido_listar_pedidos_activo(): void
    {
        $pedidoModel = new Pedido();
        $pedidos = $pedidoModel->Transaccion(['peticion' => 'listar']);

        $this->assertIsArray($pedidos, 'El listado de pedidos debe retornar un array de registros.');
    }
}
