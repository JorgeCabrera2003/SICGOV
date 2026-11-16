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
    public function testSetCedulaEmpleadoNormalizaSinGuion(): void
    {
        $asistencia = new Asistencia();
        $asistencia->setCedulaEmpleado('v12345678');
        $this->assertEquals('V-12345678', $asistencia->getCedulaEmpleado());
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
    public function testSettersGeneralesAsignanValores(): void
    {
        $asistencia = new Asistencia();
        $asistencia->setIdAsistencia('ASIS0012026010112');
        $asistencia->setTipoMarcacion('ENTRADA');
        $asistencia->setFecha('2026-11-20');
        $asistencia->setHora('08:05:00');
        $asistencia->setEstado('PRESENTE');
        $asistencia->setObservacion('Llegó a tiempo');

        $this->assertEquals('ASIS0012026010112', $asistencia->getIdAsistencia());
        $this->assertEquals('ENTRADA', $asistencia->getTipoMarcacion());
        $this->assertEquals('2026-11-20', $asistencia->getFecha());
        $this->assertEquals('08:05:00', $asistencia->getHora());
        $this->assertEquals('PRESENTE', $asistencia->getEstado());
        $this->assertEquals('Llegó a tiempo', $asistencia->getObservacion());
    }

    #[Test]
    public function testValidarAsistenciaRetornaNoImplementada(): void
    {
        $asistencia = new Asistencia();
        $res = $asistencia->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(0, $res['estado']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals('Función de validación no implementada', $res['response']['mensaje']);
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
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $asistencia = new Asistencia();
        $res = $asistencia->Transaccion(['peticion' => 'peticion_fantasma']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
