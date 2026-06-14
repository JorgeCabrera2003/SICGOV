<?php

namespace App\Helpers;

use App\Models\Security\Bitacora;
use App\Models\Security\Permiso;

class Helper
{
    /**
     * Genera un ID
     * Formato: PREFIJO + CLAVE FORÁRENA + FECHA + MILISEGUNDOS
     * 
     * @param string $prefijo Prefijo del ID (ej: 'BITA', 'PROD', 'PEDI')
     * @return string ID generado
     */
    public static function generarId($prefijo, $clave = NULL, $contador = 0)
    {
        // Formatear Parámetros
        $id = NULL;
        $prefijo = preg_replace('/[^A-Za-z0-9]/', '', $prefijo);
        $prefijo = strtoupper(substr(trim($prefijo), 0, 5));
        $milisegundo = number_format(microtime(true) * 1000, 0, '', '');
        $milisegundo = substr($milisegundo, -4);

        if ($clave == NULL) {
            $clave = substr($milisegundo, -5);
        } else {
            $clave = preg_replace('/[^A-Za-z0-9]/', '', $clave);
            $clave = strtoupper(substr(trim($clave), 0, 5));
        }
        $milisegundo = $milisegundo + $contador;
        // Componer el ID
        $fecha = date('YmdHis');
        $id = $prefijo . $clave . $fecha . $milisegundo;

        return $id;
    }

    public static function TraerPermisos($modulo = NULL)
    {
        $permisoModel = new Permiso();
        $arregloPermisos = [];
        $permisoModel->setIdRol($_SESSION['user']['id_rol']);
        $arregloPermisos = $permisoModel->Transaccion(['peticion' => 'filtrar', 'parametro' => 'nombre_modulo', 'modulo' => $modulo]);
        
        return $arregloPermisos['response']['permiso'];
    }

    /**
     * Registra un movimiento en la bitácora de forma segura
     */
    public static function Bitacora($accion, $modulo, $detalle, $prev_data = null, $new_data = null, $cedula_override = null)
    {
        try {
            // Verificar si hay sesión activa
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $bitacora = new Bitacora();
            $idBitacora = self::generarId('BIT');
            $bitacora->setIdBitacora($idBitacora);

            // Intentar obtener la identidad más precisa
            $cedula = $cedula_override;
            if (!$cedula && isset($_SESSION['user']['cedula'])) {
                $cedula = $_SESSION['user']['cedula'];
            }

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
                header("Location: " . BASE_URL . "?page=Login");
                exit();
            }
        }

        $currentPage = $_GET['page'] ?? 'home';
        $estatusClave = $_SESSION['user']['estatus_clave'] ?? 1;

        if ($estatusClave == 0 && !in_array($currentPage, ['forzar-cambiar-clave', 'logout'])) {
            if (
                isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
            ) {
                $peticion = $_POST['peticion'] ?? '';
                if ($peticion !== 'forzar-cambiar-clave') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'resultado' => 403,
                        'icon' => 'warning',
                        'mensaje' => 'Por razones de seguridad, debe cambiar su contraseña antes de continuar.',
                        'redirect' => BASE_URL . '?page=Perfil&type=forzar-cambiar-clave'
                    ]);
                    exit();
                }
            } else {
                header("Location: " . BASE_URL . "?page=Perfil&type=forzar-cambiar-clave");
                exit();
            }
        }

        return true;
    }

    public static function getDatosUsuario()
    {
        self::verificarSesion();

        $user = $_SESSION['user'];
        $foto = BASE_URL . 'assets/img/default.jpg';

        try {
            $db = \App\Core\Database::getConnection('security');
            $sql = "SELECT direccion FROM imagen WHERE entidad_tipo = 'USUARIO' AND entidad_id = :cedula AND es_principal = 1 LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['cedula' => $user['cedula'] ?? '']);
            $img = $stmt->fetch();
            if ($img && !empty($img['direccion'])) {
                $foto = rtrim(BASE_URL, '/') . $img['direccion'];
            }
        } catch (\Exception $e) {
            // Fail silently and use default
        }

        return [
            'nombre' => $user['nombre'] ?? $user['username'] ?? 'Usuario',
            'apellido' => $user['apellido'] ?? '',
            'cedula' => $user['cedula'] ?? '',
            'rol' => $user['rol'] ?? 'Usuario',
            'foto' => $foto,
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
        if(isset($vars['ver']) && $vars['ver'] != 1){
            header("Location: ?page=Dashboard");
        }

        self::verificarSesion();
        $permisos = self::TraerPermisos();

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
    /**
     * Convierte una imagen a formato WebP de forma universal
     * Soporta JPG, PNG, GIF y WEBP (copia directa)
     * 
     * @param string $source Ruta absoluta del archivo origen
     * @param string $destination Ruta absoluta del archivo .webp destino
     * @param int $quality Calidad de compresión (0-100, default 80)
     * @return bool Verdadero si la conversión fue exitosa
     */
    public static function convertirAWebP($source, $destination, $quality = 80)
    {
        if (!extension_loaded('gd')) {
            self::ErrorLog("La extensión GD no está cargada. No se pudo convertir a WebP.");
            return false;
        }

        $info = getimagesize($source);
        if (!$info)
            return false;

        $mime = $info['mime'];
        $image = null;

        try {
            $image = match ($mime) {
                'image/jpeg' => imagecreatefromjpeg($source),
                'image/png' => imagecreatefrompng($source),
                'image/gif' => imagecreatefromgif($source),
                'image/webp' => 'SKIP',
                default => null
            };

            if ($image === 'SKIP')
                return copy($source, $destination);
            if (!$image)
                return false;

            // Post-procesamiento
            if ($mime === 'image/png') {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }


            // Asegurar que el directorio destino existe
            $dir = dirname($destination);
            if (!is_dir($dir))
                mkdir($dir, 0777, true);

            $res = imagewebp($image, $destination, $quality);
            imagedestroy($image);
            return $res;
        } catch (\Exception $e) {
            self::ErrorLog("Error convirtiendo imagen a WebP: " . $e->getMessage());
            return false;
        }
    }
    public static function convertirJSON($objeto)
    {
        if (is_object($objeto)) {

            $objeto = (array) $objeto;

            foreach ($objeto as &$valor) {

                if (is_object($valor)) {
                    $valor = self::convertirJSON($valor);
                } elseif (is_array($valor)) {
                    $valor = array_map(function ($item) {
                        return (is_object($item) || is_array($item)) ? self::convertirJSON($item) : $item;
                    }, $valor);
                }
            }
            return $objeto;
        }

        if (is_array($objeto)) {
            return array_map(function ($valor) {
                return (is_object($valor) || is_array($valor)) ? self::convertirJSON($valor) : $valor;
            }, $objeto);
        }

        return $objeto;
    }

    /**
     * Calcula la diferencia de tiempo relativo en español.
     * 
     * @param \DateTime $fecha Objeto DateTime de la fecha a comparar
     * @return string Tiempo transcurrido legible
     */
    public static function tiempoTranscurrido($fecha)
    {
        $ahora = new \DateTime();
        $diferencia = $ahora->diff($fecha);

        if ($diferencia->y > 0)
            return 'Hace ' . $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '');
        if ($diferencia->m > 0)
            return 'Hace ' . $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '');
        if ($diferencia->d > 0)
            return 'Hace ' . $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '');
        if ($diferencia->h > 0)
            return 'Hace ' . $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
        if ($diferencia->i > 0)
            return 'Hace ' . $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
        return 'Hace unos instantes';
    }
}
