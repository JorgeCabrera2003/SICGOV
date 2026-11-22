<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Helpers\BackupHelper;

class BackupHelperTest extends TestCase
{
    #[Test]
    public function testGetAllowedDbs(): void
    {
        $dbs = BackupHelper::getAllowedDbs();
        $this->assertIsArray($dbs);
        $this->assertNotEmpty($dbs);
        $this->assertContains('good_vibes', $dbs);
        $this->assertContains('goobv-sistema', $dbs);
        $this->assertContains('goobv-usuarios', $dbs);
    }

    #[Test]
    public function testGenerarRespaldoBaseNoPermitida(): void
    {
        $res = BackupHelper::generarRespaldo('base_falsa_hack');
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('no permitida', $res['message']);
    }
}
