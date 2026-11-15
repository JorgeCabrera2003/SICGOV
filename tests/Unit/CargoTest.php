<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Cargo;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class CargoTest extends TestCase
{
    private function createCargoMock(PDO&MockObject $mockPdo): Cargo&MockObject
    {
        $mock = $this->getMockBuilder(Cargo::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetIdValidoEInvalido(): void
    {
        $cargo = new Cargo();
        $cargo->setId('CARG0012026010112');
        $this->assertEquals('CARG0012026010112', $cargo->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID del Cargo no cumple con el formato permitido.');
        $cargo->setId('id-invalido!');
    }

    #[Test]
    public function testSetNombreValidoEInvalido(): void
    {
        $cargo = new Cargo();
        $cargo->setNombre('Chef Ejecutivo');
        $this->assertEquals('Chef Ejecutivo', $cargo->getNombre());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El Nombre del Cargo no cumple con el formato permitido.');
        $cargo->setNombre('$$$HACK$$$');
    }

    #[Test]
    public function testConsultarCargosActivosRetornaLista(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(1);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_cargo' => 'CARG001', 'nombre_cargo' => 'Mesero Principal', 'estatus' => 1]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $cargoMock = $this->createCargoMock($mockPdo);
        $res = $cargoMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testRegistrarCargoDuplicadoRetorna409(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtValidar = $this->createMock(PDOStatement::class);

        $mockStmtValidar->method('execute')->willReturn(true);
        $mockStmtValidar->method('rowCount')->willReturn(1); // Ya existe
        $mockStmtValidar->method('fetch')->willReturn(['id_cargo' => 'CARG001', 'nombre_cargo' => 'Chef']);

        $mockPdo->method('prepare')->willReturn($mockStmtValidar);
        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $cargoMock = $this->createCargoMock($mockPdo);
        $cargoMock->setId('CARG0012026010112');
        $cargoMock->setNombre('Chef');

        $res = $cargoMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $res['estado']);
        $this->assertEquals(409, $res['response']['resultado']);
        $this->assertEquals(409, $res['HTTP_STATUS']['codigo']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $cargo = new Cargo();
        $res = $cargo->Transaccion(['peticion' => 'peticion_invalida']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
