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
        $ref->setAccessible(true);
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
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $turno = new Turno();
        $res = $turno->Transaccion(['peticion' => 'hack_turno']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
