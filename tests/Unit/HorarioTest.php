<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Horario;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class HorarioTest extends TestCase
{
    private function createHorarioMock(PDO&MockObject $mockPdo): Horario&MockObject
    {
        $mock = $this->getMockBuilder(Horario::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetIdValidoEInvalido(): void
    {
        $horario = new Horario();
        $horario->setId('HORA0012026010112');
        $this->assertEquals('HORA0012026010112', $horario->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID del Horario no cumple con el formato permitido.');
        $horario->setId('id-invalido');
    }

    #[Test]
    public function testSetCedulaEmpleadoValidaEInvalida(): void
    {
        $horario = new Horario();
        $horario->setCedulaEmpleado('V-12345678');
        $this->assertEquals('V-12345678', $horario->getCedulaEmpleado());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La cédula del empleado no es válida.');
        $horario->setCedulaEmpleado('V-1'); // Menos de 5 caracteres
    }

    #[Test]
    public function testSetFechaValidaEInvalida(): void
    {
        $horario = new Horario();
        $horario->setFecha('2026-11-20');
        // No hay getter directo para fecha, pero no lanza excepción

        // Fecha con formato incorrecto
        try {
            $horario->setFecha('20-11-2026');
            $this->fail("Debió fallar por formato YYYY-MM-DD");
        } catch (Exception $e) {
            $this->assertStringContainsString('El formato de fecha no es válido', $e->getMessage());
        }

        // Fecha calendario inexistente (ej. 30 de febrero)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La fecha proporcionada no es una fecha real.');
        $horario->setFecha('2026-02-30');
    }

    #[Test]
    public function testRegistrarAsignacionDuplicadaRetorna409(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtValidar = $this->createMock(PDOStatement::class);

        $mockStmtValidar->method('execute')->willReturn(true);
        $mockStmtValidar->method('rowCount')->willReturn(1); // Ya existe en la fecha
        $mockStmtValidar->method('fetch')->willReturn(['id_planificador_turno' => 'HORA01']);

        $mockPdo->method('prepare')->willReturn($mockStmtValidar);
        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $horarioMock = $this->createHorarioMock($mockPdo);
        $horarioMock->setId('HORA0012026010112');
        $horarioMock->setCedulaEmpleado('V-12345678');
        $horarioMock->setIdTurno('TUR0012026010112');
        $horarioMock->setFecha('2026-11-25');

        $res = $horarioMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $res['estado']);
        $this->assertEquals(409, $res['response']['resultado']);
        $this->assertEquals(409, $res['HTTP_STATUS']['codigo']);
    }

    #[Test]
    public function testRegistrarNoInsertaSiFallaValidarAsignacion(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Error de validación'));
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);
        $mockPdo->expects($this->never())->method('commit');

        $horario = $this->createHorarioMock($mockPdo);
        $horario->setId('HORA0012026010112');
        $horario->setCedulaEmpleado('V-12345678');
        $horario->setIdTurno('TUR0012026010112');
        $horario->setFecha('2026-11-25');

        $resultado = $horario->Transaccion(['peticion' => 'registrar']);

        $this->assertSame(-1, $resultado['estado']);
        $this->assertSame(500, $resultado['response']['resultado']);
    }

    #[Test]
    public function testRegistrarAsignacionNuevaExitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockValidacion = $this->createMock(PDOStatement::class);
        $mockInsert = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->exactly(2))->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('prepare')
            ->willReturnOnConsecutiveCalls($mockValidacion, $mockInsert);
        $mockValidacion->expects($this->once())->method('execute')->willReturn(true);
        $mockValidacion->expects($this->once())->method('rowCount')->willReturn(0);
        $mockInsert->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('commit')->willReturn(true);

        $horario = $this->createHorarioMock($mockPdo);
        $horario->setId('HORA0012026010112');
        $horario->setCedulaEmpleado('V-12345678');
        $horario->setIdTurno('TUR0012026010112');
        $horario->setFecha('2026-11-25');

        $resultado = $horario->Transaccion(['peticion' => 'registrar']);

        $this->assertSame(1, $resultado['estado']);
        $this->assertSame(201, $resultado['response']['resultado']);
    }

    #[Test]
    public function testConsultarAsignacionesAplicaFiltros(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->callback(function (string $sql): bool {
                $this->assertStringContainsString('pt.cedula_empleado = :cedula', $sql);
                $this->assertStringContainsString('pt.fecha BETWEEN :fecha_inicio AND :fecha_fin', $sql);
                return true;
            }))
            ->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStmt->expects($this->once())->method('fetchAll')->willReturn([
            ['id_planificador_turno' => 'HORA0012026010112', 'fecha' => '2026-11-25'],
        ]);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $horario = $this->createHorarioMock($mockPdo);
        $resultado = $horario->Transaccion([
            'peticion' => 'consultar',
            'filtros' => [
                'cedula_empleado' => 'V-12345678',
                'fecha_inicio' => '2026-11-01',
                'fecha_fin' => '2026-11-30',
            ],
        ]);

        $this->assertSame(200, $resultado['response']['resultado']);
        $this->assertCount(1, $resultado['response']['datos']);
    }

    #[Test]
    public function testModificarAsignacionConCedulaRetornaExito(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->callback(function (string $sql): bool {
                $this->assertStringContainsString('AND cedula_empleado = :cedula_empleado', $sql);
                return true;
            }))
            ->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(1);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $horario = $this->createHorarioMock($mockPdo);
        $horario->setId('HORA0012026010112');
        $horario->setIdTurno('TUR0022026010112');

        $resultado = $horario->Transaccion([
            'peticion' => 'modificar',
            'cedula_empleado' => 'V-12345678',
        ]);

        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testEliminarAsignacionInexistenteRetorna404(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(0);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $horario = $this->createHorarioMock($mockPdo);
        $horario->setId('HORA0012026010112');

        $resultado = $horario->Transaccion(['peticion' => 'eliminar']);

        $this->assertSame(404, $resultado['response']['resultado']);
    }

    #[Test]
    public function testEliminarAsignacionExistenteRetornaExito(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->stringContains('DELETE FROM planificador_turno'))
            ->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(1);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $horario = $this->createHorarioMock($mockPdo);
        $horario->setId('HORA0012026010112');

        $resultado = $horario->Transaccion(['peticion' => 'eliminar']);

        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $horario = new Horario();
        $res = $horario->Transaccion(['peticion' => 'peticion_invalida']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }

}
