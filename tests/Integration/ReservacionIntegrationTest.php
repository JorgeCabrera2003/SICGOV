<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Models\System\Reservacion;
use App\Helpers\Helper;
use App\Core\Database;
use PDO;

/**
 * Pruebas de Integración para el Módulo de Reservaciones.
 * Interactúa directamente con la base de datos de pruebas (goobv-sistema-test).
 * Valida persistencia real, relaciones de mesa/cliente, reglas de capacidad y solapamiento.
 */
class ReservacionIntegrationTest extends TestCase
{
    private PDO $db;
    private string $idReservacionTest;
    private string $idReservacionConflicto;
    private string $cedulaClienteTest = 'V-99112233';
    private string $idMesaTest;
    private string $idAreaTest;
    private string $fechaFutura;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getConnection('business');

        // IDs válidos y determinísticos para integración
        $timestamp = date('YmdHis');
        $this->idReservacionTest = 'RES111' . $timestamp . '001';
        $this->idReservacionConflicto = 'RES222' . $timestamp . '002';
        $this->idMesaTest = 'MESA111' . $timestamp . '001';
        $this->idAreaTest = 'AREA111' . $timestamp . '001';

        // Fecha de prueba: mañana
        $this->fechaFutura = date('Y-m-d', strtotime('+1 day'));

        // Limpiar registros previos
        $this->limpiarDatosPrueba();

        // 1. Crear persona de prueba si no existe
        $stmtPer = $this->db->prepare("INSERT IGNORE INTO persona (cedula, nombre, apellido, correo, direccion) 
                                       VALUES (:cedula, 'ClienteTest', 'Integracion', 'clientetest@example.com', 'Calle Test')");
        $stmtPer->execute([':cedula' => $this->cedulaClienteTest]);

        // 2. Crear cliente de prueba si no existe
        $stmtCli = $this->db->prepare("INSERT IGNORE INTO cliente (cedula, estatus) VALUES (:cedula, 1)");
        $stmtCli->execute([':cedula' => $this->cedulaClienteTest]);

        // 3. Crear área de prueba
        $stmtArea = $this->db->prepare("INSERT IGNORE INTO area_mesa (id_area, nombre, estatus) 
                                        VALUES (:id_area, 'Área Integración', 1)");
        $stmtArea->execute([':id_area' => $this->idAreaTest]);

        // 4. Crear mesa de prueba con capacidad 4
        $stmtMesa = $this->db->prepare("INSERT IGNORE INTO mesa (id_mesa, id_area, numero_mesa, capacidad, estado, estatus) 
                                        VALUES (:id_mesa, :id_area, 888, 4, 'DISPONIBLE', 1)");
        $stmtMesa->execute([
            ':id_mesa' => $this->idMesaTest,
            ':id_area' => $this->idAreaTest
        ]);
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        if (isset($this->db)) {
            $this->db->exec("DELETE FROM asignacion_mesa WHERE id_reservacion IN ('{$this->idReservacionTest}', '{$this->idReservacionConflicto}')");
            $this->db->exec("DELETE FROM reservacion WHERE id_reservacion IN ('{$this->idReservacionTest}', '{$this->idReservacionConflicto}')");
            $this->db->exec("DELETE FROM mesa WHERE id_mesa = '{$this->idMesaTest}'");
            $this->db->exec("DELETE FROM area_mesa WHERE id_area = '{$this->idAreaTest}'");
            $this->db->exec("DELETE FROM cliente WHERE cedula = '{$this->cedulaClienteTest}'");
            $this->db->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaClienteTest}'");
        }
    }

    public function testRegistroYPersistenciaRealEnBaseDeDatos(): void
    {
        $reservacion = new Reservacion();
        $reservacion->setId($this->idReservacionTest);
        $reservacion->setCedulaCliente($this->cedulaClienteTest);
        $reservacion->setIdMesa($this->idMesaTest);
        $reservacion->setFecha($this->fechaFutura);
        $reservacion->setHora('13:00:00');
        $reservacion->setHoraFin('15:00:00');
        $reservacion->setEstado('PENDIENTE');
        $reservacion->setCantidadPersonas(2);

        $resultado = $reservacion->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado'], 'El estado de la transacción debe ser 1 (éxito)');
        $this->assertEquals(200, $resultado['response']['resultado']);

        // Verificación directa en base de datos real
        $stmt = $this->db->prepare("SELECT * FROM reservacion WHERE id_reservacion = :id");
        $stmt->execute([':id' => $this->idReservacionTest]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($fila, 'La reservación debe existir físicamente en la BD');
        $this->assertEquals($this->cedulaClienteTest, $fila['cedula_cliente']);
        $this->assertEquals($this->idMesaTest, $fila['id_mesa']);
        $this->assertEquals('PENDIENTE', $fila['estado']);
        $this->assertEquals(2, (int)$fila['cantidad_personas']);

        // Verificación de asignación de mesa en tabla relacional
        $stmtAsig = $this->db->prepare("SELECT * FROM asignacion_mesa WHERE id_reservacion = :id");
        $stmtAsig->execute([':id' => $this->idReservacionTest]);
        $filaAsig = $stmtAsig->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($filaAsig, 'Debe crearse el registro en asignacion_mesa');
        $this->assertEquals($this->idMesaTest, $filaAsig['id_mesa']);
    }

    public function testValidarRechazoPorCapacidadInsuficiente(): void
    {
        $reservacion = new Reservacion();
        $reservacion->setId($this->idReservacionTest);
        $reservacion->setCedulaCliente($this->cedulaClienteTest);
        $reservacion->setIdMesa($this->idMesaTest); // Capacidad de la mesa es 4
        $reservacion->setFecha($this->fechaFutura);
        $reservacion->setHora('13:00:00');
        $reservacion->setHoraFin('15:00:00');
        $reservacion->setCantidadPersonas(8); // Excede capacidad

        $validacion = $reservacion->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(-1, $validacion['estado']);
        $this->assertEquals(400, $validacion['response']['resultado']);
        $this->assertEquals('Capacidad insuficiente', $validacion['HTTP_STATUS']['mensaje']);
    }

    public function testDeteccionConflictoHorarioSolapadoReal(): void
    {
        // 1. Registrar una reservación real en la mesa de 14:00 a 16:00
        $res1 = new Reservacion();
        $res1->setId($this->idReservacionTest);
        $res1->setCedulaCliente($this->cedulaClienteTest);
        $res1->setIdMesa($this->idMesaTest);
        $res1->setFecha($this->fechaFutura);
        $res1->setHora('14:00:00');
        $res1->setHoraFin('16:00:00');
        $res1->setEstado('CONFIRMADA');
        $res1->setCantidadPersonas(2);
        $res1->Transaccion(['peticion' => 'registrar']);

        // 2. Intentar validar una segunda reservación para la misma mesa a las 15:00 - 17:00 (solapada)
        $res2 = new Reservacion();
        $res2->setId($this->idReservacionConflicto);
        $res2->setCedulaCliente($this->cedulaClienteTest);
        $res2->setIdMesa($this->idMesaTest);
        $res2->setFecha($this->fechaFutura);
        $res2->setHora('15:00:00');
        $res2->setHoraFin('17:00:00');
        $res2->setCantidadPersonas(2);

        $validacion = $res2->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(-1, $validacion['estado']);
        $this->assertEquals(409, $validacion['response']['resultado'], 'Debe retornar HTTP 409 por conflicto de horario');
        $this->assertStringContainsString('ya se encuentra reservada en el horario', $validacion['response']['mensaje']);
    }

    public function testModificarEstadoYHorarioEnBaseDeDatos(): void
    {
        // Insertar reservación base
        $reservacion = new Reservacion();
        $reservacion->setId($this->idReservacionTest);
        $reservacion->setCedulaCliente($this->cedulaClienteTest);
        $reservacion->setIdMesa($this->idMesaTest);
        $reservacion->setFecha($this->fechaFutura);
        $reservacion->setHora('12:00:00');
        $reservacion->setHoraFin('14:00:00');
        $reservacion->setEstado('PENDIENTE');
        $reservacion->setCantidadPersonas(2);
        $reservacion->Transaccion(['peticion' => 'registrar']);

        // Modificar a CONFIRMADA y nuevo horario
        $reservacion->setEstado('CONFIRMADA');
        $reservacion->setHora('18:00:00');
        $reservacion->setHoraFin('20:00:00');
        $reservacion->setCantidadPersonas(3);

        $resultadoMod = $reservacion->Transaccion(['peticion' => 'modificar']);

        $this->assertEquals(1, $resultadoMod['estado']);
        $this->assertEquals(200, $resultadoMod['response']['resultado']);

        // Verificar en BD
        $stmt = $this->db->prepare("SELECT estado, hora, hora_fin, cantidad_personas FROM reservacion WHERE id_reservacion = :id");
        $stmt->execute([':id' => $this->idReservacionTest]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('CONFIRMADA', $fila['estado']);
        $this->assertEquals('18:00:00', $fila['hora']);
        $this->assertEquals('20:00:00', $fila['hora_fin']);
        $this->assertEquals(3, (int)$fila['cantidad_personas']);
    }

    public function testValidarRechazoFechaPasada(): void
    {
        $reservacion = new Reservacion();
        $reservacion->setId($this->idReservacionTest);
        $reservacion->setCedulaCliente($this->cedulaClienteTest);
        $reservacion->setFecha('2022-01-01'); // Fecha en el pasado
        $reservacion->setHora('12:00:00');
        $reservacion->setHoraFin('14:00:00');

        $validacion = $reservacion->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(-1, $validacion['estado']);
        $this->assertEquals(400, $validacion['response']['resultado']);
        $this->assertStringContainsString('fechas pasadas', $validacion['response']['mensaje']);
    }

    public function testValidarRechazoClienteInexistente(): void
    {
        $reservacion = new Reservacion();
        $reservacion->setId($this->idReservacionTest);
        $reservacion->setCedulaCliente('V-99999998'); // Cédula no registrada
        $reservacion->setFecha($this->fechaFutura);
        $reservacion->setHora('12:00:00');
        $reservacion->setHoraFin('14:00:00');

        $validacion = $reservacion->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(-1, $validacion['estado']);
        $this->assertEquals(404, $validacion['response']['resultado']);
        $this->assertStringContainsString('cliente seleccionado no se encuentra registrado', $validacion['response']['mensaje']);
    }

    public function testEliminarReservacionYLimpiezaEnBaseDeDatos(): void
    {
        // Insertar
        $reservacion = new Reservacion();
        $reservacion->setId($this->idReservacionTest);
        $reservacion->setCedulaCliente($this->cedulaClienteTest);
        $reservacion->setIdMesa($this->idMesaTest);
        $reservacion->setFecha($this->fechaFutura);
        $reservacion->setHora('12:00:00');
        $reservacion->setHoraFin('14:00:00');
        $reservacion->setEstado('PENDIENTE');
        $reservacion->Transaccion(['peticion' => 'registrar']);

        // Eliminar
        $resElim = $reservacion->Transaccion(['peticion' => 'eliminar']);
        $this->assertEquals(1, $resElim['estado']);

        // Comprobar ausencia física en BD
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservacion WHERE id_reservacion = :id");
        $stmt->execute([':id' => $this->idReservacionTest]);
        $this->assertEquals(0, (int)$stmt->fetchColumn(), 'La reservación no debe existir en la BD tras ser eliminada');
    }
}
