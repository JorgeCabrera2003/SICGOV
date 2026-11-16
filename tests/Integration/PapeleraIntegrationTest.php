<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\System\Papelera;
use App\Core\Database;
use PDO;

/**
 * Suite de Pruebas de Integración e Integridad para el Módulo de Papelera de Reciclaje
 * Evalúa interacción directa con las bases de datos de Negocio (goobv-sistema-test)
 * y Seguridad (goobv-usuarios-test), ciclos de restauración, eliminación física y anomalías.
 */
class PapeleraIntegrationTest extends TestCase
{
    private Papelera $papelera;
    private PDO $dbBusiness;
    private PDO $dbSecurity;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['user'] = [
            'cedula' => 'V-00000000',
            'username' => 'admin_test',
            'id_rol' => 'ADMIN00120251001',
            'rol' => 'Administrador',
            'estatus_clave' => 1
        ];

        $this->papelera = new Papelera();
        $this->dbBusiness = Database::getConnection('business');
        $this->dbSecurity = Database::getConnection('security');
    }

    #[Test]
    public function testConsultarPapeleraManejaExcepcionesDeTablasInexistentes(): void
    {
        $res = $this->papelera->Transaccion(['peticion' => 'consultar']);
        
        $this->assertIsArray($res);
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertArrayHasKey('datos', $res['response']);
        $this->assertArrayHasKey('advertencias', $res['response']);

        // Bug PAP-CONF-01: advertencias debe reportar que 'ingrediente' y 'categoria_ingrediente' fallaron
        $advertencias = implode(' | ', $res['response']['advertencias']);
        $this->assertStringContainsString('INGREDIENTES', $advertencias);
        $this->assertStringContainsString('CATEGORIAS_INGREDIENTE', $advertencias);
    }

    #[Test]
    public function testCicloVidaInactivacionYRestauracionCliente(): void
    {
        $cedulaTest = 'V-99887711';
        
        // 1. Asegurar registro de persona y cliente
        $this->dbBusiness->exec("INSERT INTO persona (cedula, documento, nombre, apellido, telefono, direccion, sexo)
            VALUES ('{$cedulaTest}', 'V', 'Test', 'Papelera', '04141112233', 'Direccion Test', 'M')
            ON DUPLICATE KEY UPDATE nombre = 'Test'");
            
        $this->dbBusiness->exec("INSERT INTO cliente (cedula, estatus)
            VALUES ('{$cedulaTest}', 0)
            ON DUPLICATE KEY UPDATE estatus = 0");

        // 2. Verificar que aparezca en la papelera
        $res = $this->papelera->Transaccion(['peticion' => 'consultar']);
        $encontrado = false;
        foreach ($res['response']['datos'] as $item) {
            if ($item['modulo'] === 'CLIENTES' && $item['id'] === $cedulaTest) {
                $encontrado = true;
                break;
            }
        }
        $this->assertTrue($encontrado, "El cliente inactivado debe aparecer en la consulta de Papelera");

        // 3. Restaurar registro
        $resRestaurar = $this->papelera->Transaccion([
            'peticion' => 'restaurar',
            'modulo' => 'CLIENTES',
            'id' => $cedulaTest
        ]);

        $this->assertEquals(200, $resRestaurar['response']['resultado']);
        $this->assertEquals('Registro restaurado correctamente', $resRestaurar['response']['mensaje']);

        // 4. Comprobar en base de datos que estatus sea 1
        $stm = $this->dbBusiness->prepare("SELECT estatus FROM cliente WHERE cedula = :cedula");
        $stm->execute([':cedula' => $cedulaTest]);
        $estatus = $stm->fetchColumn();
        $this->assertEquals(1, $estatus, "El estatus del cliente en la BD debe ser 1 tras la restauración");

        // Limpieza
        $this->dbBusiness->exec("DELETE FROM cliente WHERE cedula = '{$cedulaTest}'");
        $this->dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$cedulaTest}'");
    }

    #[Test]
    public function testCicloVidaInactivacionYRestauracionProducto(): void
    {
        $idProd = 'PROD_TEST_PAPELERA_01';

        // 1. Asegurar una categoría activa
        $this->dbBusiness->exec("INSERT INTO categoria_producto (id_categoria, nombre_categoria, estatus) 
            VALUES ('CAT_TEST_PAP_01', 'Cat Test', 1) 
            ON DUPLICATE KEY UPDATE estatus = 1");

        // 2. Insertar producto inactivado
        $this->dbBusiness->exec("INSERT INTO producto (id_producto, id_categoria, nombre_producto, precio, estatus)
            VALUES ('{$idProd}', 'CAT_TEST_PAP_01', 'Hamburguesa Test Papelera', 12.50, 0)
            ON DUPLICATE KEY UPDATE estatus = 0");

        // 3. Consultar papelera
        $res = $this->papelera->Transaccion(['peticion' => 'consultar']);
        $encontrado = false;
        foreach ($res['response']['datos'] as $item) {
            if ($item['modulo'] === 'PRODUCTOS' && $item['id'] === $idProd) {
                $encontrado = true;
                break;
            }
        }
        $this->assertTrue($encontrado, "El producto inactivado debe figurar en Papelera");

        // 4. Restaurar producto
        $resRest = $this->papelera->Transaccion([
            'peticion' => 'restaurar',
            'modulo' => 'PRODUCTOS',
            'id' => $idProd
        ]);
        $this->assertEquals(200, $resRest['response']['resultado']);

        // 5. Verificar estatus activo en BD
        $stm = $this->dbBusiness->prepare("SELECT estatus FROM producto WHERE id_producto = :id");
        $stm->execute([':id' => $idProd]);
        $this->assertEquals(1, $stm->fetchColumn());

        // Limpieza
        $this->dbBusiness->exec("DELETE FROM producto WHERE id_producto = '{$idProd}'");
        $this->dbBusiness->exec("DELETE FROM categoria_producto WHERE id_categoria = 'CAT_TEST_PAP_01'");
    }

    #[Test]
    public function testFalloArquitecturaOmisionCategoriaProducto(): void
    {
        $idCat = 'CAT_TEST_OMISION_01';
        $this->dbBusiness->exec("INSERT INTO categoria_producto (id_categoria, nombre_categoria, estatus)
            VALUES ('{$idCat}', 'Categoria Inactiva Omision', 0)
            ON DUPLICATE KEY UPDATE estatus = 0");

        // 1. Consultar papelera: nunca figurará porque la tabla no está mapeada
        $res = $this->papelera->Transaccion(['peticion' => 'consultar']);
        $encontrado = false;
        foreach ($res['response']['datos'] as $item) {
            if (isset($item['id']) && $item['id'] === $idCat) {
                $encontrado = true;
                break;
            }
        }
        $this->assertFalse($encontrado, "Defecto PAP-OMIS-01 confirmado: Categorías de productos no son contempladas en Papelera");

        // 2. Intentar restaurar: falla con 400
        $resRest = $this->papelera->Transaccion([
            'peticion' => 'restaurar',
            'modulo' => 'CATEGORIAS_PRODUCTO',
            'id' => $idCat
        ]);
        $this->assertEquals(400, $resRest['response']['resultado']);

        // Limpieza
        $this->dbBusiness->exec("DELETE FROM categoria_producto WHERE id_categoria = '{$idCat}'");
    }

    #[Test]
    public function testBugIdempotenciaRestauracionRegistroYaActivo(): void
    {
        $idProd = 'PROD_TEST_ACTIVO_01';
        $this->dbBusiness->exec("INSERT INTO categoria_producto (id_categoria, nombre_categoria, estatus) 
            VALUES ('CAT_TEST_PAP_02', 'Cat Test 2', 1) 
            ON DUPLICATE KEY UPDATE estatus = 1");
        $this->dbBusiness->exec("INSERT INTO producto (id_producto, id_categoria, nombre_producto, precio, estatus)
            VALUES ('{$idProd}', 'CAT_TEST_PAP_02', 'Pizza Activa', 15.00, 1)
            ON DUPLICATE KEY UPDATE estatus = 1");

        // Restaurar registro que ya estaba activo (estatus = 1)
        $res = $this->papelera->Transaccion([
            'peticion' => 'restaurar',
            'modulo' => 'PRODUCTOS',
            'id' => $idProd
        ]);

        // Defecto PAP-INT-02: El backend emite 200 OK aun cuando el registro ya estaba activo
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertEquals('Registro restaurado correctamente', $res['response']['mensaje']);

        // Limpieza
        $this->dbBusiness->exec("DELETE FROM producto WHERE id_producto = '{$idProd}'");
        $this->dbBusiness->exec("DELETE FROM categoria_producto WHERE id_categoria = 'CAT_TEST_PAP_02'");
    }

    #[Test]
    public function testBugRestauracionIdFantasmaInexistente(): void
    {
        $idInexistente = 'ID_FANTASMA_NO_EXISTE_999';

        $res = $this->papelera->Transaccion([
            'peticion' => 'restaurar',
            'modulo' => 'PRODUCTOS',
            'id' => $idInexistente
        ]);

        // Defecto PAP-INT-03: No verifica rowCount(), emitiendo 200 OK ante IDs inexistentes
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertEquals('Registro restaurado correctamente', $res['response']['mensaje']);
    }

    #[Test]
    public function testEliminacionPermanenteRegistroAislado(): void
    {
        $idNoticia = 'NOTI_TEST_PURGA_01';
        $this->dbSecurity->exec("INSERT INTO noticia (id_noticia, cedula, titulo, contenido, estatus)
            VALUES ('{$idNoticia}', 'V-00000000', 'Noticia Purga Test', 'Contenido Purga', 0)
            ON DUPLICATE KEY UPDATE estatus = 0");

        $res = $this->papelera->Transaccion([
            'peticion' => 'eliminar_permanente',
            'modulo' => 'NOTICIAS',
            'id' => $idNoticia
        ]);

        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertEquals('Registro eliminado permanentemente', $res['response']['mensaje']);

        // Verificar que físicamente fue purgado de la base de datos
        $stm = $this->dbSecurity->prepare("SELECT COUNT(*) FROM noticia WHERE id_noticia = :id");
        $stm->execute([':id' => $idNoticia]);
        $this->assertEquals(0, $stm->fetchColumn(), "El registro debe ser eliminado físicamente de la tabla");
    }

    #[Test]
    public function testBugIntegridadReferencialEnPurgaClienteDejaHuerfanoEnPersona(): void
    {
        $cedulaTest = 'V-99887722';

        $this->dbBusiness->exec("INSERT INTO persona (cedula, documento, nombre, apellido, telefono, direccion, sexo)
            VALUES ('{$cedulaTest}', 'V', 'Huerfano', 'Test', '04140001122', 'Calle Huerfano', 'M')
            ON DUPLICATE KEY UPDATE nombre = 'Huerfano'");

        $this->dbBusiness->exec("INSERT INTO cliente (cedula, estatus)
            VALUES ('{$cedulaTest}', 0)
            ON DUPLICATE KEY UPDATE estatus = 0");

        // Ejecutar purga de cliente
        $res = $this->papelera->Transaccion([
            'peticion' => 'eliminar_permanente',
            'modulo' => 'CLIENTES',
            'id' => $cedulaTest
        ]);

        $this->assertEquals(200, $res['response']['resultado']);

        // Verificar que el registro cliente fue purgado
        $stmCli = $this->dbBusiness->prepare("SELECT COUNT(*) FROM cliente WHERE cedula = :c");
        $stmCli->execute([':c' => $cedulaTest]);
        $this->assertEquals(0, $stmCli->fetchColumn());

        // Defecto PAP-INT-01: El registro persona continúa existiendo como registro huérfano
        $stmPer = $this->dbBusiness->prepare("SELECT COUNT(*) FROM persona WHERE cedula = :c");
        $stmPer->execute([':c' => $cedulaTest]);
        $this->assertEquals(1, $stmPer->fetchColumn(), "Defecto confirmado: La tabla persona queda contaminada con registros huérfanos");

        // Limpieza final
        $this->dbBusiness->exec("DELETE FROM persona WHERE cedula = '{$cedulaTest}'");
    }

    #[Test]
    public function testPurgaIdInexistenteRetornaExitoFalso(): void
    {
        $res = $this->papelera->Transaccion([
            'peticion' => 'eliminar_permanente',
            'modulo' => 'NOTICIAS',
            'id' => 'ID_FANTASMA_PURGA_999'
        ]);

        // Defecto PAP-INT-03: Emite 200 OK y escribe en Bitácora sin verificar afectación de filas
        $this->assertEquals(200, $res['response']['resultado']);
        $this->assertEquals('Registro eliminado permanentemente', $res['response']['mensaje']);
    }

    #[Test]
    public function testVulnerabilidadControladorSinControlDeRolesNiPermisos(): void
    {
        $archivoControlador = dirname(__DIR__, 2) . '/app/Controllers/PapeleraController.php';
        $codigo = file_get_contents($archivoControlador);

        // Validar que no existe ninguna verificación de permisos o roles
        $this->assertStringNotContainsString('tienePermiso', $codigo, "Vulnerabilidad PAP-VULN-01: El controlador carece de verificación tienePermiso");
        $this->assertStringNotContainsString('id_rol', $codigo, "Vulnerabilidad PAP-VULN-01: El controlador carece de validación del rol del usuario");
    }
}
