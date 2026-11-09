<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Areas;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class AreasTest extends TestCase
{
    /**
     * Crea un mock parcial de Areas para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createAreasMock(PDO&MockObject $mockPdo): Areas&MockObject
    {
        $mock = $this->getMockBuilder(Areas::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna datos válidos a una instancia de Areas.
     */
    private function poblarAreaValida(Areas $area): void
    {
        $area->setIdArea('AREA20261025140000001');
        $area->setNombre('Terraza VIP');
        $area->setEstatus(1);
    }

    // =========================================================================
    // 1. SETTERS, GETTERS Y VALIDACIONES DE FORMATO
    // =========================================================================

    /**
     * Prueba que los setters rechacen valores inválidos o fuera de rango.
     */
    public function test_setters_con_datos_invalidos_lanzan_excepcion(): void
    {
        $area = new Areas();

        // 1. ID con formato inválido
        try {
            $area->setIdArea('id_invalido!');
            $this->fail("Se esperaba Exception por ID inválido.");
        } catch (Exception $e) {
            $this->assertEquals("El ID del área no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Nombre vacío
        try {
            $area->setNombre('');
            $this->fail("Se esperaba Exception por nombre vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El nombre del área debe tener entre 3 y 60 caracteres.", $e->getMessage());
        }

        // 3. Nombre menor a 3 caracteres
        try {
            $area->setNombre('AB');
            $this->fail("Se esperaba Exception por nombre menor a 3 caracteres.");
        } catch (Exception $e) {
            $this->assertEquals("El nombre del área debe tener entre 3 y 60 caracteres.", $e->getMessage());
        }

        // 4. Nombre mayor a 60 caracteres
        try {
            $area->setNombre(str_repeat('A', 61));
            $this->fail("Se esperaba Exception por nombre mayor a 60 caracteres.");
        } catch (Exception $e) {
            $this->assertEquals("El nombre del área debe tener entre 3 y 60 caracteres.", $e->getMessage());
        }

        // 5. Estatus distinto de 0 o 1
        try {
            $area->setEstatus(2);
            $this->fail("Se esperaba Exception por estatus 2.");
        } catch (Exception $e) {
            $this->assertEquals("El estatus debe ser 0 (inactivo) o 1 (activo).", $e->getMessage());
        }
    }

    /**
     * Prueba que los setters y getters conserven fielmente los datos asignados.
     */
    public function test_setters_y_getters_con_datos_validos(): void
    {
        $area = new Areas();
        $this->poblarAreaValida($area);

        $this->assertEquals('AREA20261025140000001', $area->getIdArea());
        $this->assertEquals('Terraza VIP', $area->getNombre());
        $this->assertEquals(1, $area->getEstatus());
    }

    // =========================================================================
    // 2. DISPATCHER DE TRANSACCIÓN
    // =========================================================================

    /**
     * Prueba que una petición no válida en Transaccion retorne error 400.
     */
    public function test_transaccion_con_peticion_invalida_retorna_400(): void
    {
        $area = new Areas();

        $res = $area->Transaccion(['peticion' => 'operacion_inexistente']);
        $this->assertEquals(400, $res['response']['resultado']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
    }

    // =========================================================================
    // 3. MÉTODO: ConsultarAreas (Transacción 'consultar')
    // =========================================================================

    /**
     * Prueba la consulta general de áreas de forma exitosa.
     */
    public function test_consultar_areas_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);

        $mockStm->expects($this->once())->method('execute')->willReturn(true);
        $mockStm->expects($this->once())->method('rowCount')->willReturn(2);
        $mockStm->expects($this->once())->method('fetchAll')->willReturn([
            ['id_area' => 'AREA001', 'nombre' => 'Interior', 'estatus' => 1],
            ['id_area' => 'AREA002', 'nombre' => 'Terraza', 'estatus' => 1],
        ]);

        $area = $this->createAreasMock($mockPdo);
        $res = $area->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertCount(2, $res['response']['datos']);
    }

    /**
     * Prueba la respuesta en caso de error interno de base de datos al consultar.
     */
    public function test_consultar_areas_con_error_bd_retorna_500(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willThrowException(new PDOException("Error de conexión"));
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);

        $area = $this->createAreasMock($mockPdo);
        $res = $area->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(-1, $res['estado']);
        $this->assertEquals(500, $res['response']['resultado']);
    }

    // =========================================================================
    // 4. MÉTODO: RegistrarArea (Transacción 'registrar')
    // =========================================================================

    /**
     * Prueba que el registro exitoso inserte el área y retorne código 200.
     */
    public function test_registrar_area_con_datos_correctos_registra_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $mockStm->expects($this->once())->method('execute')->willReturn(true);

        $area = $this->createAreasMock($mockPdo);
        $this->poblarAreaValida($area);

        $res = $area->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertEquals("Área registrada con éxito", $res['response']['mensaje']);
    }

    /**
     * Prueba que al intentar registrar un ID duplicado retorne código 409 de conflicto.
     */
    public function test_registrar_area_con_id_duplicado_retorna_409(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);

        $pdoException = new PDOException("Duplicate entry 'AREA20261025140000001' for key 'PRIMARY'", 23000);
        $mockPdo->expects($this->once())->method('prepare')->willThrowException($pdoException);

        $area = $this->createAreasMock($mockPdo);
        $this->poblarAreaValida($area);

        $res = $area->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(0, $res['estado']);
        $this->assertEquals(409, $res['response']['resultado']);
        $this->assertStringContainsString("El ID del área ya existe", $res['response']['mensaje']);
    }

    /**
     * Prueba que ante un error inesperado de base de datos al registrar retorne 500.
     */
    public function test_registrar_area_con_error_bd_retorna_500(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);

        $mockPdo->expects($this->once())->method('prepare')->willThrowException(new PDOException("Disk full", 500));

        $area = $this->createAreasMock($mockPdo);
        $this->poblarAreaValida($area);

        $res = $area->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $res['estado']);
        $this->assertEquals(500, $res['response']['resultado']);
    }

    // =========================================================================
    // 5. MÉTODO: ModificarArea (Transacción 'actualizar' o 'modificar')
    // =========================================================================

    /**
     * Prueba la actualización exitosa de un área.
     */
    public function test_modificar_area_con_datos_correctos_modifica_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $mockStm->expects($this->once())->method('execute')->willReturn(true);

        $area = $this->createAreasMock($mockPdo);
        $this->poblarAreaValida($area);
        $area->setNombre('Terraza VIP Renovada');

        $res = $area->Transaccion(['peticion' => 'modificar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertEquals("Área actualizada con éxito", $res['response']['mensaje']);
    }

    /**
     * Prueba error de base de datos al modificar un área.
     */
    public function test_modificar_area_con_error_bd_retorna_500(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);

        $mockPdo->expects($this->once())->method('prepare')->willThrowException(new PDOException("Error al actualizar"));

        $area = $this->createAreasMock($mockPdo);
        $this->poblarAreaValida($area);

        $res = $area->Transaccion(['peticion' => 'modificar']);

        $this->assertEquals(-1, $res['estado']);
        $this->assertEquals(500, $res['response']['resultado']);
    }

    // =========================================================================
    // 6. MÉTODO: EliminarArea (Transacción 'eliminar' -> borrado lógico)
    // =========================================================================

    /**
     * Prueba la eliminación lógica exitosa (cambio de estatus a 0).
     */
    public function test_eliminar_area_con_datos_correctos_elimina_logicamente_exitosamente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $mockStm->expects($this->once())->method('execute')->willReturn(true);

        $area = $this->createAreasMock($mockPdo);
        $area->setIdArea('AREA20261025140000001');

        $res = $area->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $res['estado']);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertEquals("Área eliminada con éxito", $res['response']['mensaje']);
    }

    /**
     * Prueba error de base de datos al eliminar un área.
     */
    public function test_eliminar_area_con_error_bd_retorna_500(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);

        $mockPdo->expects($this->once())->method('prepare')->willThrowException(new PDOException("Error al eliminar"));

        $area = $this->createAreasMock($mockPdo);
        $area->setIdArea('AREA20261025140000001');

        $res = $area->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $res['estado']);
        $this->assertEquals(500, $res['response']['resultado']);
    }
}

