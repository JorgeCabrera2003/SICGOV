<?php

namespace App\Helpers;

use App\Models\Security\Bitacora;

class Helper
{
    /**
     * Genera un ID
     * Formato: PREFIJO + CLAVE FORÁRENA + FECHA + MILISEGUNDOS
     * 
     * @param string $prefijo Prefijo del ID (ej: 'BITA', 'PROD', 'PEDI')
     * @return string ID generado
     */
    public static function generarId($prefijo, $clave = NULL)
    {
        // Formatear Parámetros
        $id = NULL;
        $prefijo = preg_replace('/[^A-Za-z0-9]/', '', $prefijo);
        $prefijo = strtoupper(substr(trim($prefijo), 0, 4));
        $milisegundo = number_format(microtime(true) * 1000, 0, '', '');
        $milisegundo = substr($milisegundo, -3);

        if ($clave == NULL) {
            $clave = substr($milisegundo, -3);
        } else {
            $clave = preg_replace('/[^A-Za-z0-9]/', '', $clave);
            $clave = strtoupper(substr(trim($clave), 0, 3));
        }

        // Componer el ID
        $fecha = date('YmdHms');
        $id = $prefijo . $clave . $fecha . $milisegundo;
        usleep(30000);

        return $id;
    }

    /**
     * Registra un movimiento en la bitácora de forma segura
     */
    public static function Bitacora($accion, $modulo, $detalle, $prev_data = null, $new_data = null)
    {
        try {
            // Verificar si hay sesión activa
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            
            if (!isset($_SESSION['user'])) {
                return false;
            }

            $bitacora = new Bitacora();
            $idBitacora = self::generarId('BIT');
            $bitacora->setIdBitacora($idBitacora);

            // Intentar obtener la identidad más precisa (Cédula es la PK en usuario)
            $user = $_SESSION['user'];
            $cedula = $user['cedula'] ?? null;

            if (!$cedula) {
                return false;
            }

            $bitacora->set_cedula($cedula);
            $bitacora->set_modulo($modulo);
            $bitacora->set_accion($accion);
            $bitacora->set_detalle($detalle);
            $bitacora->set_ip_address($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            
            // Si vienen arrays u objetos, convertirlos a JSON
            if ($prev_data !== null) {
                $bitacora->set_anteriores(is_string($prev_data) ? $prev_data : json_encode($prev_data, JSON_UNESCAPED_UNICODE));
            }
            if ($new_data !== null) {
                $bitacora->set_nuevos(is_string($new_data) ? $new_data : json_encode($new_data, JSON_UNESCAPED_UNICODE));
            }

            $bitacora->set_fecha(date('Y-m-d H:i:s'));

            return $bitacora->Transaccion(['peticion' => 'registrar']);
        } catch (\Exception $e) {
            // No bloqueamos la ejecución principal si falla el log, solo lo registramos como error de sistema
            self::ErrorLog("Error crítico en Helper::Bitacora: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda el Error en un Archivo .txt
     */
    public static function ErrorLog(string $mensaje)
    {
        $ruta_log = self::fixPath(BASE_PATH . "/logs/logs.txt");
        $directorio = dirname($ruta_log);

        // Crear directorio si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true); // true para crear subdirectorios recursivamente
        }
        error_log(
            "\n[" . date('Y-m-d H:i:s') . "] Error: " . $mensaje . "\n",
            3,
            $ruta_log
        );
    }

    /**
     * Normaliza una ruta para que sea compatible con el OS actual
     * Convierte / y \ al separador correcto del sistema
     */
    public static function fixPath($path)
    {
        return str_replace(['/', '\\'], DS, $path);
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