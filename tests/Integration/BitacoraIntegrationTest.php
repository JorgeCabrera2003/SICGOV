<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Security\Bitacora;
use App\Helpers\Helper;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Bitácora y Auditoría
 * Evalúa el registro inmutable de transacciones, serialización de cambios JSON,
 * filtrado por módulos y usuarios en MariaDB (goobv-usuarios).
 */
class BitacoraIntegrationTest extends TestCase
{
    private PDO $dbSecurity;
    private Bitacora $bitacoraModel;
    private string $idBitacoraTest;
    private string $cedulaAdmin = 'V-00000000';

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $_SESSION['user'] = [
            'cedula' => $this->cedulaAdmin,
            'username' => 'admin_test',
            'id_rol' => 'ADMIN00120251001',
            'rol' => 'Administrador',
            'estatus_clave' => 1
        ];

        $this->dbSecurity = Database::getConnection('security');
        $this->bitacoraModel = new Bitacora();
        $this->idBitacoraTest = Helper::generarId('BIT');

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbSecurity->exec("DELETE FROM bitacora WHERE id_bitacora = '{$this->idBitacoraTest}'");
    }

    #[Test]
    public function testListarBitacoraEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->bitacoraModel->Transaccion(['peticion' => 'listar', 'filtros' => []]);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertLessThan(400, $duracionMs);
    }

    #[Test]
    public function testRegistrarYBuscarEventoBitacora(): void
    {
        $this->bitacoraModel->setIdBitacora($this->idBitacoraTest);
        $this->bitacoraModel->set_cedula($this->cedulaAdmin);
        $this->bitacoraModel->set_modulo('CLIENTES');
        $this->bitacoraModel->set_accion('REGISTRAR');
        $this->bitacoraModel->set_detalle('Registro de cliente de prueba para auditoria');
        $this->bitacoraModel->set_ip_address('127.0.0.1');
        $this->bitacoraModel->set_anteriores(json_encode(['status' => 'none']));
        $this->bitacoraModel->set_nuevos(json_encode(['status' => 'created']));
        $this->bitacoraModel->set_fecha(date('Y-m-d H:i:s'));

        $resReg = $this->bitacoraModel->Transaccion(['peticion' => 'registrar']);
        $this->assertTrue($resReg);

        // Validar persistencia real
        $stmt = $this->dbSecurity->prepare("SELECT * FROM bitacora WHERE id_bitacora = ?");
        $stmt->execute([$this->idBitacoraTest]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($evento);
        $this->assertEquals('CLIENTES', $evento['modulo']);
        $this->assertEquals('REGISTRAR', $evento['accion']);
        $this->assertEquals('127.0.0.1', $evento['ip_address']);
    }
}
