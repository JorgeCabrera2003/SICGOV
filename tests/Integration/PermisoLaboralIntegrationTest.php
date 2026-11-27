<?php

namespace Tests\Integration;

use App\Core\Database;
use App\Models\System\PermisoLaboral;
use App\Models\System\TipoPermiso;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PermisoLaboralIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private string $cedulaTest;
    private string $idTipoPermisoTest = 'TPERINT001202611270001';
    private string $idPermisoTest = 'PERMINT001202611270001';

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
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $stmt = $this->dbBusiness->prepare('DELETE FROM permiso_laboral WHERE id_permiso = ?');
        $stmt->execute([$this->idPermisoTest]);

        $stmt = $this->dbBusiness->prepare('DELETE FROM tipo_permiso WHERE id_tipo_permiso = ?');
        $stmt->execute([$this->idTipoPermisoTest]);
    }

    #[Test]
    public function testCicloDeVidaPermisoLaboralYRelaciones(): void
    {
        $tipoPermiso = new TipoPermiso();
        $tipoPermiso->setId($this->idTipoPermisoTest);
        $tipoPermiso->setNombre('Permiso integración');
        $registroTipo = $tipoPermiso->Transaccion(['peticion' => 'registrar']);
        $this->assertSame(200, $registroTipo['response']['resultado']);

        $permiso = new PermisoLaboral();
        $permiso->setId($this->idPermisoTest);
        $permiso->setIdTipoPermiso($this->idTipoPermisoTest);
        $permiso->setCedulaEmpleado($this->cedulaTest);
        $permiso->setFechaInicio('2099-06-01');
        $permiso->setFechaFin('2099-06-02');

        $registro = $permiso->Transaccion(['peticion' => 'registrar']);
        $this->assertSame(200, $registro['response']['resultado']);

        $consulta = $permiso->Transaccion([
            'peticion' => 'consultar',
            'cedula_empleado' => $this->cedulaTest,
        ]);
        $this->assertSame(200, $consulta['response']['resultado']);
        $this->assertCount(1, array_filter(
            $consulta['response']['datos'],
            fn (array $fila): bool => $fila['id_permiso'] === $this->idPermisoTest
        ));

        $permiso->setFechaFin('2099-06-04');
        $permiso->setFechaInicio('2099-06-03');
        $modificacion = $permiso->Transaccion(['peticion' => 'modificar']);
        $this->assertSame(200, $modificacion['response']['resultado']);

        $permiso->setFechaAprobacion(date('Y-m-d H:i:s'));
        $aprobacion = $permiso->Transaccion(['peticion' => 'aprobar']);
        $this->assertSame(200, $aprobacion['response']['resultado']);

        $consultaBD = $this->dbBusiness->prepare(
            'SELECT id_tipo_permiso, cedula_empleado, fecha_inicio, fecha_fin, estado, estatus
             FROM permiso_laboral WHERE id_permiso = ?'
        );
        $consultaBD->execute([$this->idPermisoTest]);
        $permisoPersistido = $consultaBD->fetch(PDO::FETCH_ASSOC);
        $this->assertIsArray($permisoPersistido);
        $this->assertSame($this->idTipoPermisoTest, $permisoPersistido['id_tipo_permiso']);
        $this->assertSame($this->cedulaTest, $permisoPersistido['cedula_empleado']);
        $this->assertSame('2099-06-03', $permisoPersistido['fecha_inicio']);
        $this->assertSame('2099-06-04', $permisoPersistido['fecha_fin']);
        $this->assertSame('APROBADO', $permisoPersistido['estado']);

        $eliminacion = $permiso->Transaccion(['peticion' => 'eliminar']);
        $this->assertSame(200, $eliminacion['response']['resultado']);

        $consultaBD->execute([$this->idPermisoTest]);
        $this->assertSame(0, (int) $consultaBD->fetch(PDO::FETCH_ASSOC)['estatus']);
    }
}
