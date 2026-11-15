<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Proveedor;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class ProveedorTest extends TestCase
{
    private function createProveedorMock(PDO&MockObject $mockPdo): Proveedor&MockObject
    {
        $mock = $this->getMockBuilder(Proveedor::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetDocumentoLegalValidoEInvalido(): void
    {
        $prov = new Proveedor();
        // Prefijo J o V con guion y numeros
        $prov->setDocumentoLegal('J-123456789');
        $this->assertEquals('J-123456789', $prov->getDocumentoLegal());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El documento legal debe tener un prefijo válido');
        $prov->setDocumentoLegal('RIF-INVALIDO-123');
    }

    #[Test]
    public function testSetNombreValidoEInvalido(): void
    {
        $prov = new Proveedor();
        $prov->setNombre('Distribuidora Los Andes C.A.');
        $this->assertEquals('Distribuidora Los Andes C.A.', $prov->getNombre());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nombre no válido. Debe tener al menos entre 3 a 150 carácteres');
        $prov->setNombre('$$$HACK_PROVEEDOR$$$');
    }

    #[Test]
    public function testSetTelefonoValidoEInvalido(): void
    {
        $prov = new Proveedor();
        $prov->setTelefono('0414-1234567');
        $this->assertEquals('0414-1234567', $prov->getTelefono());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Teléfono no válido.');
        $prov->setTelefono('04141234567'); // Falta guion
    }

    #[Test]
    public function testSetCorreoValidoEInvalido(): void
    {
        $prov = new Proveedor();
        $prov->setCorreo('contacto@distribuidora.com');
        $this->assertEquals('contacto@distribuidora.com', $prov->getCorreo());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El formato del correo electrónico no es válido.');
        $prov->setCorreo('correo-invalido-sin-arroba');
    }

    #[Test]
    public function testSetDireccionValidaEInvalida(): void
    {
        $prov = new Proveedor();
        $prov->setDireccion('Zona Industrial II, Calle 4 con Carrera 12, Galpón 5');
        $this->assertEquals('Zona Industrial II, Calle 4 con Carrera 12, Galpón 5', $prov->getDireccion());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Dirección no válida');
        $prov->setDireccion('Corta'); // Menos de 10 caracteres
    }

    #[Test]
    public function testConsultarProveedoresActivosRetornaLista(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(1);
        $mockStmt->method('fetchAll')->willReturn([
            [
                'documento_legal' => 'J-123456789',
                'nombre' => 'Alimentos Polar',
                'telefono' => '0251-1234567',
                'correo' => 'ventas@polar.com',
                'direccion' => 'Zona Industrial I Barquisimeto',
                'estatus' => 1
            ]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $provMock = $this->createProveedorMock($mockPdo);
        $res = $provMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testObtenerProveedoresSinIdInsumoRetorna400(): void
    {
        $prov = new Proveedor();
        $res = $prov->Transaccion(['peticion' => 'obtener_proveedores']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $prov = new Proveedor();
        $res = $prov->Transaccion(['peticion' => 'peticion_fantasma']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
