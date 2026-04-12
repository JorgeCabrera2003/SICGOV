<?php

/*
MODELO DE NOTICIAS

OPERACIONES A BASE DE DATOS:
    REGISTRAR
    CONSULTAR (ADMIN)
    CONSULTAR PUBLICAS
    MODIFICAR
    ELIMINAR
    VALIDAR
*/

namespace App\Models\Security;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;

class Noticia
{
    private $id_noticia;
    private $cedula;
    private $titulo;
    private $subtitulo;
    private $contenido;
    private $tipo;
    private $fecha_publicacion;
    private $estatus;
    private $imagenes; // Array of dynamically uploaded files
    private $db;

    public function __construct()
    {
        $this->id_noticia = "";
        $this->cedula = "";
        $this->titulo = "";
        $this->subtitulo = "";
        $this->contenido = "";
        $this->tipo = "INFO";
        $this->fecha_publicacion = date('Y-m-d H:i:s');
        $this->estatus = 1;
        $this->imagenes = [];
        $this->db = NULL;
    }

    private function LlamarConexion(PDO &$db = NULL)
    {
        if ($db != NULL) {
            $this->db = $db;
        }

        if ($this->db == NULL) {
            $this->db = Database::getConnection('security');
        }

        return $this->db;
    }

    private function DestruirConexion()
    {
        $this->db == NULL;
    }

    // SETTERS
    public function setId(string $id) { $this->id_noticia = $id; }
    public function setCedula(string $cedula) { $this->cedula = $cedula; }
    public function setTitulo(string $titulo) { $this->titulo = $titulo; }
    public function setSubtitulo(string $subtitulo) { $this->subtitulo = $subtitulo; }
    public function setContenido(string $contenido) { $this->contenido = $contenido; }
    public function setTipo(string $tipo) { $this->tipo = $tipo; }
    public function setFechaPublicacion(string $fecha) { $this->fecha_publicacion = $fecha; }
    public function setEstatus(int $estatus) { $this->estatus = $estatus; }
    public function setImagenes(array $imagenes) { $this->imagenes = $imagenes; }

    // GETTERS
    public function getId() { return $this->id_noticia; }

    // MANEJADOR DE OPERACIONES
    public function Transaccion($peticion)
    {
        $response = [];
        $response['response'] = ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"];
        $response['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => "Solicitud no válida"];

        if (isset($peticion['peticion'])) {
            $response = match ($peticion['peticion']) {
                'registrar' => $this->RegistrarNoticia(),
                'consultar' => $this->ConsultarNoticiasAdmin(),
                'consultar_publicas' => $this->ConsultarNoticiasPublicas(),
                'actualizar', 'modificar' => $this->ModificarNoticia(),
                'eliminar' => $this->EliminarNoticia(),
                'validar', 'detalle' => $this->ValidarNoticia(true),
                default => [
                    'response' => ['resultado' => 400, 'icon' => 'error', 'mensaje' => "Envió solicitud no válida"],
                    'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => "Solicitud no válida"]
                ]
            };
        }
        return $response;
    }

    private function ConsultarNoticiasAdmin()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            // Buscar todas las noticias menos las eliminadas (estatus = 0 logic)
            $sql = "SELECT n.*, u.username as autor,
                    (SELECT COUNT(i.id_imagen) FROM imagen i WHERE i.entidad_tipo = 'NOTICIA' AND i.entidad_id = n.id_noticia) as cant_imagenes
                    FROM noticia n 
                    JOIN usuario u ON n.cedula = u.cedula
                    WHERE n.estatus = 1
                    ORDER BY n.fecha_publicacion DESC";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => "OK", 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Ups, intente de nuevo más tarde", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ConsultarNoticiasPublicas()
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $sql = "SELECT n.*, u.username as autor,
                    (SELECT direccion FROM imagen WHERE entidad_tipo = 'NOTICIA' AND entidad_id = n.id_noticia ORDER BY es_principal DESC, orden ASC LIMIT 1) as imagen_principal
                    FROM noticia n
                    JOIN usuario u ON n.cedula = u.cedula
                    WHERE n.estatus = 1 
                    AND n.fecha_publicacion <= CURRENT_TIMESTAMP()
                    ORDER BY n.fecha_publicacion DESC";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }
            $stm = NULL;

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => "OK", 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Error interno del servidor", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function RegistrarNoticia()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "INSERT INTO noticia(id_noticia, cedula, titulo, subtitulo, contenido, tipo, fecha_publicacion)
                    VALUES (:id_noticia, :cedula, :titulo, :subtitulo, :contenido, :tipo, :fecha_publicacion)";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_noticia', $this->id_noticia);
            $stm->bindParam(':cedula', $this->cedula);
            $stm->bindParam(':titulo', $this->titulo);
            $stm->bindParam(':subtitulo', $this->subtitulo);
            $stm->bindParam(':contenido', $this->contenido);
            $stm->bindParam(':tipo', $this->tipo);
            $stm->bindParam(':fecha_publicacion', $this->fecha_publicacion);
            $stm->execute();

            $this->GuardarImagenesContexto();

            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Noticia u publicación registrada y/o programada con éxito"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ModificarNoticia()
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "UPDATE noticia SET 
                    titulo = :titulo, 
                    subtitulo = :subtitulo, 
                    contenido = :contenido, 
                    tipo = :tipo, 
                    fecha_publicacion = :fecha_publicacion 
                    WHERE id_noticia = :id_noticia";

            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_noticia', $this->id_noticia);
            $stm->bindParam(':titulo', $this->titulo);
            $stm->bindParam(':subtitulo', $this->subtitulo);
            $stm->bindParam(':contenido', $this->contenido);
            $stm->bindParam(':tipo', $this->tipo);
            $stm->bindParam(':fecha_publicacion', $this->fecha_publicacion);
            $stm->execute();

            $this->GuardarImagenesContexto();

            $this->LlamarConexion()->commit();

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "La noticia ha sido modificada"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Ups, intente de nuevo más tarde"];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function EliminarNoticia()
    {
        $dato = [];
        $validacion = $this->ValidarNoticia(false);

        if ($validacion['bool'] == 1) {
            try {
                $this->LlamarConexion();
                $this->LlamarConexion()->beginTransaction();
                $sql = "UPDATE noticia SET estatus = 0 WHERE id_noticia = :id_noticia";
                $stm = $this->db->prepare($sql);
                $stm->bindParam('id_noticia', $this->id_noticia);
                $stm->execute();
                $this->LlamarConexion()->commit();

                $dato['estado'] = 1;
                $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "La noticia ha sido eliminada"];
                $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
            } catch (\PDOException $e) {
                $this->LlamarConexion()->rollBack();
                Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
                $dato['estado'] = -1;
                $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor"];
                $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
            }
        } else {
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 404, 'icon' => 'error', 'mensaje' => "Registro no encontrado"];
            $dato['HTTP_STATUS'] = ['codigo' => 404, 'mensaje' => "No encontrado"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function ValidarNoticia($traer_imagenes = false)
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $sql = "SELECT n.*, u.username as autor, p.nombre, p.apellido 
                    FROM noticia n 
                    JOIN usuario u ON n.cedula = u.cedula 
                    JOIN `goobv-sistema`.persona p ON u.cedula = p.cedula
                    WHERE n.id_noticia = :id_noticia AND n.estatus = 1";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':id_noticia', $this->id_noticia);
            $stm->execute();
            
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetch(PDO::FETCH_ASSOC);
                $dato['bool'] = 1;
                
                if ($traer_imagenes) {
                    $sql_img = "SELECT * FROM imagen WHERE entidad_tipo = 'NOTICIA' AND entidad_id = :id_noticia ORDER BY orden ASC";
                    $stm_img = $this->LlamarConexion()->prepare($sql_img);
                    $stm_img->bindParam(':id_noticia', $this->id_noticia);
                    $stm_img->execute();
                    $arreglo['imagenes'] = $stm_img->fetchAll(PDO::FETCH_ASSOC);
                }
            } else {
                $dato['bool'] = 0;
            }

            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'registro' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $dato['bool'] = -1;
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'registro' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        return $dato;
    }

    private function GuardarImagenesContexto() {
        if (!empty($this->imagenes)) {
            $target_dir = BASE_PATH . '/public/assets/img/noticias/';
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Si estamos insertando fotos nuevas, reseteamos la principal previa para esta noticia
            // Esto evita conflictos y asegura que la nueva selección mande.
            try {
                $sqlReset = "UPDATE imagen SET es_principal = 0 WHERE entidad_tipo = 'NOTICIA' AND entidad_id = :entidad_id";
                $stmReset = $this->LlamarConexion()->prepare($sqlReset);
                $stmReset->bindParam(':entidad_id', $this->id_noticia);
                $stmReset->execute();
            } catch (\PDOException $e) {
                Helper::ErrorLog("Error reseteando imagen principal: " . $e->getMessage());
            }

            $sqlInsert = "INSERT INTO imagen (id_imagen, entidad_tipo, entidad_id, direccion, orden, es_principal) 
                          VALUES (:id_imagen, 'NOTICIA', :entidad_id, :direccion, :orden, :es_principal)";
            $stm = $this->LlamarConexion()->prepare($sqlInsert);

            $orden = 1; /* Determinar el maximo orden actual si es modificado omitido por simplicidad */
            foreach ($this->imagenes as $i => $imagen) {
                if ($imagen['error'] === 0) {
                    $extension = strtolower(pathinfo($imagen["name"], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'jfif'];
                    
                    // VALIDACIÓN ROBUSTA:
                    // 1. Extensión
                    if (!in_array($extension, $allowed)) {
                        Helper::ErrorLog("Extension de archivo no valida: " . $extension);
                        continue;
                    }

                    // 2. Tamaño (Máximo 5MB)
                    if ($imagen['size'] > 5 * 1024 * 1024) {
                        Helper::ErrorLog("Archivo demasiado pesado: " . ($imagen['size'] / 1024 / 1024) . "MB");
                        continue;
                    }

                    // 3. Verificar si es imagen real
                    $check = getimagesize($imagen["tmp_name"]);
                    if ($check === false) {
                        Helper::ErrorLog("El archivo no es una imagen real: " . $imagen["name"]);
                        continue;
                    }

                    $nombre_archivo = uniqid('not_') . '.' . $extension;
                    $target_file = $target_dir . $nombre_archivo;

                    if (move_uploaded_file($imagen["tmp_name"], $target_file)) {
                        try {
                            $id_imagen = "IMG-" . date('YmdHis') . rand(100,999);
                            $direccion = '/assets/img/noticias/' . $nombre_archivo;
                            $es_principal = ($orden == 1) ? 1 : 0;

                            $stm->bindParam(':id_imagen', $id_imagen);
                            $stm->bindParam(':entidad_id', $this->id_noticia);
                            $stm->bindParam(':direccion', $direccion);
                            $stm->bindParam(':orden', $orden);
                            $stm->bindParam(':es_principal', $es_principal);
                            $stm->execute();
                            $orden++;
                        } catch (\PDOException $ex) {
                            Helper::ErrorLog("Error DB insertando img Noticia: " . $ex->getMessage());
                        }
                    } else {
                        Helper::ErrorLog("Error moviendo archivo subido a: " . $target_file);
                    }
                } else {
                    Helper::ErrorLog("Error en archivo desde el cliente: " . $imagen['error']);
                }
            }
        } else {
            Helper::ErrorLog("No se recibieron imagenes en Noticia.php (vacio)");
        }
    }
}
