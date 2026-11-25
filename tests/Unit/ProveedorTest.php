<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Proveedor;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

final class ProveedorTest extends TestCase
{

    private $objProveedor;

    protected function setUp(): void
    {
        $this->objProveedor = new Proveedor();
    }


    /**
     * Crea un mock parcial de Proveedor para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createProveedorMock(PDO&MockObject $mockPdo): Proveedor&MockObject
    {
        $mock = $this->getMockBuilder(Proveedor::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto Proveedor.
     */
    private function poblarDatosValidos(Proveedor $proveedor): void
    {
        $proveedor->setDocumentoLegal('J-32984132');
        $proveedor->setNombre('Distribuidora La Toñeca');
        $proveedor->setDireccion('Av. Los Leones Calles 3 y 4');
        $proveedor->setTelefono('0412-9842321');
    }

    // =========================================================================
    // 1. MÉTODO: RegistrarProveedor (Transacción 'registrar' -> INSERT proveedor)
    // =========================================================================
    public function test_registrar_proveedor_con_datos_vacios_e_incorrectos_lanza_excepcion(): void
    {

        // 1. Docunebto Legal vacía
        try {
            $this->objProveedor->setDocumentoLegal('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El documento legal debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $this->objProveedor->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Nombre no válido. Debe tener al menos entre 3 a 150 carácteres", $e->getMessage());
        }

        // 3. Dirección vacío
        try {
            $this->objProveedor->setDireccion('');
            $this->fail("Se esperaba Exception por id de vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Dirección no válida", $e->getMessage());
        }

        // 4. Teléfono vacío
        try {
            $this->objProveedor->setTelefono('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Teléfono no válido. Debe tener el siguiente formato: 0424-1234567", $e->getMessage());
        }

        // 5. Correo Electrónico vacío
        try {
            $this->objProveedor->setCorreo('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El formato del correo electrónico no es válido.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Formatos no válidos en id y nombre.
     * - Regla de negocio: proveedor ya registrado previamente en la tabla 'proveedor'.
     * - Simulación de error SQL para generar rollback.
     */
    public function test_registrar_proveedor_con_datos_incorrectos_falla(): void
    {
        // 1. Docunebto Legal vacía
        try {
            $this->objProveedor->setDocumentoLegal('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El documento legal debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $this->objProveedor->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Nombre no válido. Debe tener al menos entre 3 a 150 carácteres", $e->getMessage());
        }

        // 3. Dirección vacío
        try {
            $this->objProveedor->setDireccion('');
            $this->fail("Se esperaba Exception por id de vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Dirección no válida", $e->getMessage());
        }

        // 4. Teléfono vacío
        try {
            $this->objProveedor->setTelefono('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Teléfono no válido. Debe tener el siguiente formato: 0424-1234567", $e->getMessage());
        }

        // 5. Correo Electrónico vacío
        try {
            $this->objProveedor->setCorreo('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El formato del correo electrónico no es válido.", $e->getMessage());
        }

        // 6. Regla de negocio: proveedor ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckProveedor = $this->createMock(PDOStatement::class);

        $mockCheckProveedor->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckProveedor->expects($this->once())->method('rowCount')->willReturn(1); // Ya existe

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckProveedor);
        $mockPdo->expects($this->once())->method('beginTransaction');

        $proveedorMock = $this->createProveedorMock($mockPdo);
        $this->poblarDatosValidos($proveedorMock);

        $resultado = $proveedorMock->Transaccion(['peticion' => 'validar']);
        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Verifica inserción en 'proveedor', confirmando la transacción con commit.
     */
    public function test_registrar_proveedor_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckProveedor = $this->createMock(PDOStatement::class);
        $mockInsertProveedor = $this->createMock(PDOStatement::class);

        // 1. Inserciones exitosas
        $mockInsertProveedor->expects($this->once())->method('execute')->willReturn(true);

        $mockCheckProveedor->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockInsertProveedor, $mockCheckProveedor) {
            if (str_contains($sql, 'INSERT INTO proveedor')) {
                return $mockInsertProveedor;
            }

            if(str_contains($sql, 'SELECT * FROM proveedor WHERE documento_legal = :documento_legal')){
                return $mockCheckProveedor;
            }  
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $proveedorMock = $this->createProveedorMock($mockPdo);
        $this->poblarDatosValidos($proveedorMock);

        $resultado = $proveedorMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(201, $resultado['response']['resultado']);
        $this->assertEquals('Proveedor registrado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: ModificarProveedor (Transacción 'actualizar' / 'modificar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * Intenta actualizar campos esenciales con valores vacíos, verificando que
     * se disparen excepciones de validación en los setters.
     */
    public function test_modificar_proveedor_con_datos_vacios_lanza_excepcion(): void
    {
        // 1. Docunebto Legal vacía
        try {
            $this->objProveedor->setDocumentoLegal('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El documento legal debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $this->objProveedor->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Nombre no válido. Debe tener al menos entre 3 a 150 carácteres", $e->getMessage());
        }

        // 3. Dirección vacío
        try {
            $this->objProveedor->setDireccion('');
            $this->fail("Se esperaba Exception por id de vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Dirección no válida", $e->getMessage());
        }

        // 4. Teléfono vacío
        try {
            $this->objProveedor->setTelefono('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Teléfono no válido. Debe tener el siguiente formato: 0424-1234567", $e->getMessage());
        }

        // 5. Correo Electrónico vacío
        try {
            $this->objProveedor->setCorreo('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El formato del correo electrónico no es válido.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Caracteres numéricos en nombre o apellido.
     * - Simulando error de PDOExcepcion para comprobar el manejo de expeciones de dicho método.
     */
    public function test_modificar_proveedor_con_datos_incorrectos_falla(): void
    {

               // 1. Docunebto Legal vacía
        try {
            $this->objProveedor->setDocumentoLegal('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El documento legal debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $this->objProveedor->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Nombre no válido. Debe tener al menos entre 3 a 150 carácteres", $e->getMessage());
        }

        // 3. Dirección vacío
        try {
            $this->objProveedor->setDireccion('');
            $this->fail("Se esperaba Exception por id de vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Dirección no válida", $e->getMessage());
        }

        // 4. Teléfono vacío
        try {
            $this->objProveedor->setTelefono('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("Teléfono no válido. Debe tener el siguiente formato: 0424-1234567", $e->getMessage());
        }

        // 5. Correo Electrónico vacío
        try {
            $this->objProveedor->setCorreo('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El formato del correo electrónico no es válido.", $e->getMessage());
        }

        // 6. Conflicto de correo duplicado en la base de datos durante el UPDATE
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt)->willThrowException(new PDOException("Error de Base de Datos"));
        $mockPdo->expects($this->never())->method('commit');
        $mockPdo->expects($this->once())->method('rollBack');

        $proveedorMock = $this->createProveedorMock($mockPdo);
        $this->poblarDatosValidos($proveedorMock);

        $resultado = $proveedorMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals('Error interno del servidor', $resultado['HTTP_STATUS']['mensaje']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Asigna datos actualizados válidos, ejecuta UPDATE en 'categoría de proveedor',
     * confirma la transacción con commit y retorna respuesta 200.
     */
    public function test_modificar_proveedor_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtProveedor = $this->createMock(PDOStatement::class);

        $mockStmtProveedor->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockStmtProveedor) {
            if (str_contains($sql, 'UPDATE proveedor')) {
                return $mockStmtProveedor;
            }
        });

        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $proveedorMock = $this->createProveedorMock($mockPdo);
        $this->poblarDatosValidos($proveedorMock);

        $proveedorMock->setDocumentoLegal('J-32984132');
        $proveedorMock->setNombre('Distribuidora La Toñeca');
        $proveedorMock->setDireccion('Av. Los Leones Calles 3 y 4');
        $proveedorMock->setTelefono('0412-9842321');

        $resultado = $proveedorMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Proveedor actualizado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 3. MÉTODO: EliminarProveedor (Transacción 'eliminar' -> UPDATE proveedor SET estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * Intenta eliminar pasando id vacío directamente en el setter,
     * verificando que se rechace antes de acceder a la base de datos.
     */
    public function test_eliminar_proveedor_con_datos_vacios_lanza_excepcion(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El documento legal debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.");

        $this->objProveedor->setDocumentoLegal('');
    }

    /**
     * Caso con datos incorrectos / error en base de datos al eliminar:
     * - Cédula con formato inválido lanza excepción.
     * - Falla de base de datos (PDOException) durante el UPDATE retorna estado -1 y código 500.
     */
    public function test_eliminar_proveedor_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // 1. ID con más de 32 caráteres
        try {
            $this->objProveedor->setDocumentoLegal('J-32984132**');
            $this->fail("Se esperaba Exception por exceder la cantidad de carácteres en el campo ID");
        } catch (Exception $e) {
            $this->assertStringContainsString("El documento legal debe tener un prefijo válido (V, E, J, P, G) seguido de 7 a 12 dígitos.", $e->getMessage());
        }

        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        // 1. Regla de negocio: la categoria proveedor ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckProveedor = $this->createMock(PDOStatement::class);
        $mockDeleteProveedor = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckProveedor->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckProveedor->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteProveedor->expects($this->once())->method('execute')->willThrowException(new PDOException("Error de conexión con la base de datos."));

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckProveedor, $mockDeleteProveedor) {
            if (str_contains($sql, 'SELECT * FROM proveedor WHERE documento_legal = :documento_legal')) {
                return $mockCheckProveedor;
            }
            if (str_contains($sql, 'UPDATE proveedor')) {
                return $mockDeleteProveedor;
            }
        });

        $proveedorMock = $this->createProveedorMock($mockPdo);
        $proveedorMock->setDocumentoLegal('J-32984132');

        $resultado = $proveedorMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Error interno del servidor', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * Proporciona id válido, ejecuta el UPDATE de estatus a 0 y retorna resultado 200.
     */
    public function test_eliminar_proveedor_con_datos_correctos_elimina_exitosamente(): void
    {
        // 1. Regla de negocio: la categoria proveedor ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckProveedor = $this->createMock(PDOStatement::class);
        $mockDeleteProveedor = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckProveedor->expects($this->once())->method('execute')->willReturn(false);
        $mockCheckProveedor->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockDeleteProveedor->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckProveedor, $mockDeleteProveedor) {
            if (str_contains($sql, 'SELECT * FROM proveedor WHERE documento_legal = :documento_legal')) {
                return $mockCheckProveedor;
            }
            if (str_contains($sql, 'UPDATE proveedor')) {
                return $mockDeleteProveedor;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $proveedorMock = $this->createProveedorMock($mockPdo);
        $this->poblarDatosValidos($proveedorMock);

        $resultado = $proveedorMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Proveedor eliminado exitosamente', $resultado['response']['mensaje']);
    }

    protected function tearDown(): void
    {
        $this->objProveedor = NULL;
    }
}