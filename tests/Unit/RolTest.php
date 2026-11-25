<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\Security\Rol;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

final class RolTest extends TestCase
{

    private $objRol;

    protected function setUp(): void
    {
        $this->objRol = new Rol();
    }


    /**
     * Crea un mock parcial de Rol para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createRolMock(PDO&MockObject $mockPdo): Rol&MockObject
    {
        $mock = $this->getMockBuilder(Rol::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto Rol.
     */
    private function poblarDatosValidos(Rol $rol): void
    {
        $rol->setNombre('Invitado Prueba');
        $rol->setId('ROL010001120260519200547298');
    }

    // =========================================================================
    // 1. MÉTODO: RegistrarRol (Transacción 'registrar' -> INSERT categoria de insumo)
    // =========================================================================
    public function test_registrar_rol_con_datos_vacios_lanza_excepcion(): void
    {

        // 1. ID vacía
        try {
            $this->objRol->setId('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("ID no válido.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $this->objRol->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Nombre no válido. Debe tener al menos entre 3 a 150 carácteres", $e->getMessage());
        }

    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Formatos no válidos en id y nombre.
     * - Regla de negocio: rol ya registrado previamente en la tabla 'rol'.
     * - Simulación de error SQL para generar rollback.
     */
    public function test_registrar_rol_con_datos_incorrectos_falla(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objRol->setId('ROL01000112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("ID no válido.", $e->getMessage());
        }

        // 2. Nombre con formato inválido
        try {
            $this->objRol->setNombre('Nombre_Fallo');
            $this->fail("Se esperaba Exception por nombre inválido.");
        } catch (Exception $e) {
            $this->assertEquals("Nombre no válido. Debe tener al menos entre 3 a 150 carácteres", $e->getMessage());
        }

        // 3. Regla de negocio: el rol ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCategoria = $this->createMock(PDOStatement::class);

        $mockCheckCategoria->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckCategoria->expects($this->once())->method('rowCount')->willReturn(1); // Ya existe

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckCategoria);
        $mockPdo->expects($this->once())->method('beginTransaction');

        $rolMock = $this->createRolMock($mockPdo);
        $this->poblarDatosValidos($rolMock);

        $resultado = $rolMock->Transaccion(['peticion' => 'registrar']);
        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(409, $resultado['response']['resultado']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Verifica inserción en 'rol', confirmando la transacción con commit.
     */
    public function test_registrar_rol_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckRol = $this->createMock(PDOStatement::class);
        $mockInsertRol = $this->createMock(PDOStatement::class);

        // 1. Verificación rol: no existe (rowCount = 0)
        $mockCheckRol->expects($this->exactly(2))->method('execute')->willReturn(true);

        // 2. Inserciones exitosas
        $mockInsertRol->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(3))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckRol, $mockInsertRol) {
            if (str_contains($sql, 'SELECT * FROM rol WHERE id_rol = :id_rol')) {
                return $mockCheckRol;
            }
            if (str_contains($sql, 'INSERT INTO rol')) {
                return $mockInsertRol;
            }
        });
        $mockPdo->expects($this->exactly(3))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $rolMock = $this->createRolMock($mockPdo);
        $this->poblarDatosValidos($rolMock);

        $resultado = $rolMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(201, $resultado['response']['resultado']);
        $this->assertEquals('Rol registrado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: ModificarCategoríaInsumo (Transacción 'actualizar' / 'modificar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * Intenta actualizar campos esenciales con valores vacíos, verificando que
     * se disparen excepciones de validación en los setters.
     */
    public function test_modificar_rol_con_datos_vacios_lanza_excepcion(): void
    {
        // 1. Nombre vacío
        try {
            $this->objRol->setNombre(' ');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Nombre no válido. Debe tener al menos entre 3 a 150 carácteres", $e->getMessage());
        }

        // 2. Apellido vacío
        try {
            $this->objRol->setId(' ');
            $this->fail("Se esperaba Exception por Id vacío.");
        } catch (Exception $e) {
            $this->assertEquals("ID no válido.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Caracteres numéricos en nombre o apellido.
     * - Simulando error de PDOExcepcion para comprobar el manejo de expeciones de dicho método.
     */
    public function test_modificar_rol_con_datos_incorrectos_falla(): void
    {

        // 1. Nombre con simbolos no permitidos (A excepción del -)
        try {
            $this->objRol->setNombre('RolPrrueba#');
            $this->fail("Se esperaba Exception por números en el nombre.");
        } catch (Exception $e) {
            $this->assertEquals("Nombre no válido. Debe tener al menos entre 3 a 150 carácteres", $e->getMessage());
        }

        // 2. ID con caracteres no permitidos
        try {
            $this->objRol->setId('ROL0100011202605192005472@#');
            $this->fail("Se esperaba Exception por caracteres inválidos en ID.");
        } catch (Exception $e) {
            $this->assertEquals("ID no válido.", $e->getMessage());
        }

        // 3. Conflicto de correo duplicado en la base de datos durante el UPDATE
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckRol = $this->createMock(PDOStatement::class);
        $mockUpdateRol = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockCheckRol->expects($this->exactly(1))->method('execute')->willReturn(true);


        $mockPdo->expects($this->exactly(2))->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckRol, $mockUpdateRol) {
            if (str_contains($sql, 'SELECT * FROM rol WHERE id_rol = :id_rol')) {
                return $mockCheckRol;
            }
            if (str_contains($sql, 'UPDATE rol')) {
                return $mockUpdateRol;
            }
        });

        $mockUpdateRol->expects($this->exactly(1))->method('execute')->willThrowException(new PDOException("Error de Base de Datos"));
        $mockPdo->expects($this->exactly(1))->method('commit');
        $mockPdo->expects($this->exactly(1))->method('rollBack');

        $rolMock = $this->createRolMock($mockPdo);
        $this->poblarDatosValidos($rolMock);

        $resultado = $rolMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals('Error interno del servidor', $resultado['HTTP_STATUS']['mensaje']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Asigna datos actualizados válidos, ejecuta UPDATE en 'categoría de insumo',
     * confirma la transacción con commit y retorna respuesta 200.
     */
    public function test_modificar_rol_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckRol = $this->createMock(PDOStatement::class);
        $mockUpdateRol = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->exactly(3))->method('beginTransaction');
        $mockCheckRol->expects($this->exactly(2))->method('execute')->willReturn(true);


        $mockPdo->expects($this->exactly(3))->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckRol, $mockUpdateRol) {
            if (str_contains($sql, 'SELECT * FROM rol WHERE id_rol = :id_rol')) {
                return $mockCheckRol;
            }
            if (str_contains($sql, 'UPDATE rol')) {
                return $mockUpdateRol;
            }
        });

        $mockPdo->expects($this->exactly(3))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $rolMock = $this->createRolMock($mockPdo);
        $this->poblarDatosValidos($rolMock);
        $rolMock->setNombre('Invitado Prueba');
        $rolMock->setId('ROL010001120260519200547298');

        $resultado = $rolMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Rol actualizado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 3. MÉTODO: EliminarCategoría (Transacción 'eliminar' -> UPDATE empleado SET estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * Intenta eliminar pasando id vacío directamente en el setter,
     * verificando que se rechace antes de acceder a la base de datos.
     */
    public function test_eliminar_rol_con_datos_vacios_lanza_excepcion(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("ID no válido.");

        $this->objRol->setId('');
    }

    /**
     * Caso con datos incorrectos / error en base de datos al eliminar:
     * - Cédula con formato inválido lanza excepción.
     * - Falla de base de datos (PDOException) durante el UPDATE retorna estado -1 y código 500.
     */
    public function test_eliminar_rol_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // 1. Cédula con formato no válido
        try {
            $this->objRol->setId('CATEGIN0112026051920054723#');
            $this->fail("El ID del Rol no cumple con el formato permitido.");
        } catch (Exception $e) {
            $this->assertStringContainsString("ID no válido.", $e->getMessage());
        }

        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckRol = $this->createMock(PDOStatement::class);
        $mockDeleteRol = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckRol->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckRol->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteRol->expects($this->once())->method('execute')->willThrowException(new PDOException("Error de conexión con la base de datos."));

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckRol, $mockDeleteRol) {
            if (str_contains($sql, 'SELECT * FROM rol WHERE id_rol = :id_rol')) {
                return $mockCheckRol;
            }
            if (str_contains($sql, 'UPDATE rol')) {
                return $mockDeleteRol;
            }
        });

        $rolMock = $this->createRolMock($mockPdo);
        $rolMock->setId('ROL010001120260519200547298');

        $resultado = $rolMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Error interno del servidor', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * Proporciona id válido, ejecuta el UPDATE de estatus a 0 y retorna resultado 200.
     */
    public function test_eliminar_rol_con_datos_correctos_elimina_exitosamente(): void
    {
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckRol = $this->createMock(PDOStatement::class);
        $mockDeleteRol = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckRol->expects($this->once())->method('execute')->willReturn(false);
        $mockCheckRol->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteRol->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckRol, $mockDeleteRol) {
            if (str_contains($sql, 'SELECT * FROM rol WHERE id_rol = :id_rol')) {
                return $mockCheckRol;
            }
            if (str_contains($sql, 'UPDATE rol')) {
                return $mockDeleteRol;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $rolMock = $this->createRolMock($mockPdo);
        $this->poblarDatosValidos($rolMock);

        $resultado = $rolMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Rol eliminado exitosamente', $resultado['response']['mensaje']);
    }

    protected function tearDown(): void
    {
        $this->objRol = NULL;
    }
}