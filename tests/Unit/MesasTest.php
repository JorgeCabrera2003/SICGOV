<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Mesas;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class MesasTest extends TestCase
{
    /**
     * Crea un mock parcial de Mesas para aislar completamente la base de datos.
     */
    private function createMesasMock(PDO&MockObject $mockPdo): Mesas&MockObject
    {
        $mock = $this->getMockBuilder(Mesas::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Puebla una mesa con datos válidos.
     */
    private function poblarMesaValida(Mesas $mesa): void
    {
        $mesa->setIdMesa('MESA20261025140000001');
        $mesa->setIdArea('AREA20261025140000001');
        $mesa->setNumeroMesa(12);
        $mesa->setCapacidad(4);
        $mesa->setEstado('DISPONIBLE');
        $mesa->setEstatus(1);
    }

    // =========================================================================
    // 1. SETTERS, VALIDACIONES Y FORMATOS
    // =========================================================================

    /**
     * Prueba validaciones de setters con valores fuera de rango o formatos inválidos.
     */
    public function test_setters_con_datos_invalidos_lanzan_excepcion(): void
    {
        $mesa = new Mesas();

        // 1. ID de mesa inválido
        try {
            $mesa->setIdMesa('id_totalmente_invalido!');
            $this->fail("Se esperaba Exception por ID inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El ID de la mesa no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. ID de área inválido
        try {
            $mesa->setIdArea('bad_area_id');
            $this->fail("Se esperaba Exception por ID de área inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El ID del área no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Número de mesa menor o igual a 0
        try {
            $mesa->setNumeroMesa(0);
            $this->fail("Se esperaba Exception por número de mesa 0.");
        } catch (Exception $e) {
            $this->assertEquals("El número de mesa debe ser un valor entre 1 y 999.", $e->getMessage());
        }

        // 4. Número de mesa mayor a 999
        try {
            $mesa->setNumeroMesa(1000);
            $this->fail("Se esperaba Exception por número de mesa 1000.");
        } catch (Exception $e) {
            $this->assertEquals("El número de mesa debe ser un valor entre 1 y 999.", $e->getMessage());
        }

        // 5. Capacidad menor a 1
        try {
            $mesa->setCapacidad(0);
            $this->fail("Se esperaba Exception por capacidad 0.");
        } catch (Exception $e) {
            $this->assertEquals("La capacidad debe ser de al menos 1 persona y máximo 50 personas.", $e->getMessage());
        }

        // 6. Capacidad mayor a 50
        try {
            $mesa->setCapacidad(51);
            $this->fail("Se esperaba Exception por capacidad 51.");
        } catch (Exception $e) {
            $this->assertEquals("La capacidad debe ser de al menos 1 persona y máximo 50 personas.", $e->getMessage());
        }

        // 7. Estado no permitido en el catálogo
        try {
            $mesa->setEstado('EN_REPARACION_EXTREMA');
            $this->fail("Se esperaba Exception por estado no válido.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Estado no válido", $e->getMessage());
        }

        // 8. Estatus distinto de 0 y 1
        try {
            $mesa->setEstatus(2);
            $this->fail("Se esperaba Exception por estatus 2.");
        } catch (Exception $e) {
            $this->assertEquals("El estatus debe ser 0 (inactivo) o 1 (activo).", $e->getMessage());
        }
    }

    /**
     * Prueba que los setters y getters conserven fielmente los datos asignados.
     */
    public function test_setters_y_getters_con_datos_validos(): void
    {
        $mesa = new Mesas();
        $this->poblarMesaValida($mesa);

        $this->assertEquals('MESA20261025140000001', $mesa->getIdMesa());
        $this->assertEquals('AREA20261025140000001', $mesa->getIdArea());
        $this->assertEquals(12, $mesa->getNumeroMesa());
        $this->assertEquals(4, $mesa->getCapacidad());
        $this->assertEquals('DISPONIBLE', $mesa->getEstado());
        $this->assertEquals(1, $mesa->getEstatus());
    }

    // =========================================================================
    // 2. DISPATCHER Y TRANSACCIONES
    // =========================================================================

    /**
     * Prueba que petición no válida en Transaccion retorne código 400.
     */
    public function test_transaccion_con_peticion_invalida_retorna_400(): void
    {
        $mesa = new Mesas();

        $res = $mesa->Transaccion(['peticion' => 'operacion_inexistente']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }

    /**
     * Prueba la consulta general de mesas con información de su área.
     */
    public function test_consultar_mesas_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockStm->expects($this->once())->method('execute')->willReturn(true);
        $mockStm->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStm->expects($this->once())->method('fetchAll')->willReturn([
            [
                'id_mesa' => 'MESA001',
                'numero_mesa' => 5,
                'capacidad' => 4,
                'estado' => 'DISPONIBLE',
                'area_nombre' => 'Terraza Principal'
            ]
        ]);

        $mesa = $this->createMesasMock($mockPdo);
        $res = $mesa->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
        $this->assertEquals(5, $res['response']['datos'][0]['numero_mesa']);
    }

    /**
     * Prueba el cambio de estado de una mesa ('DISPONIBLE', 'OCUPADA', etc.).
     */
    public function test_cambiar_estado_mesa_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockStm->expects($this->once())->method('execute')->willReturn(true);

        $mesa = $this->createMesasMock($mockPdo);
        $mesa->setIdMesa('MESA20261025140000001');
        $mesa->setEstado('OCUPADA');

        $res = $mesa->Transaccion(['peticion' => 'cambiar_estado']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertEquals('Estado de la mesa cambiado a OCUPADA', $res['response']['mensaje']);
    }

    /**
     * Prueba la desactivación/activación lógica (cambiar_estatus).
     */
    public function test_cambiar_estatus_mesa_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockStm->expects($this->once())->method('execute')->willReturn(true);

        $mesa = $this->createMesasMock($mockPdo);
        $mesa->setIdMesa('MESA20261025140000001');
        $mesa->setEstatus(0);

        $res = $mesa->Transaccion(['peticion' => 'cambiar_estatus']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertEquals('Estatus de la mesa cambiado exitosamente', $res['response']['mensaje']);
    }
}
