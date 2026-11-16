<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\Security\Bitacora;
use ReflectionProperty;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class BitacoraTest extends TestCase
{
    private function createBitacoraMock(PDO&MockObject $mockPdo): Bitacora&MockObject
    {
        $mock = $this->getMockBuilder(Bitacora::class)
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
    public function testSetCedulaConSoloNumerosAgregaPrefijoV(): void
    {
        $bitacora = new Bitacora();
        $bitacora->set_cedula('12345678');
        $this->assertEquals('V-12345678', $this->getPrivateProperty($bitacora, 'cedula'));
    }

    #[Test]
    public function testSetCedulaConLetraSinGuionAgregaGuionYMayuscula(): void
    {
        $bitacora = new Bitacora();
        $bitacora->set_cedula('e87654321');
        $this->assertEquals('E-87654321', $this->getPrivateProperty($bitacora, 'cedula'));
    }

    #[Test]
    public function testSetCedulaPermiteUsuarioSistema(): void
    {
        $bitacora = new Bitacora();
        $bitacora->set_cedula('Sistema');
        $this->assertEquals('Sistema', $this->getPrivateProperty($bitacora, 'cedula'));
    }

    #[Test]
    public function testSetCedulaInvalidaLanzaExcepcion(): void
    {
        $bitacora = new Bitacora();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La cédula en bitácora no tiene un formato válido");
        $bitacora->set_cedula('XYZ-INV-000');
    }

    #[Test]
    public function testSetModuloValidoEInvalido(): void
    {
        $bitacora = new Bitacora();
        
        // Válido
        $bitacora->set_modulo('Usuarios del Sistema');
        $this->assertEquals('Usuarios del Sistema', $this->getPrivateProperty($bitacora, 'modulo'));

        // Inválido (muy corto o caracteres no permitidos)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El nombre del módulo no es válido.");
        $bitacora->set_modulo('<script>alert(1)</script>');
    }

    #[Test]
    public function testSetAccionValidaEInvalida(): void
    {
        $bitacora = new Bitacora();
        
        // Válido
        $bitacora->set_accion('REGISTRAR USUARIO');
        $this->assertEquals('REGISTRAR USUARIO', $this->getPrivateProperty($bitacora, 'accion'));

        // Inválido
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La acción no cumple con el formato permitido.");
        $bitacora->set_accion('$$$ACCION_HACK$$$');
    }

    #[Test]
    public function testSetIdBitacoraFormatoValidoEInvalido(): void
    {
        $bitacora = new Bitacora();
        
        // Válido formato ID (ej. BITA0012026010112000000)
        $bitacora->setIdBitacora('BITA0012026010112000000');
        $this->assertEquals('BITA0012026010112000000', $this->getPrivateProperty($bitacora, 'id_bitacora'));

        // Inválido
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El ID de bitácora no tiene un formato válido.");
        $bitacora->setIdBitacora('123-id-falso');
    }

    #[Test]
    public function testRegistrarBitacoraTransaccionExitosa(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);

        $bitacoraMock = $this->createBitacoraMock($mockPdo);
        $bitacoraMock->setIdBitacora('BITA0012026010112000000');
        $bitacoraMock->set_cedula('V-12345678');
        $bitacoraMock->set_modulo('Seguridad');
        $bitacoraMock->set_accion('MODIFICAR ROL');
        $bitacoraMock->set_detalle('Cambio de privilegios');
        $bitacoraMock->set_ip_address('192.168.1.1');

        $resultado = $bitacoraMock->Transaccion(['peticion' => 'registrar']);
        $this->assertTrue($resultado);
    }

    #[Test]
    public function testRegistrarBitacoraFalloRetornaFalse(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('beginTransaction');
        $mockPdo->method('prepare')->willThrowException(new PDOException("Error de conexión a la base de datos de seguridad"));
        $mockPdo->expects($this->once())->method('rollBack');

        $bitacoraMock = $this->createBitacoraMock($mockPdo);
        $bitacoraMock->setIdBitacora('BITA0012026010112000000');
        $bitacoraMock->set_cedula('V-12345678');
        $bitacoraMock->set_modulo('Seguridad');
        $bitacoraMock->set_accion('ELIMINAR USUARIO');

        $resultado = $bitacoraMock->Transaccion(['peticion' => 'registrar']);
        $this->assertFalse($resultado);
    }

    #[Test]
    public function testListarBitacoraRetornaArreglo(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_bitacora' => 'BITA001', 'modulo' => 'Seguridad', 'accion' => 'LOGIN']
        ]);
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $bitacoraMock = $this->createBitacoraMock($mockPdo);
        $resultado = $bitacoraMock->Transaccion(['peticion' => 'listar', 'filtros' => ['modulo' => 'Seguridad']]);
        
        $this->assertIsArray($resultado);
        $this->assertCount(1, $resultado);
        $this->assertEquals('BITA001', $resultado[0]['id_bitacora']);
    }

    #[Test]
    public function testTransaccionPeticionDesconocidaRetornaFalse(): void
    {
        $bitacora = new Bitacora();
        $resultado = $bitacora->Transaccion(['peticion' => 'hack_desconocido']);
        $this->assertFalse($resultado);
    }
}
