<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\Security\Noticia;

class NoticiaController
{
	public function indexAdmin()
	{
		Helper::verificarSesion();

		$noticiaModel = new Noticia();
		if (isset($_POST["peticion"])) {

			// Entrada
			if ($_POST["peticion"] == "entrada") {
				$json['HTTP_STATUS'] = ['codigo' => 204, 'mensaje' => 'No Content'];
				$json['response'] = ['resultado' => 204, 'mensaje' => 'No hay contenido'];
			}

			// Registrar y Modificar
			if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
				$accion_permiso = true;

				if ($accion_permiso) {
					$bool_formulario = true;
					$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Datos no válidos'];

					if ($_POST["peticion"] == "modificar") {
						if (!isset($_POST["id_noticia"]) || RegexHelper::ValidarFormatos($_POST["id_noticia"], 'ID') == 0) {
							$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Id no válido'];
							$bool_formulario = false;
						}
					}

					if (!isset($_POST["titulo"]) || empty(trim($_POST["titulo"]))) {
						$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Título requerido'];
						$bool_formulario = false;
					}
					
					if (!isset($_POST["contenido"]) || empty(trim($_POST["contenido"]))) {
						$json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Contenido requerido'];
						$bool_formulario = false;
					}

					if ($bool_formulario) {
						$id = NULL;
						if ($_POST["peticion"] == "registrar") {
							$id = Helper::generarId("NOTC");
						} else {
							$id = $_POST["id_noticia"];
						}

						$noticiaModel->setId($id);
						$noticiaModel->setCedula($_SESSION['user']['cedula'] ?? $_SESSION['user']['id_usuario']); // Dependiendo del esquema de sesion
						$noticiaModel->setTitulo($_POST["titulo"]);
						$noticiaModel->setSubtitulo($_POST["subtitulo"] ?? "");
						$noticiaModel->setContenido($_POST["contenido"]);
						$noticiaModel->setTipo($_POST["tipo"] ?? 'INFO');
						
                        // Si no envia fecha, publicar ahora
						$fecha = (!empty($_POST["fecha_publicacion"])) ? $_POST["fecha_publicacion"] : date('Y-m-d H:i:s');
						$noticiaModel->setFechaPublicacion($fecha);

                        // Manejo de variables superglobales de archivos
                        if (isset($_FILES['imagenes'])) {
                            $archivos = [];
                            $count = count($_FILES['imagenes']['name']);
                            for ($i = 0; $i < $count; $i++) {
                                if ($_FILES['imagenes']['error'][$i] === 0) {
                                    $archivos[] = [
                                        'name' => $_FILES['imagenes']['name'][$i],
                                        'type' => $_FILES['imagenes']['type'][$i],
                                        'tmp_name' => $_FILES['imagenes']['tmp_name'][$i],
                                        'error' => $_FILES['imagenes']['error'][$i],
                                        'size' => $_FILES['imagenes']['size'][$i]
                                    ];
                                }
                            }
                            $noticiaModel->setImagenes($archivos);
                        }

						$json = $noticiaModel->Transaccion(['peticion' => $_POST["peticion"]]);
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'Acción no autorizada'];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Error, Permiso denegado'];
				}
			}

			// Consultar
			if ($_POST["peticion"] == "consultar") {
				$json = $noticiaModel->Transaccion(['peticion' => $_POST["peticion"]]);
			}
            
            // Validar (Obteniendo un registro individual para edicion/mostrar)
            if ($_POST["peticion"] == "validar") {
                if (isset($_POST["id_noticia"])) {
                    $noticiaModel->setId($_POST["id_noticia"]);
				    $json = $noticiaModel->Transaccion(['peticion' => $_POST["peticion"]]);
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'ID no provisto'];
					$json['response'] = ['resultado' => 400, 'mensaje' => 'Id requerido'];
                }
			}

			// Eliminar
			if ($_POST["peticion"] == "eliminar") {
				$accion_permiso = true;
				if ($accion_permiso) {
					if (isset($_POST["id_noticia"]) && RegexHelper::ValidarFormatos($_POST["id_noticia"], 'ID') != 0) {
						$noticiaModel->setId($_POST["id_noticia"]);
						$json = $noticiaModel->Transaccion(['peticion' => $_POST["peticion"]]);
					} else {
                        $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Id invalido'];
					    $json['response'] = ['resultado' => 400, 'mensaje' => 'Error, Id no válido'];
                    }
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'No autorizado'];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Permiso denegado'];
				}
			}

			header("HTTP/1.1 " . implode(' ', $json['HTTP_STATUS']));
			echo json_encode($json['response']);
			exit;
		}

		Helper::cargarVista(
			'noticias/index',
			'Gestión de Noticias - Good Vibes'
		);
	}

    public function indexPublico() {
        // Vista pública estilo blog feed (Wordpress)
        $noticiaModel = new Noticia();
        $res = $noticiaModel->Transaccion(['peticion' => 'consultar_publicas']);
        $noticias = $res['response']['datos'] ?? [];

        // No podemos usar cargarVista que quizás incluye el menu de admin.
        // O si el sistema lo maneja distinto, incluidmos layout/public si existe, 
        // o por la forma regular pero sobreescribiendo algun parametro.
        
        $page = 'noticias_publicas';
        $titulo = 'Noticias - Good Vibes';
        
        require_once BASE_PATH . '/resources/views/layout/head.php';
        // Asumiendo que también hay un layout menu público, si no, reutilizamos
        require_once BASE_PATH . '/resources/views/layout/menu.php'; 
        require_once BASE_PATH . '/resources/views/noticias/public.php';
        require_once BASE_PATH . '/resources/views/layout/footer.php';
    }

    public function detallePublico() {
        if (!isset($_GET['id'])) {
            header("Location: " . BASE_URL . "?page=noticias-publicas");
            exit;
        }

        $noticiaModel = new Noticia();
        $noticiaModel->setId($_GET['id']);
        $res = $noticiaModel->Transaccion(['peticion' => 'detalle']);
        
        if ($res['response']['resultado'] != 200 || empty($res['response']['registro'])) {
            require_once BASE_PATH . '/resources/views/errors/404.php';
            exit;
        }

        $noticia = $res['response']['registro'];

        $page = 'noticias_detalle';
        $titulo = $noticia['titulo'] . ' - Good Vibes Noticias';
        
        require_once BASE_PATH . '/resources/views/layout/head.php';
        require_once BASE_PATH . '/resources/views/layout/menu.php';
        require_once BASE_PATH . '/resources/views/noticias/show.php';
        require_once BASE_PATH . '/resources/views/layout/footer.php';
    }
}
