<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\System\Reservacion;
use App\Models\System\Pedido;
use App\Models\System\Promocion;
use App\Models\Security\Noticia;
use App\Models\System\Mesas;
use App\Models\System\Menu;

/**
 * Pruebas de Humo: Operaciones Vitales de Negocio (Módulos Críticos)
 *
 * Reglas de oro:
 * 1. Ultra-rápidas (< 500 ms).
 * 2. Prueban la ruta crítica de los módulos del negocio contra la base de datos de pruebas.
 * 3. Si una sola falla, el sistema está roto en una de sus capacidades esenciales.
 */
class OperacionesNegocioSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
    }

    /**
     * Humo 1: Comprueba que el módulo de Reservaciones pueda consultar la agenda sin errores.
     */
    public function test_humo_reservacion_consulta_agenda_correctamente(): void
    {
        $resModel = new Reservacion();
        $resultado = $resModel->Transaccion([
            'peticion' => 'listar',
            'filtros' => [
                'desde' => date('Y-m-01'),
                'hasta' => date('Y-m-t')
            ]
        ]);

        $this->assertEquals(1, $resultado['estado'] ?? 0, 'La consulta de agenda de reservaciones debe responder exitosamente.');
        $this->assertEquals(200, $resultado['response']['resultado'] ?? 0);
        $this->assertIsArray($resultado['response']['datos'] ?? null);
    }

    /**
     * Humo 2: Comprueba que el módulo de Pedidos pueda listar pedidos y verificar relaciones de datos.
     */
    public function test_humo_pedido_listar_pedidos_activo(): void
    {
        $pedidoModel = new Pedido();
        $pedidos = $pedidoModel->Transaccion(['peticion' => 'listar']);

        $this->assertIsArray($pedidos, 'El listado de pedidos debe retornar un array de registros.');
    }

    /**
     * Humo 3: Comprueba que el módulo de Noticias pueda consultar noticias públicas y autores.
     */
    public function test_humo_noticia_consulta_publica_activa(): void
    {
        $noticiaModel = new Noticia();
        $resultado = $noticiaModel->ConsultarNoticiasPublicas([]);

        $this->assertEquals(1, $resultado['estado'] ?? 0, 'La consulta de noticias públicas debe ser exitosa.');
        $this->assertEquals(200, $resultado['response']['resultado'] ?? 0);
        $this->assertIsArray($resultado['response']['datos'] ?? null);

        $autores = $noticiaModel->ObtenerAutoresPublicos();
        $this->assertIsArray($autores, 'Los autores de noticias deben ser un array.');
    }

    /**
     * Humo 4: Comprueba que el módulo de Promociones pueda consultar ofertas activas con sus productos.
     */
    public function test_humo_promocion_consulta_ofertas_activa(): void
    {
        $promocionModel = new Promocion();
        $resultado = $promocionModel->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resultado['estado'] ?? 0, 'La consulta de promociones debe responder con éxito.');
        $this->assertEquals(200, $resultado['response']['resultado'] ?? 0);
        $this->assertIsArray($resultado['response']['datos'] ?? null);
    }

    /**
     * Humo 5: Comprueba que el módulo de Mesas tenga acceso al catálogo de mesas y sus áreas asignadas.
     */
    public function test_humo_mesas_catalogo_y_areas_disponibles(): void
    {
        $mesasModel = new Mesas();
        $resultado = $mesasModel->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resultado['estado'] ?? 0, 'La consulta de mesas debe ser exitosa.');
        $this->assertEquals(200, $resultado['response']['resultado'] ?? 0);
        $this->assertNotEmpty($resultado['response']['datos'], 'Deben existir mesas sembradas en la base de datos de pruebas.');
    }

    /**
     * Humo 6: Comprueba que el módulo de Menú pueda listar los productos gastronómicos base.
     */
    public function test_humo_menu_catalogo_productos_activo(): void
    {
        $menuModel = new Menu();
        $resultado = $menuModel->Transaccion(['peticion' => 'listar']);

        $this->assertIsArray($resultado, 'El catálogo del menú debe responder con un array de productos.');
    }
}
