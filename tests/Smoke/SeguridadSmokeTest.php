<?php

namespace Tests\Smoke;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Helpers\Helper;
use App\Models\Security\Usuario;
use PDO;

/**
 * Pruebas de Humo: Seguridad y Control de Acceso
 *
 * Reglas de oro:
 * 1. Ejecución rápida y directa (< 500ms).
 * 2. Validación del flujo de autenticación, sesión y matriz de autorización.
 * 3. Comprobación de que credenciales correctas permiten acceso y credenciales erróneas son bloqueadas.
 */
class SeguridadSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::resetConnections();
    }

    /**
     * Verifica que el flujo de autenticación acepte credenciales válidas del usuario por cédula.
     */
    public function test_autenticacion_exitosa_con_credenciales_validas(): void
    {
        $usuarioModel = new Usuario();
        $usuarioModel->setCedula('V-00000000');
        $usuarioModel->setClave('1234', false);

        $resultado = $usuarioModel->Transaccion(['peticion' => 'sesion']);

        $this->assertEquals(200, $resultado['HTTP_STATUS']['codigo'] ?? 0, 'El login válido debe retornar HTTP 200.');
        $this->assertTrue((bool)($resultado['response']['verificacion'] ?? false), 'La verificación de clave debe ser true.');
        $this->assertEquals(1, $resultado['response']['estatus'] ?? 0, 'El usuario debe estar activo.');
    }

    /**
     * Verifica que el flujo de autenticación rechace inmediatamente credenciales incorrectas.
     */
    public function test_autenticacion_rechaza_credenciales_invalidas(): void
    {
        $usuarioModel = new Usuario();
        $usuarioModel->setCedula('V-00000000');
        $usuarioModel->setClave('clave_totalmente_invalida_999', false);

        $resultado = $usuarioModel->Transaccion(['peticion' => 'sesion']);

        $this->assertEquals(401, $resultado['HTTP_STATUS']['codigo'] ?? 0, 'El login inválido debe retornar HTTP 401.');
        $this->assertFalse((bool)($resultado['response']['verificacion'] ?? true), 'La verificación de clave debe ser false.');
    }

    /**
     * Verifica que una sesión activa con estatus_clave válido pase la verificación de sesión sin problemas.
     */
    public function test_sesion_activa_permite_acceso_sin_errores(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $_SESSION['user'] = [
            'cedula' => 'V-00000000',
            'id_rol' => 'ROLS71920260602140652719',
            'estatus_clave' => 1,
            'username' => 'admin_root'
        ];
        $_GET['page'] = 'Home';

        // Ejecutar verificarSesion: con datos válidos no debe interrumpir ni arrojar excepciones
        Helper::verificarSesion();

        $this->assertNotEmpty($_SESSION['user']);
        $this->assertEquals('admin_root', $_SESSION['user']['username']);
    }

    /**
     * Verifica que Helper::TraerPermisos() cargue la matriz de permisos para un rol existente.
     */
    public function test_carga_de_matriz_de_permisos_por_rol(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $_SESSION['user'] = [
            'cedula' => 'V-00000000',
            'id_rol' => 'ROLS71920260602140652719',
            'estatus_clave' => 1
        ];

        $permisos = Helper::TraerPermisos('cliente');

        $this->assertIsArray($permisos, 'TraerPermisos debe devolver un arreglo asociativo.');
        $this->assertArrayHasKey('cliente', $permisos, 'Los permisos deben contener la clave del módulo "cliente".');

        $acciones = $permisos['cliente'];
        $this->assertArrayHasKey('ver', $acciones, 'Falta la acción "ver" en la matriz.');
        $this->assertArrayHasKey('registrar', $acciones, 'Falta la acción "registrar" en la matriz.');
        $this->assertArrayHasKey('modificar', $acciones, 'Falta la acción "modificar" en la matriz.');
        $this->assertArrayHasKey('eliminar', $acciones, 'Falta la acción "eliminar" en la matriz.');
    }

    /**
     * Verifica que los usuarios en la base de datos de seguridad tengan contraseñas
     * con algoritmos de hashing seguros y reconocidos por PHP (bcrypt / argon2).
     */
    public function test_hashes_de_claves_en_base_de_datos_son_validos(): void
    {
        $db = Database::getConnection('security');
        $stmt = $db->query("SELECT username, clave FROM usuario WHERE estatus = 1 LIMIT 5");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($usuarios, 'Debe haber usuarios registrados en la base de datos de prueba.');

        foreach ($usuarios as $usuario) {
            $infoClave = password_get_info($usuario['clave']);
            $this->assertNotEquals(
                0,
                $infoClave['algo'],
                "El hash de la clave del usuario '{$usuario['username']}' no es un hash PHP válido."
            );
        }
    }
}
