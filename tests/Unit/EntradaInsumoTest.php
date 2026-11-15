<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\EntradaInsumo;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class EntradaInsumoTest extends TestCase
{
    private function createEntradaMock(PDO&MockObject $mockPdo): EntradaInsumo&MockObject
    {
        $mock = $this->getMockBuilder(EntradaInsumo::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetIdValidoEInvalido(): void
    {
        $entrada = new EntradaInsumo();
        $entrada->setId('ENTR0012026010112');
        $this->assertEquals('ENTR0012026010112', $entrada->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID no cumple con el formato permitido.');
        $entrada->setId('id-invalido!');
    }

    #[Test]
    public function testSetIdInsumoValidoEInvalido(): void
    {
        $entrada = new EntradaInsumo();
        $entrada->setIdInsumo('INSU0012026010112');
        $this->assertEquals('INSU0012026010112', $entrada->getIdInsumo());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID del Insumo no cumple con el formato permitido.');
        $entrada->setIdInsumo('insumo-malo');
    }

    #[Test]
    public function testSetDocumentoLegalValidoEInvalido(): void
    {
        $entrada = new EntradaInsumo();
        $entrada->setDocumentoLegal('J-123456789');
        $this->assertEquals('J-123456789', $entrada->getDocumentoLegal());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El documento legal debe tener un prefijo válido');
        $entrada->setDocumentoLegal('RIF-FALSO-000');
    }

    #[Test]
    public function testConsultarEntradasRetornaLista(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(1);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_entrada' => 'ENTR01', 'id_insumo' => 'INSU01', 'documento_legal' => 'J-123456789', 'estatus' => 1]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $entMock = $this->createEntradaMock($mockPdo);
        $res = $entMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testAsociarProveedoresSinProveedoresRetorna400(): void
    {
        $entrada = new EntradaInsumo();
        $res = $entrada->Transaccion(['peticion' => 'asociar_proveedores']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $entrada = new EntradaInsumo();
        $res = $entrada->Transaccion(['peticion' => 'peticion_fantasma']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
