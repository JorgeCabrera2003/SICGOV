<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Turno;
use ReflectionProperty;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class TurnoTest extends TestCase
{
    private function createTurnoMock(PDO&MockObject $mockPdo): Turno&MockObject
    {
        $mock = $this->getMockBuilder(Turno::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    private function getPrivateProperty(object $object, string $propertyName)
    {
        $ref = new ReflectionProperty($object, $propertyName);
        return $ref->getValue($object);
    }

    #[Test]
    public function testSettersTurnoAsignanPropiedades(): void
    {
        $turno = new Turno();
        $turno->setIdTurno('TUR0012026010112');
        $turno->setNombre('Turno Matutino');
        $turno->setHoraInicio('08:00:00');
        $turno->setHoraFin('16:00:00');
        $turno->setMinutoTolerancia(10);
        $turno->setEstatus(1);

        $this->assertEquals('TUR0012026010112', $turno->getIdTurno());
        $this->assertEquals('Turno Matutino', $turno->getNombre());
        $this->assertEquals('08:00:00', $this->getPrivateProperty($turno, 'hora_inicio'));
        $this->assertEquals('16:00:00', $this->getPrivateProperty($turno, 'hora_fin'));
        $this->assertEquals(10, $this->getPrivateProperty($turno, 'minuto_tolerancia'));
        $this->assertEquals(1, $this->getPrivateProperty($turno, 'estatus'));
    }

    #[Test]
    public function testPermiteTurnoNocturno(): void
    {
        $turno = new Turno();
        $turno->setHoraInicio('20:00');
        $turno->setHoraFin('06:00');

        $this->assertEquals('20:00:00', $this->getPrivateProperty($turno, 'hora_inicio'));
        $this->assertEquals('06:00:00', $this->getPrivateProperty($turno, 'hora_fin'));
    }

    #[Test]
    public function testRechazaHorasIgualesYToleranciaNegativa(): void
    {
        $turno = new Turno();
        $turno->setNombre('Turno');
        $turno->setHoraInicio('08:00');
        $turno->setHoraFin('08:00');

        try {
            $turno->Transaccion(['peticion' => 'registrar']);
            $this->fail('Se esperaba rechazar un turno sin duración.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('La hora de inicio y fin no pueden ser iguales.', $exception->getMessage());
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La tolerancia no puede ser negativa.');
        $turno->setMinutoTolerancia(-1);
    }

    #[Test]
    public function testRechazaHoraFueraDeRango(): void
    {
        $turno = new Turno();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La hora debe ser válida y usar el formato HH:MM.');
        $turno->setHoraInicio('25:00');
    }

    #[Test]
    public function testConsultarTurnosRetornaListaActivos(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(1);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_turno' => 'TUR01', 'nombre' => 'Turno Noche', 'hora_inicio' => '16:00:00', 'hora_fin' => '00:00:00']
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $turnoMock = $this->createTurnoMock($mockPdo);
        $res = $turnoMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testRegistrarTurnoInsertaExitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');
        $mockStmt->method('execute')->willReturn(true);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $turnoMock = $this->createTurnoMock($mockPdo);
        $turnoMock->setIdTurno('TUR01202601011200');
        $turnoMock->setNombre('Vespertino');
        $turnoMock->setHoraInicio('12:00:00');
        $turnoMock->setHoraFin('20:00:00');

        $res = $turnoMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(201, $res['response']['resultado']);
        $this->assertEquals('Turno registrado correctamente', $res['response']['mensaje']);
    }

    #[Test]
    public function testModificarTurnoActualizaExitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->stringContains('UPDATE turno SET'))
            ->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $turno = $this->createTurnoMock($mockPdo);
        $turno->setIdTurno('TUR0012026010112');
        $turno->setNombre('Turno Actualizado');
        $turno->setHoraInicio('09:00');
        $turno->setHoraFin('17:00');

        $resultado = $turno->Transaccion(['peticion' => 'modificar']);

        $this->assertSame(1, $resultado['estado']);
        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testEliminarTurnoDesactivaElRegistro(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->stringContains('UPDATE turno SET estatus = 0'))
            ->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $turno = $this->createTurnoMock($mockPdo);
        $turno->setIdTurno('TUR0012026010112');

        $resultado = $turno->Transaccion(['peticion' => 'eliminar']);

        $this->assertSame(1, $resultado['estado']);
        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testValidarTurnoRetornaRegistroExistente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->stringContains('estatus = 1'))
            ->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStmt->expects($this->once())->method('fetch')->willReturn([
            'id_turno' => 'TUR0012026010112',
            'nombre' => 'Turno Mañana',
        ]);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $turno = $this->createTurnoMock($mockPdo);
        $turno->setIdTurno('TUR0012026010112');

        $resultado = $turno->Transaccion(['peticion' => 'validar']);

        $this->assertSame(1, $resultado['bool']);
        $this->assertSame('Turno Mañana', $resultado['response']['registro']['nombre']);
    }

    #[Test]
    public function testErrorAlModificarTurnoRevierteLaTransaccion(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->willThrowException(new PDOException('Fallo al actualizar'));
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);
        $mockPdo->expects($this->never())->method('commit');

        $turno = $this->createTurnoMock($mockPdo);
        $turno->setIdTurno('TUR0012026010112');
        $turno->setNombre('Turno Mañana');
        $turno->setHoraInicio('08:00');
        $turno->setHoraFin('16:00');

        $resultado = $turno->Transaccion(['peticion' => 'modificar']);

        $this->assertSame(-1, $resultado['estado']);
        $this->assertSame(500, $resultado['HTTP_STATUS']['codigo']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $turno = new Turno();
        $res = $turno->Transaccion(['peticion' => 'hack_turno']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
