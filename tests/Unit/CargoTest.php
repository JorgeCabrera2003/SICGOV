<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Cargo;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

final class CargoTest extends TestCase
{

    private $objCargo;

    protected function setUp() : void {
        $this->objCargo = new Cargo();
    }


    /**
     * Crea un mock parcial de Cargo para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createCargoMock(PDO&MockObject $mockPdo): Cargo&MockObject
    {
        $mock = $this->getMockBuilder(Cargo::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto Cargo.
     */
    private function poblarDatosValidos(Cargo $categoriaInsumo): void
    {
        $categoriaInsumo->setNombre('Conserje');
        $categoriaInsumo->setId('CARGO00720260519200547232');
    }

    // =========================================================================
    // 1. MÉTODO: RegistrarCargo (Transacción 'registrar' -> INSERT categoria de insumo)
    // =========================================================================
    public function test_registrar_cargo_con_datos_vacios_lanza_excepcion(): void
    {

        // 1. ID vacía
        try {
            $this->objCargo->setId('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Cargo no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $this->objCargo->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El Nombre del Cargo no cumple con el formato permitido.", $e->getMessage());
        }

    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Formatos no válidos en id y nombre.
     * - Regla de negocio: categoria ya registrado previamente en la tabla 'cargo'.
     * - Simulación de error SQL para generar rollback.
     */
    public function test_registrar_cargo_con_datos_incorrectos_falla(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objCargo->setId('CARGO320112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Cargo no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre con formato inválido
        try {
            $this->objCargo->setNombre('Nombre_Fallo');
            $this->fail("Se esperaba Exception por nombre inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El Nombre del Cargo no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCategoria = $this->createMock(PDOStatement::class);

        $mockCheckCategoria->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckCategoria->expects($this->once())->method('rowCount')->willReturn(1); // Ya existe

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckCategoria);
        $mockPdo->expects($this->once())->method('beginTransaction');

        $cargoMock = $this->createCargoMock($mockPdo);
        $this->poblarDatosValidos($cargoMock);

        $resultado = $cargoMock->Transaccion(['peticion' => 'registrar']);
        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(409, $resultado['response']['resultado']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Verifica inserción en 'cargo', confirmando la transacción con commit.
     */
    public function test_registrar_cargo_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCargo = $this->createMock(PDOStatement::class);
        $mockInsertCargo = $this->createMock(PDOStatement::class);

        // 1. Verificación cargo: no existe (rowCount = 0)
        $mockCheckCargo->expects($this->once())->method('execute')->willReturn(true);

        // 2. Inserciones exitosas
        $mockInsertCargo->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckCargo, $mockInsertCargo) {
            if (str_contains($sql, 'SELECT * FROM cargo WHERE id_cargo = :id_cargo')) {
                return $mockCheckCargo;
            }
            if (str_contains($sql, 'INSERT INTO cargo')) {
                return $mockInsertCargo;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $cargoMock = $this->createCargoMock($mockPdo);
        $this->poblarDatosValidos($cargoMock);

        $resultado = $cargoMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Cargo registrado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: ModificarCategoríaInsumo (Transacción 'actualizar' / 'modificar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * Intenta actualizar campos esenciales con valores vacíos, verificando que
     * se disparen excepciones de validación en los setters.
     */
    public function test_modificar_cargo_con_datos_vacios_lanza_excepcion(): void
    {
        // 1. Nombre vacío
        try {
            $this->objCargo->setNombre(' ');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El Nombre del Cargo no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Apellido vacío
        try {
            $this->objCargo->setId(' ');
            $this->fail("Se esperaba Exception por Id vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El ID del Cargo no cumple con el formato permitido.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Caracteres numéricos en nombre o apellido.
     * - Simulando error de PDOExcepcion para comprobar el manejo de expeciones de dicho método.
     */
    public function test_modificar_cargo_con_datos_incorrectos_falla(): void
    {

        // 1. Nombre con simbolos no permitidos (A excepción del -)
        try {
            $this->objCargo->setNombre('Juan#');
            $this->fail("Se esperaba Exception por números en el nombre.");
        } catch (Exception $e) {
            $this->assertEquals("El Nombre de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. ID con caracteres no permitidos
        try {
            $this->objCargo->setId('CATEGIN011202605192005472@#');
            $this->fail("Se esperaba Exception por caracteres inválidos en ID.");
        } catch (Exception $e) {
            $this->assertEquals("El ID de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Conflicto de correo duplicado en la base de datos durante el UPDATE
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt)->willThrowException(new PDOException("Error de Base de Datos"));
        $mockPdo->expects($this->never())->method('commit');
        $mockPdo->expects($this->once())->method('rollBack');

        $cargoMock = $this->createCargoMock($mockPdo);
        $this->poblarDatosValidos($cargoMock);

        $resultado = $cargoMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals('Error interno del servidor', $resultado['HTTP_STATUS']['mensaje']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Asigna datos actualizados válidos, ejecuta UPDATE en 'categoría de insumo',
     * confirma la transacción con commit y retorna respuesta 200.
     */
    public function test_modificar_cargo_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCategoria = $this->createMock(PDOStatement::class);

        $mockStmtCategoria->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockStmtCategoria) {
            if (str_contains($sql, 'UPDATE cargo')) {
                return $mockStmtCategoria;
            }
        });

        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $cargoMock = $this->createCargoMock($mockPdo);
        $this->poblarDatosValidos($cargoMock);
        $cargoMock->setNombre('Conserje');
        $cargoMock->setId('CATEGIN01120260519200547298');

        $resultado = $cargoMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Cargo actualizado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 3. MÉTODO: EliminarCategoría (Transacción 'eliminar' -> UPDATE empleado SET estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * Intenta eliminar pasando id vacío directamente en el setter,
     * verificando que se rechace antes de acceder a la base de datos.
     */
    public function test_eliminar_cargo_con_datos_vacios_lanza_excepcion(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El ID del Cargo no cumple con el formato permitido.");

        $this->objCargo->setId('');
    }

    /**
     * Caso con datos incorrectos / error en base de datos al eliminar:
     * - Cédula con formato inválido lanza excepción.
     * - Falla de base de datos (PDOException) durante el UPDATE retorna estado -1 y código 500.
     */
    public function test_eliminar_cargo_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // 1. Cédula con formato no válido
        try {
            $this->objCargo->setId('CATEGIN0112026051920054723#');
            $this->fail("El ID del Cargo no cumple con el formato permitido.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Cargo no cumple con el formato permitido.", $e->getMessage());
        }

        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCargo = $this->createMock(PDOStatement::class);
        $mockDeleteCargo = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckCargo->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckCargo->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteCargo->expects($this->once())->method('execute')->willThrowException(new PDOException("Error de conexión con la base de datos."));

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckCargo, $mockDeleteCargo) {
            if (str_contains($sql, 'SELECT * FROM cargo WHERE id_cargo = :id_cargo')) {
                return $mockCheckCargo;
            }
            if (str_contains($sql, 'UPDATE cargo')) {
                return $mockDeleteCargo;
            }
        });

        $cargoMock = $this->createCargoMock($mockPdo);
        $cargoMock->setId('CATEGIN01120260519200547232');

        $resultado = $cargoMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Error interno del servidor', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * Proporciona id válido, ejecuta el UPDATE de estatus a 0 y retorna resultado 200.
     */
    public function test_eliminar_cargo_con_datos_correctos_elimina_exitosamente(): void
    {
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCargo = $this->createMock(PDOStatement::class);
        $mockDeleteCargo = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckCargo->expects($this->once())->method('execute')->willReturn(false);
        $mockCheckCargo->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteCargo->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckCargo, $mockDeleteCargo) {
            if (str_contains($sql, 'SELECT * FROM cargo WHERE id_cargo = :id_cargo')) {
                return $mockCheckCargo;
            }
            if (str_contains($sql, 'UPDATE cargo')) {
                return $mockDeleteCargo;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $cargoMock = $this->createCargoMock($mockPdo);
        $this->poblarDatosValidos($cargoMock);

        $resultado = $cargoMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Cargo eliminado exitosamente', $resultado['response']['mensaje']);
    }

    protected function tearDown() :void {
        $this->objCargo = NULL;
    }
}