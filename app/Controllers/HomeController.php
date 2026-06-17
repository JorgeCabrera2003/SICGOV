<?php

namespace App\Controllers;

use App\Models\Security\Noticia;

$type = $_REQUEST['type'] ?? 'publico';

if ($type === 'publico') {
    // Obtener todas las noticias para la sección de novedades (con filtros)
    $noticiaModel = new Noticia();
    $filtros = [
        'tipo'  => $_GET['tipo'] ?? null,
        'autor' => $_GET['autor'] ?? null,
        'mes'   => $_GET['mes'] ?? null,
        'anio'  => $_GET['anio'] ?? null
    ];
    $res = $noticiaModel->ConsultarNoticiasPublicas($filtros);
    $noticias = $res['response']['datos'] ?? [];
    $autores  = $noticiaModel->ObtenerAutoresPublicos();

    $page = 'home';
    $titulo = 'Good Vibes - Bienvenido';
    $extra_css = [
        BASE_URL . '/assets/css/landing.css?v=' . time(),
        BASE_URL . '/assets/css/noticias.css?v=' . time()
    ];
    
    require_once BASE_PATH . '/resources/views/layout/head.php';
    
    $hideSidebar = true;
    $datos = $_SESSION['user'] ?? null;
    require_once BASE_PATH . '/resources/views/layout/menu.php';

    require_once BASE_PATH . '/resources/views/public/index.php';

    echo '</div></main>';

    require_once BASE_PATH . '/resources/views/layout/footer.php';
} else {
    // Si por error entran con otro type, redirigir al landing público
    header("Location: " . BASE_URL . "?page=Home&type=publico");
    exit;
}
