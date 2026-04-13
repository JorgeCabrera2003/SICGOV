<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Security\Media;

class MediaController
{
    private $mediaModel;

    public function __construct()
    {
        $this->mediaModel = new Media();
    }

    /**
     * Punto de entrada principal para el módulo Multimedia
     */
    public function index()
    {
        Helper::verificarSesion();

        // Si es una petición AJAX (basado en el parámetro 'peticion' por POST)
        if (isset($_POST['peticion'])) {
            $this->procesarPeticion($_POST['peticion']);
            exit();
        }

        // Carga normal de la vista
        Helper::cargarVista(
            'media/index',
            'Gestor Multimedia - Good Vibes',
            ['extra_js' => [BASE_URL . '/assets/js/media.js']]
        );
    }

    /**
     * Maneja las solicitudes AJAX siguiendo el flujo del proyecto
     */
    private function procesarPeticion($peticion)
    {
        header('Content-Type: application/json');
        $json = ['resultado' => 400, 'mensaje' => 'Petición no válida'];

        try {
            switch ($peticion) {
                case 'consultar':
                    $directorio = $_POST['dir'] ?? null;
                    $archivos = $this->mediaModel->listarArchivos($directorio);
                    
                    // Enriquecer con vinculaciones
                    foreach ($archivos as &$archivo) {
                        $archivo['vinculos'] = $this->mediaModel->obtenerVinculaciones($archivo['ruta']);
                        $archivo['en_uso'] = !empty($archivo['vinculos']);
                    }
                    
                    $json = ['resultado' => 200, 'datos' => $archivos];
                    break;

                case 'registrar':
                    if (!isset($_FILES['archivo'])) throw new \Exception("Archivo no recibido");
                    
                    $directorio = $_POST['directorio'] ?? 'uploads';
                    $resultado = $this->procesarSubida($_FILES['archivo'], $directorio);
                    
                    if ($resultado['success']) {
                        Helper::Bitacora("REGISTRAR", "MULTIMEDIA", "Se subió el archivo: " . $resultado['ruta']);
                        $json = ['resultado' => 200, 'mensaje' => 'Subido correctamente', 'ruta' => $resultado['ruta']];
                    } else {
                        $json = ['resultado' => 400, 'mensaje' => $resultado['message']];
                    }
                    break;

                case 'eliminar':
                    if (empty($_POST['ruta'])) throw new \Exception("Ruta no proporcionada");
                    
                    $forzar = isset($_POST['forzar']) && $_POST['forzar'] === 'true';
                    $resultado = $this->mediaModel->eliminarArchivo($_POST['ruta'], $forzar);
                    
                    if ($resultado['success']) {
                        Helper::Bitacora("ELIMINAR", "MULTIMEDIA", "Se eliminó el archivo: " . $_POST['ruta']);
                        $json = ['resultado' => 200, 'mensaje' => 'Eliminado correctamente'];
                    } else {
                        $json = ['resultado' => 400, 'mensaje' => $resultado['message']];
                    }
                    break;
            }
        } catch (\Exception $e) {
            $json = ['resultado' => 500, 'mensaje' => 'Error: ' . $e->getMessage()];
        }

        echo json_encode($json);
    }

    /**
     * Lógica interna para mover archivos
     */
    private function procesarSubida($archivo, $carpeta)
    {
        $target_dir = BASE_PATH . "/public/assets/img/{$carpeta}/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];

        if (!in_array($extension, $allowed)) {
            return ['success' => false, 'message' => "Extensión .$extension no permitida"];
        }

        $nombre_archivo = uniqid('media_') . '.' . $extension;
        $target_file = $target_dir . $nombre_archivo;

        if (move_uploaded_file($archivo["tmp_name"], $target_file)) {
            return [
                'success' => true, 
                'ruta' => "/assets/img/{$carpeta}/{$nombre_archivo}"
            ];
        }

        return ['success' => false, 'message' => 'Error al mover el archivo'];
    }
}
