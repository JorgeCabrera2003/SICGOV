<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\PedidoPublico;

class PedidoPublicoTest extends TestCase
{
    #[Test]
    public function testInstanciacion(): void
    {
        $pedidoWeb = new PedidoPublico();
        $this->assertInstanceOf(PedidoPublico::class, $pedidoWeb);
    }
}
