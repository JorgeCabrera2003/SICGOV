<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\System\Promocion;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class PromocionTest extends TestCase
{
    private function createPromocionMock(PDO&MockObject $mockPdo): Promocion&MockObject
    {
        $mock = $this->getMockBuilder(Promocion::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

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
            ['id' => 'PROD20261025140000002', 'cantidad' => 1],
        ]);
    }

    #[Test]
    public function testSettersYGettersConDatosValidos(): void
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

    #[Test]
    public function testNormalizaValoresDeDescuentoValidosYRechazaTexto(): void
    {
        $promo = new Promocion();

        $promo->setValorDescuento('25,50');
        $this->assertEquals(25.5, $promo->getValorDescuento());

        $promo->setValorDescuento('20%');
        $this->assertEquals(20.0, $promo->getValorDescuento());

        $promo->setValorDescuento('$ 150.75 USD');
        $this->assertEquals(150.75, $promo->getValorDescuento());

        $this->expectException(Exception::class);
        $promo->setValorDescuento('gratis');
    }

    #[Test]
    public function testFechaFinVaciaSeGuardaComoNull(): void
    {
        $promo = new Promocion();
        $promo->setFechaInicio('2026-11-01');
        $promo->setFechaFin('');

        $this->assertNull($promo->getFechaFin());
    }

    #[Test]
    public function testFechaFinAnteriorAInicioEsRechazada(): void
    {
        $promo = new Promocion();
        $promo->setFechaInicio('2026-11-10');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La fecha de fin no puede ser anterior a la fecha de inicio.');
        $promo->setFechaFin('2026-11-09');
    }

    #[Test]
    public function testPromocionRechazaPorcentajeMayorACien(): void
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

    #[Test]
    public function testPromocionRechazaDescuentoCeroYValoresNoNumericos(): void
    {
        $promo = new Promocion();
        $promo->setTipoDescuento('MONTO_FIJO');
        $promo->setValorDescuento('0');
        $promo->setFechaInicio('2026-11-01');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El valor del descuento debe ser mayor que cero.');
        $promo->Transaccion(['peticion' => 'registrar']);
    }

    #[Test]
    public function testPromocionRechazaDescuentoNegativo(): void
    {
        $promo = new Promocion();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El valor del descuento debe ser un número válido.');
        $promo->setValorDescuento('-1');
    }

    #[Test]
    public function testPromocionRechazaTipoDeDescuentoInvalido(): void
    {
        $promo = new Promocion();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El tipo de descuento no es válido.');
        $promo->setTipoDescuento('GRATIS');
    }

    #[Test]
    public function testFechaDeInicioInexistenteEsRechazada(): void
    {
        $promo = new Promocion();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('La fecha de inicio no es válida.');
        $promo->setFechaInicio('2026-02-30');
    }

    #[Test]
    public function testSetImagenLimpiaEspaciosYAsignaNullSiVacio(): void
    {
        $promo = new Promocion();

        $promo->setImagen('   ');
        $this->assertNull($promo->getImagen());

        $promo->setImagen(null);
        $this->assertNull($promo->getImagen());

        $promo->setImagen('  uploads/img.png  ');
        $this->assertEquals('uploads/img.png', $promo->getImagen());
    }

    #[Test]
    public function testTransaccionConPeticionNoValidaRetorna400(): void
    {
        $promo = new Promocion();

        $resultado = $promo->Transaccion(['peticion' => 'accion_inexistente']);
        $this->assertEquals(400, $resultado['response']['resultado']);
        $this->assertEquals(400, $resultado['HTTP_STATUS']['codigo']);

        $resultado = $promo->Transaccion([]);
        $this->assertEquals(400, $resultado['response']['resultado']);
    }

    #[Test]
    public function testConsultarPromocionExitoso(): void
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
                'producto_list' => 'PROD001:::Hamburguesa Clásica:::2:::10.00',
            ],
        ]);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $resultado = $promo->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertCount(1, $resultado['response']['datos']);
        $this->assertEquals('2x1 Hamburguesas', $resultado['response']['datos'][0]['nombre']);
    }

    #[Test]
    public function testConsultarPromocionConErrorBdHaceRollbackYRetorna500(): void
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

    #[Test]
    public function testRegistrarPromocionExitosoConProductos(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmInsert = $this->createMock(PDOStatement::class);
        $mockStmPlan = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmInsert, $mockStmPlan);
        $mockStmInsert->expects($this->once())->method('execute')->willReturn(true);
        $mockStmPlan->expects($this->exactly(2))->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $this->poblarPromocionValida($promo);
        $promo->setProductos([
            ['id' => 'PROD001', 'cantidad' => 1],
            ['id' => 'PROD002', 'cantidad' => 1],
        ]);
        $promo->setImagen(null);

        $resultado = $promo->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(201, $resultado['response']['resultado']);
        $this->assertEquals('Promoción registrada exitosamente', $resultado['response']['mensaje']);
    }

    #[Test]
    public function testRegistrarPromocionFallaPorPdoException(): void
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

    #[Test]
    public function testModificarPromocionExitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmUpdate = $this->createMock(PDOStatement::class);
        $mockStmDeletePlan = $this->createMock(PDOStatement::class);
        $mockStmInsertPlan = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(3))->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmUpdate, $mockStmDeletePlan, $mockStmInsertPlan);
        $mockStmUpdate->expects($this->once())->method('execute')->willReturn(true);
        $mockStmDeletePlan->expects($this->once())->method('execute')->willReturn(true);
        $mockStmInsertPlan->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $this->poblarPromocionValida($promo);
        $promo->setProductos([['id' => 'PROD_NUEVO', 'cantidad' => 1]]);
        $promo->setImagen(null);

        $resultado = $promo->Transaccion(['peticion' => 'modificar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Promoción actualizada exitosamente', $resultado['response']['mensaje']);
    }

    #[Test]
    public function testModificarPromocionConErrorBdHaceRollbackYRetorna500(): void
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

    #[Test]
    public function testValidarPromocionExistente(): void
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
            'valor_descuento' => 50,
        ]);
        $mockPdo->expects($this->once())->method('commit')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $promo->setIdPromocion('PROM001');

        $resultado = $promo->Transaccion(['peticion' => 'validar']);

        $this->assertEquals(1, $resultado['bool']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('2x1 Viernes', $resultado['response']['registro']['nombre']);
    }

    #[Test]
    public function testValidarPromocionInexistente(): void
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

    #[Test]
    public function testEliminarPromocionInexistenteRetorna404(): void
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

    #[Test]
    public function testEliminarPromocionExistenteExitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmSelect = $this->createMock(PDOStatement::class);
        $mockStmDelete = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->exactly(2))->method('beginTransaction')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('commit')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmSelect, $mockStmDelete);

        $mockStmSelect->expects($this->once())->method('execute')->willReturn(true);
        $mockStmSelect->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStmSelect->expects($this->once())->method('fetch')->willReturn(['id_promocion' => 'PROM001']);
        $mockStmDelete->expects($this->once())->method('execute')->willReturn(true);

        $promo = $this->createPromocionMock($mockPdo);
        $promo->setIdPromocion('PROM001');

        $resultado = $promo->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Promoción eliminada exitosamente', $resultado['response']['mensaje']);
    }
}
