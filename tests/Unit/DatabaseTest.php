<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Core\Database;
use PDO;

class DatabaseTest extends TestCase
{
    #[Test]
    public function testGetSystemYSecurityDb(): void
    {
        $sysDb = Database::getSystemDb();
        $this->assertNotEmpty($sysDb);

        $secDb = Database::getSecurityDb();
        $this->assertNotEmpty($secDb);
    }

    #[Test]
    public function testGetConnectionBusiness(): void
    {
        $pdo = Database::getConnection('business');
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    #[Test]
    public function testGetConnectionSecurity(): void
    {
        $pdo = Database::getConnection('security');
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    #[Test]
    public function testLlamarYDestruirConexion(): void
    {
        $db = new Database();
        $pdo = $db->LlamarConexion('business');
        $this->assertInstanceOf(PDO::class, $pdo);

        $db->DestruirConexion(true);
        $this->assertTrue(true);
    }
}
