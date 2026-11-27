<?php

namespace Tests\Integration;

use App\Core\Database;
use App\Models\System\Horario;
use App\Models\System\Turno;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HorarioIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private string $cedulaTest;
    private string $fechaTest;
    private string $idHorarioTest = 'HORAINT001202611270001';
    private string $idTurnoInicialTest = 'TURINT001202611270001';
    private string $idTurnoModificadoTest = 'TURINT002202611270001';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbBusiness = Database::getConnection('business');
        $cedula = $this->dbBusiness->query(
            'SELECT cedula FROM empleado WHERE estatus = 1 ORDER BY cedula LIMIT 1'
        )->fetchColumn();
        $this->assertNotFalse($cedula, 'La base de pruebas debe tener al menos un empleado activo.');
        $this->cedulaTest = (string) $cedula;

        $this->limpiarDatosPrueba();
        $this->fechaTest = $this->buscarFechaSinAsignacion();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $stmt = $this->dbBusiness->prepare(
            'DELETE FROM planificador_turno WHERE id_planificador_turno = ?'
        );
        $stmt->execute([$this->idHorarioTest]);

        $stmt = $this->dbBusiness->prepare('DELETE FROM turno WHERE id_turno IN (?, ?)');
        $stmt->execute([$this->idTurnoInicialTest, $this->idTurnoModificadoTest]);
    }

    private function buscarFechaSinAsignacion(): string
    {
        $stmt = $this->dbBusiness->prepare(
            'SELECT 1 FROM planificador_turno WHERE cedula_empleado = ? AND fecha = ? LIMIT 1'
        );
        $fecha = new DateTimeImmutable('+1 year');

        for ($intento = 0; $intento < 3650; $intento++) {
            $fechaCandidata = $fecha->modify("+{$intento} days")->format('Y-m-d');
            $stmt->execute([$this->cedulaTest, $fechaCandidata]);
            if ($stmt->fetchColumn() === false) {
                return $fechaCandidata;
            }
        }

        $this->fail('No se encontró una fecha disponible para la asignación de prueba.');
    }

    private function registrarTurno(string $id, string $nombre, string $horaInicio): void
    {
        $turno = new Turno();
        $turno->setIdTurno($id);
        $turno->setNombre($nombre);
        $turno->setHoraInicio($horaInicio);
        $turno->setHoraFin('17:00');

        $resultado = $turno->Transaccion(['peticion' => 'registrar']);
        $this->assertSame(201, $resultado['response']['resultado']);
    }

    #[Test]
    public function testCicloDeVidaAsignacionDeHorario(): void
    {
        $this->registrarTurno($this->idTurnoInicialTest, 'Turno integración inicial', '08:00');
        $this->registrarTurno($this->idTurnoModificadoTest, 'Turno integración actualizado', '09:00');

        $horario = new Horario();
        $horario->setId($this->idHorarioTest);
        $horario->setCedulaEmpleado($this->cedulaTest);
        $horario->setIdTurno($this->idTurnoInicialTest);
        $horario->setFecha($this->fechaTest);

        $registro = $horario->Transaccion(['peticion' => 'registrar']);
        $this->assertSame(201, $registro['response']['resultado']);

        $validacion = $horario->Transaccion(['peticion' => 'validar']);
        $this->assertSame(1, $validacion['bool']);

        $duplicado = $horario->Transaccion(['peticion' => 'registrar']);
        $this->assertSame(409, $duplicado['response']['resultado']);

        $horario->setIdTurno($this->idTurnoModificadoTest);
        $modificacion = $horario->Transaccion([
            'peticion' => 'modificar',
            'cedula_empleado' => $this->cedulaTest,
        ]);
        $this->assertSame(200, $modificacion['response']['resultado']);

        $consulta = $this->dbBusiness->prepare(
            'SELECT id_turno, cedula_empleado, fecha
             FROM planificador_turno WHERE id_planificador_turno = ?'
        );
        $consulta->execute([$this->idHorarioTest]);
        $asignacion = $consulta->fetch(PDO::FETCH_ASSOC);
        $this->assertIsArray($asignacion);
        $this->assertSame($this->idTurnoModificadoTest, $asignacion['id_turno']);
        $this->assertSame($this->cedulaTest, $asignacion['cedula_empleado']);
        $this->assertSame($this->fechaTest, $asignacion['fecha']);

        $eliminacion = $horario->Transaccion([
            'peticion' => 'eliminar',
            'cedula_empleado' => $this->cedulaTest,
        ]);
        $this->assertSame(200, $eliminacion['response']['resultado']);
        $consulta->execute([$this->idHorarioTest]);
        $this->assertFalse($consulta->fetch(PDO::FETCH_ASSOC));
    }
}
