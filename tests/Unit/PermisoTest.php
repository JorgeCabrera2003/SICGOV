<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\Security\Permiso;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class PermisoTest extends TestCase
{
    private function createPermisoMock(PDO&MockObject $mockPdo): Permiso&MockObject
    {
        $mock = $this->getMockBuilder(Permiso::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetIdValidoEInvalido(): void
    {
        $permiso = new Permiso();
        $permiso->setId('PERM0012026010112');
        $this->assertEquals('PERM0012026010112', $permiso->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ID no válido.');
        $permiso->setId('id-invalido-bad');
    }

    #[Test]
    public function testSetIdRolValidoEInvalido(): void
    {
        $permiso = new Permiso();
        $permiso->setIdRol('ROL01202601011200');
        $this->assertEquals('ROL01202601011200', $permiso->getIdRol());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ID no válido.');
        $permiso->setIdRol('rol-malo');
    }

    #[Test]
    public function testSetIdModuloValidoEInvalido(): void
    {
        $permiso = new Permiso();
        $permiso->setIdModulo('MOD00120260101120');
        $this->assertEquals('MOD00120260101120', $permiso->getIdModulo());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ID no válido.');
        $permiso->setIdModulo('mod-invalido');
    }

    #[Test]
    public function testSetAccionValidaEInvalida(): void
    {
        $permiso = new Permiso();
        $permiso->setAccion('CONSULTAR');
        $this->assertEquals('CONSULTAR', $permiso->getAccion());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Acción no válida.');
        $permiso->setAccion('$$$HACK_ACCION$$$');
    }

    #[Test]
    public function testSetEstadoValidoAceptaUnoYCero(): void
    {
        $permiso = new Permiso();
        $permiso->setEstado('1');
        $this->assertEquals('1', $permiso->getEstado());

        $permiso->setEstado('0');
        $this->assertEquals('0', $permiso->getEstado());
    }

    #[Test]
    public function testSetEstadoInvalidoLanzaExcepcionConTipografiaExacta(): void
    {
        // En Permiso.php:79-81 lanza "Estado no válidao."
        $permiso = new Permiso();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Estado no válidao.');
        $permiso->setEstado('activo');
    }

    #[Test]
    public function testFiltrarPermisosPorRolRetornaMatriz(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            [
                'id_permiso' => 'P001',
                'id_rol' => 'ROL01202601011200',
                'id_modulo' => 'MOD01',
                'accion' => 'consultar',
                'estatus' => 1,
                'nombre' => 'Usuarios'
            ],
            [
                'id_permiso' => 'P002',
                'id_rol' => 'ROL01202601011200',
                'id_modulo' => 'MOD01',
                'accion' => 'registrar',
                'estatus' => 0,
                'nombre' => 'Usuarios'
            ]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $permisoMock = $this->createPermisoMock($mockPdo);
        $permisoMock->setIdRol('ROL01202601011200');

        $res = $permisoMock->Transaccion([
            'peticion' => 'filtrar',
            'parametro' => 'nombre_modulo',
            'modulo' => 'Usuarios'
        ]);

        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertArrayHasKey('Usuarios', $res['response']['permiso']);
        $this->assertEquals(1, $res['response']['permiso']['Usuarios']['consultar']);
        $this->assertEquals(0, $res['response']['permiso']['Usuarios']['registrar']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $permiso = new Permiso();
        $res = $permiso->Transaccion(['peticion' => 'hack_peticion']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
