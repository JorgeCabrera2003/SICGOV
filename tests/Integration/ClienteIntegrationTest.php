<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Cliente;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Clientes
 * Evalúa el ciclo completo del modelo Cliente, persistencia dual (persona y cliente),
 * eventos de Bitácora, validaciones de cédula, rendimiento y anomalías de re-registro.
 */
class ClienteIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private PDO $dbSecurity;
    private Cliente $clienteModel;
    private string $cedulaTest = 'V-77665544';

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
        $this->clienteModel = new Cliente();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM detalle_pedido WHERE id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = '{$this->cedulaTest}')");
        $this->dbBusiness->exec("DELETE FROM pago WHERE id_pedido IN (SELECT id_pedido FROM pedido WHERE cedula_cliente = '{$this->cedulaTest}')");
        $this->dbBusiness->exec("DELETE FROM pedido WHERE cedula_cliente = '{$this->cedulaTest}'");
        $this->dbBusiness->exec("DELETE FROM cliente WHERE cedula = '{$this->cedulaTest}'");
        $this->dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$this->cedulaTest}'");
    }

    #[Test]
    public function testConsultarClientesEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->clienteModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertEquals(200, $res['HTTP_STATUS']['codigo']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertIsArray($res['response']['datos']);

        // Velocidad: Resuelve en menos de 250 ms
        $this->assertLessThan(250, $duracionMs, "La consulta de clientes demoró más de 250 ms ({$duracionMs} ms)");

        if (!empty($res['response']['datos'])) {
            $cli = $res['response']['datos'][0];
            $this->assertArrayHasKey('cedula', $cli);
            $this->assertArrayHasKey('nombre', $cli);
            $this->assertArrayHasKey('apellido', $cli);
            $this->assertArrayHasKey('telefono', $cli);
            $this->assertArrayHasKey('estatus', $cli);
        }
    }

    #[Test]
    public function testCicloVidaRegistroModificacionYEliminacionCliente(): void
    {
        // 1. Asignar datos válidos
        $this->clienteModel->setCedula($this->cedulaTest);
        $this->clienteModel->setNombre('Carlos');
        $this->clienteModel->setApellido('Perez');
        $this->clienteModel->setFechaNacimiento('1990-05-15');
        $this->clienteModel->setTelefono('0414-1234567');
        $this->clienteModel->setCorreo('carlosperez@test.com');
        $this->clienteModel->setDireccion('Av. Los Jabillos, Casa 12');
        $this->clienteModel->setSexo('M');

        // 2. Transacción de Registro
        $resReg = $this->clienteModel->Transaccion(['peticion' => 'registrar']);
        $this->assertEquals(200, $resReg['HTTP_STATUS']['codigo']);
        $this->assertEquals(1, $resReg['estado']);

        // Comprobar persistencia dual en MariaDB
        $stmPer = $this->dbBusiness->prepare("SELECT nombre, apellido FROM persona WHERE cedula = :c");
        $stmPer->execute([':c' => $this->cedulaTest]);
        $rowPer = $stmPer->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('Carlos', $rowPer['nombre']);
        $this->assertEquals('Perez', $rowPer['apellido']);

        $stmCli = $this->dbBusiness->prepare("SELECT estatus FROM cliente WHERE cedula = :c");
        $stmCli->execute([':c' => $this->cedulaTest]);
        $this->assertEquals(1, $stmCli->fetchColumn());

        // 3. Modificación
        $this->clienteModel->setNombre('Carlos Alberto');
        $this->clienteModel->setTelefono('0424-9876543');
        $resMod = $this->clienteModel->Transaccion(['peticion' => 'modificar']);
        $this->assertEquals(200, $resMod['HTTP_STATUS']['codigo']);

        $stmPer->execute([':c' => $this->cedulaTest]);
        $this->assertEquals('Carlos Alberto', $stmPer->fetch(PDO::FETCH_ASSOC)['nombre']);

        // 4. Eliminación Lógica (Soft-Delete)
        $resElim = $this->clienteModel->Transaccion(['peticion' => 'eliminar']);
        $this->assertEquals(200, $resElim['HTTP_STATUS']['codigo']);

        // Debe conservar la persona pero marcar cliente con estatus 0
        $stmCli->execute([':c' => $this->cedulaTest]);
        $this->assertEquals(0, $stmCli->fetchColumn(), "El cliente debe pasar a estatus 0 (Papelera)");
    }

    #[Test]
    public function testVerificarCedulaExistenteYNoExistente(): void
    {
        // 1. Cédula que no existe
        $this->clienteModel->setCedula('V-99999991');
        $resNo = $this->clienteModel->Transaccion(['peticion' => 'verificar_cedula']);
        $this->assertEquals(200, $resNo['HTTP_STATUS']['codigo']);
        $this->assertFalse($resNo['response']['existe']);

        // 2. Crear cliente y verificar
        $this->dbBusiness->exec("INSERT INTO persona (cedula, documento, nombre, apellido, telefono, direccion, sexo)
            VALUES ('{$this->cedulaTest}', 'V', 'Verif', 'Test', '04141112233', 'Dir Test', 'F')");
        $this->dbBusiness->exec("INSERT INTO cliente (cedula, estatus) VALUES ('{$this->cedulaTest}', 1)");

        $this->clienteModel->setCedula($this->cedulaTest);
        $resSi = $this->clienteModel->Transaccion(['peticion' => 'verificar_cedula']);
        $this->assertEquals(200, $resSi['HTTP_STATUS']['codigo']);
        $this->assertTrue($resSi['response']['existe']);
    }

    #[Test]
    public function testDefectoMensajeConfusoAlReRegistrarClienteEnPapelera(): void
    {
        // Defecto CLI-UX-01:
        // Si un cliente está en Papelera (estatus = 0) e intenta registrarse de nuevo,
        // el sistema responde con 400 'El cliente ya se encuentra registrado',
        // desconcertando al operador porque el cliente no aparece en la tabla activa.
        $this->dbBusiness->exec("INSERT INTO persona (cedula, documento, nombre, apellido, telefono, direccion, sexo)
            VALUES ('{$this->cedulaTest}', 'V', 'Inactivo', 'Test', '04141112233', 'Dir Test', 'M')");
        $this->dbBusiness->exec("INSERT INTO cliente (cedula, estatus) VALUES ('{$this->cedulaTest}', 0)");

        $this->clienteModel->setCedula($this->cedulaTest);
        $this->clienteModel->setNombre('Inactivo');
        $this->clienteModel->setApellido('Test');
        $this->clienteModel->setFechaNacimiento('1992-01-01');
        $this->clienteModel->setTelefono('0414-1112233');
        $this->clienteModel->setCorreo('inactivo@test.com');
        $this->clienteModel->setDireccion('Dir Test');
        $this->clienteModel->setSexo('M');

        $res = $this->clienteModel->Transaccion(['peticion' => 'registrar']);
        $this->assertEquals(400, $res['HTTP_STATUS']['codigo']);
        $this->assertEquals('El cliente ya se encuentra registrado.', $res['response']['mensaje']);
    }

    #[Test]
    public function testIntegridadReferencialConPedidosSetNull(): void
    {
        // 1. Insertar cliente y un pedido asociado
        $this->dbBusiness->exec("INSERT INTO persona (cedula, documento, nombre, apellido, telefono, direccion, sexo)
            VALUES ('{$this->cedulaTest}', 'V', 'ClientePed', 'Test', '04141112233', 'Dir Test', 'F')");
        $this->dbBusiness->exec("INSERT INTO cliente (cedula, estatus) VALUES ('{$this->cedulaTest}', 1)");

        $idPedido = 'PEDTESTINTEG001';
        $this->dbBusiness->exec("INSERT INTO pedido (id_pedido, cedula_cliente, tipo_pedido, total, estado)
            VALUES ('{$idPedido}', '{$this->cedulaTest}', 'LLEVAR', 25.50, 'PENDIENTE')");

        // 2. Comprobar relación
        $stmPed = $this->dbBusiness->prepare("SELECT cedula_cliente FROM pedido WHERE id_pedido = :id");
        $stmPed->execute([':id' => $idPedido]);
        $this->assertEquals($this->cedulaTest, $stmPed->fetchColumn());

        // 3. Si se elimina físicamente el cliente, ON DELETE SET NULL debe desvincular el pedido sin corromper la BD
        $this->dbBusiness->exec("DELETE FROM cliente WHERE cedula = '{$this->cedulaTest}'");
        $stmPed->execute([':id' => $idPedido]);
        $this->assertNull($stmPed->fetchColumn(), "La clave foránea debe establecer NULL en el pedido");

        // Limpieza del pedido
        $this->dbBusiness->exec("DELETE FROM pedido WHERE id_pedido = '{$idPedido}'");
    }

    #[Test]
    public function testDefectoVariableIndefinidaJsonEnControlador(): void
    {
        // Defecto CLI-API-01:
        // En ClienteController.php línea 131, si llega una petición no contemplada,
        // la variable $json nunca fue inicializada, produciendo un PHP Warning / Fatal.
        $ctrlPath = dirname(__DIR__, 2) . '/app/Controllers/ClienteController.php';
        $codigo = file_get_contents($ctrlPath);

        // Comprobar que $json se usa al final sin una inicialización por defecto previa al bloque if
        $this->assertStringContainsString('header("HTTP/1.1 " . $json[\'HTTP_STATUS\'][\'codigo\']', $codigo);
        $this->assertStringNotContainsString('$json = [', substr($codigo, 0, 500));
    }
}
