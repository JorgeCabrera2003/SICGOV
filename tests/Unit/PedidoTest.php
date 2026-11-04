<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Pedido;
use App\Core\Database;
use PDO;
use PDOStatement;
use PDOException;
use Exception;
use ReflectionClass;

class PedidoTest extends TestCase
{
    /**
     * Inyecta conexiones mock en una instancia de Pedido usando reflexión.
     */
    private function injectConnections(Pedido $pedido, PDO $dbBusiness, ?PDO $dbSecurity = null): void
    {
        $reflection = new ReflectionClass($pedido);

        $propBusiness = $reflection->getProperty('dbBusiness');
        $propBusiness->setAccessible(true);
        $propBusiness->setValue($pedido, $dbBusiness);

        if ($dbSecurity !== null) {
            $propSecurity = $reflection->getProperty('dbSecurity');
            $propSecurity->setAccessible(true);
            $propSecurity->setValue($pedido, $dbSecurity);
        }
    }

    // =========================================================================
    // 1. DISPATCHER: TRANSACCION
    // =========================================================================

    /**
     * Verifica que una petición no válida en Transaccion dispare una Exception.
     */
    public function test_transaccion_con_peticion_no_valida_lanza_excepcion(): void
    {
        $pedido = new Pedido();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Petición no válida.");

        $pedido->Transaccion(['peticion' => 'operacion_inexistente']);
    }

    // =========================================================================
    // 2. MÁQUINA DE ESTADOS Y TRANSICIONES
    // =========================================================================

    /**
     * Prueba transiciones válidas desde el estado PENDIENTE:
     * - PENDIENTE -> CONFIRMADO
     * - PENDIENTE -> CANCELADO
     */
    public function test_transiciones_validas_desde_pendiente(): void
    {
        // 1. PENDIENTE -> CONFIRMADO
        $mockDb = $this->createMock(PDO::class);
        $mockDb->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockDb->expects($this->once())->method('commit')->willReturn(true);

        $mockStmEstado = $this->createMock(PDOStatement::class);
        $mockStmEstado->expects($this->once())->method('execute')->willReturn(true);
        $mockStmEstado->expects($this->once())->method('fetch')->willReturn(['estado' => 'PENDIENTE']);

        $mockStmUpdate = $this->createMock(PDOStatement::class);
        $mockStmUpdate->expects($this->once())->method('execute')->with(['CONFIRMADO', 'PED001'])->willReturn(true);

        // Stmt para verificar preparación (pedidoRequierePreparacion)
        $mockStmPrep = $this->createMock(PDOStatement::class);
        $mockStmPrep->expects($this->once())->method('execute')->willReturn(true);
        $mockStmPrep->expects($this->once())->method('fetch')->willReturn(['total' => 0]);

        $mockDb->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmEstado, $mockStmUpdate, $mockStmPrep);

        $pedido = new Pedido();
        $this->injectConnections($pedido, $mockDb);

        $res = $pedido->Transaccion([
            'peticion' => 'cambiar_estado',
            'id_pedido' => 'PED001',
            'estado' => 'CONFIRMADO'
        ]);

        $this->assertTrue($res['success']);
        $this->assertEquals('Estado actualizado correctamente', $res['message']);
    }

    /**
     * Prueba que transiciones no permitidas desde PENDIENTE sean rechazadas (Rollback y mensaje claro).
     * Por ejemplo: PENDIENTE -> ENTREGADO (no se puede saltar la preparación).
     */
    public function test_transicion_invalida_pendiente_a_entregado_es_rechazada(): void
    {
        $mockDb = $this->createMock(PDO::class);
        $mockDb->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockDb->expects($this->once())->method('rollBack')->willReturn(true);

        $mockStmEstado = $this->createMock(PDOStatement::class);
        $mockStmEstado->expects($this->once())->method('execute')->willReturn(true);
        $mockStmEstado->expects($this->once())->method('fetch')->willReturn(['estado' => 'PENDIENTE']);

        $mockDb->expects($this->once())->method('prepare')->willReturn($mockStmEstado);

        $pedido = new Pedido();
        $this->injectConnections($pedido, $mockDb);

        $res = $pedido->Transaccion([
            'peticion' => 'cambiar_estado',
            'id_pedido' => 'PED001',
            'estado' => 'ENTREGADO'
        ]);

        $this->assertFalse($res['success']);
        $this->assertStringContainsString("Transición no válida", $res['message']);
        $this->assertStringContainsString("No se puede pasar de 'PENDIENTE' a 'ENTREGADO'", $res['message']);
    }

    /**
     * Prueba que los estados terminales (PAGADO y CANCELADO) no permitan ninguna transición adicional.
     */
    public function test_estados_terminales_pagado_y_cancelado_no_permiten_cambios(): void
    {
        // 1. Caso PAGADO -> CANCELADO (No permitido)
        $mockDb1 = $this->createMock(PDO::class);
        $mockDb1->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockDb1->expects($this->once())->method('rollBack')->willReturn(true);

        $mockStm1 = $this->createMock(PDOStatement::class);
        $mockStm1->expects($this->once())->method('execute')->willReturn(true);
        $mockStm1->expects($this->once())->method('fetch')->willReturn(['estado' => 'PAGADO']);
        $mockDb1->expects($this->once())->method('prepare')->willReturn($mockStm1);

        $pedido1 = new Pedido();
        $this->injectConnections($pedido1, $mockDb1);

        $res1 = $pedido1->Transaccion([
            'peticion' => 'cambiar_estado',
            'id_pedido' => 'PED001',
            'estado' => 'CANCELADO'
        ]);

        $this->assertFalse($res1['success']);
        $this->assertStringContainsString("No se puede pasar de 'PAGADO' a 'CANCELADO'", $res1['message']);

        // 2. Caso CANCELADO -> PENDIENTE (No permitido)
        $mockDb2 = $this->createMock(PDO::class);
        $mockDb2->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockDb2->expects($this->once())->method('rollBack')->willReturn(true);

        $mockStm2 = $this->createMock(PDOStatement::class);
        $mockStm2->expects($this->once())->method('execute')->willReturn(true);
        $mockStm2->expects($this->once())->method('fetch')->willReturn(['estado' => 'CANCELADO']);
        $mockDb2->expects($this->once())->method('prepare')->willReturn($mockStm2);

        $pedido2 = new Pedido();
        $this->injectConnections($pedido2, $mockDb2);

        $res2 = $pedido2->Transaccion([
            'peticion' => 'cambiar_estado',
            'id_pedido' => 'PED002',
            'estado' => 'PENDIENTE'
        ]);

        $this->assertFalse($res2['success']);
        $this->assertStringContainsString("No se puede pasar de 'CANCELADO' a 'PENDIENTE'", $res2['message']);
    }

    /**
     * Prueba que un estado actual corrupto o no reconocido sea rechazado inmediatamente.
     */
    public function test_estado_actual_no_reconocido_retorna_error(): void
    {
        $mockDb = $this->createMock(PDO::class);
        $mockDb->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockDb->expects($this->once())->method('rollBack')->willReturn(true);

        $mockStm = $this->createMock(PDOStatement::class);
        $mockStm->expects($this->once())->method('execute')->willReturn(true);
        $mockStm->expects($this->once())->method('fetch')->willReturn(['estado' => 'ESTADO_FANTASMA']);
        $mockDb->expects($this->once())->method('prepare')->willReturn($mockStm);

        $pedido = new Pedido();
        $this->injectConnections($pedido, $mockDb);

        $res = $pedido->Transaccion([
            'peticion' => 'cambiar_estado',
            'id_pedido' => 'PED001',
            'estado' => 'CONFIRMADO'
        ]);

        $this->assertFalse($res['success']);
        $this->assertStringContainsString("Estado actual 'ESTADO_FANTASMA' no reconocido", $res['message']);
    }

    // =========================================================================
    // 3. REGLA DE NEGOCIO: LIBERACIÓN DE MESAS
    // =========================================================================

    /**
     * Prueba que al cambiar un pedido tipo 'MESA' al estado 'ENTREGADO',
     * la mesa asignada se libere automáticamente (estado 'DISPONIBLE').
     */
    public function test_cambiar_a_entregado_libera_mesa_si_es_pedido_de_mesa(): void
    {
        $mockDb = $this->createMock(PDO::class);
        $mockDb->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockDb->expects($this->once())->method('commit')->willReturn(true);

        // 1. Obtener estado actual (LISTO)
        $mockStmEstado = $this->createMock(PDOStatement::class);
        $mockStmEstado->expects($this->once())->method('execute')->willReturn(true);
        $mockStmEstado->expects($this->once())->method('fetch')->willReturn(['estado' => 'LISTO']);

        // 2. Update estado pedido a ENTREGADO
        $mockStmUpdate = $this->createMock(PDOStatement::class);
        $mockStmUpdate->expects($this->once())->method('execute')->willReturn(true);

        // 3. pedidoRequierePreparacion
        $mockStmPrep = $this->createMock(PDOStatement::class);
        $mockStmPrep->expects($this->once())->method('execute')->willReturn(true);
        $mockStmPrep->expects($this->once())->method('fetch')->willReturn(['total' => 0]);

        // 4. Info pedido para verificar si es MESA
        $mockStmInfo = $this->createMock(PDOStatement::class);
        $mockStmInfo->expects($this->once())->method('execute')->willReturn(true);
        $mockStmInfo->expects($this->once())->method('fetch')->willReturn([
            'tipo_pedido' => 'MESA',
            'id_mesa' => 'MESA_005'
        ]);

        // 5. UPDATE mesa SET estado = 'DISPONIBLE'
        $mockStmLiberar = $this->createMock(PDOStatement::class);
        $mockStmLiberar->expects($this->once())->method('execute')->with(['MESA_005'])->willReturn(true);

        $mockDb->expects($this->exactly(5))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmEstado, $mockStmUpdate, $mockStmPrep, $mockStmInfo, $mockStmLiberar);

        $pedido = new Pedido();
        $this->injectConnections($pedido, $mockDb);

        $res = $pedido->Transaccion([
            'peticion' => 'cambiar_estado',
            'id_pedido' => 'PED001',
            'estado' => 'ENTREGADO'
        ]);

        $this->assertTrue($res['success']);
    }

    /**
     * Prueba que pedidos de tipo DELIVERY o PARA LLEVAR no ejecuten consulta de liberación de mesa.
     */
    public function test_cambiar_a_entregado_en_delivery_no_libera_mesa(): void
    {
        $mockDb = $this->createMock(PDO::class);
        $mockDb->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockDb->expects($this->once())->method('commit')->willReturn(true);

        // 1. Obtener estado actual (LISTO)
        $mockStmEstado = $this->createMock(PDOStatement::class);
        $mockStmEstado->expects($this->once())->method('execute')->willReturn(true);
        $mockStmEstado->expects($this->once())->method('fetch')->willReturn(['estado' => 'LISTO']);

        // 2. Update estado pedido
        $mockStmUpdate = $this->createMock(PDOStatement::class);
        $mockStmUpdate->expects($this->once())->method('execute')->willReturn(true);

        // 3. pedidoRequierePreparacion
        $mockStmPrep = $this->createMock(PDOStatement::class);
        $mockStmPrep->expects($this->once())->method('execute')->willReturn(true);
        $mockStmPrep->expects($this->once())->method('fetch')->willReturn(['total' => 0]);

        // 4. Info pedido: tipo DELIVERY
        $mockStmInfo = $this->createMock(PDOStatement::class);
        $mockStmInfo->expects($this->once())->method('execute')->willReturn(true);
        $mockStmInfo->expects($this->once())->method('fetch')->willReturn([
            'tipo_pedido' => 'DELIVERY',
            'id_mesa' => null
        ]);

        // No hay 5to prepare porque no se libera mesa
        $mockDb->expects($this->exactly(4))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmEstado, $mockStmUpdate, $mockStmPrep, $mockStmInfo);

        $pedido = new Pedido();
        $this->injectConnections($pedido, $mockDb);

        $res = $pedido->Transaccion([
            'peticion' => 'cambiar_estado',
            'id_pedido' => 'PED_DELIVERY',
            'estado' => 'ENTREGADO'
        ]);

        $this->assertTrue($res['success']);
    }

    // =========================================================================
    // 4. GESTIÓN DE COMPROBANTES DE PAGO (Transacción 'obtener_comprobante')
    // =========================================================================

    /**
     * Prueba que cuando un pedido no tenga ningún pago asociado, se retorne false con mensaje descriptivo.
     */
    public function test_obtener_comprobante_sin_pago_asociado_retorna_error(): void
    {
        $mockDbBusiness = $this->createMock(PDO::class);
        $mockStmPago = $this->createMock(PDOStatement::class);

        $mockStmPago->expects($this->once())->method('execute')->with(['PED_SIN_PAGO'])->willReturn(true);
        $mockStmPago->expects($this->once())->method('fetch')->willReturn(false); // No hay pago

        $mockDbBusiness->expects($this->once())->method('prepare')->willReturn($mockStmPago);

        $pedido = new Pedido();
        $this->injectConnections($pedido, $mockDbBusiness);

        $res = $pedido->Transaccion([
            'peticion' => 'obtener_comprobante',
            'id_pedido' => 'PED_SIN_PAGO'
        ]);

        $this->assertFalse($res['success']);
        $this->assertEquals('No hay pago registrado para este pedido.', $res['message']);
    }

    /**
     * Prueba la obtención exitosa de la URL del comprobante desde la BD de seguridad.
     */
    public function test_obtener_comprobante_existente_retorna_url(): void
    {
        $mockDbBusiness = $this->createMock(PDO::class);
        $mockDbSecurity = $this->createMock(PDO::class);

        // Pago encontrado en Business
        $mockStmPago = $this->createMock(PDOStatement::class);
        $mockStmPago->expects($this->once())->method('execute')->with(['PED_CON_PAGO'])->willReturn(true);
        $mockStmPago->expects($this->once())->method('fetch')->willReturn(['id_pago' => 'PAG_001']);
        $mockDbBusiness->expects($this->once())->method('prepare')->willReturn($mockStmPago);

        // Imagen encontrada en Security
        $mockStmImg = $this->createMock(PDOStatement::class);
        $mockStmImg->expects($this->once())->method('execute')->willReturn(true);
        $mockStmImg->expects($this->once())->method('fetch')->willReturn(['direccion' => 'uploads/pagos/comprobante123.jpg']);
        $mockDbSecurity->expects($this->once())->method('prepare')->willReturn($mockStmImg);

        $pedido = new Pedido();
        $this->injectConnections($pedido, $mockDbBusiness, $mockDbSecurity);

        $res = $pedido->Transaccion([
            'peticion' => 'obtener_comprobante',
            'id_pedido' => 'PED_CON_PAGO'
        ]);

        $this->assertTrue($res['success']);
        $this->assertEquals('uploads/pagos/comprobante123.jpg', $res['url']);
    }

    // =========================================================================
    // 5. BÚSQUEDA DE PEDIDO Y DETALLES
    // =========================================================================

    /**
     * Prueba que buscar un pedido no existente retorne false.
     */
    public function test_buscar_pedido_inexistente_retorna_false(): void
    {
        $mockDbBusiness = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockStm->expects($this->once())->method('execute')->with(['PED_NO_EXISTE'])->willReturn(true);
        $mockStm->expects($this->once())->method('fetch')->willReturn(false);

        $mockDbBusiness->expects($this->once())->method('prepare')->willReturn($mockStm);

        $pedido = new Pedido();
        $this->injectConnections($pedido, $mockDbBusiness);

        $res = $pedido->Transaccion([
            'peticion' => 'buscar',
            'id_pedido' => 'PED_NO_EXISTE'
        ]);

        $this->assertFalse($res);
    }
}
