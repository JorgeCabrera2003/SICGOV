<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\Security\ModuloSistema;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class ModuloSistemaTest extends TestCase
{
    private function createModuloMock(PDO&MockObject $mockPdo): ModuloSistema&MockObject
    {
        $mock = $this->getMockBuilder(ModuloSistema::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetIdValidoAceptaFormato(): void
    {
        $modulo = new ModuloSistema();
        $modulo->setId('MOD01202601011200');
        $this->assertEquals('MOD01202601011200', $modulo->getId());
    }

    #[Test]
    public function testSetIdInvalidoLanzaExcepcion(): void
    {
        $modulo = new ModuloSistema();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ID no válido.');
        $modulo->setId('mod-falso!');
    }

    #[Test]
    public function testSetNombreValidoAsignaValor(): void
    {
        $modulo = new ModuloSistema();
        $modulo->setNombre('Bitácora');
        $this->assertEquals('Bitácora', $modulo->getNombre());

        $modulo->setNombre('Módulos del Sistema');
        $this->assertEquals('Módulos del Sistema', $modulo->getNombre());
    }

    #[Test]
    public function testSetNombreInvalidoLanzaExcepcion(): void
    {
        $modulo = new ModuloSistema();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nombre no válido. Debe tener al menos entre 3 a 150 carácteres');
        $modulo->setNombre('$$$HACK$$$');
    }

    #[Test]
    public function testConsultarModulosRetornaListaActivos(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_modulo' => 'MOD01', 'nombre' => 'Usuarios', 'estatus' => 1],
            ['id_modulo' => 'MOD02', 'nombre' => 'Clientes', 'estatus' => 1]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $moduloMock = $this->createModuloMock($mockPdo);
        $res = $moduloMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(2, $res['response']['datos']);
    }

    #[Test]
    public function testComprobarModulosIntegridadConDiscrepancia(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        // Retornamos lista incompleta para forzar discrepancia
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_modulo' => 'MOD01', 'nombre' => 'Usuarios', 'estatus' => 1]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $moduloMock = $this->createModuloMock($mockPdo);
        $res = $moduloMock->Transaccion(['peticion' => 'comprobar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertFalse($res['response']['verificacion']);
        $this->assertEquals('warning', $res['response']['icon']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $modulo = new ModuloSistema();
        $res = $modulo->Transaccion(['peticion' => 'peticion_invalida']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
