<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Models\Security\Noticia;
use App\Core\Database;
use PDO;

/**
 * Pruebas de Integración para el Módulo de Noticias.
 * Interactúa directamente con la base de datos de seguridad (goobv-usuarios-test).
 * Valida persistencia real, relaciones de autoría con usuario, filtros públicos y multimedia.
 */
class NoticiaIntegrationTest extends TestCase
{
    private PDO $db;
    private string $idNoticiaTest;
    private string $cedulaAutorTest = 'V-00000000';
    private string $idImagenTest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::getConnection('security');

        // ID válido de 23 caracteres compatible con RegexHelper y Noticia::setId()
        $timestamp = date('YmdHis');
        $this->idNoticiaTest = 'NOTISYS' . $timestamp . '01';
        $this->idImagenTest = 'IMGTEST' . $timestamp . '01';

        $this->limpiarDatosPrueba();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    private function limpiarDatosPrueba(): void
    {
        if (isset($this->db)) {
            $this->db->exec("DELETE FROM imagen WHERE entidad_tipo = 'NOTICIA' AND entidad_id LIKE 'NOTISYS%'");
            $this->db->exec("DELETE FROM imagen WHERE id_imagen LIKE 'IMGTEST%'");
            $this->db->exec("DELETE FROM noticia WHERE id_noticia LIKE 'NOTISYS%'");
        }
    }

    public function testRegistrarNoticiaYPersistenciaEnBaseDeDatosReal(): void
    {
        $noticia = new Noticia();
        $noticia->setId($this->idNoticiaTest);
        $noticia->setCedula($this->cedulaAutorTest);
        $noticia->setTitulo('Gran Apertura de Temporada 2026');
        $noticia->setSubtitulo('Nuevas experiencias gastronómicas');
        $noticia->setContenido('Te invitamos a disfrutar del nuevo menú y promociones especiales preparadas por nuestro equipo.');
        $noticia->setTipo('INFO');
        $noticia->setFechaPublicacion(date('Y-m-d H:i:s', strtotime('-5 hours')));

        $resultado = $noticia->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado'], 'El estado de la transacción debe ser 1 (éxito)');
        $this->assertEquals(200, $resultado['response']['resultado']);

        // Verificación directa en base de datos real
        $stmt = $this->db->prepare("SELECT * FROM noticia WHERE id_noticia = :id");
        $stmt->execute([':id' => $noticia->getId()]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($fila, 'El registro de noticia debe existir físicamente en la BD');
        $this->assertEquals($noticia->getId(), $fila['id_noticia']);
        $this->assertEquals($this->cedulaAutorTest, $fila['cedula']);
        $this->assertEquals('Gran Apertura de Temporada 2026', $fila['titulo']);
        $this->assertEquals('INFO', $fila['tipo']);
        $this->assertEquals(1, (int)$fila['estatus']);
    }

    public function testConsultarNoticiasAdminRetornaNoticiaConDatosDeAutor(): void
    {
        // 1. Registrar noticia previa
        $noticia = new Noticia();
        $noticia->setId($this->idNoticiaTest);
        $noticia->setCedula($this->cedulaAutorTest);
        $noticia->setTitulo('Noticia de Integracion con Autor');
        $noticia->setSubtitulo('Subtitulo de prueba');
        $noticia->setContenido('Contenido detallado para validacion de JOIN con tabla usuario.');
        $noticia->setTipo('EXITO');
        $noticia->setFechaPublicacion(date('Y-m-d H:i:s', strtotime('-5 hours')));
        $noticia->Transaccion(['peticion' => 'registrar']);

        // 2. Consultar como administrador
        $resAdmin = $noticia->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resAdmin['estado']);
        $this->assertEquals(200, $resAdmin['response']['resultado']);

        $noticias = $resAdmin['response']['datos'] ?? [];
        $this->assertNotEmpty($noticias, 'La lista de noticias admin no debe estar vacía');

        // Buscar nuestra noticia en la lista devuelta por el JOIN
        $encontrada = null;
        foreach ($noticias as $n) {
            if ($n['id_noticia'] === $noticia->getId()) {
                $encontrada = $n;
                break;
            }
        }

        $this->assertNotNull($encontrada, 'La noticia registrada debe aparecer en la lista');
        $this->assertEquals('admin_root', $encontrada['autor'], 'El autor debe coincidir con el username de la tabla usuario');
    }

    public function testConsultarNoticiasPublicasFiltraPorEstatus(): void
    {
        // 1. Registrar noticia activa
        $noticia = new Noticia();
        $noticia->setId($this->idNoticiaTest);
        $noticia->setCedula($this->cedulaAutorTest);
        $noticia->setTitulo('Noticia Publica Filtrable');
        $noticia->setSubtitulo('Visible para clientes');
        $noticia->setContenido('Esta noticia debe ser visible en el catálogo público.');
        $noticia->setTipo('INFO');
        $noticia->setFechaPublicacion(date('Y-m-d H:i:s', strtotime('-5 hours')));
        $noticia->Transaccion(['peticion' => 'registrar']);

        // 2. Verificar que aparece en consulta pública
        $resPub1 = $noticia->Transaccion(['peticion' => 'consultar_publicas']);
        $noticiasPub1 = $resPub1['response']['datos'] ?? [];
        $idsPub1 = array_column($noticiasPub1, 'id_noticia');
        $this->assertContains($noticia->getId(), $idsPub1, 'La noticia activa debe aparecer en el catálogo público');

        // 3. Desactivar noticia (estatus = 0)
        $this->db->prepare("UPDATE noticia SET estatus = 0 WHERE id_noticia = :id")
                 ->execute([':id' => $noticia->getId()]);

        // 4. Verificar que ya NO aparece en la consulta pública
        $resPub2 = $noticia->Transaccion(['peticion' => 'consultar_publicas']);
        $noticiasPub2 = $resPub2['response']['datos'] ?? [];
        $idsPub2 = array_column($noticiasPub2, 'id_noticia');
        $this->assertNotContains($noticia->getId(), $idsPub2, 'La noticia inactiva (estatus=0) no debe aparecer en el catálogo público');
    }

    public function testModificarNoticiaActualizaCamposEnBaseDeDatos(): void
    {
        // 1. Registrar noticia base
        $noticia = new Noticia();
        $noticia->setId($this->idNoticiaTest);
        $noticia->setCedula($this->cedulaAutorTest);
        $noticia->setTitulo('Titulo Original de Noticia');
        $noticia->setSubtitulo('Subtitulo Original');
        $noticia->setContenido('Contenido antes de la modificacion.');
        $noticia->setTipo('INFO');
        $noticia->setFechaPublicacion(date('Y-m-d H:i:s', strtotime('-5 hours')));
        $noticia->Transaccion(['peticion' => 'registrar']);

        // 2. Modificar título, subtítulo y tipo
        $noticia->setTitulo('Titulo Modificado Exitosamente');
        $noticia->setSubtitulo('Subtitulo Actualizado');
        $noticia->setContenido('Contenido modificado con nueva informacion.');
        $noticia->setTipo('ALERTA');

        $resultadoMod = $noticia->Transaccion(['peticion' => 'actualizar']);
        $this->assertEquals(1, $resultadoMod['estado']);
        $this->assertEquals(200, $resultadoMod['response']['resultado']);

        // 3. Comprobar en base de datos real
        $stmt = $this->db->prepare("SELECT titulo, subtitulo, contenido, tipo FROM noticia WHERE id_noticia = :id");
        $stmt->execute([':id' => $noticia->getId()]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Titulo Modificado Exitosamente', $fila['titulo']);
        $this->assertEquals('Subtitulo Actualizado', $fila['subtitulo']);
        $this->assertEquals('ALERTA', $fila['tipo']);
    }

    public function testPersistenciaPolimorficaImagenesDeNoticia(): void
    {
        // 1. Registrar noticia
        $noticia = new Noticia();
        $noticia->setId($this->idNoticiaTest);
        $noticia->setCedula($this->cedulaAutorTest);
        $noticia->setTitulo('Noticia con Imagen Polimorfica');
        $noticia->setSubtitulo('Galeria de fotos');
        $noticia->setContenido('Contenido con soporte para imagenes adjuntas.');
        $noticia->setTipo('INFO');
        $noticia->setFechaPublicacion(date('Y-m-d H:i:s', strtotime('-5 hours')));
        $noticia->Transaccion(['peticion' => 'registrar']);

        // 2. Insertar imagen polimórfica en tabla imagen
        $stmtImg = $this->db->prepare("INSERT INTO imagen (id_imagen, entidad_tipo, entidad_id, direccion, orden, es_principal) 
                                       VALUES (:id_imagen, 'NOTICIA', :id_noticia, '/assets/img/noticias/test.webp', 1, 1)");
        $stmtImg->execute([
            ':id_imagen' => $this->idImagenTest,
            ':id_noticia' => $noticia->getId()
        ]);

        // 3. Consultar admin y verificar que cant_imagenes sea >= 1
        $resAdmin = $noticia->Transaccion(['peticion' => 'consultar']);
        $noticias = $resAdmin['response']['datos'] ?? [];

        $encontrada = null;
        foreach ($noticias as $n) {
            if ($n['id_noticia'] === $noticia->getId()) {
                $encontrada = $n;
                break;
            }
        }

        $this->assertNotNull($encontrada);
        $this->assertGreaterThanOrEqual(1, (int)$encontrada['cant_imagenes'], 'Debe reflejar la cantidad de imágenes vinculadas polimórficamente');
    }

    public function testEliminarNoticiaRealizaBajaLogicaEnBaseDeDatos(): void
    {
        // 1. Registrar noticia
        $noticia = new Noticia();
        $noticia->setId($this->idNoticiaTest);
        $noticia->setCedula($this->cedulaAutorTest);
        $noticia->setTitulo('Noticia para Eliminar');
        $noticia->setSubtitulo('Temporal');
        $noticia->setContenido('Esta noticia sera eliminada de forma logica con estatus 0.');
        $noticia->setTipo('INFO');
        $noticia->setFechaPublicacion(date('Y-m-d H:i:s', strtotime('-5 hours')));
        $noticia->Transaccion(['peticion' => 'registrar']);

        // 2. Eliminar (baja lógica según regla de negocio)
        $resElim = $noticia->Transaccion(['peticion' => 'eliminar']);
        $this->assertEquals(1, $resElim['estado']);
        $this->assertEquals(200, $resElim['response']['resultado']);

        // 3. Verificar baja lógica en BD (estatus = 0)
        $stmt = $this->db->prepare("SELECT estatus FROM noticia WHERE id_noticia = :id");
        $stmt->execute([':id' => $noticia->getId()]);
        $estatus = (int)$stmt->fetchColumn();
        $this->assertEquals(0, $estatus, 'La noticia debe quedar con baja lógica (estatus = 0) en la base de datos');
    }
}
