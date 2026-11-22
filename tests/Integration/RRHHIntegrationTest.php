<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Cargo;
use App\Models\System\Turno;
use App\Models\System\TipoPermiso;
use App\Models\System\Asistencia;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Recursos Humanos
 * Evalúa interacción de Cargos, Turnos, Tipos de Permiso y Asistencias en MariaDB.
 */
class RRHHIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private Cargo $cargoModel;
    private Turno $turnoModel;
    private TipoPermiso $tipoPermisoModel;
    private Asistencia $asistenciaModel;

    private string $idCargoTest = 'CARG0012026010112';
    private string $idTurnoTest = 'TURN0012026010112';
    private string $idTipoPermisoTest = 'TPER0012026010112';

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $_SESSION['user'] = [
            'cedula' => 'V-00000000',
            'username' => 'admin_test',
            'id_rol' => 'ADMIN00120251001',
            'rol' => 'Administrador',
            'estatus_clave' => 1
        ];

        $this->dbBusiness = Database::getConnection('business');
        $this->cargoModel = new Cargo();
        $this->turnoModel = new Turno();
        $this->tipoPermisoModel = new TipoPermiso();
        $this->asistenciaModel = new Asistencia();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM tipo_permiso WHERE id_tipo_permiso = '{$this->idTipoPermisoTest}'");
        $this->dbBusiness->exec("DELETE FROM turno WHERE id_turno = '{$this->idTurnoTest}'");
        $this->dbBusiness->exec("DELETE FROM cargo WHERE id_cargo = '{$this->idCargoTest}'");
    }

    #[Test]
    public function testConsultasCatalogosRRHHEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $resCargos = $this->cargoModel->Transaccion(['peticion' => 'consultar']);
        $resTurnos = $this->turnoModel->Transaccion(['peticion' => 'consultar']);
        $resPermisos = $this->tipoPermisoModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertEquals(200, $resCargos['HTTP_STATUS']['codigo']);
        $this->assertEquals(200, $resTurnos['HTTP_STATUS']['codigo']);
        $this->assertEquals(200, $resPermisos['HTTP_STATUS']['codigo']);
        $this->assertLessThan(400, $duracionMs);
    }

    #[Test]
    public function testCicloDeVidaCargoYTurnoPersistencia(): void
    {
        // 1. Registrar Cargo
        $this->cargoModel->setId($this->idCargoTest);
        $this->cargoModel->setNombre('Sommelier Especialista Test');
        $resCargo = $this->cargoModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($resCargo['response']['resultado'], [200, 201]);

        // 2. Registrar Turno
        $this->turnoModel->setIdTurno($this->idTurnoTest);
        $this->turnoModel->setNombre('Turno Degustacion Nocturno Test');
        $this->turnoModel->setHoraInicio('19:00:00');
        $this->turnoModel->setHoraFin('02:00:00');
        $this->turnoModel->setMinutoTolerancia(15);
        $this->turnoModel->setEstatus(1);
        $resTurno = $this->turnoModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($resTurno['response']['resultado'], [200, 201]);

        // 3. Registrar Tipo de Permiso
        $this->tipoPermisoModel->setId($this->idTipoPermisoTest);
        $this->tipoPermisoModel->setNombre('Permiso Capacitacion Test');
        $this->tipoPermisoModel->setEstatus(1);
        $resPermiso = $this->tipoPermisoModel->Transaccion(['peticion' => 'registrar']);
        $this->assertContains($resPermiso['response']['resultado'], [200, 201]);

        // 4. Validar en Base de Datos
        $stmtCargo = $this->dbBusiness->prepare("SELECT * FROM cargo WHERE id_cargo = ?");
        $stmtCargo->execute([$this->idCargoTest]);
        $this->assertNotEmpty($stmtCargo->fetch());

        $stmtTurno = $this->dbBusiness->prepare("SELECT * FROM turno WHERE id_turno = ?");
        $stmtTurno->execute([$this->idTurnoTest]);
        $this->assertNotEmpty($stmtTurno->fetch());

        // 5. Modificar Cargo
        $this->cargoModel->setNombre('Sommelier Senior Test');
        $resMod = $this->cargoModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $resMod['response']['resultado']);
    }
}
