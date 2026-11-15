<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Areas;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class AreasTest extends TestCase
{
    private function createAreasMock(PDO&MockObject $mockPdo): Areas&MockObject
    {
        $mock = $this->getMockBuilder(Areas::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetIdAreaValidoEInvalido(): void
    {
        $area = new Areas();
        $area->setIdArea('AREA0012026010112');
        $this->assertEquals('AREA0012026010112', $area->getIdArea());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID del área no cumple con el formato permitido.');
        $area->setIdArea('id-malo');
    }

    #[Test]
    public function testSetNombreValidoEInvalido(): void
    {
        $area = new Areas();
        $area->setNombre('Terraza VIP');
        $this->assertEquals('Terraza VIP', $area->getNombre());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El nombre del área debe tener entre 3 y 60 caracteres.');
        $area->setNombre('AB'); // Menos de 3 caracteres
    }

    #[Test]
    public function testSetEstatusValidoEInvalido(): void
    {
        $area = new Areas();
        $area->setEstatus(1);
        $this->assertEquals(1, $area->getEstatus());

        $area->setEstatus(0);
        $this->assertEquals(0, $area->getEstatus());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El estatus debe ser 0 (inactivo) o 1 (activo).');
        $area->setEstatus(2);
    }

    #[Test]
    public function testConsultarAreasRetornaLista(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(1);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_area' => 'AREA01', 'nombre' => 'Salón Principal', 'estatus' => 1]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $areaMock = $this->createAreasMock($mockPdo);
        $res = $areaMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testRegistrarAreaDuplicadaRetorna409(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('beginTransaction');
        $mockPdo->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack');

        $pdoException = new PDOException("Duplicate entry 'AREA01' for key 'PRIMARY'", 23000);
        $mockPdo->method('prepare')->willThrowException($pdoException);

        $areaMock = $this->createAreasMock($mockPdo);
        $areaMock->setIdArea('AREA0012026010112');
        $areaMock->setNombre('Barra');

        $res = $areaMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(0, $res['estado']);
        $this->assertEquals(409, $res['response']['resultado']);
        $this->assertEquals(409, $res['HTTP_STATUS']['codigo']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $area = new Areas();
        $res = $area->Transaccion(['peticion' => 'hack_area']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
