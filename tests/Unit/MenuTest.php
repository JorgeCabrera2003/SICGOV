<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Menu;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class MenuTest extends TestCase
{
    /**
     * Crea un mock parcial de Menu para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createMenuMock(PDO&MockObject $mockPdo): Menu&MockObject
    {
        $mock = $this->getMockBuilder(Menu::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    // =========================================================================
    // 1. MÉTODO: registrarMenu (Transacción 'registrar' -> INSERT)
    // =========================================================================

    /**
     * Caso con datos vacíos al registrar:
     * Verifica que los campos obligatorios (nombre, descripción, precio, categoría, tipo)
     * no puedan estar vacíos y lancen sus respectivas excepciones.
     */
    public function test_registrar_menu_con_datos_vacios_lanza_excepcion(): void
    {
        $menu = new Menu();

        // 1. Nombre vacío
        try {
            $menu->setNombreProducto('');
            $this->fail("Se esperaba una Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El nombre del producto es obligatorio.", $e->getMessage());
        }

        // 2. Descripción vacía
        try {
            $menu->setDescripcion('   ');
            $this->fail("Se esperaba una Exception por descripción vacía.");
        } catch (Exception $e) {
            $this->assertEquals("La descripción es obligatoria.", $e->getMessage());
        }

        // 3. Precio vacío / cero
        try {
            $menu->setPrecio(0);
            $this->fail("Se esperaba una Exception por precio igual a 0.");
        } catch (Exception $e) {
            $this->assertEquals("El precio debe ser un número válido mayor a 0.", $e->getMessage());
        }

        // 4. Categoría vacía
        try {
            $menu->setIdCategoria('');
            $this->fail("Se esperaba una Exception por categoría vacía.");
        } catch (Exception $e) {
            $this->assertEquals("La categoría es obligatoria.", $e->getMessage());
        }

        // 5. Tipo de producto vacío
        try {
            $menu->setTipoProducto('');
            $this->fail("Se esperaba una Exception por tipo de producto vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El tipo de producto es inválido.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Validación de formato incorrecto en nombre (números o caracteres no permitidos).
     * - Validación de regla de negocio: producto COCINA sin insumos principales.
     * - Error a nivel de BD simulada: fallo en execute() con PDOException y rollback.
     */
    public function test_registrar_menu_con_datos_incorrectos_falla(): void
    {
        $menu = new Menu();

        // 1. Nombre con caracteres inválidos (solo admite letras y espacios)
        try {
            $menu->setNombreProducto('Pizza 123!');
            $this->fail("Se esperaba una Exception por números/símbolos en el nombre.");
        } catch (Exception $e) {
            $this->assertEquals("El nombre del producto solo admite letras y espacios.", $e->getMessage());
        }

        // 2. Regla de negocio: Producto COCINA sin insumos principales
        $menu->setTipoProducto('COCINA');
        try {
            $menu->setInsumosPrincipales([]);
            $this->fail("Se esperaba una Exception por insumos principales vacíos en COCINA.");
        } catch (Exception $e) {
            $this->assertEquals("El producto de cocina debe tener al menos un insumo principal.", $e->getMessage());
        }

        // 3. Simulación de fallo en base de datos con rollback
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $pdoException = new PDOException("Error de restricción de integridad foránea.");

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdo->expects($this->once())->method('rollBack');

        $menuMock = $this->createMenuMock($mockPdo);
        $menuMock->setNombreProducto('Hamburguesa Especial');
        $menuMock->setDescripcion('Deliciosa hamburguesa');
        $menuMock->setPrecio(15.0);
        $menuMock->setIdCategoria('CAT01');
        $menuMock->setTipoProducto('COCINA');

        $resultado = $menuMock->Transaccion(['peticion' => 'registrar']);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Error al registrar el menú', $resultado['message']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Proporciona datos válidos, insumos principales y adicionales válidos,
     * ejecuta la inserción, hace commit y genera el ID con prefijo 'PROD'.
     */
    public function test_registrar_menu_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $menuMock = $this->createMenuMock($mockPdo);
        $menuMock->setNombreProducto('Hamburguesa Clasica');
        $menuMock->setDescripcion('Carne de res fresca con vegetales');
        $menuMock->setPrecio(12.50);
        $menuMock->setIdCategoria('CAT01');
        $menuMock->setTipoProducto('COCINA');
        $menuMock->setInsumosPrincipales(json_encode([
            ['id' => 'INS01', 'cantidad' => 150, 'unidad' => 'GR']
        ]));
        $menuMock->setInsumosAdicionales(json_encode([
            ['id' => 'INS02', 'cantidad' => 30, 'precio' => 1.5, 'unidad' => 'GR']
        ]));

        $resultado = $menuMock->Transaccion(['peticion' => 'registrar']);

        $this->assertTrue($resultado['success']);
        $this->assertEquals('Menú registrado exitosamente', $resultado['message']);
        $this->assertStringStartsWith('PROD', $resultado['id']);
    }

    // =========================================================================
    // 2. MÉTODO: modificarMenu (Transacción 'modificar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * El nombre o la descripción no pueden estar vacíos al modificar un producto.
     */
    public function test_modificar_menu_con_datos_vacios_lanza_excepcion(): void
    {
        $menu = new Menu();

        // 1. Nombre vacío
        try {
            $menu->setNombreProducto('   ');
            $this->fail("Se esperaba una Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El nombre del producto es obligatorio.", $e->getMessage());
        }

        // 2. Descripción vacía
        try {
            $menu->setDescripcion('');
            $this->fail("Se esperaba una Exception por descripción vacía.");
        } catch (Exception $e) {
            $this->assertEquals("La descripción es obligatoria.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Validación de regla de negocio: productos de BARRA no pueden tener insumos adicionales.
     * - Simulación de fallo en base de datos al ejecutar el UPDATE con rollback.
     */
    public function test_modificar_menu_con_datos_incorrectos_falla(): void
    {
        $menu = new Menu();

        // 1. Regla de negocio: Producto BARRA con insumos adicionales lanza excepción
        $menu->setTipoProducto('BARRA');
        try {
            $menu->setInsumosAdicionales(json_encode([
                ['id' => 'INS05', 'cantidad' => 1, 'precio' => 2.0, 'unidad' => 'UN']
            ]));
            $this->fail("Se esperaba una Exception porque BARRA no permite insumos adicionales.");
        } catch (Exception $e) {
            $this->assertEquals("Los productos de barra no pueden tener insumos adicionales.", $e->getMessage());
        }

        // 2. Simulación de fallo en base de datos al actualizar
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $pdoException = new PDOException("Error de conexión durante el UPDATE.");

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdo->expects($this->once())->method('rollBack');

        $menuMock = $this->createMenuMock($mockPdo);
        $menuMock->setIdProducto('PROD202610140001');
        $menuMock->setNombreProducto('Coctel Mojito');
        $menuMock->setDescripcion('Refrescante bebida');
        $menuMock->setPrecio(8.0);
        $menuMock->setIdCategoria('CAT02');
        $menuMock->setTipoProducto('BARRA');

        $resultado = $menuMock->Transaccion(['peticion' => 'modificar']);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Error al modificar el menú', $resultado['message']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Modifica los datos del producto existente, actualiza preparaciones,
     * realiza commit y retorna respuesta exitosa.
     */
    public function test_modificar_menu_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $menuMock = $this->createMenuMock($mockPdo);
        $menuMock->setIdProducto('PROD202610140001');
        $menuMock->setNombreProducto('Limonada Frappe');
        $menuMock->setDescripcion('Bebida natural frappeada');
        $menuMock->setPrecio(5.0);
        $menuMock->setIdCategoria('CAT02');
        $menuMock->setTipoProducto('BARRA');
        $menuMock->setInsumosPrincipales(json_encode([
            ['id' => 'INS10', 'cantidad' => 1, 'unidad' => 'UN']
        ]));

        $resultado = $menuMock->Transaccion(['peticion' => 'modificar']);

        $this->assertTrue($resultado['success']);
        $this->assertEquals('Menú guardado exitosamente', $resultado['message']);
        $this->assertEquals('PROD202610140001', $resultado['id']);
    }

    // =========================================================================
    // 3. MÉTODO: eliminarMenu (Transacción 'eliminar' -> UPDATE estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * Si se intenta eliminar con un ID vacío o no coincidente, la base de datos
     * retorna false (0 filas afectadas) y el modelo devuelve error de eliminación.
     */
    public function test_eliminar_menu_con_datos_vacios_falla(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        // execute retorna false simulando que no se pudo ejecutar la baja
        $mockStmt->expects($this->once())
            ->method('execute')
            ->with(['id_producto' => ''])
            ->willReturn(false);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);

        $menuMock = $this->createMenuMock($mockPdo);
        $menuMock->setIdProducto('');

        $resultado = $menuMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertFalse($resultado['success']);
        $this->assertEquals('Error al eliminar', $resultado['message']);
    }

    /**
     * Caso con datos incorrectos / error de base de datos al eliminar:
     * Si la consulta genera una PDOException, se captura y retorna success false con el mensaje.
     */
    public function test_eliminar_menu_con_error_en_base_de_datos_falla(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $pdoException = new PDOException("Error de tabla bloqueada.");

        $mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException($pdoException);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);

        $menuMock = $this->createMenuMock($mockPdo);
        $menuMock->setIdProducto('PROD_INEXISTENTE');

        $resultado = $menuMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Error: Error de tabla bloqueada.', $resultado['message']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * Se proporciona un ID de producto válido, el UPDATE a estatus = 0 se ejecuta
     * satisfactoriamente y devuelve confirmación.
     */
    public function test_eliminar_menu_con_datos_correctos_elimina_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockStmt->expects($this->once())
            ->method('execute')
            ->with(['id_producto' => 'PROD202610140001'])
            ->willReturn(true);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);

        $menuMock = $this->createMenuMock($mockPdo);
        $menuMock->setIdProducto('PROD202610140001');

        $resultado = $menuMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertTrue($resultado['success']);
        $this->assertEquals('Producto eliminado', $resultado['message']);
    }
}
