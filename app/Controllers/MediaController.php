<?php

namespace App\Controllers;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;


Helper::verificarSesion();

if (isset($_POST['peticion'])) {

    header('Content-Type: application/json');
    $json = ['resultado' => 400, 'mensaje' => 'Petición no válida'];

    try {
        switch ($_POST['peticion']) {

            // ── CONSULTAR archivos del servidor ──────────────
            case 'consultar':
                $directorio = $_POST['dir'] ?? null;
                $archivos   = media_listar_archivos($directorio);

                foreach ($archivos as &$archivo) {
                    $archivo['vinculos'] = media_obtener_vinculaciones($archivo['ruta']);
                    $archivo['en_uso']   = !empty($archivo['vinculos']);
                }
                unset($archivo);

                $json = ['resultado' => 200, 'datos' => $archivos];
                break;

            // ── REGISTRAR / subir nueva imagen ───────────────
            case 'registrar':
                if (!isset($_FILES['archivo'])) {
                    throw new \Exception('Archivo no recibido en la petición.');
                }

                $carpeta  = $_POST['directorio'] ?? 'uploads';
                $resultado = media_procesar_subida($_FILES['archivo'], $carpeta);

                if ($resultado['success']) {
                    Helper::Bitacora('REGISTRAR', 'MULTIMEDIA', 'Se subió: ' . $resultado['ruta']);
                    $json = [
                        'resultado' => 200,
                        'mensaje'   => 'Imagen subida correctamente.',
                        'ruta'      => $resultado['ruta'],
                    ];
                } else {
                    $json = ['resultado' => 400, 'mensaje' => $resultado['message']];
                }
                break;

            // ── ELIMINAR archivo físico ───────────────────────
            case 'eliminar':
                if (empty($_POST['ruta'])) {
                    throw new \Exception('Ruta no proporcionada.');
                }

                $forzar   = (isset($_POST['forzar']) && $_POST['forzar'] === 'true');
                $resultado = media_eliminar_archivo($_POST['ruta'], $forzar);

                if ($resultado['success']) {
                    Helper::Bitacora('ELIMINAR', 'MULTIMEDIA', 'Se eliminó: ' . $_POST['ruta']);
                    $json = ['resultado' => 200, 'mensaje' => 'Archivo eliminado correctamente.'];
                } else {
                    $json = ['resultado' => 400, 'mensaje' => $resultado['message']];
                }
                break;
        }

    } catch (\Exception $e) {
        $json = ['resultado' => 500, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
    }

    echo json_encode($json);
    exit();
}

// ── CARGA NORMAL DE VISTA ────────────────────────────────────
Helper::cargarVista(
    'media/index',
    'Gestor Multimedia - Good Vibes',
    [
        'extra_js' => [BASE_URL . 'assets/js/media.js?v=' . time()],
    ]
);

// ============================================================
// FUNCIONES DEL MÓDULO MULTIMEDIA
// Prefijo media_ para evitar colisiones con otros módulos.
// ============================================================

/**
 * Lista archivos de imagen en los directorios configurados.
 */
function media_listar_archivos(?string $directorio = null): array
{
    $base_path = BASE_PATH . '/public/assets/img/';
    $carpetas  = $directorio
        ? [$directorio]
        : ['noticias', 'productos', 'usuarios', 'empleados', 'uploads'];

    $resultado = [];

    foreach ($carpetas as $carpeta) {
        $path = $base_path . $carpeta;
        if (!file_exists($path)) continue;

        foreach (scandir($path) as $archivo) {
            if ($archivo === '.' || $archivo === '..') continue;

            $full_path = $path . '/' . $archivo;
            if (!is_file($full_path)) continue;

            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'])) continue;

            $stat        = stat($full_path);
            $resultado[] = [
                'nombre'     => $archivo,
                'directorio' => $carpeta,
                'ruta'       => "/assets/img/{$carpeta}/{$archivo}",
                'size'       => $stat['size'],
                'fecha'      => date('Y-m-d H:i:s', $stat['mtime']),
                'tipo'       => $ext,
            ];
        }
    }

    // Ordenar por fecha descendente
    usort($resultado, fn($a, $b) => strtotime($b['fecha']) - strtotime($a['fecha']));

    return $resultado;
}

/**
 * Devuelve los registros de BD que referencian una imagen.
 */
function media_obtener_vinculaciones(string $ruta): array
{
    $db_security = Database::getConnection('security');
    $db_business = Database::getConnection('business');
    $vinculaciones = [];

    // 1. Tabla polimórfica 'imagen' (Security DB)
    $stmt = $db_security->prepare(
        "SELECT entidad_tipo, entidad_id FROM imagen
         WHERE direccion = :ruta OR direccion LIKE :ruta_alt"
    );
    $stmt->execute(['ruta' => $ruta, 'ruta_alt' => "%$ruta"]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $v) {
        $vinculaciones[] = [
            'tipo'   => $v['entidad_tipo'],
            'id'     => $v['entidad_id'],
            'origen' => 'Tabla Imagen',
        ];
    }

    // 2. Tabla 'producto' (Business DB)
    $archivo  = basename($ruta);
    $stmtProd = $db_business->prepare(
        "SELECT id_producto, nombre_producto FROM producto WHERE imagen = :archivo"
    );
    $stmtProd->execute(['archivo' => $archivo]);

    foreach ($stmtProd->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $vinculaciones[] = [
            'tipo'   => 'PRODUCTO',
            'id'     => $p['id_producto'],
            'nombre' => $p['nombre_producto'],
            'origen' => 'Tabla Producto',
        ];
    }

    return $vinculaciones;
}

/**
 * Elimina un archivo físico del servidor.
 * Si $forzar es false y está vinculado, rechaza la operación.
 */
function media_eliminar_archivo(string $ruta, bool $forzar = false): array
{
    if (!$forzar) {
        $vinculos = media_obtener_vinculaciones($ruta);
        if (!empty($vinculos)) {
            return ['success' => false, 'message' => 'El archivo está vinculado a registros en la base de datos.'];
        }
    }

    $full_path = BASE_PATH . '/public' . $ruta;
    if (file_exists($full_path) && unlink($full_path)) {
        return ['success' => true, 'message' => 'Archivo eliminado correctamente.'];
    }

    return ['success' => false, 'message' => 'No se pudo encontrar o eliminar el archivo físico.'];
}

/**
 * Valida y mueve un archivo subido al directorio destino.
 */
function media_procesar_subida(array $archivo, string $carpeta): array
{
    $target_dir = BASE_PATH . "/public/assets/img/{$carpeta}/";

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $allowed   = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];

    if (!in_array($extension, $allowed)) {
        return ['success' => false, 'message' => "Extensión .{$extension} no permitida."];
    }

    $nombre_archivo = uniqid('media_') . '.' . $extension;
    $target_file    = $target_dir . $nombre_archivo;

    if (move_uploaded_file($archivo['tmp_name'], $target_file)) {
        return [
            'success' => true,
            'ruta'    => "/assets/img/{$carpeta}/{$nombre_archivo}",
        ];
    }

    return ['success' => false, 'message' => 'Error al mover el archivo al servidor.'];
}
