<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Services\MarIaService;

class MarIaServiceTest extends TestCase
{
    #[Test]
    public function testInstanciacionMarIaService(): void
    {
        $service = new MarIaService();
        $this->assertInstanceOf(MarIaService::class, $service);
    }

    #[Test]
    public function testConsultarMarIaRespuestaControlada(): void
    {
        $service = new MarIaService();
        // Si el microservicio de IA local no está encendido, debe retornar array con 'error' sin arrojar unhandled exception
        $res = $service->consultar('V-12345678', 'dame el reporte de ventas');
        $this->assertIsArray($res);
    }
}
