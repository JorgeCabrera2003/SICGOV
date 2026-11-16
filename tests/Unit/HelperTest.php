<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Helpers\Helper;

class HelperTest extends TestCase
{
    #[Test]
    public function testGenerarIdFormato(): void
    {
        $id = Helper::generarId('CLIE');
        $this->assertIsString($id);
        $this->assertStringStartsWith('CLIE', $id);
        $this->assertGreaterThanOrEqual(18, strlen($id));
    }

    #[Test]
    public function testGenerarIdConClave(): void
    {
        $id = Helper::generarId('PEDI', 'MESA1', 5);
        $this->assertIsString($id);
        $this->assertStringStartsWith('PEDIMESA1', $id);
    }

    #[Test]
    public function testGenerarIdUnicidad(): void
    {
        $id1 = Helper::generarId('BIT', null, 1);
        $id2 = Helper::generarId('BIT', null, 2);
        $this->assertNotEquals($id1, $id2);
    }

    #[Test]
    public function testFixPath(): void
    {
        $path = Helper::fixPath('app/Controllers/LoginController.php');
        $this->assertStringNotContainsString('/', $path);
        $this->assertStringContainsString(DS, $path);
    }
}
