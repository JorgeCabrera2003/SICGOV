<?php

namespace Tests\Integration;

use App\Core\Database;
use App\Models\System\Asistencia;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AsistenciaIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private string $cedulaTest;
    private string $fechaTest;
    private string $idEntradaTest = 'ASISINT001202611270001';
    private string $idEntradaDuplicadaTest = 'ASISINT002202611270001';

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
        $this->fechaTest = $this->buscarFechaSinMarcaciones();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $stmt = $this->dbBusiness->prepare(
            'DELETE FROM asistencia WHERE id_asistencia IN (?, ?)'
        );
        $stmt->execute([$this->idEntradaTest, $this->idEntradaDuplicadaTest]);
    }

    private function buscarFechaSinMarcaciones(): string
    {
        $stmt = $this->dbBusiness->prepare(
            'SELECT 1 FROM asistencia WHERE cedula_empleado = ? AND fecha = ? LIMIT 1'
        );
        $fecha = new DateTimeImmutable('+1 year');

        for ($intento = 0; $intento < 3650; $intento++) {
            $fechaCandidata = $fecha->modify("+{$intento} days")->format('Y-m-d');
            $stmt->execute([$this->cedulaTest, $fechaCandidata]);
            if ($stmt->fetchColumn() === false) {
                return $fechaCandidata;
            }
        }

        $this->fail('No se encontró una fecha disponible para las marcaciones de prueba.');
    }

    #[Test]
    public function testRegistrarAsistenciaPersisteYRechazaEntradaDuplicada(): void
    {
        $asistencia = new Asistencia();
        $asistencia->setIdAsistencia($this->idEntradaTest);
        $asistencia->setCedulaEmpleado($this->cedulaTest);
        $asistencia->setTipoMarcacion('ENTRADA');
        $asistencia->setFecha($this->fechaTest);
        $asistencia->setHora('08:00:00');
        $asistencia->setEstado('A_TIEMPO');

        $resultado = $asistencia->Transaccion(['peticion' => 'registrar']);

        $this->assertSame(200, $resultado['response']['resultado']);

        $consulta = $this->dbBusiness->prepare(
            'SELECT cedula_empleado, tipo_marcacion, fecha, hora, estado
             FROM asistencia WHERE id_asistencia = ?'
        );
        $consulta->execute([$this->idEntradaTest]);
        $registro = $consulta->fetch(PDO::FETCH_ASSOC);

        $this->assertSame($this->cedulaTest, $registro['cedula_empleado']);
        $this->assertSame('ENTRADA', $registro['tipo_marcacion']);
        $this->assertSame($this->fechaTest, $registro['fecha']);
        $this->assertSame('08:00:00', $registro['hora']);
        $this->assertSame('A_TIEMPO', $registro['estado']);

        $duplicada = new Asistencia();
        $duplicada->setIdAsistencia($this->idEntradaDuplicadaTest);
        $duplicada->setCedulaEmpleado($this->cedulaTest);
        $duplicada->setTipoMarcacion('ENTRADA');
        $duplicada->setFecha($this->fechaTest);
        $duplicada->setHora('08:05:00');
        $duplicada->setEstado('A_TIEMPO');

        $resultadoDuplicado = $duplicada->Transaccion(['peticion' => 'registrar']);

        $this->assertSame(409, $resultadoDuplicado['response']['resultado']);
        $conteo = $this->dbBusiness->prepare(
            'SELECT COUNT(*) FROM asistencia WHERE cedula_empleado = ? AND fecha = ?'
        );
        $conteo->execute([$this->cedulaTest, $this->fechaTest]);
        $this->assertSame(1, (int) $conteo->fetchColumn());
    }
}
