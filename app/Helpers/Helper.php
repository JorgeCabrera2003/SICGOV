<?php

namespace App\Helpers;

use App\Models\Security\Bitacora;

class Helper
{
    /**
     * Genera un ID
     * Formato: PREFIJO + TIMESTAMP + RANDOM
     * 
     * @param string $prefijo Prefijo del ID (ej: 'BIT', 'PROD', 'PED')
     * @return string ID generado
     */
    public static function generarId($prefijo)
    {
        $fecha = date('YmdHis');
        $random = rand(1000, 9999);
        return $prefijo . $fecha . $random;
    }

    /**
     * Registra un movimiento en la bitácora
     */
    public static function Bitacora($accion, $modulo, $detalles = null)
    {
        try {
            if (!isset($_SESSION['user'])) {
                return false;
            }
            
            $bitacora = new Bitacora();
            $idBitacora = self::generarId('BIT');
            $bitacora->setIdBitacora($idBitacora);

            $usuarioId = $_SESSION['user']['id_usuario'] ?? $_SESSION['user']['cedula'] ?? null;

            if (!$usuarioId) {
                return false;
            }
            
            $bitacora->set_usuario($usuarioId);
            $bitacora->set_modulo($modulo);
            $bitacora->set_accion($accion);
            $bitacora->set_detalles($detalles);
            $bitacora->set_fecha(date('Y-m-d H:i:s'));

            return $bitacora->Transaccion(['peticion' => 'registrar']);
        } catch (\Exception $e) {
            error_log("Error en Helper::Bitacora: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda el Error en un Archivo .txt
     */
    public static function ErrorLog(string $mensaje){
        error_log(
                "\nError: " . $mensaje . "\n",
                3,
                "logs/logs.txt"
            );
    }
    public static function verificarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            if (
                isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
            ) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
                exit();
            } else {
                header("Location: " . BASE_URL . "/?page=login");
                exit();
            }
        }

        return true;
    }

    public static function getDatosUsuario()
    {
        self::verificarSesion();

        $user = $_SESSION['user'];

        return [
            'nombres' => $user['nombres'] ?? $user['username'] ?? 'Usuario',
            'apellidos' => $user['apellidos'] ?? '',
            'cedula' => $user['cedula'] ?? '',
            'rol' => $user['rol'] ?? 'Usuario',
            'foto' => BASE_URL . '/assets/img/default.jpg',
            'username' => $user['username'] ?? ''
        ];
    }

    public static function getVarsVista($tituloPagina = 'Good Vibes')
    {
        self::verificarSesion();

        return [
            'titulo' => $tituloPagina,
            'page' => $_GET['page'] ?? 'home',
            'tema_actual' => $_SESSION['tema'] ?? 0,
            'datos' => self::getDatosUsuario(),
            'base_url' => BASE_URL,
            'base_path' => dirname(__DIR__, 2)
        ];
    }

    public static function cargarVista($vistaPath, $titulo = 'Good Vibes', $vars = [])
    {
        self::verificarSesion();

        $varsVista = self::getVarsVista($titulo);
        $vars = array_merge($varsVista, $vars);
        extract($vars);

        $basePath = dirname(__DIR__, 2);

        $headFile = $basePath . '/resources/views/layout/head.php';
        $menuFile = $basePath . '/resources/views/layout/menu.php';
        $vistaFile = $basePath . '/resources/views/' . $vistaPath . '.php';
        $footerFile = $basePath . '/resources/views/layout/footer.php';

        if (!file_exists($headFile)) {
            die("Error: No se encuentra el archivo head.php en: $headFile");
        }
        if (!file_exists($menuFile)) {
            die("Error: No se encuentra el archivo menu.php en: $menuFile");
        }
        if (!file_exists($vistaFile)) {
            die("Error: No se encuentra la vista en: $vistaFile");
        }
        if (!file_exists($footerFile)) {
            die("Error: No se encuentra el archivo footer.php en: $footerFile");
        }

        require_once $headFile;
        require_once $menuFile;
        require_once $vistaFile;
        require_once $footerFile;
    }
}