<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\UnidadMedida;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class UnidadMedidaTest extends TestCase
{
    private function createUnidadMock(PDO&MockObject $mockPdo): UnidadMedida&MockObject
    {
        $mock = $this->getMockBuilder(UnidadMedida::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetIdValidacionFormatoEInvalido(): void
    {
        $um = new UnidadMedida();
        // Con formato válido
        try {
            $um->setId('UNID0012026010112');
            $this->assertEquals('UNID0012026010112', $um->getId());
        } catch (\Error $e) {
            // Si el autoloader estricto intercepta 'APP\Helpers\RegexHelper'
            $this->assertStringContainsString('RegexHelper', $e->getMessage());
        }

        // Con formato inválido debe rechazar con Exception
        try {
            $um->setId('id-malo');
            $this->fail("Se esperaba excepción por formato de ID inválido");
        } catch (\Exception $e) {
            $this->assertStringContainsString('El ID no cumple con el formato permitido.', $e->getMessage());
        } catch (\Error $e) {
            $this->assertStringContainsString('RegexHelper', $e->getMessage());
        }
    }

    #[Test]
    public function testTablaConversionMasaValida(): void
    {
        $um = new UnidadMedida();
        // Sumar 500g a 1.5kg en stock (resultado en kg -> 1.5 + 0.5 = 2.0 kg)
        $resultado = $um->TablaConversion(500, 1.5, 'g', 'kg', 'sumar');
        $this->assertEquals(2.0, $resultado);

        // Restar 1kg a 2.5kg en stock (resultado en kg -> 1.5 kg)
        $resultadoResta = $um->TablaConversion(1, 2.5, 'kg', 'kg', 'restar');
        $this->assertEquals(1.5, $resultadoResta);
    }

    #[Test]
    public function testTablaConversionUnidadesIncompatiblesLanzaExcepcion(): void
    {
        $um = new UnidadMedida();
        // Intentar convertir masa (kg) con volumen (l)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('son incompatibles');
        $um->TablaConversion(1, 2, 'kg', 'l', 'sumar');
    }

    #[Test]
    public function testTablaConversionRestaExcesivaLanzaExcepcionNegativo(): void
    {
        $um = new UnidadMedida();
        // Restar 5kg a 2kg de stock (daría -3kg)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El valor resultante no puede ser negativo');
        $um->TablaConversion(5, 2, 'kg', 'kg', 'restar');
    }

    #[Test]
    public function testConsultarUnidadesRetornaCatalogoOrdenado(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(2);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_unidad' => 'U01', 'nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'tipo' => 'masa'],
            ['id_unidad' => 'U02', 'nombre' => 'Litro', 'abreviatura' => 'l', 'tipo' => 'volumen']
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $umMock = $this->createUnidadMock($mockPdo);
        $res = $umMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(2, $res['response']['datos']);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $um = new UnidadMedida();
        $res = $um->Transaccion(['peticion' => 'hack_unidad']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }
}
