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
        $ref->setAccessible(true);
        return $ref->getValue($object);
    }

    #[Test]
    public function testSetIdValidoEInvalido(): void
    {
        $pl = new PermisoLaboral();
        $pl->setId('PERM0012026010112');
        $this->assertEquals('PERM0012026010112', $pl->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID del permiso no cumple con el formato permitido.');
        $pl->setId('id-malo');
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
    public function testSetEstadoValidoEInvalido(): void
    {
        $pl = new PermisoLaboral();
        $pl->setEstado('APROBADO');
        $this->assertEquals('APROBADO', $this->getPrivateProperty($pl, 'estado'));

        $pl->setEstado('RECHAZADO');
        $this->assertEquals('RECHAZADO', $this->getPrivateProperty($pl, 'estado'));

        $pl->setEstado('PENDIENTE');
        $this->assertEquals('PENDIENTE', $this->getPrivateProperty($pl, 'estado'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Estado no válido.');
        $pl->setEstado('CANCELADO');
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
