<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\UnidadMedida;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

final class UnidadMedidaTest extends TestCase
{

    private $objUnidadMedida;

    protected function setUp(): void
    {
        $this->objUnidadMedida = new UnidadMedida();
    }

    /**
     * Crea un mock parcial de UnidadMedida para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createUnidadMedidaMock(PDO&MockObject $mockPdo): UnidadMedida&MockObject
    {
        $mock = $this->getMockBuilder(UnidadMedida::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    private function createMassMock(PDO&MockObject $mockPdo): UnidadMedida&MockObject
    {
        $mock = $this->getMockBuilder(UnidadMedida::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna un conjunto de datos válidos a un objeto UnidadMedida.
     */
    private function poblarDatosValidos(UnidadMedida $unidadMedida): void
    {
        $unidadMedida->setId('MEDIAKG23220260519200547232');
    }

    // =========================================================================
    // 1. Validaciones
    // =========================================================================
    public function test_validar_campos_unidad_de_medida_con_datos_vacios_lanza_excepcion(): void
    {
        // 1. ID vacía
        try {
            $this->objUnidadMedida->setId('');
            $this->fail("Se esperaba Exception por id vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID no cumple con el formato permitido.", $e->getMessage());
        }

    }


    // =========================================================================
    // 3. MÉTODO: FiltrarUnidadMedida (Transacción 'filtrar' -> SELECT * FROM unidad_medida WHERE tipo = :tipo)
    // =========================================================================

    /**
     * Caso con datos vacíos al filtrar:
     * Intenta filtrar pasando id vacío directamente en el setter,
     * verificando que se rechace antes de acceder a la base de datos.
     */
    public function test_filtrar_unidad_de_medida_con_datos_vacios_lanza_excepcion(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El ID no cumple con el formato permitido.");

        $this->objUnidadMedida->setId('');
    }

    /**
     * Caso con datos incorrectos / error en base de datos al filtrar:
     * - ID con formato inválido lanza excepción.
     * - Falla de base de datos (PDOException) durante el SELECT retorna estado -1 y código 500.
     */
    public function test_filtrar_unidad_de_medida_con_datos_incorrectos_o_error_bd_falla(): void
    {
        // 1. Cédula con formato no válido
        try {
            $this->objUnidadMedida->setId('CATEGIN0112026051920054723#');
            $this->fail("El ID no cumple con el formato permitido.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El ID no cumple con el formato permitido.", $e->getMessage());
        }

        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        // 1. Regla de negocio: la unidad de medida ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckUnidadMedida = $this->createMock(PDOStatement::class);
        $mockFilterUnidadMedida = $this->createMock(PDOStatement::class);

        // 1. Verificación de unidad de medida: no existe (rowCount = 0)
        $mockCheckUnidadMedida->expects($this->once())->method('execute')->willReturn(true);
        $mockCheckUnidadMedida->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockFilterUnidadMedida->expects($this->once())->method('execute')->willThrowException(new PDOException("Error de conexión con la base de datos."));

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckUnidadMedida, $mockFilterUnidadMedida) {
            if (str_contains($sql, 'SELECT * FROM unidad_medida WHERE id_unidad = :id_unidad')) {
                return $mockCheckUnidadMedida;
            }if (str_contains($sql, 'SELECT * FROM unidad_medida WHERE tipo = :tipo ORDER BY tipo ASC')) {
                return $mockFilterUnidadMedida;
            }
        });

        $unidadMedidaMock = $this->createUnidadMedidaMock($mockPdo);
        $unidadMedidaMock->setId('MEDIAKG23220260519200547232');

        $resultado = $unidadMedidaMock->Transaccion(['peticion' => 'filtrar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals('Ups, intente de nuevo más tarde', $resultado['response']['mensaje']);
    }

    /**
     * Caso con datos correctos al filtrar:
     * Proporciona id válido, ejecuta el SELECT y retorna resultado 200.
     */
    public function test_flitrar_unidad_de_medida_con_datos_correctos_exitosamente(): void
    {
        // 1. Regla de negocio: la unidad de medida ya existe en la base de datos
        $mockPdo = $this->createMock(PDO::class);
        $mockCheckUnidadMedida = $this->createMock(PDOStatement::class);
        $mockFilterUnidadMedida = $this->createMock(PDOStatement::class);

        // 1. Verificación unidad de medida: no existe (rowCount = 0)
        $mockCheckUnidadMedida->expects($this->once())->method('execute')->willReturn(false);
        $mockCheckUnidadMedida->expects($this->once())->method('rowCount')->willReturn(1);

        // 2. Inserciones exitosas
        $mockFilterUnidadMedida->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->method('prepare')->willReturnCallback(function (string $sql) use ($mockCheckUnidadMedida, $mockFilterUnidadMedida) {
            if (str_contains($sql, 'SELECT * FROM unidad_medida WHERE id_unidad = :id_unidad')) {
                return $mockCheckUnidadMedida;
            }
            if (str_contains($sql, 'SELECT * FROM unidad_medida WHERE tipo = :tipo ORDER BY tipo ASC')) {
                return $mockFilterUnidadMedida;
            }
        });
        $mockPdo->expects($this->exactly(2))->method('commit');
        $mockPdo->expects($this->never())->method('rollBack');

        $unidadMedidaMock = $this->createUnidadMedidaMock($mockPdo);
        $this->poblarDatosValidos($unidadMedidaMock);

        $resultado = $unidadMedidaMock->Transaccion(['peticion' => 'filtrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
    }

    /**
     * Caso para probar método CalcularValor() sumando stock con la misma unidad de kilos
     */
    public function test_calcular_stock_de_masa_con_misma_unidad(): void
    {
        $resultado = null;
        // 1. Sumar Valores según las unidedades de medida 
        $resultado = $this->objUnidadMedida->CalcularValor(10, 10, "kg", "kg", "sumar");

        // 2. Verificación resultado 10Kg + 10Kg = 20Kg
        $this->assertEquals(20, $resultado);
    }


    /**
     * Caso para probar método CalcularValor() sumando stock con diferentes unidades
     * de masa, usando kilos y gramos
     */
    public function test_calcular_stock_de_masa_con_diferente_unidad(): void
    {
        $resultado = null;
        // 1. Sumar Valores según las unidedades de medida 
        $resultado = $this->objUnidadMedida->CalcularValor(0.5, 13000, "kg", "g", "sumar");

        // 2. Verificación resultado 10Kg + 10Kg = 20Kg
        $this->assertEquals(13500, $resultado);
    }

    public function test_calcular_stock_de_volumen_con_misma_unidad(): void
    {
        $resultado = null;
        // 1. Sumar Valores según las unidedades de medida 
        $resultado = $this->objUnidadMedida->CalcularValor(5, 3, "L", "L", "sumar");

        // 2. Verificación resultado 5L + 3L = 8L
        $this->assertEquals(8, $resultado);
    }

    public function test_calcular_stock_de_volumen_con_diferente_unidad(): void
    {
        $resultado = null;
        // 1. Sumar Valores según las unidedades de medida 
        $resultado = $this->objUnidadMedida->CalcularValor(400, 2.6, "ml", "L", "sumar");

        // 2. Verificación resultado 400ml + 2.6L = 3L
        $this->assertEquals(3, $resultado);
    }

    protected function tearDown(): void
    {
        $this->objUnidadMedida = NULL;
    }
}