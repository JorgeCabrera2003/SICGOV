<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\Security\Usuario;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class UsuarioTest extends TestCase
{
    /**
     * Crea un mock parcial de Usuario para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createUsuarioMock(PDO&MockObject $mockPdo): Usuario&MockObject
    {
        $mock = $this->getMockBuilder(Usuario::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto Usuario.
     */
    private function poblarDatosValidos(Usuario $usuario): void
    {
        $usuario->setCedula('V-12345678');
        $usuario->setUsername('Administrador');
        $usuario->setClave('ClaveFuerte123!');
        $usuario->setIdRol('ROL01');
        $usuario->setEstatus(1);
        $usuario->setEstatusClave(1);
    }

    // =========================================================================
    // 1. MÉTODO: RegistrarUsuario (Transacción 'registrar' -> INSERT usuario)
    // =========================================================================

    /**
     * Caso con datos vacíos al registrar:
     * Comprueba que los campos requeridos (cédula, username, etc.) rechacen
     * valores vacíos o espacios.
     */
    public function test_registrar_usuario_con_datos_vacios_lanza_excepcion(): void
    {
        $usuario = new Usuario();

        // 1. Cédula vacía
        try {
            $usuario->setCedula('');
            $this->fail("Se esperaba Exception por cédula vacía.");
        } catch (Exception $e) {
            $this->assertStringContainsString("La cédula debe tener un prefijo válido", $e->getMessage());
        }

        // 2. Username vacío / solo espacios (< 3 caracteres)
        try {
            $usuario->setUsername('   ');
            $this->fail("Se esperaba Exception por username vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El usuario debe tener al menos 3 caracteres.", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al registrar:
     * - Username con números o símbolos (solo admite letras).
     * - Contraseña que no cumple requisitos de dificultad.
     * - Conflicto: Cédula o username ya existente en la base de datos (409 Conflict).
     * - Error a nivel de BD simulada: fallo en execute() con PDOException y rollback.
     */
    public function test_registrar_usuario_con_datos_incorrectos_falla(): void
    {
        $usuario = new Usuario();

        // 1. Username con caracteres inválidos (contiene números)
        try {
            $usuario->setUsername('admin123');
            $this->fail("Se esperaba Exception por números en el username.");
        } catch (Exception $e) {
            $this->assertEquals("El usuario solo puede contener letras, sin números ni símbolos.", $e->getMessage());
        }

        // 2. Contraseña débil (menos de 8 caracteres o sin símbolo)
        try {
            $usuario->setClave('password');
            $this->fail("Se esperaba Exception por contraseña débil.");
        } catch (Exception $e) {
            $this->assertStringContainsString("La contraseña debe tener al menos 8 caracteres", $e->getMessage());
        }

        // 3. Validación de duplicado: usuario/cédula ya registrado
        $mockPdoDuplicado = $this->createMock(PDO::class);
        $mockStmtValidar = $this->createMock(PDOStatement::class);

        $mockStmtValidar->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtValidar->expects($this->once())->method('rowCount')->willReturn(1); // Ya existe
        $mockStmtValidar->expects($this->once())->method('fetch')->willReturn(['cedula' => 'V-12345678']);

        $mockPdoDuplicado->expects($this->once())->method('beginTransaction');
        $mockPdoDuplicado->expects($this->once())->method('prepare')->willReturn($mockStmtValidar);
        $mockPdoDuplicado->expects($this->once())->method('commit');

        $usuarioMockDuplicado = $this->createUsuarioMock($mockPdoDuplicado);
        $this->poblarDatosValidos($usuarioMockDuplicado);

        $resultadoDuplicado = $usuarioMockDuplicado->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $resultadoDuplicado['estado']);
        $this->assertEquals(409, $resultadoDuplicado['response']['resultado']);
        $this->assertEquals('Registro duplicado', $resultadoDuplicado['response']['mensaje']);

        // 4. Error simulado de BD al insertar (PDOException con rollback)
        $mockPdoError = $this->createMock(PDO::class);
        $mockStmtVal = $this->createMock(PDOStatement::class);
        $mockStmtIns = $this->createMock(PDOStatement::class);

        $mockStmtVal->method('execute')->willReturn(true);
        $mockStmtVal->method('rowCount')->willReturn(0); // No existe previamente

        $pdoException = new PDOException("Error de clave foránea al registrar usuario.");

        $mockPdoError->method('prepare')->willReturnCallback(function (string $sql) use ($mockStmtVal, $mockStmtIns) {
            if (str_contains($sql, 'vw_validar_usuario')) {
                return $mockStmtVal;
            }
            return $mockStmtIns;
        });

        $mockStmtIns->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdoError->expects($this->exactly(2))->method('beginTransaction');
        $mockPdoError->expects($this->once())->method('rollBack');

        $usuarioMockError = $this->createUsuarioMock($mockPdoError);
        $this->poblarDatosValidos($usuarioMockError);

        $resultadoError = $usuarioMockError->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $resultadoError['estado']);
        $this->assertEquals(500, $resultadoError['response']['resultado']);
    }

    /**
     * Caso con datos correctos al registrar:
     * Verifica que si no existe conflicto previo, se inserte el nuevo usuario,
     * se encripte la clave, se confirme la transacción y retorne código 201.
     */
    public function test_registrar_usuario_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtValidar = $this->createMock(PDOStatement::class);
        $mockStmtInsert = $this->createMock(PDOStatement::class);

        // 1. Verificación previa: no existe usuario
        $mockStmtValidar->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtValidar->expects($this->once())->method('rowCount')->willReturn(0);

        // 2. Inserción exitosa
        $mockStmtInsert->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockStmtValidar, $mockStmtInsert) {
            if (str_contains($sql, 'vw_validar_usuario')) {
                return $mockStmtValidar;
            }
            return $mockStmtInsert;
        });

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $usuarioMock = $this->createUsuarioMock($mockPdo);
        $this->poblarDatosValidos($usuarioMock);

        $resultado = $usuarioMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(201, $resultado['response']['resultado']);
        $this->assertEquals('Usuario registrado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 2. MÉTODO: actualizarUsuario (Transacción 'actualizar' / 'modificar' -> UPDATE)
    // =========================================================================

    /**
     * Caso con datos vacíos al modificar:
     * Comprueba que intentar actualizar asignando un username vacío o cédula vacía
     * dispare excepciones de validación en los setters.
     */
    public function test_modificar_usuario_con_datos_vacios_lanza_excepcion(): void
    {
        $usuario = new Usuario();

        // 1. Username vacío
        try {
            $usuario->setUsername('');
            $this->fail("Se esperaba Exception por username vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El usuario debe tener al menos 3 caracteres.", $e->getMessage());
        }

        // 2. Cédula vacía
        try {
            $usuario->setCedula('');
            $this->fail("Se esperaba Exception por cédula vacía.");
        } catch (Exception $e) {
            $this->assertStringContainsString("La cédula debe tener un prefijo válido", $e->getMessage());
        }
    }

    /**
     * Caso con datos incorrectos al modificar:
     * - Username con formato incorrecto.
     * - Simulación de error en base de datos al ejecutar el UPDATE con rollback.
     */
    public function test_modificar_usuario_con_datos_incorrectos_falla(): void
    {
        $usuario = new Usuario();

        // 1. Username con símbolos
        try {
            $usuario->setUsername('admin@system');
            $this->fail("Se esperaba Exception por caracteres inválidos en username.");
        } catch (Exception $e) {
            $this->assertEquals("El usuario solo puede contener letras, sin números ni símbolos.", $e->getMessage());
        }

        // 2. Simulación de fallo en base de datos al actualizar
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $pdoException = new PDOException("Fallo de conexión durante el UPDATE de usuario.");

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdo->expects($this->once())->method('rollBack');

        $usuarioMock = $this->createUsuarioMock($mockPdo);
        $this->poblarDatosValidos($usuarioMock);

        $resultado = $usuarioMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Error interno del servidor', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al modificar:
     * Proporciona datos válidos y actualizados, ejecuta el UPDATE, confirma la transacción
     * con commit y retorna resultado 200.
     */
    public function test_modificar_usuario_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $usuarioMock = $this->createUsuarioMock($mockPdo);
        $this->poblarDatosValidos($usuarioMock);
        $usuarioMock->setUsername('NuevoAdmin');
        $usuarioMock->setIdRol('ROL02');

        $resultado = $usuarioMock->Transaccion(['peticion' => 'actualizar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Usuario actualizado exitosamente', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 3. MÉTODO: toggleEstatus (Transacción 'toggle-estatus' -> UPDATE estatus)
    // =========================================================================

    /**
     * Caso con datos vacíos al alternar estatus / eliminar lógicamente:
     * Intenta operar con cédula vacía directamente en el setter,
     * verificando que se rechace antes de acceder a la base de datos.
     */
    public function test_toggle_estatus_usuario_con_datos_vacios_lanza_excepcion(): void
    {
        $usuario = new Usuario();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("La cédula debe tener un prefijo válido");

        $usuario->setCedula('');
    }

    /**
     * Caso con datos incorrectos / error en BD al alternar estatus:
     * - Formato de cédula incorrecto lanza excepción.
     * - Falla de base de datos durante el UPDATE ejecuta rollback y retorna 500.
     */
    public function test_toggle_estatus_usuario_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // 1. Cédula con formato no válido
        $usuario = new Usuario();
        try {
            $usuario->setCedula('CEDULA-ERRONEA');
            $this->fail("Se esperaba Exception por cédula inválida.");
        } catch (Exception $e) {
            $this->assertStringContainsString("prefijo válido", $e->getMessage());
        }

        // 2. Falla en base de datos al actualizar el estatus
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $pdoException = new PDOException("Error de tabla bloqueada.");

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willThrowException($pdoException);
        $mockPdo->expects($this->once())->method('rollBack');

        $usuarioMock = $this->createUsuarioMock($mockPdo);
        $usuarioMock->setCedula('V-12345678');
        $usuarioMock->setEstatus(0);

        $resultado = $usuarioMock->Transaccion(['peticion' => 'toggle-estatus']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Error interno del servidor', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al alternar estatus / eliminar:
     * Cédula válida, estatus asignado (ej. 0 para inactivar/eliminar),
     * ejecuta el UPDATE satisfactoriamente, hace commit y retorna resultado 200.
     */
    public function test_toggle_estatus_usuario_con_datos_correctos_actualiza_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $usuarioMock = $this->createUsuarioMock($mockPdo);
        $usuarioMock->setCedula('V-12345678');
        $usuarioMock->setEstatus(0); // Inactivar / eliminar lógicamente

        $resultado = $usuarioMock->Transaccion(['peticion' => 'toggle-estatus']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Estado de usuario cambiado exitosamente', $resultado['response']['mensaje']);
    }
}
