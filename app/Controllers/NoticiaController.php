<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\Security\Noticia;
use App\Helpers\NotificacionHelper;

$type = $_REQUEST['type'] ?? 'admin';

if ($type === 'admin') {
		Helper::verificarSesion();

		$noticiaModel = new Noticia();
		
		// Detección mejorada de peticiones AJAX
		$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

		if (isset($_POST["peticion"]) || $isAjax && isset($_POST['peticion'])) {
			
			// Limpiar cualquier salida previa (avisos de PHP, etc) para no corromper el JSON
			if (ob_get_length()) ob_clean();
			
			header('Content-Type: application/json');

			// Entrada
			if ($_POST["peticion"] == "entrada") {
				$json['HTTP_STATUS'] = ['codigo' => 200, 'mensaje' => 'OK'];
				$json['response'] = ['resultado' => 200, 'mensaje' => 'Conexión establecida'];
			}

			// Registrar y Modificar
			if ($_POST["peticion"] == "registrar" || $_POST["peticion"] == "modificar") {
				$accion_permiso = true;

				if ($accion_permiso) {
					try {
						$id = ($_POST["peticion"] == "registrar") ? Helper::generarId("NOTC") : ($_POST["id_noticia"] ?? "");
						
						$noticiaModel->setId($id);
						$id = $noticiaModel->getId(); // Obtener el ID final (truncado si aplica) para consistencia en BD y Auditoría
						$noticiaModel->setCedula($_SESSION['user']['cedula'] ?? $_SESSION['user']['id_usuario'] ?? ""); 
						$noticiaModel->setTitulo($_POST["titulo"] ?? "");
						$noticiaModel->setSubtitulo($_POST["subtitulo"] ?? "");
						$noticiaModel->setContenido($_POST["contenido"] ?? "");
						$noticiaModel->setTipo($_POST["tipo"] ?? 'INFO');
						
						$fecha = (!empty($_POST["fecha_publicacion"])) ? $_POST["fecha_publicacion"] : date('Y-m-d H:i:s');
						$noticiaModel->setFechaPublicacion($fecha);

						// Manejo de imágenes seleccionadas de la galería
						if (!empty($_POST['imagenes_galeria'])) {
							$rutas_galeria = json_decode($_POST['imagenes_galeria'], true);
							if (is_array($rutas_galeria) && count($rutas_galeria) > 0) {
								$noticiaModel->setImagenesGaleria($rutas_galeria);
							}
						}

						// Manejo de subidas nuevas
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
							if (count($archivos) > 0) {
								$noticiaModel->setImagenes($archivos);
							}
						}

						// --- AUDITORÍA: Capturar estado previo ---
						$datos_anteriores = null;
						if ($_POST["peticion"] == "modificar") {
							$res_prev = $noticiaModel->Transaccion(['peticion' => 'validar']);
							$datos_anteriores = $res_prev['response']['registro'] ?? null;
						}

						$json = $noticiaModel->Transaccion(['peticion' => $_POST["peticion"]]);

						// --- AUDITORÍA: Registrar en Bitácora ---
						if ($json['estado'] == 1) {
							$accion_bitacora = ($_POST["peticion"] == "registrar") ? 'REGISTRAR' : 'MODIFICAR';
							$detalle_bitacora = ($_POST["peticion"] == "registrar") 
								? "Se creó la noticia: {$_POST['titulo']} (ID: {$id})"
								: "Se modificó la noticia: {$_POST['titulo']} (ID: {$id})";
							
							$datos_nuevos = [
								'id_noticia' => $id,
								'titulo' => $_POST['titulo'] ?? "",
								'subtitulo' => $_POST['subtitulo'] ?? '',
								'contenido' => $_POST['contenido'] ?? "",
								'tipo' => $_POST['tipo'] ?? 'INFO',
								'fecha_publicacion' => $fecha
							];

							Helper::Bitacora($accion_bitacora, 'NOTICIAS', $detalle_bitacora, $datos_anteriores, $datos_nuevos);

							// --- NOTIFICACIONES: Enviar alerta general si es una nueva publicación ---
							if ($_POST["peticion"] == "registrar") {
								$tituloNotif = "Nueva Publicación";
								$mensajeNotif = "Se ha publicado una nueva noticia: " . ($_POST['titulo'] ?? "");
								NotificacionHelper::notificarATodos('INFO', $mensajeNotif, $tituloNotif);
							}
						}
					} catch (\Exception $e) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error de validación'];
						$json['response'] = ['resultado' => 400, 'icon' => 'warning', 'mensaje' => $e->getMessage()];
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
                try {
                    $noticiaModel->setId($_POST["id_noticia"] ?? "");
                    $json = $noticiaModel->Transaccion(['peticion' => $_POST["peticion"]]);
                } catch (\Exception $e) {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'ID no provisto'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
                }
			}

			// Eliminar
			if ($_POST["peticion"] == "eliminar") {
				$accion_permiso = true;
				if ($accion_permiso) {
					try {
						$noticiaModel->setId($_POST["id_noticia"] ?? "");
						$json = $noticiaModel->Transaccion(['peticion' => $_POST["peticion"]]);

						// --- AUDITORÍA: Registrar eliminación ---
						if ($json['estado'] == 1) {
							Helper::Bitacora('ELIMINAR', 'NOTICIAS', "Se eliminó la noticia ID: {$_POST['id_noticia']}");
						}
					} catch (\Exception $e) {
						$json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'Error al eliminar'];
						$json['response'] = ['resultado' => 400, 'mensaje' => $e->getMessage()];
					}
				} else {
					$json['HTTP_STATUS'] = ['codigo' => 403, 'mensaje' => 'No autorizado'];
					$json['response'] = ['resultado' => 403, 'mensaje' => 'Permiso denegado'];
				}
			}

            // Gestionar Imágenes Individuales
            if ($_POST["peticion"] == "eliminar_imagen" || $_POST["peticion"] == "marcar_principal") {
                if (isset($_POST["id_imagen"])) {
                    $json = $noticiaModel->Transaccion($_POST);

					// --- AUDITORÍA: Gestión de imágenes ---
					if ($json['resultado'] == 200) {
						$accion_img = ($_POST["peticion"] == 'eliminar_imagen') ? 'ELIMINAR IMAGEN' : 'MARCAR PRINCIPAL';
						$detalle_img = ($accion_img == 'ELIMINAR IMAGEN') 
							? "Se eliminó físicamente la imagen ID: {$_POST['id_imagen']}"
							: "Se cambió la imagen de portada ID: {$_POST['id_imagen']}";
						
						Helper::Bitacora($accion_img, 'NOTICIAS', $detalle_img);
					}
                } else {
                    $json['HTTP_STATUS'] = ['codigo' => 400, 'mensaje' => 'ID de imagen requerido'];
                    $json['response'] = ['resultado' => 400, 'mensaje' => 'ID de imagen requerido'];
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
} elseif ($type === 'publico') {
        // Vista pública estilo portal de noticias
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

        // No podemos usar cargarVista que quizás incluye el menu de admin.
        // O si el sistema lo maneja distinto, incluidmos layout/public si existe, 
        // o por la forma regular pero sobreescribiendo algun parametro.
        
        $page = 'noticias_publicas';
        $titulo = 'Noticias - Good Vibes';
        $extra_css = [BASE_URL . '/assets/css/noticias.css?v=' . time()];
        
        require_once BASE_PATH . '/resources/views/layout/head.php';
        
        $hideSidebar = true;
        $datos = $_SESSION['user'] ?? null;
        require_once BASE_PATH . '/resources/views/layout/menu.php';

        require_once BASE_PATH . '/resources/views/noticias/public.php';

        echo '</div></main>';

        require_once BASE_PATH . '/resources/views/layout/footer.php';
} elseif ($type === 'detalle') {
        if (!isset($_GET['id'])) {
            header("Location: " . BASE_URL . "?page=Noticia&type=publico");
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
        $extra_css = [BASE_URL . '/assets/css/noticias.css?v=' . time()];
        
        require_once BASE_PATH . '/resources/views/layout/head.php';
        
        $hideSidebar = true;
        $datos = $_SESSION['user'] ?? null;
        require_once BASE_PATH . '/resources/views/layout/menu.php';

        require_once BASE_PATH . '/resources/views/noticias/show.php';

        echo '</div></main>';

        require_once BASE_PATH . '/resources/views/layout/footer.php';
}
