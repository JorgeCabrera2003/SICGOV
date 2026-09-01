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
        if (!empty($_REQUEST['module'])) {
            $module = strtolower(trim($_REQUEST['module']));
            $filtered = array_filter($topics, function($t) use ($module) {
                if (isset($t['modules'])) {
                    foreach ($t['modules'] as $m) {
                        $m_lower = strtolower($m);
                        if ($m_lower === $module || $m_lower . 's' === $module || $m_lower === $module . 's') {
                            return true;
                        }
                    }
                }
                return false;
            });
            // Si el módulo no tiene ayudas, devolvemos todo por defecto
            $results = !empty($filtered) ? array_values($filtered) : $topics;
        } else {
            $results = $topics;
        }
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
    header('Location: ?page=Dashboard');
    exit;
}