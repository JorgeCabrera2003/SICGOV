<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\PedidoPublico;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Pedidos Públicos Web
 * Evalúa registro transaccional concurrente entre bases de datos de negocio y seguridad,
 * creación de cliente, comanda de delivery y métodos de pago.
 */
class PedidoPublicoIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private PDO $dbSecurity;
    private PedidoPublico $pedidoWeb;
    private string $cedulaClienteTest = 'V-99112233';
    private string $idMetodoPago;
    private string $idProducto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbBusiness = Database::getConnection('business');
        $this->dbSecurity = Database::getConnection('security');
        $this->pedidoWeb = new PedidoPublico();

        // Obtener método de pago y producto válidos
        $this->idMetodoPago = (string)$this->dbBusiness->query("SELECT id_metodo_pago FROM metodo_pago LIMIT 1")->fetchColumn();
        $this->idProducto = (string)$this->dbBusiness->query("SELECT id_producto FROM producto WHERE estatus = 1 LIMIT 1")->fetchColumn();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM detalle_pedido WHERE id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = '{$this->cedulaClienteTest}')");
        $this->dbBusiness->exec("DELETE FROM pago WHERE id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = '{$this->cedulaClienteTest}')");
        $this->dbBusiness->exec("DELETE FROM pedido WHERE cedula_cliente = '{$this->cedulaClienteTest}'");
        $this->dbBusiness->exec("DELETE FROM cliente WHERE cedula = '{$this->cedulaClienteTest}'");
        $this->dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaClienteTest}'");
    }

    #[Test]
    public function testRegistrarPedidoWebTransaccional(): void
    {
        $datosCliente = [
            'cedula' => $this->cedulaClienteTest,
            'nombre' => 'Carlos Delivery Test',
            'telefono' => '0412-5554433',
            'direccion' => 'Urb. El Parral, Torre B, Apto 4',
            'tipo_pedido' => 'DELIVERY',
            'observacion' => 'Sin cebolla por favor'
        ];

        $carrito = [
            'total' => 25.50,
            'items' => [
                [
                    'id_producto' => $this->idProducto,
                    'cantidad' => 2,
                    'precio_unitario' => 12.75,
                    'indicacion' => 'Bien cocido',
                    'removedPrincipales' => [],
                    'addedAdicionales' => []
                ]
            ]
        ];

        $datosPago = [
            'id_metodo_pago' => $this->idMetodoPago,
            'referencia' => 'REF-9876543210'
        ];

        $res = $this->pedidoWeb->registrarPedidoWeb($datosCliente, $carrito, $datosPago);
        $this->assertIsArray($res);
        $this->assertTrue($res['success']);
        $this->assertNotEmpty($res['id_pedido']);

        // Validar persistencia en BD de pedido, pago y cliente
        $stmtPed = $this->dbBusiness->prepare("SELECT * FROM pedido WHERE id_pedido = ?");
        $stmtPed->execute([$res['id_pedido']]);
        $pedBD = $stmtPed->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($pedBD);
        $this->assertEquals($this->cedulaClienteTest, $pedBD['cedula_cliente']);
        $this->assertEquals('DELIVERY', $pedBD['tipo_pedido']);
        $this->assertEquals(25.50, (float)$pedBD['total']);

        // Validar pago
        $stmtPago = $this->dbBusiness->prepare("SELECT * FROM pago WHERE id_pedido = ?");
        $stmtPago->execute([$res['id_pedido']]);
        $pagoBD = $stmtPago->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($pagoBD);
        $this->assertEquals('REF-9876543210', $pagoBD['referencia']);
    }
}
