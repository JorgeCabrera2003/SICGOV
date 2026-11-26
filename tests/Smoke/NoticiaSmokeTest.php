<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\Security\Noticia;

/**
 * Pruebas de Humo: Módulo Noticias
 *
 * Reglas de oro:
 * 1. Ultra-rápidas (< 500 ms).
 * 2. Prueban la ruta crítica del módulo de Noticias contra la base de datos de pruebas.
 * 3. Si falla, el portal de noticias públicas está inoperativo.
 */
class NoticiaSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
    }

    /**
     * Comprueba que el módulo de Noticias pueda consultar noticias públicas y autores.
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
}
