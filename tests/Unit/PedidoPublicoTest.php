<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\PedidoPublico;
use ReflectionClass;

class PedidoPublicoTest extends TestCase
{
    #[Test]
    public function testInstanciacion(): void
    {
        $reflection = new ReflectionClass(PedidoPublico::class);
        $pedidoWeb = $reflection->newInstanceWithoutConstructor();
        $this->assertInstanceOf(PedidoPublico::class, $pedidoWeb);
    }
}
