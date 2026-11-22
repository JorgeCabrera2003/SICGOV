<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Helpers\Helper;
use App\Models\Security\Bitacora;
use PDO;

/**
 * Pruebas de Humo: Auditoría y Registro (Bitácora)
 *
 * Reglas de oro:
 * 1. Ejecución rápida y confiable (< 500ms).
 * 2. Comprobación del subsistema de auditoría sin romper flujos principales.
 * 3. Limpieza automática de datos creados durante la prueba.
 */
class BitacoraSmokeTest extends TestCase
{
    private PDO $dbSecurity;
    private const MODULO_PRUEBA = 'SmokeTestAudit';

    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
        $this->dbSecurity = Database::getConnection('security');

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    protected function tearDown(): void
    {
        // Limpiar registros de auditoría de prueba para mantener la BD limpia
        $stmt = $this->dbSecurity->prepare("DELETE FROM bitacora WHERE modulo = :modulo");
        $stmt->execute([':modulo' => self::MODULO_PRUEBA]);

        parent::tearDown();
    }

    /**
     * Verifica que Helper::Bitacora() registre una entrada de auditoría correctamente en MySQL.
     */
    public function test_registro_exitoso_de_accion_en_bitacora(): void
    {
        $cedula = 'V-00000000';
        $_SESSION['user'] = ['cedula' => $cedula];

        $accion = 'REGISTRO';
        $detalle = 'Registro de prueba de humo para auditoría del sistema';

        $resultado = Helper::Bitacora(
            $accion,
            self::MODULO_PRUEBA,
            $detalle,
            ['estado_anterior' => 'inactivo'],
            ['estado_nuevo' => 'activo']
        );

        $this->assertTrue($resultado, 'Helper::Bitacora debe retornar true al registrar correctamente.');

        // Verificar inserción física en la base de datos de seguridad
        $stmt = $this->dbSecurity->prepare(
            "SELECT * FROM bitacora WHERE modulo = :modulo AND accion = :accion ORDER BY fecha DESC LIMIT 1"
        );
        $stmt->execute([
            ':modulo' => self::MODULO_PRUEBA,
            ':accion' => $accion
        ]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($registro, 'El registro no fue encontrado en la tabla bitacora.');
        $this->assertEquals($cedula, $registro['cedula']);
        $this->assertEquals(self::MODULO_PRUEBA, $registro['modulo']);
        $this->assertEquals($accion, $registro['accion']);
        $this->assertEquals($detalle, $registro['detalle']);

        // Verificar que los datos anteriores y nuevos se hayan serializado a JSON
        $this->assertJson($registro['valores_anteriores']);
        $this->assertJson($registro['valores_nuevos']);
    }

    /**
     * Verifica que Helper::Bitacora() maneje limpiamente la ausencia de usuario autenticado
     * retornando false sin lanzar excepciones fatales.
     */
    public function test_bitacora_retorna_false_si_no_hay_usuario_en_sesion(): void
    {
        unset($_SESSION['user']);

        $resultado = Helper::Bitacora(
            'REGISTRO',
            self::MODULO_PRUEBA,
            'Intento de registrar bitácora sin usuario'
        );

        $this->assertFalse($resultado, 'Bitacora debe retornar false cuando no hay cédula en sesión.');
    }

    /**
     * Verifica que el modelo Bitacora pueda listar los eventos auditados.
     */
    public function test_modelo_bitacora_puede_listar_eventos(): void
    {
        $bitacoraModel = new Bitacora();
        $listado = $bitacoraModel->Transaccion([
            'peticion' => 'listar',
            'filtros' => []
        ]);

        $this->assertIsArray($listado, 'El listado de bitácora debe ser un arreglo.');
    }
}
