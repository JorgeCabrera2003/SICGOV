<?php

namespace App\Models\Security;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;

class Media
{
    private $db_security;
    private $db_business;
    private $base_img_path;

    public function __construct()
    {
        $this->db_security = Database::getConnection('security');
        $this->db_business = Database::getConnection('business');
        $this->base_img_path = BASE_PATH . '/public/assets/img/';
    }

    /**
     * Lista todos los archivos de imagen en los directorios configurados
     */
    public function listarArchivos($directorio = null)
    {
        $carpetas = $directorio ? [$directorio] : ['noticias', 'productos', 'usuarios', 'empleados', 'uploads'];
        $resultado = [];

        foreach ($carpetas as $carpeta) {
            $path = $this->base_img_path . $carpeta;
            
            if (!file_exists($path)) continue;

            $archivos = scandir($path);
            foreach ($archivos as $archivo) {
                if ($archivo === '.' || $archivo === '..') continue;

                $full_path = $path . '/' . $archivo;
                if (is_file($full_path)) {
                    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];

                    if (in_array($ext, $allowed)) {
                        $rel_path = "/assets/img/{$carpeta}/{$archivo}";
                        $stat = stat($full_path);
                        
                        $resultado[] = [
                            'nombre' => $archivo,
                            'directorio' => $carpeta,
                            'ruta' => $rel_path,
                            'size' => $stat['size'],
                            'fecha' => date('Y-m-d H:i:s', $stat['mtime']),
                            'tipo' => $ext
                        ];
                    }
                }
            }
        }

        // Ordenar por fecha descendente por defecto
        usort($resultado, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        return $resultado;
    }

    /**
     * Verifica en qué tablas y registros se está usando una imagen
     */
    public function obtenerVinculaciones($ruta)
    {
        $vinculaciones = [];

        // 1. Verificar en tabla polimórfica 'imagen' (Security)
        $sqlImg = "SELECT entidad_tipo, entidad_id FROM imagen WHERE direccion = :ruta OR direccion LIKE :ruta_alt";
        $stmt = $this->db_security->prepare($sqlImg);
        $stmt->execute(['ruta' => $ruta, 'ruta_alt' => "%$ruta"]);
        $resImg = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resImg as $v) {
            $vinculaciones[] = [
                'tipo' => $v['entidad_tipo'],
                'id' => $v['entidad_id'],
                'origen' => 'Tabla Imagen'
            ];
        }

        // 2. Verificar en tabla 'producto' (Business)
        $archivo = basename($ruta);
        $sqlProd = "SELECT id_producto, nombre_producto FROM producto WHERE imagen = :archivo";
        $stmtP = $this->db_business->prepare($sqlProd);
        $stmtP->execute(['archivo' => $archivo]);
        $resProd = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resProd as $p) {
            $vinculaciones[] = [
                'tipo' => 'PRODUCTO',
                'id' => $p['id_producto'],
                'nombre' => $p['nombre_producto'],
                'origen' => 'Tabla Producto (Principal)'
            ];
        }

        foreach ($resProd as $p) {
            $vinculaciones[] = [
                'tipo' => 'PRODUCTO',
                'id' => $p['id_producto'],
                'nombre' => $p['nombre_producto'],
                'origen' => 'Tabla Producto (Principal)'
            ];
        }
        
        return $vinculaciones;
    }

    /**
     * Elimina un archivo físico si no está vinculado (o si se fuerza)
     */
    public function eliminarArchivo($ruta, $forzar = false)
    {
        if (!$forzar) {
            $vinculos = $this->obtenerVinculaciones($ruta);
            if (!empty($vinculos)) {
                return ['success' => false, 'message' => 'El archivo está vinculado a registros en la base de datos.'];
            }
        }

        $full_path = BASE_PATH . '/public' . $ruta;
        if (file_exists($full_path)) {
            if (unlink($full_path)) {
                // Si estaba en la tabla imagen, lo borramos también preventivamente?
                // Mejor dejarlo para que el usuario sepa que borró algo vinculado si usó 'forzar'
                return ['success' => true, 'message' => 'Archivo eliminado correctamente.'];
            }
        }

        return ['success' => false, 'message' => 'No se pudo encontrar o eliminar el archivo físico.'];
    }
}
