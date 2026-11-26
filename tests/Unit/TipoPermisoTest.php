<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\TipoPermiso;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class TipoPermisoTest extends TestCase
{

    private $objTipoPermiso;

    protected function setUp(): void
    {
        $this->objTipoPermiso = new TipoPermiso();
    }


    /**
     * Crea un mock parcial de TipoPermiso para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createTipoPermisoMock(PDO&MockObject $mockPdo): TipoPermiso&MockObject
    {
        $mock = $this->getMockBuilder(TipoPermiso::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto TipoPermiso.
     */
    private function poblarDatosValidos(TipoPermiso $tipoPermiso): void
    {
        $tipoPermiso->setId('PERMI0001120260519200547298');
        $tipoPermiso->setNombre('Nombre Prueba');
    }

    // =========================================================================
    // 1. MÉTODO: RegistrarTipoPermiso (Transacción 'registrar' -> INSERT categoria de insumo)
    // =========================================================================
    public function test_registrar_tipo_permiso_con_datos_vacios_lanza_excepcion(): void
    {

        // 1. ID vacía
        try {
            $this->objTipoPermiso->setId('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Tipo de Permiso no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $this->objTipoPermiso->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El Nombre del Tipo de Permiso no cumple con el formato permitido.", $e->getMessage());
        }

    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Formatos no válidos en id y nombre.
     * - Regla de negocio: tipo de permiso ya registrado previamente en la tabla 'tipo_permiso'.
     * - Simulación de error SQL para generar rollback.
     */
    public function test_registrar_tipo_permiso_con_datos_incorrectos_falla(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objTipoPermiso->setId('PERMI000112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Tipo de Permiso no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre con formato inválido
        try {
            $this->objTipoPermiso->setNombre('Nombre_Fallo');
            $this->fail("Se esperaba Exception por nombre inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El Nombre del Tipo de Permiso no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Regla de negocio: el tipo de permiso ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCategoria = $this->createMock(PDOStatement::class);

        $mockCheckCategoria->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckCategoria->expects($this->once())->method('rowCount')->willReturn(1); // Ya existe

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckCategoria);
        $mockPdo->expects($this->once())->method('beginTransaction');

        $rolMock = $this->createTipoPermisoMock($mockPdo);
        $this->poblarDatosValidos($rolMock);

        $resultado = $rolMock->Transaccion(['peticion' => 'registrar']);
        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(409, $resultado['response']['resultado']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Verifica inserción en 'rol', confirmando la transacción con commit.
     */
    public function test_registrar_tipo_permiso_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckTipoPermiso = $this->createMock(PDOStatement::class);
        $mockInsertTipoPermiso = $this->createMock(PDOStatement::class);

        // 1. Verificación rol: no existe (rowCount = 0)
        $mockCheckTipoPermiso->expects($this->exactly(1))->method('execute')->willReturn(true);

        // 2. Inserciones exitosas
        $mockInsertTipoPermiso->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckTipoPermiso, $mockInsertTipoPermiso) {
            if (str_contains($sql, 'SELECT * FROM tipo_permiso WHERE id_tipo_permiso = :id_tipo_permiso')) {
                return $mockCheckTipoPermiso;
            }
            if (str_contains($sql, 'INSERT INTO tipo_permiso')) {
                return $mockInsertTipoPermiso;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $rolMock = $this->createTipoPermisoMock($mockPdo);
        $this->poblarDatosValidos($rolMock);

        $resultado = $rolMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Tipo de Permiso registrado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: ModificarCategoríaInsumo (Transacción 'actualizar' / 'modificar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * Intenta actualizar campos esenciales con valores vacíos, verificando que
     * se disparen excepciones de validación en los setters.
     */
    public function test_modificar_tipo_permiso_con_datos_vacios_lanza_excepcion(): void
    {
        // 1. Nombre vacío
        try {
            $this->objTipoPermiso->setNombre(' ');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El Nombre del Tipo de Permiso no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Apellido vacío
        try {
            $this->objTipoPermiso->setId(' ');
            $this->fail("Se esperaba Exception por Id vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El ID del Tipo de Permiso no cumple con el formato permitido.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Caracteres numéricos en nombre o apellido.
     * - Simulando error de PDOExcepcion para comprobar el manejo de expeciones de dicho método.
     */
    public function test_modificar_tipo_permiso_con_datos_incorrectos_falla(): void
    {

        // 1. Nombre con simbolos no permitidos (A excepción del -)
        try {
            $this->objTipoPermiso->setNombre('TipoPermisoPrrueba#');
            $this->fail("Se esperaba Exception por números en el nombre.");
        } catch (Exception $e) {
            $this->assertEquals("El Nombre del Tipo de Permiso no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. ID con caracteres no permitidos
        try {
            $this->objTipoPermiso->setId('ROL0100011202605192005472@#');
            $this->fail("Se esperaba Exception por caracteres inválidos en ID.");
        } catch (Exception $e) {
            $this->assertEquals("El ID del Tipo de Permiso no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Conflicto de correo duplicado en la base de datos durante el UPDATE
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckTipoPermiso = $this->createMock(PDOStatement::class);
        $mockUpdateTipoPermiso = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->exactly(1))->method('beginTransaction');
        $mockCheckTipoPermiso->expects($this->exactly(0))->method('execute')->willReturn(true);


        $mockPdo->expects($this->exactly(1))->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckTipoPermiso, $mockUpdateTipoPermiso) {
            if (str_contains($sql, 'SELECT * FROM tipo_permiso WHERE id_tipo_permiso = :id_tipo_permiso')) {
                return $mockCheckTipoPermiso;
            }
            if (str_contains($sql, 'UPDATE tipo_permiso')) {
                return $mockUpdateTipoPermiso;
            }
        });

        $mockUpdateTipoPermiso->expects($this->exactly(1))->method('execute')->willThrowException(new PDOException("Error de Base de Datos"));
        $mockPdo->expects($this->exactly(0))->method('commit');
        $mockPdo->expects($this->exactly(1))->method('rollBack');

        $rolMock = $this->createTipoPermisoMock($mockPdo);
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
    public function test_modificar_tipo_permiso_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckTipoPermiso = $this->createMock(PDOStatement::class);
        $mockUpdateTipoPermiso = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->exactly(1))->method('beginTransaction');
        $mockCheckTipoPermiso->expects($this->exactly(0))->method('execute')->willReturn(true);


        $mockPdo->expects($this->exactly(1))->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckTipoPermiso, $mockUpdateTipoPermiso) {
            if (str_contains($sql, 'SELECT * FROM tipo_permiso WHERE id_tipo_permiso = :id_tipo_permiso')) {
                return $mockCheckTipoPermiso;
            }
            if (str_contains($sql, 'UPDATE tipo_permiso')) {
                return $mockUpdateTipoPermiso;
            }
        });

        $mockPdo->expects($this->exactly(1))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $rolMock = $this->createTipoPermisoMock($mockPdo);
        $this->poblarDatosValidos($rolMock);
        $rolMock->setNombre('Invitado Prueba');
        $rolMock->setId('PERMI0001120260519200547298');

        $resultado = $rolMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Tipo de Permiso actualizado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 3. MÉTODO: EliminarCategoría (Transacción 'eliminar' -> UPDATE empleado SET estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * Intenta eliminar pasando id vacío directamente en el setter,
     * verificando que se rechace antes de acceder a la base de datos.
     */
    public function test_eliminar_tipo_permiso_con_datos_vacios_lanza_excepcion(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El ID del Tipo de Permiso no cumple con el formato permitido.");

        $this->objTipoPermiso->setId('');
    }

    /**
     * Caso con datos incorrectos / error en base de datos al eliminar:
     * - Cédula con formato inválido lanza excepción.
     * - Falla de base de datos (PDOException) durante el UPDATE retorna estado -1 y código 500.
     */
    public function test_eliminar_tipo_permiso_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // 1. Cédula con formato no válido
        try {
            $this->objTipoPermiso->setId('CATEGIN0112026051920054723#');
            $this->fail("El Nombre del Tipo de Permiso no cumple con el formato permitido.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Tipo de Permiso no cumple con el formato permitido.", $e->getMessage());
        }

        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckTipoPermiso = $this->createMock(PDOStatement::class);
        $mockDeleteTipoPermiso = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckTipoPermiso->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckTipoPermiso->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteTipoPermiso->expects($this->once())->method('execute')->willThrowException(new PDOException("Error de conexión con la base de datos."));

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckTipoPermiso, $mockDeleteTipoPermiso) {
            if (str_contains($sql, 'SELECT * FROM tipo_permiso WHERE id_tipo_permiso = :id_tipo_permiso')) {
                return $mockCheckTipoPermiso;
            }
            if (str_contains($sql, 'UPDATE tipo_permiso')) {
                return $mockDeleteTipoPermiso;
            }
        });

        $rolMock = $this->createTipoPermisoMock($mockPdo);
        $rolMock->setId('PERMI0001120260519200547298');

        $resultado = $rolMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Error interno del servidor', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * Proporciona id válido, ejecuta el UPDATE de estatus a 0 y retorna resultado 200.
     */
    public function test_eliminar_tipo_permiso_con_datos_correctos_elimina_exitosamente(): void
    {
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckTipoPermiso = $this->createMock(PDOStatement::class);
        $mockDeleteTipoPermiso = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckTipoPermiso->expects($this->once())->method('execute')->willReturn(false);
        $mockCheckTipoPermiso->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteTipoPermiso->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckTipoPermiso, $mockDeleteTipoPermiso) {
            if (str_contains($sql, 'SELECT * FROM tipo_permiso WHERE id_tipo_permiso = :id_tipo_permiso')) {
                return $mockCheckTipoPermiso;
            }
            if (str_contains($sql, 'UPDATE tipo_permiso')) {
                return $mockDeleteTipoPermiso;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $rolMock = $this->createTipoPermisoMock($mockPdo);
        $this->poblarDatosValidos($rolMock);

        $resultado = $rolMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Tipo de Permiso eliminado exitosamente', $resultado['response']['mensaje']);
    }

    protected function tearDown(): void
    {
        $this->objTipoPermiso = NULL;
    }
}
