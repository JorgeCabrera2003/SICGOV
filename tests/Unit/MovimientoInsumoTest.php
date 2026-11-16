<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\MovimientoInsumo;
use Exception;

class MovimientoInsumoTest extends TestCase
{
    private MovimientoInsumo $movimiento;

    protected function setUp(): void
    {
        parent::setUp();
        $this->movimiento = new MovimientoInsumo();
    }

    #[Test]
    public function testSetIdValidoEInvalido(): void
    {
        $this->movimiento->setId('MOVI2026010112');
        $this->assertEquals('MOVI2026010112', $this->movimiento->getId());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID no cumple con el formato permitido.');
        $this->movimiento->setId('id-invalido!');
    }

    #[Test]
    public function testSetIdInsumoValidoEInvalido(): void
    {
        $this->movimiento->setIdInsumo('INSU2026010112');
        $this->assertEquals('INSU2026010112', $this->movimiento->getIdInsumo());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El ID del Insumo no cumple con el formato permitido.');
        $this->movimiento->setIdInsumo('malo!');
    }

    #[Test]
    public function testSetCantidadValidaENegativa(): void
    {
        $this->movimiento->setCantidad('25.0');
        $this->assertEquals('25.0', $this->movimiento->getCantidad());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El valor ingresado no puede ser negativo');
        $this->movimiento->setCantidad('-10.0');
    }

    #[Test]
    public function testTransaccionPeticionInvalida(): void
    {
        $res = $this->movimiento->Transaccion(['peticion' => 'desconocida']);
        $this->assertEquals(400, $res['response']['resultado']);
    }
}
