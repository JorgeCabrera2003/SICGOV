<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Insumo;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

final class InsumoTest extends TestCase
{

    private $objInsumo;

    protected function setUp(): void
    {
        $this->objInsumo = new Insumo();
    }


    /**
     * Crea un mock parcial de Insumo para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createInsumoMock(PDO&MockObject $mockPdo): Insumo&MockObject
    {
        $mock = $this->getMockBuilder(Insumo::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto Insumo.
     */
    private function poblarDatosValidos(Insumo $insumo): void
    {
        $insumo->setId('INSUM00720260519200547232');
        $insumo->setIdCategoria('CATEGIN00720260519200547232');
        $insumo->setNombre('Patilla');
        $insumo->setStockActual(3.5);
        $insumo->setStockMinimo(1.5);
        $insumo->setStockMaximo(15);
        $insumo->setIdUnidadMedida('MEDIAKG23220260519200547232');
    }

    // =========================================================================
    // 1. MÉTODO: RegistrarInsumo (Transacción 'registrar' -> INSERT insumo)
    // =========================================================================
    public function test_registrar_insumo_con_datos_vacios_e_incorrectos_lanza_excepcion(): void
    {

        // 1. ID vacía
        try {
            $this->objInsumo->setId('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $this->objInsumo->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El Nombre del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Id de Categoria vacío
        try {
            $this->objInsumo->setIdCategoria('');
            $this->fail("Se esperaba Exception por id de vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 4. Id de Unidad de Medida vacío
        try {
            $this->objInsumo->setIdUnidadMedida('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Unidad de Medida no cumple con el formato permitido.", $e->getMessage());
        }

        // 5. Precio Unitario negativo
        try {
            $this->objInsumo->setPrecioUnitario(-1);
            $this->fail("Se esperaba Exception por precio unitario negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 6. Stock Inicial negativo
        try {
            $this->objInsumo->setStockActual(-1);
            $this->fail("Se esperaba Exception por stock inicial negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 7. Stock Máximo negativo
        try {
            $this->objInsumo->setStockMaximo(-1);
            $this->fail("Se esperaba Exception por stock máximo negativo.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 8. Stock Mínimo negativo
        try {
            $this->objInsumo->setStockMinimo(-1);
            $this->fail("Se esperaba Exception por stock mínimo negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Formatos no válidos en id y nombre.
     * - Regla de negocio: insumo ya registrado previamente en la tabla 'insumo'.
     * - Simulación de error SQL para generar rollback.
     */
    public function test_registrar_insumo_con_datos_incorrectos_falla(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objInsumo->setId('INSUM320112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre con formato inválido
        try {
            $this->objInsumo->setNombre('Nombre_Fallo');
            $this->fail("Se esperaba Exception por nombre inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El Nombre del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Id de Categoria vacío
        try {
            $this->objInsumo->setIdCategoria('CATEG320112026051920054729800012');
            $this->fail("Se esperaba Exception por id de vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 4. Id de Unidad de Medida vacío
        try {
            $this->objInsumo->setIdUnidadMedida('UNIDA320112026051920054729800012');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Unidad de Medida no cumple con el formato permitido.", $e->getMessage());
        }

        // 5. Precio Unitario negativo
        try {
            $this->objInsumo->setPrecioUnitario(-1);
            $this->fail("Se esperaba Exception por precio unitario negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 6. Stock Inicial negativo
        try {
            $this->objInsumo->setStockActual(-1);
            $this->fail("Se esperaba Exception por stock inicial negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 7. Stock Máximo negativo
        try {
            $this->objInsumo->setStockMaximo(-1);
            $this->fail("Se esperaba Exception por stock máximo negativo.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 8. Stock Mínimo negativo
        try {
            $this->objInsumo->setStockMinimo(-1);
            $this->fail("Se esperaba Exception por stock mínimo negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 9. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckInsumo = $this->createMock(PDOStatement::class);

        $mockCheckInsumo->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckInsumo->expects($this->once())->method('rowCount')->willReturn(1); // Ya existe

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckInsumo);
        $mockPdo->expects($this->once())->method('beginTransaction');

        $insumoMock = $this->createInsumoMock($mockPdo);
        $this->poblarDatosValidos($insumoMock);

        $resultado = $insumoMock->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Verifica inserción en 'insumo', confirmando la transacción con commit.
     */
    public function test_registrar_insumo_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckInsumo = $this->createMock(PDOStatement::class);
        $mockInsertInsumo = $this->createMock(PDOStatement::class);

        // 1. Inserciones exitosas
        $mockInsertInsumo->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(1))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockInsertInsumo) {
            if (str_contains($sql, 'INSERT INTO insumo')) {
                return $mockInsertInsumo;
            }
        });
        $mockPdo->expects($this->exactly(1))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $insumoMock = $this->createInsumoMock($mockPdo);
        $this->poblarDatosValidos($insumoMock);

        $resultado = $insumoMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(201, $resultado['response']['resultado']);
        $this->assertEquals('Insumo registrado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: ModificarInsumo (Transacción 'actualizar' / 'modificar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * Intenta actualizar campos esenciales con valores vacíos, verificando que
     * se disparen excepciones de validación en los setters.
     */
    public function test_modificar_insumo_con_datos_vacios_lanza_excepcion(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objInsumo->setId('INSUM320112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre con formato inválido
        try {
            $this->objInsumo->setNombre('Nombre_Fallo');
            $this->fail("Se esperaba Exception por nombre inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El Nombre del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Id de Categoria vacío
        try {
            $this->objInsumo->setIdCategoria('CATEG320112026051920054729800012');
            $this->fail("Se esperaba Exception por id de vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 4. Id de Unidad de Medida vacío
        try {
            $this->objInsumo->setIdUnidadMedida('UNIDA320112026051920054729800012');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Unidad de Medida no cumple con el formato permitido.", $e->getMessage());
        }

        // 5. Precio Unitario negativo
        try {
            $this->objInsumo->setPrecioUnitario(-1);
            $this->fail("Se esperaba Exception por precio unitario negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 6. Stock Máximo negativo
        try {
            $this->objInsumo->setStockMaximo(-1);
            $this->fail("Se esperaba Exception por stock máximo negativo.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 7. Stock Mínimo negativo
        try {
            $this->objInsumo->setStockMinimo(-1);
            $this->fail("Se esperaba Exception por stock mínimo negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Caracteres numéricos en nombre o apellido.
     * - Simulando error de PDOExcepcion para comprobar el manejo de expeciones de dicho método.
     */
    public function test_modificar_insumo_con_datos_incorrectos_falla(): void
    {

        // 1. ID con más de 32 caráteres
        try {
            $this->objInsumo->setId('INSUM320112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre con formato inválido
        try {
            $this->objInsumo->setNombre('Nombre_Fallo');
            $this->fail("Se esperaba Exception por nombre inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El Nombre del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Id de Categoria vacío
        try {
            $this->objInsumo->setIdCategoria('CATEG320112026051920054729800012');
            $this->fail("Se esperaba Exception por id de vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 4. Id de Unidad de Medida vacío
        try {
            $this->objInsumo->setIdUnidadMedida('UNIDA320112026051920054729800012');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Unidad de Medida no cumple con el formato permitido.", $e->getMessage());
        }

        // 5. Precio Unitario negativo
        try {
            $this->objInsumo->setPrecioUnitario(-1);
            $this->fail("Se esperaba Exception por precio unitario negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 6. Stock Máximo negativo
        try {
            $this->objInsumo->setStockMaximo(-1);
            $this->fail("Se esperaba Exception por stock máximo negativo.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 7. Stock Mínimo negativo
        try {
            $this->objInsumo->setStockMinimo(-1);
            $this->fail("Se esperaba Exception por stock mínimo negativo o cero.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        // 8. Conflicto de correo duplicado en la base de datos durante el UPDATE
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt)->willThrowException(new PDOException("Error de Base de Datos"));
        $mockPdo->expects($this->never())->method('commit');
        $mockPdo->expects($this->once())->method('rollBack');

        $insumoMock = $this->createInsumoMock($mockPdo);
        $this->poblarDatosValidos($insumoMock);

        $resultado = $insumoMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals('Error interno del servidor', $resultado['HTTP_STATUS']['mensaje']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Asigna datos actualizados válidos, ejecuta UPDATE en 'categoría de insumo',
     * confirma la transacción con commit y retorna respuesta 200.
     */
    public function test_modificar_insumo_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtInsumo = $this->createMock(PDOStatement::class);

        $mockStmtInsumo->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockStmtInsumo) {
            if (str_contains($sql, 'UPDATE insumo')) {
                return $mockStmtInsumo;
            }
        });

        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $insumoMock = $this->createInsumoMock($mockPdo);
        $this->poblarDatosValidos($insumoMock);

        $insumoMock->setId('INSUM00720260519200547232');
        $insumoMock->setIdCategoria('CATEGIN00720260519200547232');
        $insumoMock->setNombre('Patilla');
        $insumoMock->setStockMinimo(1.5);
        $insumoMock->setStockMaximo(15);
        $insumoMock->setIdUnidadMedida('MEDIAKG23220260519200547232');

        $resultado = $insumoMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Insumo actualizado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 3. MÉTODO: EliminarInsumo (Transacción 'eliminar' -> UPDATE insumo SET estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * Intenta eliminar pasando id vacío directamente en el setter,
     * verificando que se rechace antes de acceder a la base de datos.
     */
    public function test_eliminar_insumo_con_datos_vacios_lanza_excepcion(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El ID del Insumo no cumple con el formato permitido.");

        $this->objInsumo->setId('');
    }

    /**
     * Caso con datos incorrectos / error en base de datos al eliminar:
     * - Cédula con formato inválido lanza excepción.
     * - Falla de base de datos (PDOException) durante el UPDATE retorna estado -1 y código 500.
     */
    public function test_eliminar_insumo_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objInsumo->setId('INSUM320112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckInsumo = $this->createMock(PDOStatement::class);
        $mockDeleteInsumo = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckInsumo->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckInsumo->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteInsumo->expects($this->once())->method('execute')->willThrowException(new PDOException("Error de conexión con la base de datos."));

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckInsumo, $mockDeleteInsumo) {
            if (str_contains($sql, 'SELECT * FROM vw_insumo WHERE id_insumo = :id_insumo')) {
                return $mockCheckInsumo;
            }
            if (str_contains($sql, 'UPDATE insumo')) {
                return $mockDeleteInsumo;
            }
        });

        $insumoMock = $this->createInsumoMock($mockPdo);
        $insumoMock->setId('INSUM00720260519200547232');

        $resultado = $insumoMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Error interno del servidor', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * Proporciona id válido, ejecuta el UPDATE de estatus a 0 y retorna resultado 200.
     */
    public function test_eliminar_insumo_con_datos_correctos_elimina_exitosamente(): void
    {
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckInsumo = $this->createMock(PDOStatement::class);
        $mockDeleteInsumo = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckInsumo->expects($this->once())->method('execute')->willReturn(false);
        $mockCheckInsumo->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteInsumo->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckInsumo, $mockDeleteInsumo) {
            if (str_contains($sql, 'SELECT * FROM vw_insumo WHERE id_insumo = :id_insumo')) {
                return $mockCheckInsumo;
            }
            if (str_contains($sql, 'UPDATE insumo')) {
                return $mockDeleteInsumo;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $insumoMock = $this->createInsumoMock($mockPdo);
        $this->poblarDatosValidos($insumoMock);

        $resultado = $insumoMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Insumo eliminado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: ActualizarStockInsumo (Transacción 'actualizar_stock' / 'actualizar_stock' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al actualizar stock:
     * Intenta actualizar campos esenciales con valores vacíos, verificando que
     * se disparen excepciones de validación en los setters.
     */
    public function test_actualizar_stock_insumo_con_datos_vacios_lanza_excepcion(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objInsumo->setId('INSUM320112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Stock Actual negativo
        try {
            $this->objInsumo->setStockActual(-1);
            $this->fail("Se esperaba Exception por stock máximo negativo.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al actualizar stock:
     * - Simulando caso que no consiga el insumo.
     */
    public function test_actualizar_stock_insumo_con_datos_incorrectos_falla(): void
    {

        // 1. ID con más de 32 caráteres
        try {
            $this->objInsumo->setId('INSUM320112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID del Insumo no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Stock Actual negativo
        try {
            $this->objInsumo->setStockActual(-1);
            $this->fail("Se esperaba Exception por stock máximo negativo.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El valor ingresado no puede ser negativo", $e->getMessage());
        }

        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt)->willThrowException(new PDOException("Error de Base de Datos"));
        $mockPdo->expects($this->never())->method('commit');
        $mockPdo->expects($this->once())->method('rollBack');

        $insumoMock = $this->createInsumoMock($mockPdo);
        $this->poblarDatosValidos($insumoMock);

        $resultado = $insumoMock->Transaccion(['peticion' => 'actualizar_stock']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(404, $resultado['HTTP_STATUS']['codigo']);
        $this->assertEquals('No encontrado', $resultado['HTTP_STATUS']['mensaje']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Asigna datos actualizados válidos, ejecuta UPDATE en 'categoría de insumo',
     * confirma la transacción con commit y retorna respuesta 200.
     */
    public function test_actualizar_stock_insumo_con_datos_correctos_modifica_exitosamente(): void
    {
        // 1. Regla de negocio: el insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckInsumo = $this->createMock(PDOStatement::class);
        $mockDeleteInsumo = $this->createMock(PDOStatement::class);

        $mockCheckInsumo->expects($this->once())->method('execute')->willReturn(false);
        $mockCheckInsumo->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteInsumo->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckInsumo, $mockDeleteInsumo) {
            if (str_contains($sql, 'SELECT * FROM vw_insumo WHERE id_insumo = :id_insumo')) {
                return $mockCheckInsumo;
            }
            if (str_contains($sql, 'UPDATE insumo')) {
                return $mockDeleteInsumo;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $insumoMock = $this->createInsumoMock($mockPdo);
        $this->poblarDatosValidos($insumoMock);
        $this->poblarDatosValidos($insumoMock);

        $insumoMock->setId('INSUM00720260519200547232');
        $insumoMock->setStockActual(1);

        $resultado = $insumoMock->Transaccion(['peticion' => 'actualizar_stock']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Stock del Insumo actualizado exitosamente', $resultado['response']['mensaje']);
    }

    protected function tearDown(): void
    {
        $this->objInsumo = NULL;
    }
}