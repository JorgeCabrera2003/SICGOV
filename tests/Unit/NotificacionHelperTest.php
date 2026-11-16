<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Helpers\NotificacionHelper;

class NotificacionHelperTest extends TestCase
{
    #[Test]
    public function testInstanciacionNotificacionHelper(): void
    {
        $helper = new NotificacionHelper();
        $this->assertInstanceOf(NotificacionHelper::class, $helper);
    }
}
