<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Menu;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Menú y Catálogo de Productos
 * Evalúa el ciclo de vida del modelo Menu (Producto), verificación de stock,
 * relación con categorías e insumos, búsqueda por ID y permisos del controlador.
 */
class MenuIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private Menu $menuModel;
    private string $idProductoTest = 'PROD202611122000001111';
    private string $idCategoriaTest = 'CATPROD00320260519200547232'; // Bebidas

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
        $this->menuModel = new Menu();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM preparacion WHERE id_producto = '{$this->idProductoTest}'");
        $this->dbBusiness->exec("DELETE FROM producto WHERE id_producto = '{$this->idProductoTest}' OR nombre_producto LIKE 'Producto Test Integ%'");
    }

    #[Test]
    public function testConsultarCategoriasInsumosYUnidadesParaMenu(): void
    {
        $tInicio = microtime(true);
        $categorias = $this->menuModel->Transaccion(['peticion' => 'categorias']);
        $insumos = $this->menuModel->Transaccion(['peticion' => 'insumos']);
        $unidades = $this->menuModel->Transaccion(['peticion' => 'unidades']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($categorias);
        $this->assertNotEmpty($categorias);
        $this->assertIsArray($insumos);
        $this->assertIsArray($unidades);

        // Rendimiento conjunto: Menos de 300 ms
        $this->assertLessThan(300, $duracionMs, "Carga de catálogos para menú demoró {$duracionMs} ms");
    }

    #[Test]
    public function testListarProductosMenuEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->menuModel->Transaccion(['peticion' => 'listar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);
        $this->assertLessThan(200, $duracionMs, "Listar menú demoró {$duracionMs} ms");

        if (!empty($res)) {
            $item = $res[0];
            $this->assertArrayHasKey('id_producto', $item);
            $this->assertArrayHasKey('nombre_producto', $item);
            $this->assertArrayHasKey('precio', $item);
            $this->assertArrayHasKey('estatus', $item);
        }
    }

    #[Test]
    public function testCicloVidaRegistroModificacionYEliminacionProducto(): void
    {
        // 1. Obtener un insumo y unidad válidos para barra
        $stmIns = $this->dbBusiness->query("SELECT id_insumo FROM insumo WHERE estatus = 1 LIMIT 1");
        $idInsumo = $stmIns->fetchColumn();
        $idUnidad = $this->dbBusiness->query("SELECT id_unidad FROM unidad_medida LIMIT 1")->fetchColumn();
        $this->assertNotEmpty($idInsumo, "Debe existir al menos un insumo en la BD");
        $this->assertNotEmpty($idUnidad, "Debe existir al menos una unidad de medida en la BD");

        // 2. Configurar Producto de Barra
        $nombreProd = 'Producto Test Integ Cocktail';
        $this->menuModel->setNombreProducto($nombreProd);
        $this->menuModel->setDescripcion('Cóctel refrescante de prueba');
        $this->menuModel->setPrecio(8.50);
        $this->menuModel->setIdCategoria($this->idCategoriaTest);
        $this->menuModel->setTipoProducto('BARRA');
        $this->menuModel->setImagen('default.png');
        $this->menuModel->setInsumosPrincipales(json_encode([
            ['id' => $idInsumo, 'cantidad' => 1, 'unidad' => $idUnidad]
        ]));

        // 3. Registrar Producto
        $resReg = $this->menuModel->Transaccion(['peticion' => 'registrar']);
        $this->assertTrue($resReg['success']);
        $idProdGenerado = $resReg['id_producto'] ?? $resReg['id'] ?? null;

        // Comprobar inserción en BD
        $stm = $this->dbBusiness->prepare("SELECT id_producto, precio, estatus FROM producto WHERE nombre_producto = :n");
        $stm->execute([':n' => $nombreProd]);
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEmpty($row);
        $idProd = $row['id_producto'];
        $this->assertEquals(8.50, (float)$row['precio']);
        $this->assertEquals(1, $row['estatus']);

        // 4. Modificar Producto (Precio y descripción)
        $this->menuModel->setIdProducto($idProd);
        $this->menuModel->setNombreProducto($nombreProd . ' Premium');
        $this->menuModel->setPrecio(11.00);
        $this->menuModel->setDescripcion('Edición especial');
        $resMod = $this->menuModel->Transaccion(['peticion' => 'modificar']);
        $this->assertTrue($resMod['success']);

        $stm->execute([':n' => $nombreProd . ' Premium']);
        $rowMod = $stm->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(11.00, (float)$rowMod['precio']);

        // 5. Eliminación Lógica (Soft-Delete)
        $resElim = $this->menuModel->Transaccion(['peticion' => 'eliminar', 'id_producto' => $idProd]);
        $this->assertTrue($resElim['success']);

        $stmEst = $this->dbBusiness->prepare("SELECT estatus FROM producto WHERE id_producto = :id");
        $stmEst->execute([':id' => $idProd]);
        $this->assertEquals(0, $stmEst->fetchColumn(), "El producto debe pasar a estatus 0");

        // Limpieza
        $this->dbBusiness->exec("DELETE FROM preparacion WHERE id_producto = '{$idProd}'");
        $this->dbBusiness->exec("DELETE FROM producto WHERE id_producto = '{$idProd}'");
    }

    #[Test]
    public function testVerificarStockProducto(): void
    {
        // Buscar un producto activo en la base de datos
        $idProd = $this->dbBusiness->query("SELECT id_producto FROM producto WHERE estatus = 1 LIMIT 1")->fetchColumn();
        $this->assertNotEmpty($idProd);

        $resStock = $this->menuModel->verificarStockProducto($idProd, 1);
        $this->assertIsArray($resStock);
        $this->assertArrayHasKey('success', $resStock);
        $this->assertArrayHasKey('stock_disponible', $resStock);
    }

    #[Test]
    public function testObtenerInsumosProducto(): void
    {
        $idProd = $this->dbBusiness->query("SELECT id_producto FROM producto WHERE estatus = 1 LIMIT 1")->fetchColumn();
        $this->assertNotEmpty($idProd);

        $insumos = $this->menuModel->obtenerInsumosProducto($idProd);
        $this->assertIsArray($insumos);
    }

    #[Test]
    public function testBuscarProductoPorId(): void
    {
        $idProd = $this->dbBusiness->query("SELECT id_producto FROM producto WHERE estatus = 1 LIMIT 1")->fetchColumn();
        $this->assertNotEmpty($idProd);

        $this->menuModel->setIdProducto($idProd);
        $prod = $this->menuModel->Transaccion(['peticion' => 'buscar']);
        $this->assertIsArray($prod);
        $this->assertEquals($idProd, $prod['id_producto']);
    }

    #[Test]
    public function testControlDePermisosEnMenuController(): void
    {
        // Validar que MenuController.php protege las acciones de registrar/modificar/eliminar
        $ctrlPath = dirname(__DIR__, 2) . '/app/Controllers/MenuController.php';
        $codigo = file_get_contents($ctrlPath);

        $this->assertStringContainsString("Helper::TraerPermisos(\"producto\")", $codigo);
        $this->assertStringContainsString("\$permisosMenu['producto']['registrar']", $codigo);
        $this->assertStringContainsString("\$permisosMenu['producto']['modificar']", $codigo);
    }
}
