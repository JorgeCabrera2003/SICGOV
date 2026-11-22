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
    public function testSetIdValidoEInvalido(): void
    {
        $tipo = new TipoPermiso();
        $tipo->setId('TPERM2026010112');
        $this->assertEquals('TPERM2026010112', $tipo->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID del Tipo de Permiso no cumple con el formato permitido.');
        $tipo->setId('id-invalido!');
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
    public function testSetEstatusValidoEInvalido(): void
    {
        $tipo = new TipoPermiso();
        $tipo->setEstatus(1);
        $this->assertEquals(1, $tipo->getEstatus());

        $tipo->setEstatus(0);
        $this->assertEquals(0, $tipo->getEstatus());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Estatus no válido.');
        $tipo->setEstatus(9);
    }

    #[Test]
    public function testTransaccionPeticionInvalida(): void
    {
        $tipo = new TipoPermiso();
        $res = $tipo->Transaccion(['peticion' => 'desconocida']);
        $this->assertEquals(400, $res['response']['resultado']);
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
}
