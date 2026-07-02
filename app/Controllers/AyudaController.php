<?php

namespace App\Controllers;

$action = $_REQUEST['action'] ?? 'index';
$topicsFile = BASE_PATH . '/resources/data/ayuda_topics.json';

function getTopics($file) {
    if (file_exists($file)) {
        $json = file_get_contents($file);
        return json_decode($json, true);
    }
    return [];
}

if ($action === 'search') {
    header('Content-Type: application/json');
    $query = isset($_REQUEST['q']) ? strtolower(trim($_REQUEST['q'])) : '';
    
    $topics = getTopics($topicsFile);
    $results = [];

    if (empty($query)) {
        $results = $topics;
    } else {
        foreach ($topics as $topic) {
            $match = false;
            if (strpos(strtolower($topic['title']), $query) !== false) {
                $match = true;
            }
            if (!$match && isset($topic['keywords'])) {
                foreach ($topic['keywords'] as $keyword) {
                    if (strpos(strtolower($keyword), $query) !== false) {
                        $match = true;
                        break;
                    }
                }
            }
            if ($match) {
                $results[] = $topic;
            }
        }
    }

    $searchData = array_map(function($t) {
        return [
            'id' => $t['id'],
            'title' => $t['title']
        ];
    }, $results);

    echo json_encode(['status' => 'success', 'data' => $searchData]);
    exit;

} elseif ($action === 'getTopic') {
    header('Content-Type: application/json');
    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado']);
        exit;
    }

    $topics = getTopics($topicsFile);
    $topicData = null;

    foreach ($topics as $topic) {
        if ($topic['id'] == $id) {
            $topicData = $topic;
            break;
        }
    }

    if ($topicData) {
        echo json_encode(['status' => 'success', 'data' => $topicData]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tema no encontrado']);
    }
    exit;

} elseif ($action === 'index') {
    $page = 'ayuda';
    $datos = $_SESSION['user'] ?? ['nombre' => 'Usuario'];
    $permisosGlobales = $_SESSION['permisos'] ?? [];

    require_once BASE_PATH . '/resources/views/layout/head.php';
    require_once BASE_PATH . '/resources/views/layout/menu.php';

    echo '<div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h2>Centro de Ayuda</h2>
                    <p class="lead">Utiliza la barra de búsqueda en la parte superior ("¿Qué deseas hacer?") para encontrar guías interactivas sobre cómo utilizar el sistema.</p>
                    <i class="bi bi-search" style="font-size: 5rem; color: var(--brand-orange, #f39c12);"></i>
                </div>
            </div>
          </div>';

    require_once BASE_PATH . '/resources/views/layout/footer.php';
}
