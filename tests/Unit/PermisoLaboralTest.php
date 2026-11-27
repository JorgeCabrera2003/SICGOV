<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\PermisoLaboral;
use ReflectionProperty;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class PermisoLaboralTest extends TestCase
{
    private function createPermisoLaboralMock(PDO&MockObject $mockPdo): PermisoLaboral&MockObject
    {
        $mock = $this->getMockBuilder(PermisoLaboral::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    private function getPrivateProperty(object $object, string $propertyName)
    {
        $ref = new ReflectionProperty($object, $propertyName);
        return $ref->getValue($object);
    }

    #[Test]
    public function testSetIdTipoPermisoValidoEInvalido(): void
    {
        $pl = new PermisoLaboral();
        $pl->setIdTipoPermiso('TIPO0012026010112');
        $this->assertEquals('TIPO0012026010112', $this->getPrivateProperty($pl, 'id_tipo_permiso'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El tipo de permiso no es válido.');
        $pl->setIdTipoPermiso('tipo-malo');
    }

    #[Test]
    public function testFechasDebenSerRealesYEstarEnOrden(): void
    {
        $pl = new PermisoLaboral();
        $pl->setFechaInicio('2026-11-20');
        $pl->setFechaFin('2026-11-22');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La fecha de fin no puede ser anterior a la fecha de inicio.');
        $pl->setFechaFin('2026-11-19');
    }

    #[Test]
    public function testFechaInexistenteEsRechazada(): void
    {
        $pl = new PermisoLaboral();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La fecha de inicio no es válida.');
        $pl->setFechaInicio('2026-02-30');
    }

    #[Test]
    public function testRegistrarPermisoLaboralNuevoExitosamente(): void
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

        $permiso = $this->createPermisoLaboralMock($mockPdo);
        $permiso->setId('PERM0012026010112');
        $permiso->setIdTipoPermiso('TIPO0012026010112');
        $permiso->setCedulaEmpleado('V-12345678');
        $permiso->setFechaInicio('2026-11-20');
        $permiso->setFechaFin('2026-11-22');

        $resultado = $permiso->Transaccion(['peticion' => 'registrar']);

        $this->assertSame(1, $resultado['estado']);
        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testAprobarPermisoPendienteExitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->stringContains("estado = 'PENDIENTE'"))
            ->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(1);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $permiso = $this->createPermisoLaboralMock($mockPdo);
        $permiso->setId('PERM0012026010112');
        $permiso->setFechaAprobacion('2026-11-26 12:00:00');

        $resultado = $permiso->Transaccion(['peticion' => 'aprobar']);

        $this->assertSame(200, $resultado['response']['resultado']);
        $this->assertSame('Permiso aprobado correctamente', $resultado['response']['mensaje']);
    }

    #[Test]
    public function testRechazarPermisoPendienteExitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->stringContains("estado = 'PENDIENTE'"))
            ->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(1);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $permiso = $this->createPermisoLaboralMock($mockPdo);
        $permiso->setId('PERM0012026010112');
        $permiso->setFechaAprobacion('2026-11-26 12:00:00');

        $resultado = $permiso->Transaccion(['peticion' => 'rechazar']);

        $this->assertSame(200, $resultado['response']['resultado']);
        $this->assertSame('Permiso rechazado correctamente', $resultado['response']['mensaje']);
    }

    #[Test]
    public function testModificarPermisoLaboralExitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->with($this->stringContains('UPDATE permiso_laboral SET'))
            ->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $permiso = $this->createPermisoLaboralMock($mockPdo);
        $permiso->setId('PERM0012026010112');
        $permiso->setIdTipoPermiso('TIPO0012026010112');
        $permiso->setCedulaEmpleado('V-12345678');
        $permiso->setFechaInicio('2026-11-20');
        $permiso->setFechaFin('2026-11-22');

        $resultado = $permiso->Transaccion(['peticion' => 'modificar']);

        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testEliminarPermisoExistenteDesactivaElRegistro(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockValidacion = $this->createMock(PDOStatement::class);
        $mockDelete = $this->createMock(PDOStatement::class);
        $mockPdo->expects($this->exactly(2))->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('prepare')
            ->willReturnOnConsecutiveCalls($mockValidacion, $mockDelete);
        $mockValidacion->expects($this->once())->method('execute')->willReturn(true);
        $mockValidacion->expects($this->once())->method('rowCount')->willReturn(1);
        $mockValidacion->expects($this->once())->method('fetch')->willReturn(['id_permiso' => 'PERM001']);
        $mockDelete->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('commit')->willReturn(true);

        $permiso = $this->createPermisoLaboralMock($mockPdo);
        $permiso->setId('PERM0012026010112');

        $resultado = $permiso->Transaccion(['peticion' => 'eliminar']);

        $this->assertSame(200, $resultado['response']['resultado']);
    }

    #[Test]
    public function testConsultarPermisosLaboralesRetornaLista(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            [
                'id_permiso' => 'PERM01',
                'id_tipo_permiso' => 'TIPO01',
                'cedula_empleado' => 'V-12345678',
                'fecha_inicio' => '2026-11-20',
                'fecha_fin' => '2026-11-22',
                'estado' => 'PENDIENTE',
                'tipo_nombre' => 'Médico',
                'empleado' => 'Carlos Perez'
            ]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $plMock = $this->createPermisoLaboralMock($mockPdo);
        $res = $plMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $pl = new PermisoLaboral();
        $res = $pl->Transaccion(['peticion' => 'hack_permiso']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
