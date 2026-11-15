<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\CategoriaInsumo;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class CategoriaInsumoTest extends TestCase
{
    private function createCategoriaInsumoMock(PDO&MockObject $mockPdo): CategoriaInsumo&MockObject
    {
        $mock = $this->getMockBuilder(CategoriaInsumo::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetIdValidoEInvalido(): void
    {
        $cat = new CategoriaInsumo();
        $cat->setId('CATI0012026010112');
        $this->assertEquals('CATI0012026010112', $cat->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID de la Categoría no cumple con el formato permitido.');
        $cat->setId('id-invalido-!');
    }

    #[Test]
    public function testSetNombreValidoEInvalido(): void
    {
        $cat = new CategoriaInsumo();
        $cat->setNombre('Carnes y Aves');
        $this->assertEquals('Carnes y Aves', $cat->getNombre());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El Nombre de la Categoría no cumple con el formato permitido.');
        $cat->setNombre('$$$HACK_CATEGORIA$$$');
    }

    #[Test]
    public function testSetEstatusValidoAceptaUnoYCero(): void
    {
        $cat = new CategoriaInsumo();
        $cat->setEstatus(1);
        $this->assertEquals(1, $cat->getEstatus());

        $cat->setEstatus(0);
        $this->assertEquals(0, $cat->getEstatus());
    }

    #[Test]
    public function testSetEstatusInvalidoLanzaExcepcionConMensajeCopiado(): void
    {
        // En CategoriaInsumo.php:59 lanza "El ID de la noticia no cumple con el formato permitido."
        $cat = new CategoriaInsumo();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID de la noticia no cumple con el formato permitido.');
        $cat->setEstatus(9);
    }

    #[Test]
    public function testConsultarCategoriasActivasRetornaLista(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(1);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_categoria' => 'CATI001', 'nombre' => 'Lácteos y Derivados', 'estatus' => 1]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $catMock = $this->createCategoriaInsumoMock($mockPdo);
        $res = $catMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(1, $res['response']['datos']);
    }

    #[Test]
    public function testRegistrarCategoriaInsumoDuplicadaRetorna409(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtValidar = $this->createMock(PDOStatement::class);

        $mockStmtValidar->method('execute')->willReturn(true);
        $mockStmtValidar->method('rowCount')->willReturn(1); // Ya existe
        $mockStmtValidar->method('fetch')->willReturn(['id_categoria' => 'CATI001', 'nombre' => 'Lácteos']);

        $mockPdo->method('prepare')->willReturn($mockStmtValidar);
        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $catMock = $this->createCategoriaInsumoMock($mockPdo);
        $catMock->setId('CATI0012026010112');
        $catMock->setNombre('Lácteos');

        $res = $catMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $res['estado']);
        $this->assertEquals(409, $res['response']['resultado']);
        $this->assertEquals(409, $res['HTTP_STATUS']['codigo']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $cat = new CategoriaInsumo();
        $res = $cat->Transaccion(['peticion' => 'peticion_invalida']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
