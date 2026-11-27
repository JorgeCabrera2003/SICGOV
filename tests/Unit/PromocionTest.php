<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Promocion;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class PromocionTest extends TestCase
{
    /**
     * Crea un mock parcial de Promocion para aislar completamente la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createPromocionMock(PDO&MockObject $mockPdo): Promocion&MockObject
    {
        $mock = $this->getMockBuilder(Promocion::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna valores válidos a una instancia de Promocion.
     */
    private function poblarPromocionValida(Promocion $promocion): void
    {
        $promocion->setIdPromocion('PROM20261025140000001');
        $promocion->setNombre('Combo Fin de Semana Familiar');
        $promocion->setTipoDescuento('PORCENTAJE');
        $promocion->setValorDescuento('15.5');
        $promocion->setDescripcion('Disfruta de 15.5% de descuento en platos principales y bebidas.');
        $promocion->setFechaInicio('2026-11-01');
        $promocion->setFechaFin('2026-11-30');
        $promocion->setHoraInicio('12:00');
        $promocion->setHoraFin('22:00');
        $promocion->setImagen('uploads/promociones/promo1.jpg');
        $promocion->setProductos([
            ['id' => 'PROD20261025140000001', 'cantidad' => 2],
            ['id' => 'PROD20261025140000002', 'cantidad' => 1]
        ]);
    }

    // =========================================================================
    // 1. SETTERS, GETTERS Y SANITIZACIÓN DE DATOS
    // =========================================================================

    /**
     * Prueba que los setters y getters conserven fielmente los datos asignados.
     */
    public function test_setters_y_getters_con_datos_validos(): void
    {
        $promo = new Promocion();
        $this->poblarPromocionValida($promo);

        $this->assertEquals('PROM20261025140000001', $promo->getIdPromocion());
        $this->assertEquals('Combo Fin de Semana Familiar', $promo->getNombre());
        $this->assertEquals('PORCENTAJE', $promo->getTipoDescuento());
        $this->assertEquals(15.5, $promo->getValorDescuento());
        $this->assertEquals('Disfruta de 15.5% de descuento en platos principales y bebidas.', $promo->getDescripcion());
        $this->assertEquals('2026-11-01', $promo->getFechaInicio());
        $this->assertEquals('2026-11-30', $promo->getFechaFin());
        $this->assertEquals('12:00', $promo->getHoraInicio());
        $this->assertEquals('22:00', $promo->getHoraFin());
        $this->assertEquals('uploads/promociones/promo1.jpg', $promo->getImagen());
    }

    /**
     * Prueba la sanitización rigurosa de valor_descuento (comas por puntos, limpieza de símbolos).
     */
    public function test_normaliza_valores_de_descuento_validos_y_rechaza_texto(): void
    {
        $promo = new Promocion();

        // 1. Número con coma decimal: '25,50' -> 25.5
        $promo->setValorDescuento('25,50');
        $this->assertEquals(25.5, $promo->getValorDescuento());

        // 2. Número con símbolo de porcentaje: '20%' -> 20.0
        $promo->setValorDescuento('20%');
        $this->assertEquals(20.0, $promo->getValorDescuento());

        // 3. Número con símbolo de moneda y texto: '$ 150.75 USD' -> 150.75
        $promo->setValorDescuento('$ 150.75 USD');
        $this->assertEquals(150.75, $promo->getValorDescuento());

        $this->expectException(Exception::class);
        $promo->setValorDescuento('gratis');
    }

    public function test_fecha_fin_vacia_se_guarda_como_null(): void
    {
        $promo = new Promocion();
        $promo->setFechaInicio('2026-11-01');
        $promo->setFechaFin('');

        $this->assertNull($promo->getFechaFin());
    }

    public function test_fecha_fin_anterior_a_inicio_es_rechazada(): void
    {
        $promo = new Promocion();
        $promo->setFechaInicio('2026-11-10');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La fecha de fin no puede ser anterior a la fecha de inicio.');
        $promo->setFechaFin('2026-11-09');
    }

    public function test_promocion_rechaza_porcentaje_mayor_a_cien(): void
    {
        $promo = new Promocion();
        $promo->setNombre('Oferta');
        $promo->setTipoDescuento('PORCENTAJE');
        $promo->setValorDescuento('100,01');
        $promo->setFechaInicio('2026-11-01');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El porcentaje de descuento no puede superar el 100%.');
        $promo->Transaccion(['peticion' => 'registrar']);
    }

    public function test_promocion_rechaza_descuento_cero_y_valores_no_numericos(): void
    {
        $promo = new Promocion();
        $promo->setTipoDescuento('MONTO_FIJO');
        $promo->setValorDescuento('0');
        $promo->setFechaInicio('2026-11-01');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El valor del descuento debe ser mayor que cero.');
        $promo->Transaccion(['peticion' => 'registrar']);
    }

    public function test_promocion_rechaza_descuento_negativo(): void
    {
        $promo = new Promocion();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El valor del descuento debe ser un número válido.');
        $promo->setValorDescuento('-1');
    }

    public function test_promocion_rechaza_tipo_de_descuento_invalido(): void
    {
        $promo = new Promocion();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El tipo de descuento no es válido.');
        $promo->setTipoDescuento('GRATIS');
    }

    public function test_fecha_de_inicio_inexistente_es_rechazada(): void
    {
        $promo = new Promocion();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La fecha de inicio no es válida.');
        $promo->setFechaInicio('2026-02-30');
    }

    /**
     * Prueba el manejo seguro de la imagen (limpieza de espacios en blanco y null si está vacía).
     */
    public function test_set_imagen_limpia_espacios_y_asigna_null_si_vacio(): void
    {
        $promo = new Promocion();

        $promo->setImagen('   ');
        $this->assertNull($promo->getImagen());

        $promo->setImagen(null);
        $this->assertNull($promo->getImagen());

        $promo->setImagen('  uploads/img.png  ');
        $this->assertEquals('uploads/img.png', $promo->getImagen());
    }

    /**
     * Prueba el dispatcher Transaccion con petición no válida o vacía.
     */
    public function test_transaccion_con_peticion_no_valida_retorna_400(): void
    {
        $promo = new Promocion();

        // Petición no soportada
        $resultado = $promo->Transaccion(['peticion' => 'accion_inexistente']);
        $this->assertEquals(400, $resultado['response']['resultado']);
        $this->assertEquals(400, $resultado['HTTP_STATUS']['codigo']);

        // Sin parámetro peticion
        $resultado2 = $promo->Transaccion([]);
        $this->assertEquals(400, $resultado2['response']['resultado']);
    }

    // =========================================================================
    // 2. CONSULTAR PROMOCIÓN
    // =========================================================================

    /**
     * Prueba la consulta exitosa de promociones con productos asociados.
     */
    public function test_consultar_promocion_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockStm->expects($this->once())->method('execute')->willReturn(true);
        $mockStm->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStm->expects($this->once())->method('fetchAll')->willReturn([
            [
                'id_promocion' => 'PROM001',
                'nombre' => '2x1 Hamburguesas',
                'tipo_descuento' => 'PORCENTAJE',
                'valor_descuento' => 50,
                'producto_nombre' => 'Hamburguesa Clásica',
                'producto_ids' => 'PROD001',
                'producto_list' => 'PROD001:::Hamburguesa Clásica:::2:::10.00'
            ]
        ]);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $resultado = $promo->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertCount(1, $resultado['response']['datos']);
        $this->assertEquals('2x1 Hamburguesas', $resultado['response']['datos'][0]['nombre']);
    }

    /**
     * Prueba que consultar promocion capture PDOException, ejecute rollback y retorne 500.
     */
    public function test_consultar_promocion_con_error_bd_hace_rollback_y_retorna_500(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willThrowException(new PDOException('Error de conexión a BD'));
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $resultado = $promo->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals(500, $resultado['HTTP_STATUS']['codigo']);
    }

    // =========================================================================
    // 3. REGISTRAR PROMOCIÓN
    // =========================================================================

    /**
     * Prueba el registro exitoso de una promoción incluyendo su planificador de productos.
     */
    public function test_registrar_promocion_exitoso_con_productos(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmInsert = $this->createMock(PDOStatement::class);
        $mockStmPlan = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);

        // Se preparan 2 consultas: insert en promocion e insert en planificador_promocion
        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmInsert, $mockStmPlan);

        $mockStmInsert->expects($this->once())->method('execute')->willReturn(true);

        // Se insertarán 2 productos según la configuración de productos
        $mockStmPlan->expects($this->exactly(2))->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $this->poblarPromocionValida($promo);
        $promo->setProductos([
            ['id' => 'PROD001', 'cantidad' => 1],
            ['id' => 'PROD002', 'cantidad' => 1]
        ]);
        $promo->setImagen(null); // Sin imagen para no invocar Database::getConnection('security')

        $resultado = $promo->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(201, $resultado['response']['resultado']);
        $this->assertEquals('Promoción registrada exitosamente', $resultado['response']['mensaje']);
    }

    /**
     * Prueba que ante un error en la base de datos al registrar, se realice rollback y retorne 500.
     */
    public function test_registrar_promocion_falla_por_pdoexception(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willThrowException(new PDOException('Fallo al insertar'));
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $this->poblarPromocionValida($promo);

        $resultado = $promo->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
        $this->assertEquals(500, $resultado['HTTP_STATUS']['codigo']);
    }

    // =========================================================================
    // 4. MODIFICAR PROMOCIÓN
    // =========================================================================

    /**
     * Prueba la actualización exitosa de una promoción reemplazando productos asociados.
     */
    public function test_modificar_promocion_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmUpdate = $this->createMock(PDOStatement::class);
        $mockStmDeletePlan = $this->createMock(PDOStatement::class);
        $mockStmInsertPlan = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);

        $mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmUpdate, $mockStmDeletePlan, $mockStmInsertPlan);

        $mockStmUpdate->expects($this->once())->method('execute')->willReturn(true);
        $mockStmDeletePlan->expects($this->once())->method('execute')->willReturn(true);
        $mockStmInsertPlan->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $this->poblarPromocionValida($promo);
        $promo->setProductos([
            ['id' => 'PROD_NUEVO', 'cantidad' => 1]
        ]);
        $promo->setImagen(null);

        $resultado = $promo->Transaccion(['peticion' => 'modificar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Promoción actualizada exitosamente', $resultado['response']['mensaje']);
    }

    public function test_modificar_promocion_con_error_bd_hace_rollback_y_retorna_500(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')
            ->willThrowException(new PDOException('Fallo al actualizar'));
        $mockPdo->expects($this->once())->method('rollBack')->willReturn(true);
        $mockPdo->expects($this->never())->method('commit');

        $promo = $this->createPromocionMock($mockPdo);
        $this->poblarPromocionValida($promo);
        $promo->setImagen(null);

        $resultado = $promo->Transaccion(['peticion' => 'modificar']);

        $this->assertSame(-1, $resultado['estado']);
        $this->assertSame(500, $resultado['response']['resultado']);
        $this->assertSame(500, $resultado['HTTP_STATUS']['codigo']);
    }

    // =========================================================================
    // 5. VALIDAR Y ELIMINAR PROMOCIÓN
    // =========================================================================

    /**
     * Prueba la validación de existencia: cuando la promoción sí existe en la BD.
     */
    public function test_validar_promocion_existente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockStm->expects($this->once())->method('execute')->willReturn(true);
        $mockStm->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStm->expects($this->once())->method('fetch')->willReturn([
            'id_promocion' => 'PROM001',
            'nombre' => '2x1 Viernes',
            'tipo_descuento' => 'PORCENTAJE',
            'valor_descuento' => 50
        ]);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $promo->setIdPromocion('PROM001');

        $resultado = $promo->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(1, $resultado['bool']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('2x1 Viernes', $resultado['response']['registro']['nombre']);
    }

    /**
     * Prueba la validación de existencia: cuando la promoción no existe.
     */
    public function test_validar_promocion_inexistente(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockStm->expects($this->once())->method('execute')->willReturn(true);
        $mockStm->expects($this->once())->method('rowCount')->willReturn(0);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $promo->setIdPromocion('PROM_NO_EXISTE');

        $resultado = $promo->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(0, $resultado['bool']);
        $this->assertEquals(200, $resultado['response']['resultado']);
    }

    /**
     * Prueba la eliminación de una promoción que no existe: debe retornar 404.
     */
    public function test_eliminar_promocion_inexistente_retorna_404(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStm = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStm);
        $mockStm->expects($this->once())->method('execute')->willReturn(true);
        $mockStm->expects($this->once())->method('rowCount')->willReturn(0);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $promo->setIdPromocion('PROM_INEXISTENTE');

        $resultado = $promo->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(404, $resultado['response']['resultado']);
        $this->assertEquals('Registro no encontrado', $resultado['response']['mensaje']);
    }

    /**
     * Prueba la eliminación exitosa de una promoción existente.
     */
    public function test_eliminar_promocion_existente_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmSelect = $this->createMock(PDOStatement::class);
        $mockStmDelete = $this->createMock(PDOStatement::class);

        // 2 transacciones: 1 en ValidarPromocion y 1 en EliminarPromocion
        $mockPdo->expects($this->exactly(2))->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('commit')->willReturn(true);

        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmSelect, $mockStmDelete);

        // Select de validación
        $mockStmSelect->expects($this->once())->method('execute')->willReturn(true);
        $mockStmSelect->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStmSelect->expects($this->once())->method('fetch')->willReturn(['id_promocion' => 'PROM001']);

        // Delete de eliminación
        $mockStmDelete->expects($this->once())->method('execute')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $promo->setIdPromocion('PROM001');

        $resultado = $promo->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Promoción eliminada exitosamente', $resultado['response']['mensaje']);
    }
}

