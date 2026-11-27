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
