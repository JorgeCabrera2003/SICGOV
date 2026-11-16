<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Helpers\NotificacionHelper;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Notificaciones
 * Evalúa registro transaccional, consulta de no leídas, marcado y conteo en MariaDB.
 */
class NotificacionIntegrationTest extends TestCase
{
    private PDO $dbSecurity;
    private string $cedulaTest = 'V-00000000';
    private ?string $idNotificacionCreada = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbSecurity = Database::getConnection('security');
        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbSecurity->exec("DELETE FROM notificacion WHERE cedula = '{$this->cedulaTest}' AND mensaje LIKE '%Prueba de integracion%'");
    }

    #[Test]
    public function testFlujoCompletoNotificaciones(): void
    {
        // 1. Registrar notificación
        $id = NotificacionHelper::registrarNotificacion(
            'INFO',
            'Prueba de integracion de notificaciones en tiempo real',
            $this->cedulaTest,
            'Alerta de Sistema Test'
        );

        $this->assertNotFalse($id);
        $this->assertIsString($id);
        $this->idNotificacionCreada = $id;

        // 2. Obtener no leídas
        $noLeidas = NotificacionHelper::obtenerNoLeidas($this->cedulaTest);
        $this->assertIsArray($noLeidas);
        $this->assertNotEmpty($noLeidas);

        // 3. Obtener cantidad de no leídas
        $cantidad = NotificacionHelper::obtenerCantidadNoLeidas($this->cedulaTest);
        $this->assertGreaterThanOrEqual(1, $cantidad);

        // 4. Marcar como leída
        $resMarcar = NotificacionHelper::marcarComoLeida($id, $this->cedulaTest);
        $this->assertTrue($resMarcar);

        // 5. Verificar en Base de Datos que leido = 1
        $stmt = $this->dbSecurity->prepare("SELECT leido FROM notificacion WHERE id_notificacion = ?");
        $stmt->execute([$id]);
        $leido = $stmt->fetchColumn();
        $this->assertEquals(1, (int)$leido);
    }
}
