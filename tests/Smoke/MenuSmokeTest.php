<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\System\Menu;

/**
 * Pruebas de Humo: Módulo Menú
 *
 * Reglas de oro:
 * 1. Ultra-rápidas (< 500 ms).
 * 2. Prueban la ruta crítica del catálogo de menú gastronómico contra la base de datos de pruebas.
 * 3. Si falla, el catálogo de platillos del menú está inoperativo.
 */
class MenuSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
    }

    /**
     * Comprueba que el módulo de Menú pueda listar los productos gastronómicos base.
     */
    public function test_humo_menu_catalogo_productos_activo(): void
    {
        $menuModel = new Menu();
        $resultado = $menuModel->Transaccion(['peticion' => 'listar']);

        $this->assertIsArray($resultado, 'El catálogo del menú debe responder con un array de productos.');
    }
}
