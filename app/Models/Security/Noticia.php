<?php
namespace App\Models\Security;

use App\Core\Database;
use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use PDO;
use Exception;

class Noticia extends Database
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
    private $imagenes_galeria; // Array of paths from media manager

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
    }

    public function LlamarConexion($nombreBD = 'security', PDO &$pdo = NULL)
    {
        return parent::LlamarConexion($nombreBD, $pdo);
    }

    // SETTERS CON VALIDACIÓN RIGUROSA (RegexHelper)
    public function setId(string $id) { 
        if (RegexHelper::ValidarFormatos($id, 'ID') == 0) {
            throw new Exception("El ID de la noticia no cumple con el formato permitido.");
        }
        $this->id_noticia = $id; 
    }

    public function setCedula(string $cedula) { 
        if (RegexHelper::ValidarFormatos($cedula, 'Cedula') == 0) {
            throw new Exception("La cédula del autor no tiene un formato válido (Ej: V-12345678).");
        }
        $this->cedula = $cedula; 
    }

    public function setTitulo(string $titulo) { 
        $titulo = trim($titulo);
        if (RegexHelper::ValidarFormatos($titulo, 'Titulo') == 0) {
            throw new Exception("El título contiene caracteres no permitidos o longitud inválida (3-150 caracteres).");
        }
        $this->titulo = $titulo; 
    }

    public function setSubtitulo(string $subtitulo) { 
        $subtitulo = trim($subtitulo);
        if (!empty($subtitulo) && RegexHelper::ValidarFormatos($subtitulo, 'ObjetoLargo') == 0) {
            throw new Exception("El subtítulo contiene caracteres no permitidos.");
        }
        $this->subtitulo = $subtitulo; 
    }

    public function setContenido(string $contenido) { 
        if (empty(trim($contenido))) {
            throw new Exception("El contenido de la noticia es obligatorio.");
        }
        $this->contenido = $contenido; 
    }

    public function setTipo(string $tipo) { 
        $tipos_validos = ['INFO', 'ALERTA', 'EXITO'];
        if (!in_array($tipo, $tipos_validos)) {
            throw new Exception("Categoría de noticia no válida.");
        }
        $this->tipo = $tipo; 
    }

    public function setFechaPublicacion(string $fecha) { 
        $this->fecha_publicacion = $fecha; 
    }

    public function setEstatus(int $estatus) { 
        $this->estatus = $estatus; 
    }

    public function setImagenes(array $imagenes) { 
        $this->imagenes = $imagenes; 
    }

    public function setImagenesGaleria(array $rutas) { 
        $this->imagenes_galeria = $rutas; 
    }

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
                'consultar_publicas' => $this->ConsultarNoticiasPublicas($peticion['filtros'] ?? []),
                'actualizar', 'modificar' => $this->ModificarNoticia(),
                'eliminar' => $this->EliminarNoticia(),
                'validar', 'detalle' => $this->ValidarNoticia(true),
                'eliminar_imagen' => $this->DesvincularImagenNoticia($peticion['id_imagen'] ?? ''),
                'marcar_principal' => $this->SetImagenPrincipal($peticion['id_imagen'] ?? ''),
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
            $this->LlamarConexion()->beginTransaction();

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
            
            $this->LlamarConexion()->commit();
            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => "OK", 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            Helper::ErrorLog("Error en ConsultarNoticiasAdmin: " . $e->getMessage());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Error al obtener noticias admin", 'datos' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    public function ConsultarNoticiasPublicas($filtros = [])
    {
        $dato = [];
        $arreglo = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "SELECT n.*, u.username as autor,
                    (SELECT direccion FROM imagen WHERE entidad_tipo = 'NOTICIA' AND entidad_id = n.id_noticia ORDER BY es_principal DESC, orden ASC LIMIT 1) as imagen_principal
                    FROM noticia n
                    JOIN usuario u ON n.cedula = u.cedula
                    WHERE n.estatus = 1 
                    AND n.fecha_publicacion <= CURRENT_TIMESTAMP()";

            $params = [];
            if (!empty($filtros['tipo'])) {
                $sql .= " AND n.tipo = :tipo";
                $params[':tipo'] = $filtros['tipo'];
            }
            if (!empty($filtros['autor'])) {
                $sql .= " AND u.username = :autor";
                $params[':autor'] = $filtros['autor'];
            }
            if (!empty($filtros['mes'])) {
                $sql .= " AND MONTH(n.fecha_publicacion) = :mes";
                $params[':mes'] = $filtros['mes'];
            }
            if (!empty($filtros['anio'])) {
                $sql .= " AND YEAR(n.fecha_publicacion) = :anio";
                $params[':anio'] = $filtros['anio'];
            }

            $sql .= " ORDER BY n.fecha_publicacion DESC";
            
            $stm = $this->LlamarConexion()->prepare($sql);
            foreach ($params as $key => $val) {
                $stm->bindValue($key, $val);
            }
            $stm->execute();
            if ($stm->rowCount() > 0) {
                $arreglo = $stm->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $this->LlamarConexion()->commit();
            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'mensaje' => "OK", 'datos' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
            Helper::ErrorLog("Error en ConsultarNoticiasPublicas: " . $e->getMessage());
            $dato['estado'] = -1;
            $dato['response'] = ['resultado' => 500, 'icon' => 'error', 'mensaje' => "Error al cargar el blog público", 'datos' => []];
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
            $dato['response'] = ['resultado' => 200, 'icon' => 'success', 'mensaje' => "Noticia registrada con éxito"];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];

        } catch (\PDOException $e) {
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
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
            if ($this->LlamarConexion()->inTransaction()) $this->LlamarConexion()->rollBack();
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
                $stm = $this->LlamarConexion()->prepare($sql);
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
            $this->LlamarConexion()->beginTransaction();

            $dbSystem = self::getSystemDb();
            $sql = "SELECT n.*, u.username as autor, p.nombre, p.apellido 
                    FROM noticia n 
                    JOIN usuario u ON n.cedula = u.cedula 
                    JOIN `{$dbSystem}`.persona p ON u.cedula = p.cedula
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

            $this->LlamarConexion()->commit();
            $dato['estado'] = 1;
            $dato['response'] = ['resultado' => 200, 'registro' => $arreglo];
            $dato['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => "OK"];
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            $dato['bool'] = -1;
            $dato['estado'] = -1;
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            $dato['response'] = ['resultado' => 500, 'mensaje' => "Error interno del servidor", 'registro' => []];
            $dato['HTTP_STATUS'] = ['codigo' => 500, 'mensaje' => "Error interno del servidor"];
        }
        $this->DestruirConexion();
        return $dato;
    }

    private function GuardarImagenesContexto() {
        if (!empty($this->imagenes) || !empty($this->imagenes_galeria)) {
            $target_dir = Helper::fixPath(BASE_PATH . '/public/assets/img/noticias/');
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            try {
                $sqlReset = "UPDATE imagen SET es_principal = 0 WHERE entidad_tipo = 'NOTICIA' AND entidad_id = :entidad_id";
                $stmReset = $this->LlamarConexion()->prepare($sqlReset);
                $stmReset->bindParam(':entidad_id', $this->id_noticia);
                $stmReset->execute();
            } catch (\PDOException $e) {
                Helper::ErrorLog("Error reseteando imagen principal: " . $e->getMessage());
                throw $e; // Re-lanzar para que el transaccion capture el error
            }

            $sqlInsert = "INSERT INTO imagen (id_imagen, entidad_tipo, entidad_id, direccion, orden, es_principal) 
                          VALUES (:id_imagen, 'NOTICIA', :entidad_id, :direccion, :orden, :es_principal)";
            $stm = $this->LlamarConexion()->prepare($sqlInsert);

            $orden = 1;

            if (!empty($this->imagenes_galeria)) {
                foreach ($this->imagenes_galeria as $ruta) {
                    $id_imagen = "IMG-G" . date('YmdHis') . rand(100,999);
                    $es_principal = ($orden == 1) ? 1 : 0;
                    $stm->bindParam(':id_imagen', $id_imagen);
                    $stm->bindParam(':entidad_id', $this->id_noticia);
                    $stm->bindParam(':direccion', $ruta);
                    $stm->bindParam(':orden', $orden);
                    $stm->bindParam(':es_principal', $es_principal);
                    $stm->execute();
                    $orden++;
                }
            }

            if (!empty($this->imagenes)) {
                foreach ($this->imagenes as $i => $imagen) {
                    if ($imagen['error'] === 0) {
                        $extension = strtolower(pathinfo($imagen["name"], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'jfif'];
                        
                        if (!in_array($extension, $allowed)) continue;
                        if ($imagen['size'] > 5 * 1024 * 1024) continue;

                        $nombre_base = uniqid('not_');
                        $nombre_archivo = $nombre_base . '.webp';
                        $target_file = $target_dir . DS . $nombre_archivo;

                        // Convertir a WebP usando el nuevo Helper
                        if (Helper::convertirAWebP($imagen["tmp_name"], $target_file)) {
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
                        }

                    }
                }
            }
        }
    }

    public function ObtenerAutoresPublicos()
    {
        $autores = [];
        try {
            $this->LlamarConexion();
            $sql = "SELECT DISTINCT u.username 
                    FROM noticia n 
                    JOIN usuario u ON n.cedula = u.cedula 
                    WHERE n.estatus = 1 AND n.fecha_publicacion <= CURRENT_TIMESTAMP()";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute();
            $autores = $stm->fetchAll(PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            Helper::ErrorLog("Error obteniendo autores: " . $e->getMessage());
        }
        $this->DestruirConexion();
        return $autores;
    }

    private function DesvincularImagenNoticia($id_imagen)
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sql = "SELECT id_imagen FROM imagen WHERE id_imagen = :id AND entidad_tipo = 'NOTICIA'";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->execute(['id' => $id_imagen]);
            $img = $stm->fetch(PDO::FETCH_ASSOC);

            if ($img) {
                $sqlDel = "DELETE FROM imagen WHERE id_imagen = :id";
                $this->LlamarConexion()->prepare($sqlDel)->execute(['id' => $id_imagen]);

                $this->LlamarConexion()->commit();
                $dato['resultado'] = 200;
                $dato['mensaje'] = "Imagen desvinculada de la noticia correctamente";
            } else {
                $this->LlamarConexion()->rollBack();
                $dato['resultado'] = 404;
                $dato['mensaje'] = "Imagen no encontrada";
            }
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog("Error desvinculando imagen: " . $e->getMessage());
            $dato['resultado'] = 500;
            $dato['mensaje'] = "Error interno";
        }
        $this->DestruirConexion();
        return ['response' => $dato];
    }

    private function SetImagenPrincipal($id_imagen)
    {
        $dato = [];
        try {
            $this->LlamarConexion();
            $this->LlamarConexion()->beginTransaction();

            $sqlSearch = "SELECT entidad_id FROM imagen WHERE id_imagen = :id AND entidad_tipo = 'NOTICIA'";
            $stmS = $this->LlamarConexion()->prepare($sqlSearch);
            $stmS->execute(['id' => $id_imagen]);
            $res = $stmS->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                $sqlReset = "UPDATE imagen SET es_principal = 0 WHERE entidad_tipo = 'NOTICIA' AND entidad_id = :nid";
                $this->LlamarConexion()->prepare($sqlReset)->execute(['nid' => $res['entidad_id']]);

                $sqlSet = "UPDATE imagen SET es_principal = 1 WHERE id_imagen = :id";
                $this->LlamarConexion()->prepare($sqlSet)->execute(['id' => $id_imagen]);

                $this->LlamarConexion()->commit();

                $dato['resultado'] = 200;
                $dato['mensaje'] = "Imagen principal actualizada";
            } else {
                $this->LlamarConexion()->rollBack();
                $dato['resultado'] = 404;
                $dato['mensaje'] = "Imagen no encontrada";
            }
        } catch (\PDOException $e) {
            $this->LlamarConexion()->rollBack();
            Helper::ErrorLog("Error marcando principal: " . $e->getMessage());
            $dato['resultado'] = 500;
            $dato['mensaje'] = "Error interno";
        }
        $this->DestruirConexion();
        return ['response' => $dato];
    }
}
