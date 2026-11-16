<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Insumo;
use ReflectionProperty;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class InsumoTest extends TestCase
{
    private function createInsumoMock(PDO&MockObject $mockPdo): Insumo&MockObject
    {
        $mock = $this->getMockBuilder(Insumo::class)
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
        $insumo = new Insumo();
        $insumo->setId('INSU0012026010112');
        $this->assertEquals('INSU0012026010112', $insumo->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID de la Categoría no cumple con el formato permitido.');
        $insumo->setId('id-invalido');
    }

    #[Test]
    public function testSetNombreValidoEInvalido(): void
    {
        $insumo = new Insumo();
        $insumo->setNombre('Harina de Trigo');
        $this->assertEquals('Harina de Trigo', $insumo->getNombre());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID de la Categoría no cumple con el formato permitido.');
        $insumo->setNombre('$$$HACK$$$');
    }

    #[Test]
    public function testStockNegativoLanzaExcepcion(): void
    {
        $insumo = new Insumo();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El valor ingresado no puede ser negativo');
        $insumo->setStockActual(-5.0);
    }

    #[Test]
    public function testStockMaximoCeroSeConvierteEnNull(): void
    {
        $insumo = new Insumo();
        $insumo->setStockMaximo(0);
        $this->assertNull($this->getPrivateProperty($insumo, 'stock_maximo'));
    }

    #[Test]
    public function testPrecioUnitarioNegativoLanzaExcepcion(): void
    {
        $insumo = new Insumo();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El valor ingresado no puede ser negativo');
        $insumo->setPrecioUnitario(-1.50);
    }

    #[Test]
    public function testSetIdUnidadMedidaValidaEInvalida(): void
    {
        $insumo = new Insumo();
        $insumo->setIdUnidadMedida('UNID0012026010112');
        $this->assertEquals('UNID0012026010112', $insumo->getUnidadMedida());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID de la Unidad de Medida no cumple con el formato permitido.');
        $insumo->setIdUnidadMedida('unidad-mala');
    }

    #[Test]
    public function testConsultarInsumoRetornaListaActivos(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(1);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_insumo' => 'INSU001', 'nombre' => 'Queso Mozzarella', 'stock_actual' => 10.5]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $insumoMock = $this->createInsumoMock($mockPdo);
        $res = $insumoMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $insumo = new Insumo();
        $res = $insumo->Transaccion(['peticion' => 'peticion_fantasma']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
