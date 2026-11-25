<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\Security\Permiso;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

final class PermisoTest extends TestCase
{

    private $objPermiso;

    protected function setUp(): void
    {
        $this->objPermiso = new Permiso();
    }


    /**
     * Crea un mock parcial de Permiso para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createPermisoMock(PDO&MockObject $mockPdo): Permiso&MockObject
    {
        $mock = $this->getMockBuilder(Permiso::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto Permiso.
     */
    private function poblarDatosValidos(Permiso $permiso): void
    {
        $permiso->setIdModulo('MOD010001120260519200547298');
        $permiso->setId('PERMPRU01120260519200547298');
        $permiso->setIdRol('ROL010001120260519200547298');
        $permiso->setAccion('prueba');
        $permiso->setEstado(1);
    }

    // =========================================================================
    // 1. MÉTODO: FiltrarPermiso (Transacción 'filtrar' -> SELECT categoria de insumo)
    // =========================================================================
    public function test_iltrar_permisos_con_datos_vacios_lanza_excepcion(): void
    {

        // 1. ID vacía
        try {
            $this->objPermiso->setId('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("ID no válido.", $e->getMessage());
        }

        // 2. ID Modulo vacío
        try {
            $this->objPermiso->setIdModulo('');
            $this->fail("Se esperaba Exception por id del modulo vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("ID no válido.", $e->getMessage());
        }

        // 3. ID Rol vacío
        try {
            $this->objPermiso->setIdRol('');
            $this->fail("Se esperaba Exception por id del rol vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("ID no válido.", $e->getMessage());
        }

        // 4. Nombre de Accion vacío
        try {
            $this->objPermiso->setAccion('');
            $this->fail("Se esperaba Exception por accion vacía.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Acción no válida.", $e->getMessage());
        }

        // 5. Estado vacío
        try {
            $this->objPermiso->setEstado('');
            $this->fail("Se esperaba Exception por estado vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Estado no válido.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Formatos no válidos en id y nombre.
     * - Regla de negocio: rol ya registrado previamente en la tabla 'rol'.
     * - Simulación de error SQL para generar rollback.
     */
    public function test_filtrar_permisos_con_datos_incorrectos_falla(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objPermiso->setId('');
            $this->fail("Se esperaba Exception por id del modulo vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("ID no válido.", $e->getMessage());
        }

        // 2. ID Modulo vacío
        try {
            $this->objPermiso->setIdModulo('');
            $this->fail("Se esperaba Exception por id del modulo vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("ID no válido.", $e->getMessage());
        }

        // 3. ID Rol vacío
        try {
            $this->objPermiso->setIdRol('');
            $this->fail("Se esperaba Exception por id del rol vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("ID no válido.", $e->getMessage());
        }

        // 4. Nombre de Accion vacío
        try {
            $this->objPermiso->setAccion('');
            $this->fail("Se esperaba Exception por acción vacía.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Acción no válida.", $e->getMessage());
        }

        // 5. Estado vacío
        try {
            $this->objPermiso->setEstado('');
            $this->fail("Se esperaba Exception por estado vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Estado no válido.", $e->getMessage());
        }

        // 6. Manejo de excepciones por si ocurre algún error al ejecutar la sentencia SQL
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckPermiso = $this->createMock(PDOStatement::class);


        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckPermiso);
        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockCheckPermiso->expects($this->once())->method('execute')->willReturn(true)->willThrowException(new PDOException("Error de Base de Datos"));

        $permisoMock = $this->createPermisoMock($mockPdo);
        $this->poblarDatosValidos($permisoMock);

        $resultado = $permisoMock->Transaccion(['peticion' => 'filtrar', 'parametro' => 'nombre', 'modulo' => 'MOD010001120260519200547298']);
        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
    }

    /**
     * Caso con datos correctos al filtrar:
     * Verifica consulta en 'permiso', confirmando la transacción con commit.
     */
    public function test_filtrar_permisos_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockSelectPermiso = $this->createMock(PDOStatement::class);

        // 1. Ejecución de la consulta
        $mockSelectPermiso->expects($this->exactly(1))->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(1))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockSelectPermiso) {
            if (
                str_contains($sql, 'FROM permiso AS p')
            ) {
                return $mockSelectPermiso;
            }
        });
        $mockPdo->expects($this->exactly(1))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $permisoMock = $this->createPermisoMock($mockPdo);
        $this->poblarDatosValidos($permisoMock);

        $resultado = $permisoMock->Transaccion(['peticion' => 'filtrar', 'parametro' => 'nombre', 'modulo' => 'MOD010001120260519200547298']);

        $this->assertEquals(200, $resultado['response']['resultado']);
    }

    protected function tearDown(): void
    {
        $this->objPermiso = NULL;
    }
}