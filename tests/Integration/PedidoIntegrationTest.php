<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Pedido;
use App\Models\System\PedidoPublico;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Pedidos (POS y Web)
 * Evalúa el ciclo de vida de los pedidos, persistencia transaccional (pedido, detalle_pedido, pago),
 * vulnerabilidad crítica de manipulación de precios ($0.01) y omisión de inventario en pedidos web.
 */
class PedidoIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private PDO $dbSecurity;
    private Pedido $pedidoModel;
    private PedidoPublico $pedidoWebModel;
    private string $cedulaClienteTest = 'V-55443322';
    private string $idPedidoTest = 'PEDTESTPOS001';

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // Simular sesión de Administrador
        $_SESSION['user'] = [
            'cedula' => 'V-00000000',
            'username' => 'admin_test',
            'id_rol' => 'ADMIN00120251001',
            'rol' => 'Administrador',
            'estatus_clave' => 1
        ];

        $this->dbBusiness = Database::getConnection('business');
        $this->dbSecurity = Database::getConnection('security');
        $this->pedidoModel = new Pedido();
        $this->pedidoWebModel = new PedidoPublico();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM detalle_pedido WHERE id_pedido = '{$this->idPedidoTest}' OR id_pedido LIKE 'PEDTEST%' OR id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = '{$this->cedulaClienteTest}')");
        $this->dbBusiness->exec("DELETE FROM pago WHERE id_pedido = '{$this->idPedidoTest}' OR id_pedido LIKE 'PEDTEST%' OR id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = '{$this->cedulaClienteTest}')");
        $this->dbBusiness->exec("DELETE FROM pedido WHERE id_pedido = '{$this->idPedidoTest}' OR id_pedido LIKE 'PEDTEST%' OR cedula_cliente = '{$this->cedulaClienteTest}'");
        $this->dbBusiness->exec("DELETE FROM cliente WHERE cedula = '{$this->cedulaClienteTest}'");
        $this->dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaClienteTest}'");
    }

    #[Test]
    public function testListarPedidosPOSyAdminEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->pedidoModel->Transaccion(['peticion' => 'listar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertLessThan(250, $duracionMs, "Listar pedidos demoró {$duracionMs} ms");
    }

    #[Test]
    public function testListarMesasDisponiblesParaPOS(): void
    {
        $mesas = $this->pedidoModel->Transaccion(['peticion' => 'listar_mesas_disponibles']);
        $this->assertIsArray($mesas);
    }

    #[Test]
    public function testCicloVidaCrearPedidoPOSYActualizarEstado(): void
    {
        // 1. Obtener producto y método de pago
        $idProd = $this->dbBusiness->query("SELECT id_producto FROM producto WHERE estatus = 1 LIMIT 1")->fetchColumn();
        $idMetodo = $this->dbBusiness->query("SELECT id_metodo_pago FROM metodo_pago WHERE estatus = 1 LIMIT 1")->fetchColumn();
        $this->assertNotEmpty($idProd);
        $this->assertNotEmpty($idMetodo);

        // 2. Preparar payload de pedido POS
        $datosCliente = [
            'cedula' => $this->cedulaClienteTest,
            'nombre' => 'Cliente Mostrador Test',
            'telefono' => '0414-9988776',
            'direccion' => 'Calle Comercio #5',
            'tipo_pedido' => 'LLEVAR',
            'id_mesa' => null,
            'observacion' => 'Pedido POS Prueba Automatizada'
        ];

        $carrito = [
            'total' => 20.00,
            'items' => [
                [
                    'id_producto' => $idProd,
                    'nombre' => 'Producto POS Test',
                    'precio_unitario' => 20.00,
                    'cantidad' => 1,
                    'indicacion' => 'Sin sal'
                ]
            ]
        ];

        $datosPago = [
            'id_metodo_pago' => $idMetodo,
            'referencia' => 'POS-REF-998877'
        ];

        // 3. Crear Pedido POS
        $res = $this->pedidoModel->Transaccion([
            'peticion' => 'crear_pos',
            'datosCliente' => $datosCliente,
            'carrito' => $carrito,
            'datosPago' => $datosPago
        ]);

        $this->assertTrue($res['success']);
        $idPedidoGenerado = $res['id_pedido'];
        $this->assertNotEmpty($idPedidoGenerado);

        // Verificar en BD
        $stmPed = $this->dbBusiness->prepare("SELECT total, estado FROM pedido WHERE id_pedido = :id");
        $stmPed->execute([':id' => $idPedidoGenerado]);
        $rowPed = $stmPed->fetch(PDO::FETCH_ASSOC);
        $this->assertContains($rowPed['estado'], ['PENDIENTE', 'LISTO']);

        // 4. Cambiar Estado a ENTREGADO
        $resEstado = $this->pedidoModel->Transaccion([
            'peticion' => 'cambiar_estado',
            'id_pedido' => $idPedidoGenerado,
            'estado' => 'ENTREGADO'
        ]);
        $this->assertTrue($resEstado['success']);

        $stmPed->execute([':id' => $idPedidoGenerado]);
        $this->assertEquals('ENTREGADO', $stmPed->fetch(PDO::FETCH_ASSOC)['estado']);

        // Limpieza del pedido generado
        $this->dbBusiness->exec("DELETE FROM detalle_pedido WHERE id_pedido = '{$idPedidoGenerado}'");
        $this->dbBusiness->exec("DELETE FROM pago WHERE id_pedido = '{$idPedidoGenerado}'");
        $this->dbBusiness->exec("DELETE FROM pedido WHERE id_pedido = '{$idPedidoGenerado}'");
    }

    #[Test]
    public function testVulnerabilidadManipulacionDePreciosEnPedidoWeb(): void
    {
        // Vulnerabilidad Crítica PED-VULN-01:
        // PedidoPublico.php:60 asigna $totalPedido = $carrito['total'] confiando ciegamente
        // en el JSON enviado desde el cliente web. Un atacante puede alterar el precio a $0.01.
        $idProd = $this->dbBusiness->query("SELECT id_producto FROM producto WHERE estatus = 1 LIMIT 1")->fetchColumn();
        $idMetodo = $this->dbBusiness->query("SELECT id_metodo_pago FROM metodo_pago WHERE estatus = 1 LIMIT 1")->fetchColumn();

        $datosCliente = [
            'cedula' => $this->cedulaClienteTest,
            'nombre' => 'Atacante Price Tampering',
            'telefono' => '0414-0000000',
            'direccion' => 'Internet Web',
            'tipo_pedido' => 'DELIVERY',
            'observacion' => 'Hack Price 0.01'
        ];

        // Carrito con precio manipulado a $0.01
        $carritoTampered = [
            'total' => 0.01,
            'items' => [
                [
                    'id_producto' => $idProd,
                    'nombre' => 'Producto Costoso',
                    'precio_unitario' => 0.01,
                    'cantidad' => 1
                ]
            ]
        ];

        $datosPago = [
            'id_metodo_pago' => $idMetodo,
            'referencia' => 'HACK0001',
            'comprobante_url' => null
        ];

        $res = $this->pedidoWebModel->registrarPedidoWeb($datosCliente, $carritoTampered, $datosPago);
        $this->assertTrue($res['success'], "Vulnerabilidad confirmada: Pedido registrado exitosamente a $0.01");

        // Comprobar que en la BD se grabó con total = 0.01
        $stm = $this->dbBusiness->prepare("SELECT total FROM pedido WHERE id_pedido = :id");
        $stm->execute([':id' => $res['id_pedido']]);
        $totalGrabado = (float)$stm->fetchColumn();
        $this->assertEquals(0.01, $totalGrabado, "Vulnerabilidad confirmada: Total almacenado en BD es $0.01");

        // Limpieza
        $this->dbBusiness->exec("DELETE FROM detalle_pedido WHERE id_pedido = '{$res['id_pedido']}'");
        $this->dbBusiness->exec("DELETE FROM pago WHERE id_pedido = '{$res['id_pedido']}'");
        $this->dbBusiness->exec("DELETE FROM pedido WHERE id_pedido = '{$res['id_pedido']}'");
    }

    #[Test]
    public function testDefectoOmisionDescuentoDeInsumosEnPedidosWeb(): void
    {
        // Defecto PED-LOGIC-01:
        // PedidoPublico.php no descuenta ni reserva stock en la tabla insumo
        $stmIns = $this->dbBusiness->query("SELECT id_insumo, stock_actual FROM insumo WHERE estatus = 1 LIMIT 1");
        $rowIns = $stmIns->fetch(PDO::FETCH_ASSOC);
        $idInsumo = $rowIns['id_insumo'];
        $stockAntes = (float)$rowIns['stock_actual'];

        // Encontrar o crear un producto que use este insumo
        $idProd = $this->dbBusiness->query("SELECT id_producto FROM producto WHERE estatus = 1 LIMIT 1")->fetchColumn();
        $idMetodo = $this->dbBusiness->query("SELECT id_metodo_pago FROM metodo_pago WHERE estatus = 1 LIMIT 1")->fetchColumn();

        $datosCliente = [
            'cedula' => $this->cedulaClienteTest,
            'nombre' => 'Test Insumo Omission',
            'telefono' => '0414-1111111',
            'direccion' => 'Dir Test',
            'tipo_pedido' => 'DELIVERY',
            'observacion' => 'Test Stock'
        ];

        $carrito = [
            'total' => 15.00,
            'items' => [
                [
                    'id_producto' => $idProd,
                    'nombre' => 'Producto Stock Test',
                    'precio_unitario' => 15.00,
                    'cantidad' => 5
                ]
            ]
        ];

        $datosPago = [
            'id_metodo_pago' => $idMetodo,
            'referencia' => 'STOCKTEST01',
            'comprobante_url' => null
        ];

        $res = $this->pedidoWebModel->registrarPedidoWeb($datosCliente, $carrito, $datosPago);
        $this->assertTrue($res['success']);

        // Comprobar que el stock del insumo NO cambió (permanece idéntico)
        $stmInsDespues = $this->dbBusiness->prepare("SELECT stock_actual FROM insumo WHERE id_insumo = :id");
        $stmInsDespues->execute([':id' => $idInsumo]);
        $stockDespues = (float)$stmInsDespues->fetchColumn();

        $this->assertEquals($stockAntes, $stockDespues, "Defecto confirmado: El pedido web no descuenta insumos de la BD");

        // Limpieza
        $this->dbBusiness->exec("DELETE FROM detalle_pedido WHERE id_pedido = '{$res['id_pedido']}'");
        $this->dbBusiness->exec("DELETE FROM pago WHERE id_pedido = '{$res['id_pedido']}'");
        $this->dbBusiness->exec("DELETE FROM pedido WHERE id_pedido = '{$res['id_pedido']}'");
    }

    #[Test]
    public function testVulnerabilidadFaltaControlDePermisosEnPedidoController(): void
    {
        // Vulnerabilidad PED-VULN-03:
        // PedidoController.php no valida permisos de rol para acciones como cambiar_estado o crear_pos
        $ctrlPath = dirname(__DIR__, 2) . '/app/Controllers/PedidoController.php';
        $codigo = file_get_contents($ctrlPath);

        // Validar que dentro del bloque switch($action) no se invoca Helper::TraerPermisos ni tienePermiso
        $posInicioSwitch = strpos($codigo, 'switch ($action)');
        $posFinSwitch = strpos($codigo, 'default:', $posInicioSwitch);
        $codigoSwitch = substr($codigo, $posInicioSwitch, $posFinSwitch - $posInicioSwitch);

        $this->assertStringNotContainsString('tienePermiso', $codigoSwitch);
        $this->assertStringNotContainsString('TraerPermisos', $codigoSwitch);
    }
}
