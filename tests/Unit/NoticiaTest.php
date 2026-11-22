<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\Security\Noticia;
use PDO;
use PDOStatement;
use PDOException;
use Exception;

class NoticiaTest extends TestCase
{
    /**
     * Crea un mock parcial de Noticia para aislar la base de datos.
     * Sobrescribe 'LlamarConexion' y 'DestruirConexion'.
     */
    private function createNoticiaMock(PDO&MockObject $mockPdo): Noticia&MockObject
    {
        $mock = $this->getMockBuilder(Noticia::class)
            ->onlyMethods(['LlamarConexion', 'DestruirConexion'])
            ->getMock();

        $mock->method('LlamarConexion')->willReturn($mockPdo);
        $mock->method('DestruirConexion')->willReturn(null);

        return $mock;
    }

    /**
     * Asigna valores válidos a una instancia de Noticia.
     */
    private function poblarNoticiaValida(Noticia $noticia): void
    {
        $noticia->setId('NOTC20261022120000001');
        $noticia->setCedula('V-12345678');
        $noticia->setTitulo('Gran Inauguración de la Nueva Terraza');
        $noticia->setSubtitulo('Ven a disfrutar de los mejores platillos al aire libre');
        $noticia->setContenido('Nos complace anunciar la apertura oficial de nuestro espacio exclusivo de terraza con vista panorámica y menú degustación.');
        $noticia->setTipo('INFO');
        $noticia->setFechaPublicacion('2026-10-22 18:00:00');
        $noticia->setEstatus(1);
    }

    // =========================================================================
    // 1. VALIDACIONES DE FORMATO Y SETTERS (Datos vacíos e incorrectos)
    // =========================================================================

    /**
     * Prueba que campos obligatorios vacíos o nulos disparen excepciones.
     */
    public function test_setters_con_datos_vacios_lanzan_excepcion(): void
    {
        $noticia = new Noticia();

        // 1. ID vacío
        try {
            $noticia->setId('');
            $this->fail("Se esperaba Exception por ID vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El ID de la noticia no cumple con el formato permitido.", $e->getMessage());
        }

        // 2. Cédula vacía
        try {
            $noticia->setCedula('');
            $this->fail("Se esperaba Exception por cédula vacía.");
        } catch (Exception $e) {
            $this->assertStringContainsString("cédula del autor no tiene un formato válido", $e->getMessage());
        }

        // 3. Título vacío
        try {
            $noticia->setTitulo('');
            $this->fail("Se esperaba Exception por título vacío.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El título contiene caracteres no permitidos", $e->getMessage());
        }

        // 4. Contenido vacío
        try {
            $noticia->setContenido('   ');
            $this->fail("Se esperaba Exception por contenido vacío.");
        } catch (Exception $e) {
            $this->assertEquals("El contenido de la noticia es obligatorio.", $e->getMessage());
        }
    }

    /**
     * Prueba validaciones de formatos incorrectos y límites de datos.
     */
    public function test_setters_con_datos_incorrectos_fallan(): void
    {
        $noticia = new Noticia();

        // 1. Cédula con caracteres inválidos
        try {
            $noticia->setCedula('V-INVALIDA');
            $this->fail("Se esperaba Exception por cédula con letras en el número.");
        } catch (Exception $e) {
            $this->assertStringContainsString("cédula del autor no tiene un formato válido", $e->getMessage());
        }

        // 2. Cédula demasiado corta (menos de 7 dígitos)
        try {
            $noticia->setCedula('V-1234');
            $this->fail("Se esperaba Exception por cédula de longitud menor a 7 dígitos.");
        } catch (Exception $e) {
            $this->assertStringContainsString("cédula del autor no tiene un formato válido", $e->getMessage());
        }

        // 3. Normalización automática de cédula: '12345678' -> 'V-12345678'
        $noticia->setCedula('12345678');
        $this->assertEquals('V-12345678', $noticia->getCedula());

        // 4. Normalización automática de cédula sin guion: 'V26999888' -> 'V-26999888'
        $noticia->setCedula('V26999888');
        $this->assertEquals('V-26999888', $noticia->getCedula());

        // 5. Título con longitud menor a 3 caracteres
        try {
            $noticia->setTitulo('Ab');
            $this->fail("Se esperaba Exception por título menor a 3 caracteres.");
        } catch (Exception $e) {
            $this->assertStringContainsString("longitud inválida (3-150 caracteres)", $e->getMessage());
        }

        // 6. Título con caracteres peligrosos o no permitidos (<script>)
        try {
            $noticia->setTitulo('Título <script>alert(1)</script>');
            $this->fail("Se esperaba Exception por caracteres no permitidos en título.");
        } catch (Exception $e) {
            $this->assertStringContainsString("El título contiene caracteres no permitidos", $e->getMessage());
        }

        // 7. Subtítulo con caracteres inválidos
        try {
            $noticia->setSubtitulo('Subtítulo con etiquetas <> no permitidas');
            $this->fail("Se esperaba Exception por subtítulo con caracteres no permitidos.");
        } catch (Exception $e) {
            $this->assertEquals("El subtítulo contiene caracteres no permitidos.", $e->getMessage());
        }

        // 8. Tipo de noticia inválido (solo permite INFO, ALERTA, EXITO)
        try {
            $noticia->setTipo('URGENTE_INVALIDO');
            $this->fail("Se esperaba Exception por categoría de noticia no válida.");
        } catch (Exception $e) {
            $this->assertEquals("Categoría de noticia no válida.", $e->getMessage());
        }
    }

    /**
     * Prueba que los setters y getters preserven correctamente datos válidos.
     */
    public function test_setters_y_getters_con_datos_correctos(): void
    {
        $noticia = new Noticia();
        $this->poblarNoticiaValida($noticia);

        $this->assertEquals('NOTC20261022120000001', $noticia->getId());
        $this->assertEquals('V-12345678', $noticia->getCedula());
        $this->assertEquals('Gran Inauguración de la Nueva Terraza', $noticia->getTitulo());
        $this->assertEquals('Ven a disfrutar de los mejores platillos al aire libre', $noticia->getSubtitulo());
        $this->assertStringContainsString('Nos complace anunciar', $noticia->getContenido());
        $this->assertEquals('INFO', $noticia->getTipo());
        $this->assertEquals('2026-10-22 18:00:00', $noticia->getFechaPublicacion());
        $this->assertEquals(1, $noticia->getEstatus());
    }

    // =========================================================================
    // 2. MÉTODO: RegistrarNoticia (Transacción 'registrar')
    // =========================================================================

    /**
     * Prueba el registro exitoso de una noticia sin imágenes adjuntas.
     */
    public function test_registrar_noticia_sin_imagenes_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);
        $this->poblarNoticiaValida($noticiaMock);

        $resultado = $noticiaMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('success', $resultado['response']['icon']);
        $this->assertEquals('Noticia registrada con éxito', $resultado['response']['mensaje']);
    }

    /**
     * Prueba el registro exitoso de una noticia con imágenes de galería.
     */
    public function test_registrar_noticia_con_imagenes_galeria_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtInsertNoticia = $this->createMock(PDOStatement::class);
        $mockStmtReset = $this->createMock(PDOStatement::class);
        $mockStmtInsertImagen = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');

        // Se preparan: 1 para insertar noticia, 1 para resetear imágenes, 1 para insertar imagen
        $mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $mockStmtInsertNoticia,
                $mockStmtReset,
                $mockStmtInsertImagen
            );

        $mockStmtInsertNoticia->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtReset->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtInsertImagen->expects($this->once())->method('execute')->willReturn(true);

        $mockPdo->expects($this->once())->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);
        $this->poblarNoticiaValida($noticiaMock);
        $noticiaMock->setImagenesGaleria(['/assets/img/galeria/evento1.jpg']);

        $resultado = $noticiaMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
    }

    /**
     * Prueba que un fallo en base de datos al registrar aplique rollback.
     */
    public function test_registrar_noticia_falla_en_bd_aplica_rollback(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException("Error de clave foránea de autor"));

        $mockPdo->method('inTransaction')->willReturn(true);
        $mockPdo->expects($this->once())->method('rollBack');

        $noticiaMock = $this->createNoticiaMock($mockPdo);
        $this->poblarNoticiaValida($noticiaMock);

        $resultado = $noticiaMock->Transaccion(['peticion' => 'registrar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(500, $resultado['response']['resultado']);
    }

    // =========================================================================
    // 3. MÉTODO: ModificarNoticia (Transacción 'modificar')
    // =========================================================================

    /**
     * Prueba la modificación exitosa de una noticia existente.
     */
    public function test_modificar_noticia_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);
        $this->poblarNoticiaValida($noticiaMock);
        $noticiaMock->setTitulo('Título de Noticia Actualizado');

        $resultado = $noticiaMock->Transaccion(['peticion' => 'modificar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('La noticia ha sido modificada', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 4. MÉTODO: EliminarNoticia (Transacción 'eliminar')
    // =========================================================================

    /**
     * Prueba la eliminación lógica exitosa de una noticia existente.
     */
    public function test_eliminar_noticia_existente_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtValidar = $this->createMock(PDOStatement::class);
        $mockStmtEliminar = $this->createMock(PDOStatement::class);

        // ValidarNoticia inicia transacción, busca registro, hace commit
        // EliminarNoticia inicia transacción, ejecuta UPDATE estatus = 0, hace commit
        $mockPdo->expects($this->exactly(2))->method('beginTransaction');
        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtValidar, $mockStmtEliminar);

        $mockStmtValidar->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtValidar->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStmtValidar->expects($this->once())->method('fetch')->willReturn([
            'id_noticia' => 'NOTC20261022120000001',
            'titulo' => 'Noticia Existente',
            'estatus' => 1
        ]);

        $mockStmtEliminar->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->exactly(2))->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);
        $this->poblarNoticiaValida($noticiaMock);

        $resultado = $noticiaMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('La noticia ha sido eliminada', $resultado['response']['mensaje']);
    }

    /**
     * Prueba intento de eliminar una noticia inexistente (devuelve 404).
     */
    public function test_eliminar_noticia_inexistente_retorna_404(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtValidar = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmtValidar);
        $mockStmtValidar->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtValidar->expects($this->once())->method('rowCount')->willReturn(0);
        $mockPdo->expects($this->once())->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);
        $noticiaMock->setId('NOTC99999999999999999');

        $resultado = $noticiaMock->Transaccion(['peticion' => 'eliminar']);

        $this->assertEquals(-1, $resultado['estado']);
        $this->assertEquals(404, $resultado['response']['resultado']);
        $this->assertEquals('Registro no encontrado', $resultado['response']['mensaje']);
    }

    // =========================================================================
    // 5. MÉTODO: ValidarNoticia / Detalle (Transacción 'validar' / 'detalle')
    // =========================================================================

    /**
     * Prueba la obtención de detalle individual con sus imágenes asociadas.
     */
    public function test_detalle_noticia_con_imagenes_retorna_registro_completo(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtNoticia = $this->createMock(PDOStatement::class);
        $mockStmtImagenes = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtNoticia, $mockStmtImagenes);

        $mockStmtNoticia->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtNoticia->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStmtNoticia->expects($this->once())->method('fetch')->willReturn([
            'id_noticia' => 'NOTC20261022120000001',
            'titulo' => 'Noticia con Galería',
            'autor' => 'admin',
            'nombre' => 'Administrador',
            'apellido' => 'General'
        ]);

        $mockStmtImagenes->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtImagenes->expects($this->once())->method('fetchAll')->willReturn([
            ['id_imagen' => 'IMG-001', 'direccion' => '/assets/img/noticias/not_01.webp', 'es_principal' => 1]
        ]);

        $mockPdo->expects($this->once())->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);
        $noticiaMock->setId('NOTC20261022120000001');

        $resultado = $noticiaMock->Transaccion(['peticion' => 'detalle']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertNotEmpty($resultado['response']['registro']);
        $this->assertCount(1, $resultado['response']['registro']['imagenes']);
    }

    // =========================================================================
    // 6. MÉTODO: ConsultarNoticiasAdmin y ConsultarNoticiasPublicas
    // =========================================================================

    /**
     * Prueba la consulta administrativa de listado de noticias.
     */
    public function test_consultar_noticias_admin_retorna_listado(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(2);
        $mockStmt->expects($this->once())->method('fetchAll')->willReturn([
            ['id_noticia' => 'NOTC1', 'titulo' => 'Noticia Uno', 'cant_imagenes' => 2],
            ['id_noticia' => 'NOTC2', 'titulo' => 'Noticia Dos', 'cant_imagenes' => 0],
        ]);
        $mockPdo->expects($this->once())->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);

        $resultado = $noticiaMock->Transaccion(['peticion' => 'consultar']);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertCount(2, $resultado['response']['datos']);
    }

    /**
     * Prueba la consulta pública con filtros dinámicos (tipo, autor, mes, anio).
     */
    public function test_consultar_noticias_publicas_con_filtros(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->once())->method('prepare')->willReturn($mockStmt);
        $mockStmt->expects($this->once())->method('execute')->willReturn(true);
        $mockStmt->expects($this->once())->method('rowCount')->willReturn(1);
        $mockStmt->expects($this->once())->method('fetchAll')->willReturn([
            ['id_noticia' => 'NOTC1', 'titulo' => 'Noticia Filtrada', 'tipo' => 'INFO']
        ]);
        $mockPdo->expects($this->once())->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);

        $filtros = [
            'tipo' => 'INFO',
            'autor' => 'admin',
            'mes' => 10,
            'anio' => 2026
        ];

        $resultado = $noticiaMock->ConsultarNoticiasPublicas($filtros);

        $this->assertEquals(1, $resultado['estado']);
        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertCount(1, $resultado['response']['datos']);
    }

    // =========================================================================
    // 7. GESTIÓN DE IMÁGENES: Desvincular y Marcar Principal
    // =========================================================================

    /**
     * Prueba la desvinculación de una imagen asociada a una noticia.
     */
    public function test_desvincular_imagen_noticia_existente_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtBuscar = $this->createMock(PDOStatement::class);
        $mockStmtBorrar = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtBuscar, $mockStmtBorrar);

        $mockStmtBuscar->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtBuscar->expects($this->once())->method('fetch')->willReturn(['id_imagen' => 'IMG-001']);
        $mockStmtBorrar->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);

        $resultado = $noticiaMock->Transaccion(['peticion' => 'eliminar_imagen', 'id_imagen' => 'IMG-001']);

        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Imagen desvinculada de la noticia correctamente', $resultado['response']['mensaje']);
    }

    /**
     * Prueba marcar una imagen específica como la principal de la noticia.
     */
    public function test_marcar_imagen_como_principal_exitoso(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockStmtBuscar = $this->createMock(PDOStatement::class);
        $mockStmtReset = $this->createMock(PDOStatement::class);
        $mockStmtSet = $this->createMock(PDOStatement::class);

        $mockPdo->expects($this->once())->method('beginTransaction');
        $mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtBuscar, $mockStmtReset, $mockStmtSet);

        $mockStmtBuscar->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtBuscar->expects($this->once())->method('fetch')->willReturn([
            'id_imagen' => 'IMG-002',
            'entidad_id' => 'NOTC20261022120000001'
        ]);

        $mockStmtReset->expects($this->once())->method('execute')->willReturn(true);
        $mockStmtSet->expects($this->once())->method('execute')->willReturn(true);
        $mockPdo->expects($this->once())->method('commit');

        $noticiaMock = $this->createNoticiaMock($mockPdo);

        $resultado = $noticiaMock->Transaccion(['peticion' => 'marcar_principal', 'id_imagen' => 'IMG-002']);

        $this->assertEquals(200, $resultado['response']['resultado']);
        $this->assertEquals('Imagen principal actualizada', $resultado['response']['mensaje']);
    }
}
