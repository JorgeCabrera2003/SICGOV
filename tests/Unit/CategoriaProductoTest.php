<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\CategoriaProducto;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class CategoriaProductoTest extends TestCase
{
    /**
     * Crea un mock parcial de CategoriaProducto para aislar la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createCategoriaMock(PDO&MockObject $mockPdo): CategoriaProducto&MockObject
    {
        $mock = $this->getMockBuilder(CategoriaProducto::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    // =========================================================================
    // 1. MÉTODO: guardarCategoria (Transacción 'guardar' -> INSERT)
    // =========================================================================

    /**
     * Caso con datos vacíos al guardar:
     * El nombre de la categoría es obligatorio y no puede estar vacío ni contener solo espacios.
     */
    public function test_guardar_categoria_con_datos_vacios_lanza_excepcion(): void
    {
        $categoria = new CategoriaProducto();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El nombre de la categoría es obligatorio.");

        $categoria->setNombreCategoria('');
    }

    /**
     * Caso con datos incorrectos al guardar:
     * - A nivel de formato: no inicia con mayúscula o contiene caracteres no permitidos.
     * - A nivel de persistencia: simula colisión de clave única (código 1062) en la BD.
     */
    public function test_guardar_categoria_con_datos_incorrectos_falla(): void
    {
        // 1. Validación de formato incorrecto (debe iniciar con mayúscula)
        $categoria = new CategoriaProducto();
        try {
            $categoria->setNombreCategoria('postres'); // Minúscula inicial
            $this->fail("Se esperaba una Exception por formato incorrecto.");
        } catch (Exception $e) {
            $this->assertStringContainsString("debe comenzar con una letra mayúscula", $e->getMessage());
        }

        // 2. Validación de persistencia simulada con error en base de datos (Duplicado SQL 1062)
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $pdoException = new PDOException("Duplicate entry 'Bebidas' for key 'nombre_categoria'");
        $pdoException->errorInfo = ['23000', 1062, "Duplicate entry"];

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdo->expects($this->once())->method('rollBack');

        $categoriaMock = $this->createCategoriaMock($mockPdo);
        $categoriaMock->setNombreCategoria('Bebidas Calientes');

        $resultado = $categoriaMock->Transaccion(['peticion' => 'guardar']);

        $this->assertFalse($resultado['success']);
        $this->assertEquals('Ya existe una categoría con ese nombre', $resultado['message']);
    }

    /**
     * Caso con datos correctos al guardar:
     * Guarda la categoría exitosamente, genera el ID con prefijo 'CAT' y confirma la transacción.
     */
    public function test_guardar_categoria_con_datos_correctos_guarda_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $categoriaMock = $this->createCategoriaMock($mockPdo);
        $categoriaMock->setNombreCategoria('Platos Fuertes');

        $resultado = $categoriaMock->Transaccion(['peticion' => 'guardar']);

        $this->assertTrue($resultado['success']);
        $this->assertEquals('Categoría guardada exitosamente', $resultado['message']);
        $this->assertStringStartsWith('CAT', $resultado['id']);
    }

    // =========================================================================
    // 2. MÉTODO: actualizarCategoria (Transacción 'actualizar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al actualizar:
     * El ID de la categoría y el nombre no pueden ser cadenas vacías.
     */
    public function test_actualizar_categoria_con_datos_vacios_lanza_excepcion(): void
    {
        $categoria = new CategoriaProducto();

        // 1. Probar ID vacío
        try {
            $categoria->setIdCategoria('');
            $this->fail("Se esperaba una Exception por ID vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El ID de la categoría no es válido.", $e->getMessage());
        }

        // 2. Probar Nombre vacío
        try {
            $categoria->setNombreCategoria('   ');
            $this->fail("Se esperaba una Exception por nombre con solo espacios.");
        } catch (Exception $e) {
            $this->assertEquals("El nombre de la categoría es obligatorio.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al actualizar:
     * - A nivel de validación: ID con formato inválido o estatus no permitido.
     * - A nivel de base de datos: la sentencia execute retorna false y se ejecuta rollback.
     */
    public function test_actualizar_categoria_con_datos_incorrectos_falla(): void
    {
        $categoria = new CategoriaProducto();

        // 1. ID con formato inválido (contiene símbolos no permitidos)
        try {
            $categoria->setIdCategoria('ID-INVALIDO!#');
            $this->fail("Se esperaba una Exception por ID con caracteres no válidos.");
        } catch (Exception $e) {
            $this->assertEquals("El ID de la categoría no es válido.", $e->getMessage());
        }

        // 2. Estatus con valor no válido (solo se permite '0' o '1')
        try {
            $categoria->setEstatus('activo');
            $this->fail("Se esperaba una Exception por estatus no válido.");
        } catch (Exception $e) {
            $this->assertEquals("Estatus de categoría no válido.", $e->getMessage());
        }

        // 3. Fallo en ejecución de actualización en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(false);
        $mockPdo->expects($this->once())->method('rollBack');
        $mockPdo->expects($this->never())->method('commit');

        $categoriaMock = $this->createCategoriaMock($mockPdo);
        $categoriaMock->setIdCategoria('CAT202610140000001234');
        $categoriaMock->setNombreCategoria('Postres Caseros');
        $categoriaMock->setEstatus(1);

        $resultado = $categoriaMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertFalse($resultado['success']);
        $this->assertEquals('Error al actualizar', $resultado['message']);
    }

    /**
     * Caso con datos correctos al actualizar:
     * Proporciona ID válido, nombre válido y estatus válido; actualiza y ejecuta commit.
     */
    public function test_actualizar_categoria_con_datos_correctos_actualiza_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $categoriaMock = $this->createCategoriaMock($mockPdo);
        $categoriaMock->setIdCategoria('CAT202610140000001234');
        $categoriaMock->setNombreCategoria('Bebidas Frias');
        $categoriaMock->setEstatus(1);

        $resultado = $categoriaMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertTrue($resultado['success']);
        $this->assertEquals('Categoría actualizada', $resultado['message']);
    }

    // =========================================================================
    // 3. MÉTODO: eliminarCategoria (Transacción 'eliminar' -> UPDATE estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * Intentar eliminar con un ID vacío debe lanzar una excepción antes de acceder a la BD.
     */
    public function test_eliminar_categoria_con_datos_vacios_lanza_excepcion(): void
    {
        $categoria = new CategoriaProducto();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El ID de la categoría no es válido.");

        $categoria->setIdCategoria('');
    }

    /**
     * Caso con datos incorrectos / restricción de negocio al eliminar:
     * Si la categoría tiene productos vinculados (total > 0), no se debe eliminar y hace rollback.
     */
    public function test_eliminar_categoria_con_productos_asociados_falla(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckStmt = $this->createMock(PDOStatement::class);

        // Simular que la consulta SELECT COUNT(*) devuelve productos existentes
        $mockCheckStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $mockCheckStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['total' => 3]);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckStmt);
        $mockPdo->expects($this->once())->method('rollBack');
        $mockPdo->expects($this->never())->method('commit');

        $categoriaMock = $this->createCategoriaMock($mockPdo);
        $categoriaMock->setIdCategoria('CAT202610140000001234');

        $resultado = $categoriaMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertFalse($resultado['success']);
        $this->assertEquals('No se puede eliminar: Hay productos usando esta categoría', $resultado['message']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * La categoría existe, no tiene productos vinculados (total = 0), se actualiza su estatus a 0 y se confirma.
     */
    public function test_eliminar_categoria_con_datos_correctos_elimina_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckStmt = $this->createMock(PDOStatement::class);
        $mockUpdateStmt = $this->createMock(PDOStatement::class);

        // 1. Simular verificación: 0 productos asociados
        $mockCheckStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckStmt->expects($this->once())->method('fetch')->willReturn(['total' => 0]);

        // 2. Simular actualización a estatus = 0
        $mockUpdateStmt->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('beginTransaction');

        // Retornar el statement correspondiente según la sentencia SQL preparada
        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnCallback(function (string $sql) use ($mockCheckStmt, $mockUpdateStmt) {
                if (str_contains($sql, 'COUNT(*)')) {
                    return $mockCheckStmt;
                }
                return $mockUpdateStmt;
            });

        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $categoriaMock = $this->createCategoriaMock($mockPdo);
        $categoriaMock->setIdCategoria('CAT202610140000001234');

        $resultado = $categoriaMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertTrue($resultado['success']);
        $this->assertEquals('Categoría eliminada', $resultado['message']);
    }
}
