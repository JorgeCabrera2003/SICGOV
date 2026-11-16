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
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $horario = new Horario();
        $res = $horario->Transaccion(['peticion' => 'peticion_invalida']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
