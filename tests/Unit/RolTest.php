<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\Security\Rol;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class RolTest extends TestCase
{
    private function createRolMock(PDO&MockObject $mockPdo): Rol&MockObject
    {
        $mock = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    #[Test]
    public function testSetIdValidoEnMayusculas(): void
    {
        $rol = new Rol();
        // ID con formato válido en mayúsculas de acuerdo a RegexHelper
        $rol->setId('ROL0120260101120000');
        $this->assertEquals('ROL0120260101120000', $rol->getId());
    }

    #[Test]
    public function testSetIdConMinusculasEsRechazadoPorRegexEstricto(): void
    {
        // Revela incoherencia en Rol.php: la línea 40 intenta hacer strtoupper($id[0]),
        // pero la validación previa de RegexHelper rechaza cualquier minúscula lanzando excepción.
        $rol = new Rol();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ID no válido.');
        $rol->setId('rol0120260101120000');
    }

    #[Test]
    public function testSetIdInvalidoLanzaExcepcion(): void
    {
        $rol = new Rol();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ID no válido.');
        $rol->setId('rol-invalido-!');
    }

    #[Test]
    public function testSetNombreValidoAsignaValor(): void
    {
        $rol = new Rol();
        $rol->setNombre('Administrador');
        $this->assertEquals('Administrador', $rol->getNombre());

        $rol->setNombre('Mesero de Barra');
        $this->assertEquals('Mesero de Barra', $rol->getNombre());
    }

    #[Test]
    public function testSetNombreConCaracteresEspecialesLanzaExcepcion(): void
    {
        $rol = new Rol();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nombre no válido. Debe tener al menos entre 3 a 150 carácteres');
        $rol->setNombre('Admin$$$123');
    }

    #[Test]
    public function testSetNombreDemasiadoCortoLanzaExcepcion(): void
    {
        $rol = new Rol();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nombre no válido. Debe tener al menos entre 3 a 150 carácteres');
        $rol->setNombre('AB');
    }

    #[Test]
    public function testConsultarRolesActivosRetornaLista(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('rowCount')->willReturn(2);
        $mockStmt->method('fetchAll')->willReturn([
            ['id_rol' => 'ROL001', 'nombre_rol' => 'Administrador', 'estatus' => 1],
            ['id_rol' => 'ROL002', 'nombre_rol' => 'Mesero', 'estatus' => 1]
        ]);

        $mockPdo->method('prepare')->willReturn($mockStmt);

        $rolMock = $this->createRolMock($mockPdo);
        $resultado = $rolMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertCount(2, $resultado['response']['datos']);
    }

    #[Test]
    public function testRegistrarRolNuevoTransaccionExitosa(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtValidar = $this->createMock(PDOStatement::class);
        $mockStmtInsert = $this->createMock(PDOStatement::class);

        // Validar no existe
        $mockStmtValidar->method('execute')->willReturn(true);
        $mockStmtValidar->method('rowCount')->willReturn(0);
        $mockStmtValidar->method('fetch')->willReturn(false);

        // Inserción
        $mockStmtInsert->method('execute')->willReturn(true);

        $mockPdo->method('prepare')->willReturnCallback(function($sql) use ($mockStmtValidar, $mockStmtInsert) {
            if (str_contains($sql, 'SELECT * FROM rol')) {
                return $mockStmtValidar;
            }
            return $mockStmtInsert;
        });

        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $rolMock = $this->createRolMock($mockPdo);
        $rolMock->setId('ROL0012026010112');
        $rolMock->setNombre('Bartender');

        $resultado = $rolMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(201, $resultado['response']['resultado']);
        $this->assertEquals('Rol registrado exitosamente', $resultado['response']['mensaje']);
    }

    #[Test]
    public function testRegistrarRolDuplicadoOmiteInsercionRetornaArregloVacio(): void
    {
        // Revela detalle de implementación: si el rol ya existe, la función no define el estado de error y retorna []
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtValidar = $this->createMock(PDOStatement::class);

        $mockStmtValidar->method('execute')->willReturn(true);
        $mockStmtValidar->method('rowCount')->willReturn(1);
        $mockStmtValidar->method('fetch')->willReturn(['id_rol' => 'ROL001', 'nombre_rol' => 'Administrador']);

        $mockPdo->method('prepare')->willReturn($mockStmtValidar);
        $mockPdo->method('beginTransaction');
        $mockPdo->method('commit');

        $rolMock = $this->createRolMock($mockPdo);
        $rolMock->setId('ROL0012026010112');
        $rolMock->setNombre('Administrador');

        $resultado = $rolMock->Transaccion(['peticion' => 'registrar']);
        $this->assertEmpty($resultado);
    }

    #[Test]
    public function testTransaccionPeticionInvalidaRetorna400(): void
    {
        $rol = new Rol();
        $resultado = $rol->Transaccion(['peticion' => 'hack_inexistente']);
        $this->assertEquals(400, $resultado['response']['resultado']);
        $this->assertEquals(400, $resultado['HTTP_STATUS']['codigo']);
    }
}
