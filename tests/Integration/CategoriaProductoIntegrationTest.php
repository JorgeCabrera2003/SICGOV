<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\CategoriaProducto;
use App\Models\System\Papelera;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración para el Módulo de Categorías del Menú
 * Evalúa el ciclo completo del modelo CategoriaProducto, transacciones reales en MariaDB (goobv-sistema-test),
 * validaciones de duplicados, el bug de bloqueo por productos soft-deleted y la omisión en Papelera.
 */
class CategoriaProductoIntegrationTest extends TestCase
{
    private PDO $dbBusiness;
    private CategoriaProducto $categoriaModel;
    private string $idCategoriaTest = 'CAT202611122000001111';
    private string $idBloqueoTest = 'CAT202611122000002222';
    private string $nombreCatTest = 'Postres Exquisitos';

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
        $this->categoriaModel = new CategoriaProducto();

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        $this->dbBusiness->exec("DELETE FROM producto WHERE id_categoria = '{$this->idCategoriaTest}' OR id_categoria = '{$this->idBloqueoTest}'");
        $this->dbBusiness->exec("DELETE FROM categoria_producto WHERE id_categoria = '{$this->idCategoriaTest}' OR id_categoria = '{$this->idBloqueoTest}' OR nombre_categoria LIKE 'Postres Exquisitos%'");
    }

    #[Test]
    public function testConsultarYListarCategoriasEstructuraYVelocidad(): void
    {
        $tInicio = microtime(true);
        $res = $this->categoriaModel->Transaccion(['peticion' => 'consultar']);
        $duracionMs = (microtime(true) - $tInicio) * 1000;

        $this->assertIsArray($res);

        // Velocidad: Resuelve en menos de 200 ms
        $this->assertLessThan(200, $duracionMs, "La consulta de categorías demoró más de 200 ms ({$duracionMs} ms)");

        if (!empty($res)) {
            $cat = $res[0];
            $this->assertArrayHasKey('id_categoria', $cat);
            $this->assertArrayHasKey('nombre_categoria', $cat);
            $this->assertArrayHasKey('estatus', $cat);
        }
    }

    #[Test]
    public function testCicloVidaRegistroModificacionYEliminacionCategoria(): void
    {
        // 1. Registro (Guardar)
        $this->categoriaModel->setNombreCategoria($this->nombreCatTest);
        $resReg = $this->categoriaModel->Transaccion(['peticion' => 'guardar']);

        $this->assertTrue($resReg['success']);
        $this->assertEquals('Categoría guardada exitosamente', $resReg['message']);
        $idGenerado = $resReg['id'];
        $this->assertNotEmpty($idGenerado);

        // Comprobar persistencia en base de datos
        $stm = $this->dbBusiness->prepare("SELECT nombre_categoria, estatus FROM categoria_producto WHERE id_categoria = :id");
        $stm->execute([':id' => $idGenerado]);
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals($this->nombreCatTest, $row['nombre_categoria']);
        $this->assertEquals(1, $row['estatus']);

        // 2. Modificación (Actualizar)
        $nuevoNombre = 'Postres Artesanales';
        $this->categoriaModel->setIdCategoria($idGenerado);
        $this->categoriaModel->setNombreCategoria($nuevoNombre);
        $this->categoriaModel->setEstatus(1);
        $resMod = $this->categoriaModel->Transaccion(['peticion' => 'actualizar']);
        $this->assertTrue($resMod['success']);

        $stm->execute([':id' => $idGenerado]);
        $this->assertEquals($nuevoNombre, $stm->fetch(PDO::FETCH_ASSOC)['nombre_categoria']);

        // 3. Eliminación Lógica (Soft-Delete)
        $resElim = $this->categoriaModel->Transaccion(['peticion' => 'eliminar']);
        $this->assertTrue($resElim['success']);

        $stm->execute([':id' => $idGenerado]);
        $this->assertEquals(0, $stm->fetch(PDO::FETCH_ASSOC)['estatus'], "La categoría debe tener estatus 0");

        // Limpieza del registro generado dinámicamente
        $this->dbBusiness->exec("DELETE FROM categoria_producto WHERE id_categoria = '{$idGenerado}'");
    }

    #[Test]
    public function testVerificarNombreCategoriaDuplicado(): void
    {
        // 1. Nombre no existente
        $this->categoriaModel->setNombreCategoria('Pizzas Rellenas Inexistentes');
        $resNo = $this->categoriaModel->Transaccion(['peticion' => 'verificar']);
        $this->assertIsArray($resNo);
        $this->assertFalse($resNo['existe']);

        // 2. Insertar categoría y verificar duplicado
        $this->dbBusiness->exec("INSERT INTO categoria_producto (id_categoria, nombre_categoria, estatus)
            VALUES ('{$this->idCategoriaTest}', '{$this->nombreCatTest}', 1)");

        $this->categoriaModel->setNombreCategoria($this->nombreCatTest);
        $resSi = $this->categoriaModel->Transaccion(['peticion' => 'verificar']);
        $this->assertTrue($resSi['existe']);
    }

    #[Test]
    public function testDefectoBloqueoDefinitivoCategoriaConProductosInactivos(): void
    {
        // Defecto CAT-BUG-01:
        // CategoriaProducto.php:302 verifica "SELECT COUNT(*) FROM producto WHERE id_categoria = :id"
        // sin filtrar "AND estatus = 1". Si los productos están dados de baja (estatus = 0),
        // la categoría queda permanentemente bloqueada para ser eliminada.
        $this->dbBusiness->exec("INSERT INTO categoria_producto (id_categoria, nombre_categoria, estatus)
            VALUES ('{$this->idBloqueoTest}', 'Cat Bloqueada Test', 1)");

        $this->dbBusiness->exec("INSERT INTO producto (id_producto, id_categoria, nombre_producto, precio, estatus)
            VALUES ('PROD_INACT_01', '{$this->idBloqueoTest}', 'Plato Inactivo', 10.00, 0)");

        $this->categoriaModel->setIdCategoria($this->idBloqueoTest);
        $res = $this->categoriaModel->Transaccion(['peticion' => 'eliminar']);

        // Debe retornar success = false impidiendo el borrado debido a productos inactivos
        $this->assertFalse($res['success']);
        $this->assertStringContainsString('productos usando esta categoría', $res['message']);
    }

    #[Test]
    public function testDefectoEliminacionExitosaDeCategoriaInexistente(): void
    {
        // Defecto CAT-BUG-02:
        // CategoriaProducto.php:314 ejecuta UPDATE sin verificar rowCount().
        // Si el ID de categoría no existe, emite success = true falsamente.
        $idInexistente = 'CAT202611122000009999';
        $this->categoriaModel->setIdCategoria($idInexistente);
        $res = $this->categoriaModel->Transaccion(['peticion' => 'eliminar']);

        $this->assertTrue($res['success']);
        $this->assertEquals('Categoría eliminada', $res['message']);
    }

    #[Test]
    public function testDefectoOmisionDeCategoriaEnPapelera(): void
    {
        // Defecto CAT-OMIS-01 / PAP-OMIS-01:
        // Las categorías inactivadas nunca figuran en la Papelera de Reciclaje
        $idCat = 'CAT202611122000003333';
        $this->dbBusiness->exec("INSERT INTO categoria_producto (id_categoria, nombre_categoria, estatus)
            VALUES ('{$idCat}', 'Cat Omision Papelera', 0)
            ON DUPLICATE KEY UPDATE estatus = 0");

        $papelera = new Papelera();
        $res = $papelera->Transaccion(['peticion' => 'consultar']);

        $encontrado = false;
        foreach ($res['response']['datos'] as $item) {
            if (isset($item['id']) && $item['id'] === $idCat) {
                $encontrado = true;
                break;
            }
        }
        $this->assertFalse($encontrado, "Defecto confirmado: La categoría nunca es devuelta por la Papelera");

        $this->dbBusiness->exec("DELETE FROM categoria_producto WHERE id_categoria = '{$idCat}'");
    }
}
