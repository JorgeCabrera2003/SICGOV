<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Models\System\Reservacion;

/**
 * Pruebas de Humo: Módulo Reservaciones
 *
 * Reglas de oro:
 * 1. Ultra-rápidas (< 500 ms).
 * 2. Prueban la ruta crítica del módulo de Reservaciones contra la base de datos de pruebas.
 * 3. Si falla, el subsistema de reservaciones está inoperativo.
 */
class ReservacionSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
    }

    /**
     * Comprueba que el módulo de Reservaciones pueda consultar la agenda sin errores.
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
}
