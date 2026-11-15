<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\DetalleEntrada;
use Exception;

class DetalleEntradaTest extends TestCase
{
    private DetalleEntrada $detalle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detalle = new DetalleEntrada();
    }

    #[Test]
    public function testSetIdValidoEInvalido(): void
    {
        $this->detalle->setId('DETE2026010112');
        $this->assertEquals('DETE2026010112', $this->detalle->getId('DETE2026010112'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID no cumple con el formato permitido.');
        $this->detalle->setId('id-invalido!');
    }

    #[Test]
    public function testSetIdEntradaValidoEInvalido(): void
    {
        $this->detalle->setIdEntrada('ENTR2026010112');
        $this->assertEquals('ENTR2026010112', $this->detalle->getIdEntrada('ENTR2026010112'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID de la Entrada no cumple con el formato permitido.');
        $this->detalle->setIdEntrada('error@id');
    }

    #[Test]
    public function testSetIdUnidadValidoEInvalido(): void
    {
        $this->detalle->setIdUnidad('UNID2026010112');
        $this->assertEquals('UNID2026010112', $this->detalle->getIdUnidad('UNID2026010112'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID de la Unidad de Medida no cumple con el formato permitido.');
        $this->detalle->setIdUnidad('bad-unit');
    }

    #[Test]
    public function testSetCantidadValidaENegativa(): void
    {
        $this->detalle->setCantidad('15.5');
        $this->assertEquals('15.5', $this->detalle->getCantidad('15.5'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El valor ingresado no puede ser negativo');
        $this->detalle->setCantidad('-5.0');
    }

    #[Test]
    public function testTransaccionPeticionInvalida(): void
    {
        $res = $this->detalle->Transaccion(['peticion' => 'desconocida']);
        $this->assertEquals(400, $res['response']['resultado']);
    }
}
