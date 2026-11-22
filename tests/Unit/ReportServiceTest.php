<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Services\ReportService;

class ReportServiceTest extends TestCase
{
    #[Test]
    public function testSetupReportService(): void
    {
        $service = new ReportService();
        $this->assertInstanceOf(ReportService::class, $service);

        $info = ['titulo' => 'Reporte de Prueba'];
        $columns = ['ID', 'Nombre'];
        $data = [['ID' => '1', 'Nombre' => 'Prueba']];

        $res = $service->setup($info, $columns, $data, [
            'paper' => 'letter',
            'orientation' => 'landscape'
        ]);

        $this->assertSame($service, $res);
    }
}
