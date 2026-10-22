<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Reservacion;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class ReservacionTest extends TestCase
{
    /**
     * Crea un mock parcial de Reservacion para aislar la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createReservacionMock(PDO&MockObject $mockPdo): Reservacion&MockObject
    {
        $mock = $this->getMockBuilder(Reservacion::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna valores válidos a una instancia de Reservacion.
     * Se usa una fecha futura garantizada (año 2030) para evitar colisión con 'hoy'.
     */
    private function poblarReservacionValida(Reservacion $reservacion): void
    {
        $reservacion->setId('RESV20261025140000001');
        $reservacion->setCedulaCliente('V-12345678');
        $reservacion->setIdMesa('MESA20261025140000001');
        $reservacion->setFecha('2030-10-25');
        $reservacion->setHora('19:00');
        $reservacion->setHoraFin('21:00');
        $reservacion->setEstado('PENDIENTE');
        $reservacion->setCantidadPersonas(4);
    }

    // =========================================================================
    // 1. VALIDACIONES DE FORMATO Y SETTERS (Datos vacíos e incorrectos)
    // =========================================================================

    /**
     * Prueba que campos obligatorios vacíos o nulos disparen excepciones.
     */
    public function test_setters_con_datos_vacios_lanzan_excepcion(): void
    {
        $reservacion = new Reservacion();

        // 1. ID vacío
        try {
            $reservacion->setId('');
            $this->fail("Se esperaba Exception por ID vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El formato del ID de reservación no es válido.", $e->getMessage());
        }

        // 2. Cédula cliente vacía
        try {
            $reservacion->setCedulaCliente('');
            $this->fail("Se esperaba Exception por cédula vacía.");
        } catch (Exception $e) {
            $this->assertEquals("El formato de la cédula del cliente no es válido.", $e->getMessage());
        }

        // 3. Fecha vacía o formato inválido
        try {
            $reservacion->setFecha('');
            $this->fail("Se esperaba Exception por fecha vacía.");
        } catch (Exception $e) {
            $this->assertEquals("La fecha no tiene un formato válido (AAAA-MM-DD).", $e->getMessage());
        }

        // 4. Hora inicio vacía
        try {
            $reservacion->setHora('');
            $this->fail("Se esperaba Exception por hora vacía.");
        } catch (Exception $e) {
            $this->assertEquals("La hora de inicio no tiene un formato válido (HH:MM).", $e->getMessage());
        }

        // 5. Hora fin vacía
        try {
            $reservacion->setHoraFin('');
            $this->fail("Se esperaba Exception por hora fin vacía.");
        } catch (Exception $e) {
            $this->assertEquals("La hora de fin no tiene un formato válido (HH:MM).", $e->getMessage());
        }
    }

    /**
     * Prueba validaciones de formatos incorrectos, calendario real y límites de negocio.
     */
    public function test_setters_con_datos_incorrectos_fallan(): void
    {
        $reservacion = new Reservacion();

        // 1. Cédula con caracteres alfabéticos en el número
        try {
            $reservacion->setCedulaCliente('V-ABC999');
            $this->fail("Se esperaba Exception por cédula inválida.");
        } catch (Exception $e) {
            $this->assertEquals("El formato de la cédula del cliente no es válido.", $e->getMessage());
        }

        // 2. Normalización automática de cédula: '12345678' -> 'V-12345678'
        $reservacion->setCedulaCliente('12345678');
        $this->assertEquals('V-12345678', $reservacion->getCedulaCliente());

        // 3. Normalización automática de cédula sin guion: 'V25888999' -> 'V-25888999'
        $reservacion->setCedulaCliente('V25888999');
        $this->assertEquals('V-25888999', $reservacion->getCedulaCliente());

        // 4. Fecha inexistente en el calendario (31 de Febrero)
        try {
            $reservacion->setFecha('2026-02-31');
            $this->fail("Se esperaba Exception por fecha inexistente en el calendario.");
        } catch (Exception $e) {
            $this->assertEquals("La fecha no es una fecha válida en el calendario.", $e->getMessage());
        }

        // 5. Hora fuera de rango de 24 horas (ej: 25:00 o 12:65)
        try {
            $reservacion->setHora('25:00');
            $this->fail("Se esperaba Exception por hora mayor a 23:59.");
        } catch (Exception $e) {
            $this->assertEquals("La hora de inicio no tiene un formato válido (HH:MM).", $e->getMessage());
        }

        try {
            $reservacion->setHoraFin('12:65');
            $this->fail("Se esperaba Exception por minutos mayores a 59.");
        } catch (Exception $e) {
            $this->assertEquals("La hora de fin no tiene un formato válido (HH:MM).", $e->getMessage());
        }

        // 6. Estado no permitido en el enum
        try {
            $reservacion->setEstado('EN_ESPERA');
            $this->fail("Se esperaba Exception por estado no válido.");
        } catch (Exception $e) {
            $this->assertEquals("Estado de reservación no válido.", $e->getMessage());
        }

        // 7. Cantidad de personas menor a 1
        try {
            $reservacion->setCantidadPersonas(0);
            $this->fail("Se esperaba Exception por cantidad de personas 0.");
        } catch (Exception $e) {
            $this->assertEquals("La cantidad de personas debe ser entre 1 y 50.", $e->getMessage());
        }

        // 8. Cantidad de personas mayor a 50
        try {
            $reservacion->setCantidadPersonas(51);
            $this->fail("Se esperaba Exception por cantidad de personas mayor a 50.");
        } catch (Exception $e) {
            $this->assertEquals("La cantidad de personas debe ser entre 1 y 50.", $e->getMessage());
        }
    }

    /**
     * Prueba que los setters y getters preserven correctamente los datos asignados.
     */
    public function test_setters_y_getters_con_datos_correctos(): void
    {
        $reservacion = new Reservacion();
        $this->poblarReservacionValida($reservacion);

        $this->assertEquals('RESV20261025140000001', $reservacion->getId());
        $this->assertEquals('V-12345678', $reservacion->getCedulaCliente());
        $this->assertEquals('MESA20261025140000001', $reservacion->getIdMesa());
        $this->assertEquals('2030-10-25', $reservacion->getFecha());
        $this->assertEquals('19:00', $reservacion->getHora());
        $this->assertEquals('21:00', $reservacion->getHoraFin());
        $this->assertEquals('PENDIENTE', $reservacion->getEstado());
        $this->assertEquals(4, $reservacion->getCantidadPersonas());
    }

    // =========================================================================
    // 2. REGLAS DE NEGOCIO: Método ValidarReservacion (Transacción 'validar')
    // =========================================================================

    /**
     * Regla 1: Bloqueo de fechas pasadas para reservaciones no canceladas.
     */
    public function test_validar_reservacion_con_fecha_pasada_retorna_400(): void
    {
        $reservacion = new Reservacion();
        $this->poblarReservacionValida($reservacion);
        $reservacion->setFecha('2020-01-15'); // Fecha en el pasado

        $resultado = $reservacion->ValidarReservacion();

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(0, $resultado['bool']);
        $this->assertEquals(400, $resultado['response']['resultado']);
        $this->assertEquals('warning', $resultado['response']['icon']);
        $this->assertStringContainsString('fechas pasadas', $resultado['response']['mensaje']);
    }

    /**
     * Regla 2: La hora de fin debe ser estrictamente posterior a la hora de inicio.
     */
    public function test_validar_reservacion_con_rango_horario_invalido_retorna_400(): void
    {
        $reservacion = new Reservacion();
        $this->poblarReservacionValida($reservacion);

        // Caso A: Hora fin igual a hora inicio
        $reservacion->setHora('19:00');
        $reservacion->setHoraFin('19:00');
        $resA = $reservacion->ValidarReservacion();
        $this->assertEquals(400, $resA['response']['resultado']);
        $this->assertStringContainsString('hora de fin debe ser posterior', $resA['response']['mensaje']);

        // Caso B: Hora fin anterior a hora inicio
        $reservacion->setHora('20:00');
        $reservacion->setHoraFin('18:00');
        $resB = $reservacion->ValidarReservacion();
        $this->assertEquals(400, $resB['response']['resultado']);
        $this->assertStringContainsString('hora de fin debe ser posterior', $resB['response']['mensaje']);
    }

    /**
     * Regla 3: Si el cliente no existe en la base de datos, retorna 404.
     */
    public function test_validar_reservacion_cliente_inexistente_retorna_404(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCli = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmtCli);
        $mockStmtCli->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtCli->expects($this->once())->method('fetchColumn')->willReturn(0); // Cliente no existe

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $this->poblarReservacionValida($reservacionMock);

        $resultado = $reservacionMock->ValidarReservacion();

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(404, $resultado['response']['resultado']);
        $this->assertStringContainsString('no se encuentra registrado', $resultado['response']['mensaje']);
    }

    /**
     * Regla 4: Si se asigna mesa y ésta no existe en la base de datos, retorna 404.
     */
    public function test_validar_reservacion_mesa_inexistente_retorna_404(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCli = $this->createMock(PDOStatement::class);
        $mockStmMesa = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtCli, $mockStmMesa);

        // Cliente existe
        $mockStmtCli->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtCli->expects($this->once())->method('fetchColumn')->willReturn(1);

        // Mesa no existe
        $mockStmMesa->expects($this->once())->method('execute')->willReturn(true);
        $mockStmMesa->expects($this->once())->method('fetch')->willReturn(false);

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $this->poblarReservacionValida($reservacionMock);

        $resultado = $reservacionMock->ValidarReservacion();

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(404, $resultado['response']['resultado']);
        $this->assertStringContainsString('mesa seleccionada no existe', $resultado['response']['mensaje']);
    }

    /**
     * Regla 5: Capacidad insuficiente en la mesa para la cantidad de personas.
     */
    public function test_validar_reservacion_capacidad_mesa_insuficiente_retorna_400(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCli = $this->createMock(PDOStatement::class);
        $mockStmMesa = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtCli, $mockStmMesa);

        $mockStmtCli->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtCli->expects($this->once())->method('fetchColumn')->willReturn(1);

        // Mesa con capacidad para 2 personas
        $mockStmMesa->expects($this->once())->method('execute')->willReturn(true);
        $mockStmMesa->expects($this->once())->method('fetch')->willReturn([
            'id_mesa' => 'MESA20261025140000001',
            'numero_mesa' => 5,
            'capacidad' => 2,
            'nombre_area' => 'Salón Principal'
        ]);

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $this->poblarReservacionValida($reservacionMock);
        $reservacionMock->setCantidadPersonas(6); // 6 comensales > 2 de capacidad

        $resultado = $reservacionMock->ValidarReservacion();

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(400, $resultado['response']['resultado']);
        $this->assertStringContainsString('capacidad para 2 personas, pero la reservación es para 6', $resultado['response']['mensaje']);
    }

    /**
     * Regla 6: Conflicto de horario por solapamiento en la misma mesa (409 Conflict).
     */
    public function test_validar_reservacion_solapamiento_mesa_retorna_409(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCli = $this->createMock(PDOStatement::class);
        $mockStmMesa = $this->createMock(PDOStatement::class);
        $mockStmtDisp = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtCli, $mockStmMesa, $mockStmtDisp);

        $mockStmtCli->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtCli->expects($this->once())->method('fetchColumn')->willReturn(1);

        $mockStmMesa->expects($this->once())->method('execute')->willReturn(true);
        $mockStmMesa->expects($this->once())->method('fetch')->willReturn([
            'id_mesa' => 'MESA20261025140000001',
            'numero_mesa' => 8,
            'capacidad' => 4,
            'nombre_area' => 'Terraza'
        ]);

        // Ya existe 1 reservación solapada en esa mesa
        $mockStmtDisp->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtDisp->expects($this->once())->method('fetchColumn')->willReturn(1);

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $this->poblarReservacionValida($reservacionMock);

        $resultado = $reservacionMock->ValidarReservacion();

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(409, $resultado['response']['resultado']);
        $this->assertStringContainsString('ya se encuentra reservada en el horario especificado', $resultado['response']['mensaje']);
    }

    /**
     * Regla 7: Reservación exitosa y disponible devuelve 200 y bool = 1.
     */
    public function test_validar_reservacion_datos_correctos_y_disponibles_retorna_200(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCli = $this->createMock(PDOStatement::class);
        $mockStmMesa = $this->createMock(PDOStatement::class);
        $mockStmtDisp = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtCli, $mockStmMesa, $mockStmtDisp);

        $mockStmtCli->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtCli->expects($this->once())->method('fetchColumn')->willReturn(1);

        $mockStmMesa->expects($this->once())->method('execute')->willReturn(true);
        $mockStmMesa->expects($this->once())->method('fetch')->willReturn([
            'id_mesa' => 'MESA20261025140000001',
            'numero_mesa' => 8,
            'capacidad' => 6,
            'nombre_area' => 'Terraza'
        ]);

        // Sin solapamiento (0 reservaciones activas en ese horario)
        $mockStmtDisp->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtDisp->expects($this->once())->method('fetchColumn')->willReturn(0);

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $this->poblarReservacionValida($reservacionMock);

        $resultado = $reservacionMock->ValidarReservacion();

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(1, $resultado['bool']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('success', $resultado['response']['icon']);
    }

    // =========================================================================
    // 3. PERSISTENCIA: Registrar, Modificar, Eliminar
    // =========================================================================

    /**
     * Prueba registro exitoso con asignación de mesa en transacción SQL.
     */
    public function test_registrar_reservacion_con_mesa_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtRes = $this->createMock(PDOStatement::class);
        $mockStmtMesa = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtRes, $mockStmtMesa);

        $mockStmtRes->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtMesa->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $this->poblarReservacionValida($reservacionMock);

        $resultado = $reservacionMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(1, $resultado['bool']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Reservación registrada con éxito', $resultado['response']['mensaje']);
    }

    /**
     * Prueba registro exitoso sin mesa asignada (mesa null).
     */
    public function test_registrar_reservacion_sin_mesa_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtRes = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        // Solo 1 prepare: no se inserta en asignacion_mesa
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmtRes);
        $mockStmtRes->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $this->poblarReservacionValida($reservacionMock);
        $reservacionMock->setIdMesa(null); // Sin mesa

        $resultado = $reservacionMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
    }

    /**
     * Prueba que un fallo SQL al registrar aplique rollback y devuelva 500.
     */
    public function test_registrar_reservacion_falla_en_bd_aplica_rollback(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException("Error de conexión a la base de datos"));

        $mockPdo->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack');

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $this->poblarReservacionValida($reservacionMock);

        $resultado = $reservacionMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
    }

    /**
     * Prueba modificación exitosa de una reservación actualizando fecha, hora y mesa.
     */
    public function test_modificar_reservacion_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtUpdate = $this->createMock(PDOStatement::class);
        $mockStmtDelMesa = $this->createMock(PDOStatement::class);
        $mockStmtInsMesa = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtUpdate, $mockStmtDelMesa, $mockStmtInsMesa);

        $mockStmtUpdate->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtDelMesa->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtInsMesa->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $this->poblarReservacionValida($reservacionMock);
        $reservacionMock->setEstado('CONFIRMADA');

        $resultado = $reservacionMock->Transaccion(['peticion' => 'modificar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Reservación actualizada exitosamente', $resultado['response']['mensaje']);
    }

    /**
     * Prueba eliminación exitosa de una reservación.
     */
    public function test_eliminar_reservacion_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $reservacionMock->setId('RESV20261025140000001');

        $resultado = $reservacionMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Reservación eliminada exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 4. CONSULTAS: ListarEventos, ObtenerDetalle, ObtenerClientes
    // =========================================================================

    /**
     * Prueba listar reservaciones y formatearlas como eventos FullCalendar.
     */
    public function test_listar_eventos_formatea_estructura_fullcalendar(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('fetchAll')->willReturn([
            [
                'id_reservacion' => 'RES1',
                'cedula_cliente' => 'V-12345678',
                'fecha' => '2030-10-25',
                'hora' => '19:00:00',
                'hora_fin' => '21:00:00',
                'estado' => 'PENDIENTE',
                'cantidad_personas' => 4,
                'nombre' => 'María',
                'apellido' => 'González',
                'telefono' => '0414-1234567',
                'numero_mesa' => 3,
                'id_mesa' => 'MESA3',
                'capacidad' => 4,
                'area_nombre' => 'Terraza'
            ]
        ]);

        $reservacionMock = $this->createReservacionMock($mockPdo);

        $resultado = $reservacionMock->Transaccion(['peticion' => 'listar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertCount(1, $resultado['response']['datos']);

        $evento = $resultado['response']['datos'][0];
        $this->assertEquals('RES1', $evento['id']);
        $this->assertStringContainsString('María González', $evento['title']);
        $this->assertStringContainsString('(Mesa #3)', $evento['title']);
        $this->assertEquals('2030-10-25T19:00:00', $evento['start']);
        $this->assertEquals('2030-10-25T21:00:00', $evento['end']);
        $this->assertTrue($evento['editable']); // PENDIENTE es editable
        $this->assertEquals('status-pendiente', $evento['className']);
        $this->assertEquals(4, $evento['extendedProps']['cantidad_personas']);
    }

    /**
     * Prueba obtención de detalle de una reservación existente.
     */
    public function test_obtener_detalle_existente_retorna_200(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('fetch')->willReturn([
            'id_reservacion' => 'RESV20261025140000001',
            'nombre' => 'Carlos',
            'apellido' => 'Pérez',
            'estado' => 'PENDIENTE'
        ]);

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $reservacionMock->setId('RESV20261025140000001');

        $resultado = $reservacionMock->Transaccion(['peticion' => 'detalle']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertNotEmpty($resultado['response']['registro']);
        $this->assertEquals('Carlos', $resultado['response']['registro']['nombre']);
    }

    /**
     * Prueba obtención de detalle de una reservación inexistente (retorna 404).
     */
    public function test_obtener_detalle_inexistente_retorna_404(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('fetch')->willReturn(false);

        $reservacionMock = $this->createReservacionMock($mockPdo);
        $reservacionMock->setId('RESV999920261025000000000');

        $resultado = $reservacionMock->Transaccion(['peticion' => 'detalle']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(404, $resultado['response']['resultado']);
        $this->assertEquals('Reservación no encontrada', $resultado['response']['mensaje']);
    }

    /**
     * Prueba consulta de clientes disponibles para el selector de reservación.
     */
    public function test_obtener_clientes_retorna_listado(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('fetchAll')->willReturn([
            ['cedula' => 'V-11111111', 'nombre' => 'Ana', 'apellido' => 'Ramos'],
            ['cedula' => 'V-22222222', 'nombre' => 'Betania', 'apellido' => 'Suárez']
        ]);

        $reservacionMock = $this->createReservacionMock($mockPdo);

        $clientes = $reservacionMock->ObtenerClientes();

        $this->assertCount(2, $clientes);
        $this->assertEquals('Ana', $clientes[0]['nombre']);
    }
}
