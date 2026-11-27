<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\TipoPermiso;
use PDO;
use PDOStatement;
use Exception;

class TipoPermisoTest extends TestCase
{
    private function createTipoPermisoMock(PDO&MockObject $mockPdo): TipoPermiso&MockObject
    {
        $mock = $this->getMockBuilder(TipoPermiso::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetNombreValidoEInvalido(): void
    {
        $tipo = new TipoPermiso();
        $tipo->setNombre('Permiso Médico');
        $this->assertEquals('Permiso Médico', $tipo->getNombre());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El Nombre del Tipo de Permiso no cumple con el formato permitido.');
        $tipo->setNombre('A');
    }

    #[Test]
    public function testConsultarTipoPermisoExitoso(): void
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(1);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_tipo_permiso' => 'TPERM2026010112', 'nombre' => 'Permiso Médico', 'estatus' => 1]
        ]);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('beginTransaction')->willReturn(true);
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockPdo->method('commit')->willReturn(true);

        $tipo = $this->createTipoPermisoMock($mockPdo);
        $res = $tipo->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testRegistrarTipoPermisoNuevoExitosamente(): void
    {
        $mockValidacion = $this->createMock(PDOStatement::class);
        $mockInsert = $this->createMock(PDOStatement::class);
        $mockValidacion->expects($this->once())->method('execute')->willReturn(true);
        $mockValidacion->expects($this->once())->method('rowCount')->willReturn(0);
        $mockInsert->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->exactly(2))->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('prepare')
            ->willReturnOnConsecutiveCalls($mockValidacion, $mockInsert);
        $mockPdo->expects($this->exactly(2))->method('commit')->willReturn(true);

        $tipo = $this->createTipoPermisoMock($mockPdo);
        $tipo->setId('TPERM2026010112');
        $tipo->setNombre('Permiso Académico');

        $resultado = $tipo->Transaccion(['peticion' => 'registrar']);

        $this->assertSame(200, $resultado['response']['resultado']);
        $this->assertSame('Tipo de Permiso registrado exitosamente', $resultado['response']['mensaje']);
    }


    #[Test]
    public function testModificarTipoPermisoActualizaNombre(): void
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->stringContains('UPDATE tipo_permiso SET nombre'))
            ->willReturn($mockStmt);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $tipo = $this->createTipoPermisoMock($mockPdo);
        $tipo->setId('TPERM2026010112');
        $tipo->setNombre('Permiso Actualizado');

        $resultado = $tipo->Transaccion(['peticion' => 'modificar']);

        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testEliminarTipoPermisoExistenteLoDesactiva(): void
    {
        $mockValidacion = $this->createMock(PDOStatement::class);
        $mockEliminar = $this->createMock(PDOStatement::class);
        $mockValidacion->expects($this->once())->method('execute')->willReturn(true);
        $mockValidacion->expects($this->once())->method('rowCount')->willReturn(1);
        $mockValidacion->expects($this->once())->method('fetch')->willReturn(['id_tipo_permiso' => 'TPERM01']);
        $mockEliminar->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->exactly(2))->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('prepare')
            ->willReturnOnConsecutiveCalls($mockValidacion, $mockEliminar);
        $mockPdo->expects($this->exactly(2))->method('commit')->willReturn(true);

        $tipo = $this->createTipoPermisoMock($mockPdo);
        $tipo->setId('TPERM2026010112');

        $resultado = $tipo->Transaccion(['peticion' => 'eliminar']);

        $this->assertSame(200, $resultado['response']['resultado']);
        $this->assertSame('Tipo de Permiso eliminado exitosamente', $resultado['response']['mensaje']);
    }

    #[Test]
    public function testValidarTipoPermisoExistenteDevuelveRegistro(): void
    {
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStmt->expects($this->once())->method('fetch')->willReturn([
            'id_tipo_permiso' => 'TPERM2026010112',
            'nombre' => 'Permiso Médico',
            'estatus' => 1,
        ]);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $tipo = $this->createTipoPermisoMock($mockPdo);
        $tipo->setId('TPERM2026010112');

        $resultado = $tipo->Transaccion(['peticion' => 'validar']);

        $this->assertSame(1, $resultado['bool']);
        $this->assertSame('Permiso Médico', $resultado['response']['registro']['nombre']);
    }
}
