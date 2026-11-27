<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Asistencia;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class AsistenciaTest extends TestCase
{
    private function createAsistenciaMock(PDO&MockObject $mockPdo): Asistencia&MockObject
    {
        $mock = $this->getMockBuilder(Asistencia::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }


    #[Test]
    public function testSetCedulaEmpleadoInvalidaLanzaExcepcion(): void
    {
        $asistencia = new Asistencia();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La cédula debe tener un prefijo válido');
        $asistencia->setCedulaEmpleado('CEDULA_MALICIOSA');
    }

    #[Test]
    public function testValidarAsistenciaRechazaDatosIncompletos(): void
    {
        $asistencia = new Asistencia();
        $res = $asistencia->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(0, $res['estado']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals('Datos de marcación incompletos.', $res['response']['mensaje']);
    }

    #[Test]
    public function testValidarAsistenciaAceptaEmpleadoActivoYSecuenciaValida(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockEmpleado = $this->createMock(PDOStatement::class);
        $mockMarcaciones = $this->createMock(PDOStatement::class);
        $mockEmpleado->expects($this->once())->method('execute')->willReturn(true);
        $mockEmpleado->expects($this->once())->method('fetchColumn')->willReturn(1);
        $mockMarcaciones->expects($this->once())->method('execute')->willReturn(true);
        $mockMarcaciones->expects($this->once())->method('fetchAll')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn([]);
        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockEmpleado, $mockMarcaciones);

        $asistencia = $this->createAsistenciaMock($mockPdo);
        $asistencia->setCedulaEmpleado('V-12345678');
        $asistencia->setFecha('2026-11-25');
        $asistencia->setTipoMarcacion('ENTRADA');

        $resultado = $asistencia->Transaccion(['peticion' => 'validar']);

        $this->assertSame(1, $resultado['estado']);
        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testEntradaRespetaHoraDeInicioYToleranciaDelTurno(): void
    {
        $asistencia = new Asistencia();
        $turno = [
            'hora_inicio' => '08:00:00',
            'hora_fin' => '16:00:00',
            'minuto_tolerancia' => 10,
        ];

        $this->assertSame(
            'A_TIEMPO',
            $asistencia->calcularEstadoMarcacion('ENTRADA', '08:10:00', $turno, '2026-11-25', new \DateTimeImmutable('2026-11-25 08:10:00'))
        );
        $this->assertSame(
            'TARDE',
            $asistencia->calcularEstadoMarcacion('ENTRADA', '08:11:00', $turno, '2026-11-25', new \DateTimeImmutable('2026-11-25 08:11:00'))
        );
    }

    #[Test]
    public function testSalidaAnticipadaTieneEstadoPropioYNoSeMarcaComoFalta(): void
    {
        $asistencia = new Asistencia();
        $turno = [
            'hora_inicio' => '08:00:00',
            'hora_fin' => '16:00:00',
            'minuto_tolerancia' => 15,
        ];

        $this->assertSame(
            'SALIDA_ANTICIPADA',
            $asistencia->calcularEstadoMarcacion('SALIDA', '15:00:00', $turno, '2026-11-25', new \DateTimeImmutable('2026-11-25 15:00:00'))
        );
        $this->assertSame(
            'A_TIEMPO',
            $asistencia->calcularEstadoMarcacion('SALIDA', '15:45:00', $turno, '2026-11-25', new \DateTimeImmutable('2026-11-25 15:45:00'))
        );

        $turnoNocturno = [
            'hora_inicio' => '22:00:00',
            'hora_fin' => '06:00:00',
            'minuto_tolerancia' => 15,
        ];
        $this->assertSame(
            'SALIDA_ANTICIPADA',
            $asistencia->calcularEstadoMarcacion('SALIDA', '02:00:00', $turnoNocturno, '2026-11-25', new \DateTimeImmutable('2026-11-26 02:00:00'))
        );
        $this->assertSame(
            'A_TIEMPO',
            $asistencia->calcularEstadoMarcacion('SALIDA', '05:45:00', $turnoNocturno, '2026-11-25', new \DateTimeImmutable('2026-11-26 05:45:00'))
        );
    }

    #[Test]
    public function testNoPermiteCerrarDescansoSinIniciarloNiMarcarDespuesDeSalida(): void
    {
        try {
            Asistencia::validarSecuenciaMarcacion(['ENTRADA'], 'DESCANSO_OUT');
            $this->fail('Se esperaba que rechace terminar un descanso no iniciado.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('iniciar el descanso', $e->getMessage());
        }

        $this->expectException(\DomainException::class);
        Asistencia::validarSecuenciaMarcacion(['ENTRADA', 'SALIDA'], 'DESCANSO_IN');
    }

    #[Test]
    public function testConsultarAsistenciaRetornaLista(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(1);
        $mockStmt->method('fetchAll')->willReturn([
            [
                'id_asistencia' => 'ASIS01',
                'cedula_empleado' => 'V-12345678',
                'tipo_marcacion' => 'ENTRADA',
                'fecha' => '2026-11-20',
                'hora' => '08:00:00',
                'estado' => 'PRESENTE',
                'primer_nombre' => 'Juan',
                'primer_apellido' => 'Perez'
            ]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $asisMock = $this->createAsistenciaMock($mockPdo);
        $res = $asisMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testRegistrarAsistenciaGuardaMarcacionDeEmpleadoActivo(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockEmpleado = $this->createMock(PDOStatement::class);
        $mockMarcaciones = $this->createMock(PDOStatement::class);
        $mockInsert = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockEmpleado, $mockMarcaciones, $mockInsert);
        $mockEmpleado->expects($this->once())->method('execute')
            ->with([':cedula' => 'V-12345678'])->willReturn(true);
        $mockEmpleado->expects($this->once())->method('fetchColumn')->willReturn(1);
        $mockMarcaciones->expects($this->once())->method('execute')
            ->with([':cedula' => 'V-12345678', ':fecha' => '2026-11-25'])->willReturn(true);
        $mockMarcaciones->expects($this->once())->method('fetchAll')
            ->with(PDO::FETCH_COLUMN)->willReturn([]);
        $mockInsert->expects($this->once())->method('execute')
            ->with($this->callback(function (array $params): bool {
                return $params[':id_asistencia'] === 'ASIS0012026010112'
                    && $params[':cedula_empleado'] === 'V-12345678'
                    && $params[':tipo_marcacion'] === 'ENTRADA'
                    && $params[':fecha'] === '2026-11-25'
                    && $params[':hora'] === '08:00:00';
            }))
            ->willReturn(true);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $asistencia = $this->createAsistenciaMock($mockPdo);
        $asistencia->setIdAsistencia('ASIS0012026010112');
        $asistencia->setCedulaEmpleado('V-12345678');
        $asistencia->setTipoMarcacion('ENTRADA');
        $asistencia->setFecha('2026-11-25');
        $asistencia->setHora('08:00:00');
        $asistencia->setEstado('A_TIEMPO');

        $resultado = $asistencia->Transaccion(['peticion' => 'registrar']);

        $this->assertSame(1, $resultado['estado']);
        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testRegistrarAsistenciaRechazaEmpleadoInactivoYRevierte(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockEmpleado = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockEmpleado);
        $mockEmpleado->expects($this->once())->method('execute')->willReturn(true);
        $mockEmpleado->expects($this->once())->method('fetchColumn')->willReturn(0);
        $mockPdo->expects($this->once())->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);
        $mockPdo->expects($this->never())->method('commit');

        $asistencia = $this->createAsistenciaMock($mockPdo);
        $asistencia->setCedulaEmpleado('V-12345678');
        $asistencia->setTipoMarcacion('ENTRADA');
        $asistencia->setFecha('2026-11-25');

        $resultado = $asistencia->Transaccion(['peticion' => 'registrar']);

        $this->assertSame(409, $resultado['response']['resultado']);
    }

    #[Test]
    public function testAgregarObservacionConservaLasExistentes(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockSelect = $this->createMock(PDOStatement::class);
        $mockUpdate = $this->createMock(PDOStatement::class);
        $observacionExistente = json_encode([[
            'id' => 'OBS001',
            'texto' => 'Observación anterior',
            'eliminada' => false,
        ]]);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockSelect, $mockUpdate);
        $mockSelect->expects($this->once())->method('execute')
            ->with([':id_asistencia' => 'ASIS001'])->willReturn(true);
        $mockSelect->expects($this->once())->method('fetchColumn')->willReturn($observacionExistente);
        $mockUpdate->expects($this->once())->method('execute')
            ->with($this->callback(function (array $params): bool {
                $observaciones = json_decode($params[':observacion'], true);
                return $params[':id_asistencia'] === 'ASIS001'
                    && count($observaciones) === 2
                    && $observaciones[1]['texto'] === 'Observación nueva';
            }))
            ->willReturn(true);
        $mockUpdate->expects($this->once())->method('rowCount')->willReturn(1);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $asistencia = $this->createAsistenciaMock($mockPdo);
        $asistencia->setIdAsistencia('ASIS001');
        $asistencia->setObservacion('Observación nueva');

        $resultado = $asistencia->Transaccion(['peticion' => 'agregar_observacion']);

        $this->assertSame(1, $resultado['estado']);
        $this->assertCount(2, $resultado['response']['datos']['observaciones']);
    }

    #[Test]
    public function testEliminarObservacionLaMarcaComoEliminada(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockSelect = $this->createMock(PDOStatement::class);
        $mockUpdate = $this->createMock(PDOStatement::class);
        $observacion = json_encode([[
            'id' => 'OBS001',
            'texto' => 'Observación anterior',
            'eliminada' => false,
        ]]);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockSelect, $mockUpdate);
        $mockSelect->expects($this->once())->method('execute')
            ->with([':id_asistencia' => 'ASIS001'])->willReturn(true);
        $mockSelect->expects($this->once())->method('fetchColumn')->willReturn($observacion);
        $mockUpdate->expects($this->once())->method('execute')
            ->with($this->callback(function (array $params): bool {
                $observaciones = json_decode($params[':observacion'], true);
                return $params[':id_asistencia'] === 'ASIS001'
                    && $observaciones[0]['id'] === 'OBS001'
                    && $observaciones[0]['eliminada'] === true;
            }))
            ->willReturn(true);
        $mockUpdate->expects($this->once())->method('rowCount')->willReturn(1);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $asistencia = $this->createAsistenciaMock($mockPdo);
        $asistencia->setIdAsistencia('ASIS001');
        $asistencia->setObservacion('OBS001');

        $resultado = $asistencia->Transaccion(['peticion' => 'eliminar_observacion']);

        $this->assertSame(1, $resultado['estado']);
        $this->assertTrue($resultado['response']['datos']['observaciones'][0]['eliminada']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $asistencia = new Asistencia();
        $res = $asistencia->Transaccion(['peticion' => 'peticion_fantasma']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
