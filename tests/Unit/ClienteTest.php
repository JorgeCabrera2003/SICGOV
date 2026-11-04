<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Cliente;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class ClienteTest extends TestCase
{
    /**
     * Crea un mock parcial de Cliente para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createClienteMock(PDO&MockObject $mockPdo): Cliente&MockObject
    {
        $mock = $this->getMockBuilder(Cliente::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto Cliente/Persona.
     */
    private function poblarDatosValidos(Cliente $cliente): void
    {
        $cliente->setCedula('V-12345678');
        $cliente->setNombre('Carlos');
        $cliente->setApellido('Perez');
        $cliente->setFechaNacimiento('1995-05-15');
        $cliente->setTelefono('0414-1234567');
        $cliente->setCorreo('carlos@example.com');
        $cliente->setDireccion('Av. Principal Edif. Los Roques');
        $cliente->setSexo('M');
        $cliente->setEstatus(1);
    }

    // =========================================================================
    // 1. MÉTODO: RegistrarCliente (Transacción 'registrar' -> INSERT persona y cliente)
    // =========================================================================

    /**
     * Caso con datos vacíos al registrar:
     * Comprueba que los campos obligatorios heredados de Persona (cédula, nombre,
     * apellido, fecha de nacimiento, dirección, sexo) rechacen valores vacíos.
     */
    public function test_registrar_cliente_con_datos_vacios_lanza_excepcion(): void
    {
        // -----------------------------------------------------------------
        // 1. Cédula vacía
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setCedula('');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por cédula vacía.");
        $this->assertStringContainsString("La cédula debe tener un prefijo válido", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 2. Nombre vacío
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setNombre('');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por nombre vacío.");
        $this->assertStringContainsString("El nombre es obligatoria", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 3. Apellido vacío
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setApellido('   ');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por apellido vacío.");
        $this->assertEquals("El apellido es obligatorio.", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 4. Fecha de nacimiento vacía
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setFechaNacimiento('');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por fecha de nacimiento vacía.");
        $this->assertEquals("La fecha de nacimiento es obligatoria.", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 5. Dirección vacía
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setDireccion('');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por dirección vacía.");
        $this->assertEquals("La dirección es obligatoria.", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 6. Sexo vacío
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setSexo('');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por sexo vacío.");
        $this->assertEquals("El sexo debe ser M (Masculino) o F (Femenino).", $excepcion->getMessage());
    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Formatos no válidos en cédula, correo, teléfono o fecha futura.
     * - Regla de negocio: cliente ya registrado previamente en la tabla 'cliente'.
     * - Simulación de error SQL: duplicado en correo (código 23000) genera rollback.
     */
    public function test_registrar_cliente_con_datos_incorrectos_falla(): void
    {
        // -----------------------------------------------------------------
        // 1. Cédula sin prefijo ni guion
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setCedula('12345678');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por formato de cédula incorrecto.");
        $this->assertStringContainsString("prefijo válido", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 2. Correo con formato inválido
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setCorreo('correo-invalido-sin-arroba');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por correo inválido.");
        $this->assertEquals("El formato del correo electrónico no es válido.", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 3. Fecha de nacimiento futura
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setFechaNacimiento('2099-12-31');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por fecha futura.");
        $this->assertEquals("La fecha de nacimiento debe ser anterior a hoy.", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 4. Regla de negocio: La cédula ya está registrada como cliente
        // -----------------------------------------------------------------
        // Arrange
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCliente = $this->createMock(PDOStatement::class);

        $mockCheckCliente->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckCliente->expects($this->once())->method('rowCount')->willReturn(1); // Ya existe

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckCliente);
        $mockPdo->expects($this->never())->method('beginTransaction');

        $clienteMock = $this->createClienteMock($mockPdo);
        $this->poblarDatosValidos($clienteMock);

        // Act
        $resultado = $clienteMock->Transaccion(['peticion' => 'registrar']);

        // Assert
        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(400, $resultado['response']['resultado']);
        $this->assertEquals('El cliente ya se encuentra registrado.', $resultado['response']['mensaje']);

        // -----------------------------------------------------------------
        // 5. Error a nivel de BD simulada: correo duplicado (SQLSTATE 23000)
        // -----------------------------------------------------------------
        // Arrange
        $mockPdoError = $this->createMock(PDO::class);
        $mockCheckNotFound = $this->createMock(PDOStatement::class);
        $mockStmtInsert = $this->createMock(PDOStatement::class);

        $mockCheckNotFound->method('execute')->willReturn(true);
        $mockCheckNotFound->method('rowCount')->willReturn(0);

        $pdoException = new PDOException("Duplicate entry 'carlos@example.com' for key 'correo'", 23000);

        $mockPdoError->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckNotFound, $mockStmtInsert) {
            if (str_contains($sql, 'SELECT')) {
                return $mockCheckNotFound;
            }
            return $mockStmtInsert;
        });

        $mockPdoError->expects($this->once())->method('beginTransaction');
        $mockPdoError->method('inTransaction')->willReturn(true);
        $mockStmtInsert->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdoError->expects($this->once())->method('rollBack');

        $clienteMockError = $this->createClienteMock($mockPdoError);
        $this->poblarDatosValidos($clienteMockError);

        // Act
        $resultadoError = $clienteMockError->Transaccion(['peticion' => 'registrar']);

        // Assert
        $this->assertEquals(-1, $resultadoError['estado']);
        $this->assertEquals('El correo electrónico ya se encuentra registrado.', $resultadoError['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Verifica que cuando la persona y el cliente no existen, se inserten ambos
     * registros, se ejecute commit y retorne estado exitoso 200.
     */
    public function test_registrar_cliente_con_datos_correctos_registra_exitosamente(): void
    {
        // Arrange
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckCliente = $this->createMock(PDOStatement::class);
        $mockCheckPersona = $this->createMock(PDOStatement::class);
        $mockInsertPersona = $this->createMock(PDOStatement::class);
        $mockInsertCliente = $this->createMock(PDOStatement::class);

        // 1. Verificación cliente: no existe (rowCount = 0)
        $mockCheckCliente->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckCliente->expects($this->once())->method('rowCount')->willReturn(0);

        // 2. Verificación persona: no existe (rowCount = 0)
        $mockCheckPersona->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckPersona->expects($this->once())->method('rowCount')->willReturn(0);

        // 3. Inserciones exitosas
        $mockInsertPersona->expects($this->once())->method('execute')->willReturn(true);
        $mockInsertCliente->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use (
            $mockCheckCliente,
            $mockCheckPersona,
            $mockInsertPersona,
            $mockInsertCliente
        ) {
            if (str_contains($sql, 'SELECT c.cedula FROM cliente')) {
                return $mockCheckCliente;
            }
            if (str_contains($sql, 'SELECT cedula FROM persona')) {
                return $mockCheckPersona;
            }
            if (str_contains($sql, 'INSERT INTO persona')) {
                return $mockInsertPersona;
            }
            return $mockInsertCliente;
        });

        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $clienteMock = $this->createClienteMock($mockPdo);
        $this->poblarDatosValidos($clienteMock);

        // Act
        $resultado = $clienteMock->Transaccion(['peticion' => 'registrar']);

        // Assert
        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Cliente registrado exitosamente.', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: ModificarCliente (Transacción 'actualizar' / 'modificar' -> UPDATE persona)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * Intenta actualizar campos esenciales con valores vacíos, verificando que
     * se disparen excepciones de validación en los setters.
     */
    public function test_modificar_cliente_con_datos_vacios_lanza_excepcion(): void
    {
        // -----------------------------------------------------------------
        // 1. Nombre vacío
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setNombre('');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por nombre vacío.");
        $this->assertStringContainsString("El nombre es obligatoria", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 2. Apellido vacío
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setApellido('');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por apellido vacío.");
        $this->assertEquals("El apellido es obligatorio.", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 3. Dirección demasiado corta (< 3 caracteres)
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setDireccion('Av');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por dirección demasiado corta.");
        $this->assertEquals("La dirección debe tener al menos 3 caracteres.", $excepcion->getMessage());
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Caracteres no numéricos/no permitidos en nombre o apellido.
     * - Simulación de conflicto de base de datos (correo ya pertenece a otra persona).
     */
    public function test_modificar_cliente_con_datos_incorrectos_falla(): void
    {
        // -----------------------------------------------------------------
        // 1. Nombre con números
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setNombre('Carlos 123');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por números en el nombre.");
        $this->assertEquals("El nombre solo puede contener letras y espacios.", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 2. Estatus no permitido
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setEstatus(9);
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por estatus no válido.");
        $this->assertEquals("El estatus no es válido.", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 3. Conflicto de correo duplicado en la base de datos durante el UPDATE
        // -----------------------------------------------------------------
        // Arrange
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $pdoException = new PDOException("Duplicate entry for key correo", 23000);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdo->expects($this->once())->method('rollBack');

        $clienteMock = $this->createClienteMock($mockPdo);
        $this->poblarDatosValidos($clienteMock);

        // Act
        $resultado = $clienteMock->Transaccion(['peticion' => 'actualizar']);

        // Assert
        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals('El correo electrónico ya se encuentra registrado a otra persona.', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Asigna datos actualizados válidos, ejecuta UPDATE en 'persona', confirma transacción
     * y retorna respuesta exitosa 200.
     */
    public function test_modificar_cliente_con_datos_correctos_modifica_exitosamente(): void
    {
        // Arrange
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $clienteMock = $this->createClienteMock($mockPdo);
        $this->poblarDatosValidos($clienteMock);
        $clienteMock->setNombre('Carlos Alberto');
        $clienteMock->setTelefono('0424-9876543');

        // Act
        $resultado = $clienteMock->Transaccion(['peticion' => 'actualizar']);

        // Assert
        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Cliente actualizado exitosamente.', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 3. MÉTODO: EliminarCliente (Transacción 'eliminar' -> UPDATE cliente SET estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * No se asigna cédula (queda vacía); la verificación inicial no encuentra ningún registro
     * y retorna 404 Registro no encontrado sin iniciar transacción.
     */
    public function test_eliminar_cliente_con_datos_vacios_retorna_no_encontrado(): void
    {
        // Arrange
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCheck = $this->createMock(PDOStatement::class);

        $mockStmtCheck->expects($this->once())
            ->method('execute')
            ->with([':cedula' => ''])
            ->willReturn(true);
        $mockStmtCheck->expects($this->once())
            ->method('rowCount')
            ->willReturn(0); // No encontrado

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmtCheck);
        $mockPdo->expects($this->never())->method('beginTransaction');

        $clienteMock = $this->createClienteMock($mockPdo);
        // Sin asignar cédula

        // Act
        $resultado = $clienteMock->Transaccion(['peticion' => 'eliminar']);

        // Assert
        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(404, $resultado['response']['resultado']);
        $this->assertEquals('Registro no encontrado.', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos incorrectos / error en base de datos al eliminar:
     * - Cédula inexistente en la tabla cliente retorna 404.
     * - Falla de base de datos (PDOException) durante el UPDATE dispara rollback.
     */
    public function test_eliminar_cliente_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // -----------------------------------------------------------------
        // 1. Cédula con formato no válido dispara excepción en setter
        // -----------------------------------------------------------------
        // Arrange
        $cliente = new Cliente();
        $excepcion = null;

        // Act
        try {
            $cliente->setCedula('CEDULA-FALSA');
        } catch (Exception $e) {
            $excepcion = $e;
        }

        // Assert
        $this->assertNotNull($excepcion, "Se esperaba Exception por cédula inválida.");
        $this->assertStringContainsString("prefijo válido", $excepcion->getMessage());

        // -----------------------------------------------------------------
        // 2. Falla en base de datos al actualizar el estatus (Rollback)
        // -----------------------------------------------------------------
        // Arrange
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCheck = $this->createMock(PDOStatement::class);
        $mockStmtUpdate = $this->createMock(PDOStatement::class);

        $mockStmtCheck->method('execute')->willReturn(true);
        $mockStmtCheck->method('rowCount')->willReturn(1); // Existe

        $pdoException = new PDOException("Error de timeout en la base de datos.");

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('inTransaction')->willReturn(true);
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockStmtCheck, $mockStmtUpdate) {
            if (str_contains($sql, 'SELECT')) {
                return $mockStmtCheck;
            }
            return $mockStmtUpdate;
        });

        $mockStmtUpdate->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdo->expects($this->once())->method('rollBack');

        $clienteMock = $this->createClienteMock($mockPdo);
        $clienteMock->setCedula('V-12345678');

        // Act
        $resultado = $clienteMock->Transaccion(['peticion' => 'eliminar']);

        // Assert
        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * Proporciona cédula existente, actualiza el estatus a 0 en la tabla cliente,
     * ejecuta commit y retorna resultado 200.
     */
    public function test_eliminar_cliente_con_datos_correctos_elimina_exitosamente(): void
    {
        // Arrange
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtCheck = $this->createMock(PDOStatement::class);
        $mockStmtUpdate = $this->createMock(PDOStatement::class);

        // 1. Verificación: existe cliente
        $mockStmtCheck->expects($this->once())
            ->method('execute')
            ->with([':cedula' => 'V-12345678'])
            ->willReturn(true);
        $mockStmtCheck->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // 2. Actualización a estatus = 0
        $mockStmtUpdate->expects($this->once())
            ->method('execute')
            ->with([':cedula' => 'V-12345678'])
            ->willReturn(true);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockStmtCheck, $mockStmtUpdate) {
            if (str_contains($sql, 'SELECT')) {
                return $mockStmtCheck;
            }
            return $mockStmtUpdate;
        });

        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $clienteMock = $this->createClienteMock($mockPdo);
        $clienteMock->setCedula('V-12345678');

        // Act
        $resultado = $clienteMock->Transaccion(['peticion' => 'eliminar']);

        // Assert
        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Cliente eliminado exitosamente.', $resultado['response']['mensaje']);
    }






    // =========================================================================
    // 4. CASOS DE PRUEBA: ERROR Y WARNING EN CONSOLA
    // =========================================================================

    /*
   
    public function test_error_en_consola(): void
    {
        $cliente = new Cliente();
        $this->poblarDatosValidos($cliente);

        // Provoca un Error no capturado en la consola de PHPUnit
        throw new Exception("Error en consola: Fallo crítico no controlado en la prueba de Cliente.");
    }

   
    public function test_warning_en_consola(): void
    {
        $cliente = new Cliente();
        $this->poblarDatosValidos($cliente);

        // Provoca un Warning de PHP registrado por PHPUnit
        trigger_error("Warning en consola: Atributo o configuración de Cliente potencialmente obsoleta o en revisión.", E_USER_WARNING);

        $this->assertEquals('Carlos', $cliente->getNombre());
    }
        */
}


