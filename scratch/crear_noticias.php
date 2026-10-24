<?php

require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

echo "Iniciando limpieza y creacion de noticias (Raw PDO)...\n";

$target_dir = __DIR__ . '/../public/assets/img/noticias/';
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}
copy(__DIR__ . '/noticia1.jpg', $target_dir . 'noticia1.jpg');
copy(__DIR__ . '/noticia2.jpg', $target_dir . 'noticia2.jpg');

$db = Database::getConnection('security');

try {
    $db->beginTransaction();

    // 1. Limpiar noticias existentes
    $db->exec("DELETE FROM imagen WHERE entidad_tipo = 'NOTICIA'");
    $db->exec("DELETE FROM noticia");

    // 2. Noticia 1: Digitalizacion
    $id1 = "NOTC" . date('YmdHis') . "1"; // Sin guiones
    $sql1 = "INSERT INTO noticia(id_noticia, cedula, titulo, subtitulo, contenido, tipo, fecha_publicacion) 
             VALUES (?, 'V00000000', 'SICGOV alcanza el 100% de digitalización en trámites', 'Un hito histórico para la administración pública', '<p>En un esfuerzo sin precedentes por modernizar la atención ciudadana, el Sistema de Control Gubernamental (SICGOV) ha anunciado hoy que todos los trámites administrativos se encuentran ahora disponibles en formato digital. Esta iniciativa busca reducir los tiempos de espera y eliminar la burocracia innecesaria.</p><p>Según estimaciones oficiales, esta medida ahorrará más de 2 millones de horas en tiempos de traslado a los ciudadanos anualmente, además de reducir el uso de papel en las oficinas gubernamentales en un 85%.</p>', 'INFO', NOW())";
    $db->prepare($sql1)->execute([$id1]);

    $id_img1 = "IMG" . date('YmdHis') . "1"; // Sin guiones
    $sql_img1 = "INSERT INTO imagen(id_imagen, entidad_tipo, entidad_id, direccion, orden, es_principal)
                 VALUES (?, 'NOTICIA', ?, '/assets/img/noticias/noticia1.jpg', 1, 1)";
    $db->prepare($sql_img1)->execute([$id_img1, $id1]);

    // 3. Noticia 2: Nuevas oficinas
    $id2 = "NOTC" . date('YmdHis') . "2"; // Sin guiones
    $sql2 = "INSERT INTO noticia(id_noticia, cedula, titulo, subtitulo, contenido, tipo, fecha_publicacion) 
             VALUES (?, 'V00000000', 'Inauguración del nuevo Centro de Atención al Ciudadano', 'Modernas instalaciones para un servicio de calidad', '<p>Esta mañana, las autoridades inauguraron el nuevo Centro Integral de Atención al Ciudadano, diseñado para brindar soporte presencial a aquellos usuarios que requieran asistencia especializada o no cuenten con acceso a plataformas digitales.</p><p>El centro cuenta con más de 50 taquillas de atención simultánea, áreas de espera climatizadas, y zonas de auto-gestión equipadas con equipos de última tecnología donde facilitadores guiarán a los ciudadanos en el uso del portal web de SICGOV.</p>', 'INFO', NOW())";
    $db->prepare($sql2)->execute([$id2]);

    $id_img2 = "IMG" . date('YmdHis') . "2"; // Sin guiones
    $sql_img2 = "INSERT INTO imagen(id_imagen, entidad_tipo, entidad_id, direccion, orden, es_principal)
                 VALUES (?, 'NOTICIA', ?, '/assets/img/noticias/noticia2.jpg', 1, 1)";
    $db->prepare($sql_img2)->execute([$id_img2, $id2]);

    $db->commit();
    echo "Noticias creadas exitosamente.\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
