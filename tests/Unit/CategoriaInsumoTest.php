<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\CategoriaInsumo;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

final class CategoriaInsumoTest extends TestCase
{

    private $objCategoriaInsumo;

    protected function setUp() : void {
        $this->objCategoriaInsumo = new CategoriaInsumo();
    }


    /**
     * Crea un mock parcial de CategoriaInsumo para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createCategoriaInsumoMock(PDO&MockObject $mockPdo): CategoriaInsumo&MockObject
    {
        $mock = $this->getMockBuilder(CategoriaInsumo::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto CategoriaInsumo.
     */
    private function poblarDatosValidos(CategoriaInsumo $categoriaInsumo): void
    {
        $categoriaInsumo->setNombre('Enlatados');
        $categoriaInsumo->setId('CATEGIN01120260519200547298');
    }

    // =========================================================================
    // 1. MÉTODO: RegistrarCategoriaInsumo (Transacción 'registrar' -> INSERT categoria de insumo)
    // =========================================================================
    public function test_registrar_categoria_insumo_con_datos_vacios_lanza_excepcion(): void
    {

        // 1. ID vacía
        try {
            $this->objCategoriaInsumo->setId('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $this->objCategoriaInsumo->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El Nombre de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Formatos no válidos en id y nombre.
     * - Regla de negocio: categoria ya registrado previamente en la tabla 'categoria_insumo'.
     * - Simulación de error SQL para generar rollback.
     */
    public function test_registrar_categoria_insumo_con_datos_incorrectos_falla(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objCategoriaInsumo->setId('CATEGIN0112026051920054729800012');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre con formato inválido
        try {
            $this->objCategoriaInsumo->setNombre('Nombre_Fallo');
            $this->fail("Se esperaba Exception por nombre inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El Nombre de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 3. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCategoria = $this->createMock(PDOStatement::class);

        $mockCheckCategoria->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckCategoria->expects($this->once())->method('rowCount')->willReturn(1); // Ya existe

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckCategoria);
        $mockPdo->expects($this->once())->method('beginTransaction');

        $categoriaInsumoMock = $this->createCategoriaInsumoMock($mockPdo);
        $this->poblarDatosValidos($categoriaInsumoMock);

        $resultado = $categoriaInsumoMock->Transaccion(['peticion' => 'registrar']);
        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(409, $resultado['response']['resultado']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Verifica inserción en 'categoria_insumo', confirmando la transacción con commit.
     */
    public function test_registrar_categoria_insumo_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCategoriaInsumo = $this->createMock(PDOStatement::class);
        $mockInsertCategoriaInsumo = $this->createMock(PDOStatement::class);

        // 1. Verificación categoria_insumo: no existe (rowCount = 0)
        $mockCheckCategoriaInsumo->expects($this->once())->method('execute')->willReturn(true);

        // 2. Inserciones exitosas
        $mockInsertCategoriaInsumo->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckCategoriaInsumo, $mockInsertCategoriaInsumo) {
            if (str_contains($sql, 'SELECT * FROM categoria_insumo WHERE id_categoria = :id_categoria')) {
                return $mockCheckCategoriaInsumo;
            }
            if (str_contains($sql, 'INSERT INTO categoria_insumo')) {
                return $mockInsertCategoriaInsumo;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $categoriaInsumoMock = $this->createCategoriaInsumoMock($mockPdo);
        $this->poblarDatosValidos($categoriaInsumoMock);

        $resultado = $categoriaInsumoMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Categoria registrada exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: ModificarCategoríaInsumo (Transacción 'actualizar' / 'modificar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * Intenta actualizar campos esenciales con valores vacíos, verificando que
     * se disparen excepciones de validación en los setters.
     */
    public function test_modificar_categoria_insumo_con_datos_vacios_lanza_excepcion(): void
    {
        // 1. Nombre vacío
        try {
            $this->objCategoriaInsumo->setNombre(' ');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El Nombre de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Apellido vacío
        try {
            $this->objCategoriaInsumo->setId(' ');
            $this->fail("Se esperaba Exception por Id vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El ID de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Caracteres numéricos en nombre o apellido.
     * - Simulando error de PDOExcepcion para comprobar el manejo de expeciones de dicho método.
     */
    public function test_modificar_categoria_insumo_con_datos_incorrectos_falla(): void
    {

        // 1. Nombre con simbolos no permitidos (A excepción del -)
        try {
            $this->objCategoriaInsumo->setNombre('Juan#');
            $this->fail("Se esperaba Exception por números en el nombre.");
        } catch (Exception $e) {
            $this->assertEquals("El Nombre de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. ID con caracteres no permitidos
        try {
            $this->objCategoriaInsumo->setId('CATEGIN011202605192005472@#');
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

        $categoriaInsumoMock = $this->createCategoriaInsumoMock($mockPdo);
        $this->poblarDatosValidos($categoriaInsumoMock);

        $resultado = $categoriaInsumoMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals('Error interno del servidor', $resultado['HTTP_STATUS']['mensaje']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Asigna datos actualizados válidos, ejecuta UPDATE en 'categoría de insumo',
     * confirma la transacción con commit y retorna respuesta 200.
     */
    public function test_modificar_categoria_insumo_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCategoria = $this->createMock(PDOStatement::class);

        $mockStmtCategoria->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockStmtCategoria) {
            if (str_contains($sql, 'UPDATE categoria_insumo')) {
                return $mockStmtCategoria;
            }
        });

        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $categoriaInsumoMock = $this->createCategoriaInsumoMock($mockPdo);
        $this->poblarDatosValidos($categoriaInsumoMock);
        $categoriaInsumoMock->setNombre('Enlatados');
        $categoriaInsumoMock->setId('CATEGIN01120260519200547298');

        $resultado = $categoriaInsumoMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Categoría actualizada exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 3. MÉTODO: EliminarCategoría (Transacción 'eliminar' -> UPDATE empleado SET estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * Intenta eliminar pasando id vacío directamente en el setter,
     * verificando que se rechace antes de acceder a la base de datos.
     */
    public function test_eliminar_categoria_insumo_con_datos_vacios_lanza_excepcion(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El ID de la Categoría no cumple con el formato permitido.");

        $this->objCategoriaInsumo->setId('');
    }

    /**
     * Caso con datos incorrectos / error en base de datos al eliminar:
     * - Cédula con formato inválido lanza excepción.
     * - Falla de base de datos (PDOException) durante el UPDATE retorna estado -1 y código 500.
     */
    public function test_eliminar_categoria_insumo_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // 1. Cédula con formato no válido
        try {
            $this->objCategoriaInsumo->setId('CATEGIN0112026051920054723#');
            $this->fail("El ID de la Categoría no cumple con el formato permitido.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID de la Categoría no cumple con el formato permitido.", $e->getMessage());
        }

        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCategoriaInsumo = $this->createMock(PDOStatement::class);
        $mockDeleteCategoriaInsumo = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckCategoriaInsumo->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckCategoriaInsumo->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteCategoriaInsumo->expects($this->once())->method('execute')->willThrowException(new PDOException("Error de conexión con la base de datos."));

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckCategoriaInsumo, $mockDeleteCategoriaInsumo) {
            if (str_contains($sql, 'SELECT * FROM categoria_insumo WHERE id_categoria = :id_categoria')) {
                return $mockCheckCategoriaInsumo;
            }
            if (str_contains($sql, 'UPDATE categoria_insumo')) {
                return $mockDeleteCategoriaInsumo;
            }
        });

        $categoriaInsumoMock = $this->createCategoriaInsumoMock($mockPdo);
        $categoriaInsumoMock->setId('CATEGIN01120260519200547232');

        $resultado = $categoriaInsumoMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Error interno del servidor', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * Proporciona id válido, ejecuta el UPDATE de estatus a 0 y retorna resultado 200.
     */
    public function test_eliminar_categoria_insumo_con_datos_correctos_elimina_exitosamente(): void
    {
        // 1. Regla de negocio: la categoria insumo ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCategoriaInsumo = $this->createMock(PDOStatement::class);
        $mockDeleteCategoriaInsumo = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckCategoriaInsumo->expects($this->once())->method('execute')->willReturn(false);
        $mockCheckCategoriaInsumo->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteCategoriaInsumo->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckCategoriaInsumo, $mockDeleteCategoriaInsumo) {
            if (str_contains($sql, 'SELECT * FROM categoria_insumo WHERE id_categoria = :id_categoria')) {
                return $mockCheckCategoriaInsumo;
            }
            if (str_contains($sql, 'UPDATE categoria_insumo')) {
                return $mockDeleteCategoriaInsumo;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $categoriaInsumoMock = $this->createCategoriaInsumoMock($mockPdo);
        $this->poblarDatosValidos($categoriaInsumoMock);

        $resultado = $categoriaInsumoMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Categoría eliminada exitosamente', $resultado['response']['mensaje']);
    }

    protected function tearDown() :void {
        $this->objCategoriaInsumo = NULL;
    }
}