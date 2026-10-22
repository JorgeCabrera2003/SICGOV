<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Empleado;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class EmpleadoTest extends TestCase
{
    /**
     * Crea un mock parcial de Empleado para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createEmpleadoMock(PDO&MockObject $mockPdo): Empleado&MockObject
    {
        $mock = $this->getMockBuilder(Empleado::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto Empleado/Persona.
     */
    private function poblarDatosValidos(Empleado $empleado): void
    {
        $empleado->setCedula('V-12345678');
        $empleado->setNombre('Juan');
        $empleado->setApellido('Perez');
        $empleado->setFechaNacimiento('1990-04-20');
        $empleado->setTelefono('0412-1234567');
        $empleado->setCorreo('juan.perez@example.com');
        $empleado->setDireccion('Calle Las Flores Casa 4');
        $empleado->setSexo('M');
        $empleado->setIdCargo('CARGO01');
        $empleado->setEstatus(1);
    }

    // =========================================================================
    // 1. MÉTODO: RegistrarEmpleado (Transacción 'registrar' -> INSERT persona y empleado)
    // =========================================================================

    /**
     * Caso con datos vacíos al registrar:
     * Comprueba que los campos obligatorios heredados de Persona (cédula, nombre,
     * apellido, fecha de nacimiento, dirección, sexo) rechacen valores vacíos.
     */
    public function test_registrar_empleado_con_datos_vacios_lanza_excepcion(): void
    {
        $empleado = new Empleado();

        // 1. Cédula vacía
        try {
            $empleado->setCedula('');
            $this->fail("Se esperaba Exception por cédula vacía.");
        } catch (Exception $e) {
            $this->assertStringContainsString("La cédula debe tener un prefijo válido", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $empleado->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El nombre es obligatoria", $e->getMessage());
        }

        // 3. Apellido vacío
        try {
            $empleado->setApellido('   ');
            $this->fail("Se esperaba Exception por apellido vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El apellido es obligatorio.", $e->getMessage());
        }

        // 4. Fecha de nacimiento vacía
        try {
            $empleado->setFechaNacimiento('');
            $this->fail("Se esperaba Exception por fecha de nacimiento vacía.");
        } catch (Exception $e) {
            $this->assertEquals("La fecha de nacimiento es obligatoria.", $e->getMessage());
        }

        // 5. Dirección vacía
        try {
            $empleado->setDireccion('');
            $this->fail("Se esperaba Exception por dirección vacía.");
        } catch (Exception $e) {
            $this->assertEquals("La dirección es obligatoria.", $e->getMessage());
        }

        // 6. Sexo vacío
        try {
            $empleado->setSexo('');
            $this->fail("Se esperaba Exception por sexo vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El sexo debe ser M (Masculino) o F (Femenino).", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Formatos no válidos en cédula, correo o fecha futura.
     * - Regla de negocio: empleado ya registrado previamente en la tabla 'empleado'.
     * - Simulación de error SQL: duplicado en correo (código 23000) genera rollback.
     */
    public function test_registrar_empleado_con_datos_incorrectos_falla(): void
    {
        $empleado = new Empleado();

        // 1. Cédula sin prefijo ni guion
        try {
            $empleado->setCedula('12345678');
            $this->fail("Se esperaba Exception por cédula sin prefijo.");
        } catch (Exception $e) {
            $this->assertStringContainsString("prefijo válido", $e->getMessage());
        }

        // 2. Correo con formato inválido
        try {
            $empleado->setCorreo('correo-invalido-sin-arroba');
            $this->fail("Se esperaba Exception por correo inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El formato del correo electrónico no es válido.", $e->getMessage());
        }

        // 3. Fecha de nacimiento futura
        try {
            $empleado->setFechaNacimiento('2099-12-31');
            $this->fail("Se esperaba Exception por fecha de nacimiento futura.");
        } catch (Exception $e) {
            $this->assertEquals("La fecha de nacimiento debe ser anterior a hoy.", $e->getMessage());
        }

        // 4. Regla de negocio: El empleado ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckEmpleado = $this->createMock(PDOStatement::class);

        $mockCheckEmpleado->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckEmpleado->expects($this->once())->method('rowCount')->willReturn(1); // Ya existe

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockCheckEmpleado);
        $mockPdo->expects($this->never())->method('beginTransaction');

        $empleadoMock = $this->createEmpleadoMock($mockPdo);
        $this->poblarDatosValidos($empleadoMock);

        $resultado = $empleadoMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(400, $resultado['response']['resultado']);
        $this->assertEquals('El empleado ya se encuentra registrado.', $resultado['response']['mensaje']);

        // 5. Error a nivel de BD simulada: correo duplicado (SQLSTATE 23000)
        $mockPdoError = $this->createMock(PDO::class);
        $mockCheckNotFound = $this->createMock(PDOStatement::class);
        $mockStmtInsert = $this->createMock(PDOStatement::class);

        $mockCheckNotFound->method('execute')->willReturn(true);
        $mockCheckNotFound->method('rowCount')->willReturn(0);

        $pdoException = new PDOException("Duplicate entry 'juan.perez@example.com' for key 'correo'", 23000);

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

        $empleadoMockError = $this->createEmpleadoMock($mockPdoError);
        $this->poblarDatosValidos($empleadoMockError);

        $resultadoError = $empleadoMockError->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $resultadoError['estado']);
        $this->assertEquals('El correo electrónico ya se encuentra registrado.', $resultadoError['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Verifica inserción en 'persona' y en 'empleado', confirmando la transacción con commit.
     */
    public function test_registrar_empleado_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckEmpleado = $this->createMock(PDOStatement::class);
        $mockCheckPersona = $this->createMock(PDOStatement::class);
        $mockInsertPersona = $this->createMock(PDOStatement::class);
        $mockInsertEmpleado = $this->createMock(PDOStatement::class);

        // 1. Verificación empleado: no existe (rowCount = 0)
        $mockCheckEmpleado->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckEmpleado->expects($this->once())->method('rowCount')->willReturn(0);

        // 2. Verificación persona: no existe (rowCount = 0)
        $mockCheckPersona->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckPersona->expects($this->once())->method('rowCount')->willReturn(0);

        // 3. Inserciones exitosas
        $mockInsertPersona->expects($this->once())->method('execute')->willReturn(true);
        $mockInsertEmpleado->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use (
            $mockCheckEmpleado,
            $mockCheckPersona,
            $mockInsertPersona,
            $mockInsertEmpleado
        ) {
            if (str_contains($sql, 'SELECT cedula FROM empleado')) {
                return $mockCheckEmpleado;
            }
            if (str_contains($sql, 'SELECT cedula FROM persona')) {
                return $mockCheckPersona;
            }
            if (str_contains($sql, 'INSERT INTO persona')) {
                return $mockInsertPersona;
            }
            return $mockInsertEmpleado;
        });

        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $empleadoMock = $this->createEmpleadoMock($mockPdo);
        $this->poblarDatosValidos($empleadoMock);

        $resultado = $empleadoMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Empleado registrado exitosamente.', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: ModificarEmpleado (Transacción 'actualizar' / 'modificar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * Intenta actualizar campos esenciales con valores vacíos, verificando que
     * se disparen excepciones de validación en los setters.
     */
    public function test_modificar_empleado_con_datos_vacios_lanza_excepcion(): void
    {
        $empleado = new Empleado();

        // 1. Nombre vacío
        try {
            $empleado->setNombre('  ');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El nombre es obligatoria", $e->getMessage());
        }

        // 2. Apellido vacío
        try {
            $empleado->setApellido('');
            $this->fail("Se esperaba Exception por apellido vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El apellido es obligatorio.", $e->getMessage());
        }

        // 3. Dirección demasiado corta (< 3 caracteres)
        try {
            $empleado->setDireccion('Av');
            $this->fail("Se esperaba Exception por dirección corta.");
        } catch (Exception $e) {
            $this->assertEquals("La dirección debe tener al menos 3 caracteres.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Caracteres numéricos en nombre o apellido.
     * - Simulación de conflicto de base de datos (correo ya pertenece a otra persona).
     */
    public function test_modificar_empleado_con_datos_incorrectos_falla(): void
    {
        $empleado = new Empleado();

        // 1. Nombre con números
        try {
            $empleado->setNombre('Juan 123');
            $this->fail("Se esperaba Exception por números en el nombre.");
        } catch (Exception $e) {
            $this->assertEquals("El nombre solo puede contener letras y espacios.", $e->getMessage());
        }

        // 2. Apellido con caracteres no permitidos
        try {
            $empleado->setApellido('Perez #');
            $this->fail("Se esperaba Exception por caracteres inválidos en apellido.");
        } catch (Exception $e) {
            $this->assertEquals("El apellido solo puede contener letras y espacios.", $e->getMessage());
        }

        // 3. Conflicto de correo duplicado en la base de datos durante el UPDATE
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $pdoException = new PDOException("Duplicate entry for key correo", 23000);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdo->expects($this->once())->method('rollBack');

        $empleadoMock = $this->createEmpleadoMock($mockPdo);
        $this->poblarDatosValidos($empleadoMock);

        $resultado = $empleadoMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals('El correo electrónico ya se encuentra registrado.', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Asigna datos actualizados válidos, ejecuta UPDATE en 'persona' y 'empleado',
     * confirma la transacción con commit y retorna respuesta 200.
     */
    public function test_modificar_empleado_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtPersona = $this->createMock(PDOStatement::class);
        $mockStmtEmpleado = $this->createMock(PDOStatement::class);

        $mockStmtPersona->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtEmpleado->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockStmtPersona, $mockStmtEmpleado) {
            if (str_contains($sql, 'UPDATE persona')) {
                return $mockStmtPersona;
            }
            return $mockStmtEmpleado;
        });

        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $empleadoMock = $this->createEmpleadoMock($mockPdo);
        $this->poblarDatosValidos($empleadoMock);
        $empleadoMock->setNombre('Juan Carlos');
        $empleadoMock->setIdCargo('CARGO02');

        $resultado = $empleadoMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Empleado actualizado exitosamente.', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 3. MÉTODO: EliminarEmpleado (Transacción 'eliminar' -> UPDATE empleado SET estatus = 0)
    // =========================================================================

    /**
     * Caso con datos vacíos al eliminar:
     * Intenta eliminar pasando cédula vacía directamente en el setter,
     * verificando que se rechace antes de acceder a la base de datos.
     */
    public function test_eliminar_empleado_con_datos_vacios_lanza_excepcion(): void
    {
        $empleado = new Empleado();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La cédula debe tener un prefijo válido");

        $empleado->setCedula('');
    }

    /**
     * Caso con datos incorrectos / error en base de datos al eliminar:
     * - Cédula con formato inválido lanza excepción.
     * - Falla de base de datos (PDOException) durante el UPDATE retorna estado -1 y código 500.
     */
    public function test_eliminar_empleado_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // 1. Cédula con formato no válido
        $empleado = new Empleado();
        try {
            $empleado->setCedula('CEDULA-ERRONEA');
            $this->fail("Se esperaba Exception por cédula inválida.");
        } catch (Exception $e) {
            $this->assertStringContainsString("prefijo válido", $e->getMessage());
        }

        // 2. Falla en base de datos al actualizar el estatus
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $pdoException = new PDOException("Error de conexión con la base de datos.");

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException($pdoException);

        $empleadoMock = $this->createEmpleadoMock($mockPdo);
        $empleadoMock->setCedula('V-12345678');

        $resultado = $empleadoMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Error interno del servidor', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al eliminar:
     * Proporciona cédula válida, ejecuta el UPDATE de estatus a 0 y retorna resultado 200.
     */
    public function test_eliminar_empleado_con_datos_correctos_elimina_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockStmt->expects($this->once())
            ->method('execute')
            ->with([':cedula' => 'V-12345678'])
            ->willReturn(true);

        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);

        $empleadoMock = $this->createEmpleadoMock($mockPdo);
        $empleadoMock->setCedula('V-12345678');

        $resultado = $empleadoMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Empleado eliminado exitosamente.', $resultado['response']['mensaje']);
    }
}
