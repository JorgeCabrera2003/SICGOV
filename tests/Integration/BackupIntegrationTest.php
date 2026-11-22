<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Helpers\BackupHelper;

/**
 * Suite de Pruebas de Integración para el Módulo de Respaldo de Base de Datos
 * Evalúa lista blanca de bases de datos, generación de dumps SQL y exploración de backups.
 */
class BackupIntegrationTest extends TestCase
{
    private string $backupTestDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupTestDir = BASE_PATH . '/storage/backups_test';
        if (!is_dir($this->backupTestDir)) {
            @mkdir($this->backupTestDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->backupTestDir)) {
            $files = glob($this->backupTestDir . '/*');
            foreach ($files as $f) {
                if (is_file($f)) @unlink($f);
            }
            @rmdir($this->backupTestDir);
        }
        parent::tearDown();
    }

    #[Test]
    public function testValidacionListaBlancaBasesDeDatos(): void
    {
        $allowed = BackupHelper::getAllowedDbs();
        $this->assertIsArray($allowed);
        $this->assertContains('goobv-sistema', $allowed);
        $this->assertContains('goobv-usuarios', $allowed);

        // Intentar respaldar base de datos no permitida
        $resInvalido = BackupHelper::generarRespaldo('mysql_inyeccion_test', $this->backupTestDir);
        $this->assertFalse($resInvalido['success']);
        $this->assertStringContainsString('no permitida', $resInvalido['message']);
    }

    #[Test]
    public function testListarRespaldosEstructura(): void
    {
        $respaldos = BackupHelper::listarRespaldos();
        $this->assertIsArray($respaldos);
    }
}
