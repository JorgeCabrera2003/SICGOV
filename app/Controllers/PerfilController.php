<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\Security\Usuario;
use App\Core\Database;
use Exception;
use PDO;

class PerfilController
{
    public function forzarCambioClave()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/?page=login");
            exit;
        }

        $user = $_SESSION['user'];
        $cedula = $user['cedula'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $peticion = $_POST['peticion'] ?? '';

            if ($peticion === 'forzar-cambiar-clave') {
                $clave_nueva = $_POST['clave_nueva'] ?? '';
                $clave_confirmar = $_POST['clave_confirmar'] ?? '';

                if (empty($clave_nueva) || empty($clave_confirmar)) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Ambos campos son obligatorios']);
                    exit;
                }

                if (strlen($clave_nueva) < 8 || 
                    !preg_match('/[A-Z]/', $clave_nueva) || 
                    !preg_match('/[0-9]/', $clave_nueva) || 
                    !preg_match('/[\W_]/', $clave_nueva)) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'La contraseña no cumple con los requisitos de seguridad']);
                    exit;
                }

                if ($clave_nueva !== $clave_confirmar) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Las contraseñas no coinciden']);
                    exit;
                }

                try {
                    $db = Database::getConnection('security');
                    $hashed_clave = password_hash($clave_nueva, PASSWORD_DEFAULT);
                    // Actualizar clave y cambiar estatus_clave a 1
                    $stmtUpdate = $db->prepare("UPDATE usuario SET clave = :clave, estatus_clave = 1 WHERE cedula = :cedula");
                    $stmtUpdate->execute(['clave' => $hashed_clave, 'cedula' => $cedula]);

                    $_SESSION['user']['estatus_clave'] = 1;

                    Helper::Bitacora('Modificar', 'Seguridad', 'Usuario realizó el cambio obligatorio de contraseña');

                    echo json_encode(['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Contraseña cambiada exitosamente']);
                } catch (Exception $e) {
                    Helper::ErrorLog("Error forzando cambio de clave: " . $e->getMessage());
                    echo json_encode(['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error interno al cambiar la contraseña']);
                }
                exit;
            }
        }

        $titulo = 'Cambio de Contraseña Requerido';
        require_once BASE_PATH . '/resources/views/auth/forzar_cambio_clave.php';
    }

    public function index()
    {
        Helper::verificarSesion();

        $user = $_SESSION['user'];
        $cedula = $user['cedula'] ?? '';

        // Superuser admin_root V-00000000 is not allowed to have/access "Mi perfil"
        if ($cedula === 'V-00000000') {
            header("Location: " . BASE_URL . "/?page=home");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $peticion = $_POST['peticion'] ?? '';

            if ($peticion === 'actualizar-perfil') {
                $nombre = trim($_POST['nombre'] ?? '');
                $apellido = trim($_POST['apellido'] ?? '');
                $correo = trim($_POST['correo'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $direccion = trim($_POST['direccion'] ?? '');
                $sexo = trim($_POST['sexo'] ?? '');
                $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');

                // Validation
                if (empty($nombre) || RegexHelper::ValidarFormatos($nombre, 'Persona') == 0) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Nombre inválido (letras, de 3 a 65 caracteres)']);
                    exit;
                }
                if (empty($apellido) || RegexHelper::ValidarFormatos($apellido, 'Persona') == 0) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Apellido inválido (letras, de 3 a 65 caracteres)']);
                    exit;
                }
                if (empty($correo) || RegexHelper::ValidarFormatos($correo, 'Correo') == 0) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Formato de correo electrónico inválido']);
                    exit;
                }
                if (empty($telefono) || RegexHelper::ValidarFormatos($telefono, 'Telefono') == 0) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Teléfono inválido (formato: XXXX-XXXXXXX)']);
                    exit;
                }
                if (empty($direccion) || RegexHelper::ValidarFormatos($direccion, 'Direccion') == 0) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Dirección inválida (mínimo 10 caracteres)']);
                    exit;
                }
                if (empty($sexo) || RegexHelper::ValidarFormatos($sexo, 'Sexo') == 0) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Sexo inválido (debe ser M o F)']);
                    exit;
                }
                if (empty($fecha_nacimiento) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento) || $fecha_nacimiento >= date('Y-m-d')) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Fecha de nacimiento debe ser una fecha pasada válida (YYYY-MM-DD)']);
                    exit;
                }

                try {
                    $db = Database::getConnection('business');
                    
                    // Fetch current values to log to audit trail
                    $stmtSelect = $db->prepare("SELECT nombre, apellido, correo, telefono, direccion, sexo, fecha_nacimiento FROM persona WHERE cedula = :cedula");
                    $stmtSelect->execute(['cedula' => $cedula]);
                    $old_data = $stmtSelect->fetch(PDO::FETCH_ASSOC);

                    $stmt = $db->prepare("UPDATE persona SET nombre = :nombre, apellido = :apellido, correo = :correo, telefono = :telefono, direccion = :direccion, sexo = :sexo, fecha_nacimiento = :fecha_nacimiento WHERE cedula = :cedula");
                    $stmt->execute([
                        'nombre' => $nombre,
                        'apellido' => $apellido,
                        'correo' => $correo,
                        'telefono' => $telefono,
                        'direccion' => $direccion,
                        'sexo' => $sexo,
                        'fecha_nacimiento' => $fecha_nacimiento,
                        'cedula' => $cedula
                    ]);

                    $new_data = [
                        'nombre' => $nombre,
                        'apellido' => $apellido,
                        'correo' => $correo,
                        'telefono' => $telefono,
                        'direccion' => $direccion,
                        'sexo' => $sexo,
                        'fecha_nacimiento' => $fecha_nacimiento
                    ];

                    // Write to Bitacora (audit log)
                    Helper::Bitacora('Modificar', 'Perfil', 'Usuario actualizó su información personal de perfil', $old_data, $new_data);

                    // Update session
                    $_SESSION['user']['nombre'] = $nombre;
                    $_SESSION['user']['nombres'] = $nombre;
                    $_SESSION['user']['apellido'] = $apellido;
                    $_SESSION['user']['apellidos'] = $apellido;
                    $_SESSION['user']['correo'] = $correo;
                    $_SESSION['user']['telefono'] = $telefono;
                    $_SESSION['user']['direccion'] = $direccion;
                    $_SESSION['user']['sexo'] = $sexo;
                    $_SESSION['user']['fecha_nacimiento'] = $fecha_nacimiento;

                    echo json_encode(['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Perfil actualizado exitosamente']);
                } catch (Exception $e) {
                    Helper::ErrorLog("Error actualizando perfil: " . $e->getMessage());
                    echo json_encode(['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error interno al actualizar perfil']);
                }
                exit;
            }

            if ($peticion === 'cambiar-clave') {
                $clave_actual = $_POST['clave_actual'] ?? '';
                $clave_nueva = $_POST['clave_nueva'] ?? '';
                $clave_confirmar = $_POST['clave_confirmar'] ?? '';

                if (empty($clave_actual) || empty($clave_nueva) || empty($clave_confirmar)) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Todos los campos de contraseña son obligatorios']);
                    exit;
                }

                if (strlen($clave_nueva) < 4) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'La nueva contraseña debe tener al menos 4 caracteres']);
                    exit;
                }

                if ($clave_nueva !== $clave_confirmar) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'La nueva contraseña y su confirmación no coinciden']);
                    exit;
                }

                try {
                    $db = Database::getConnection('security');
                    $stmtSelect = $db->prepare("SELECT clave FROM usuario WHERE cedula = :cedula");
                    $stmtSelect->execute(['cedula' => $cedula]);
                    $user_db = $stmtSelect->fetch(PDO::FETCH_ASSOC);

                    if (!$user_db || !password_verify($clave_actual, $user_db['clave'])) {
                        echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'La contraseña actual es incorrecta']);
                        exit;
                    }

                    $hashed_clave = password_hash($clave_nueva, PASSWORD_DEFAULT);
                    $stmtUpdate = $db->prepare("UPDATE usuario SET clave = :clave WHERE cedula = :cedula");
                    $stmtUpdate->execute(['clave' => $hashed_clave, 'cedula' => $cedula]);

                    // Bitacora
                    Helper::Bitacora('Modificar', 'Seguridad', 'Usuario cambió su contraseña de acceso');

                    echo json_encode(['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Contraseña cambiada exitosamente']);
                } catch (Exception $e) {
                    Helper::ErrorLog("Error cambiando clave: " . $e->getMessage());
                    echo json_encode(['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error interno al cambiar la contraseña']);
                }
                exit;
            }

            if ($peticion === 'subir-avatar' || $peticion === 'subir-portada') {
                $es_principal = ($peticion === 'subir-avatar') ? 1 : 0;
                $fileKey = ($peticion === 'subir-avatar') ? 'foto' : 'portada';

                if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'No se recibió ninguna imagen o hubo un error al cargarla']);
                    exit;
                }

                $file = $_FILES[$fileKey];
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'jfif'];

                if (!in_array($extension, $allowed)) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Formato de imagen no permitido. Formatos permitidos: JPG, PNG, WEBP, GIF, JFIF']);
                    exit;
                }

                if ($file['size'] > 5 * 1024 * 1024) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El tamaño máximo de imagen permitido es de 5 MB']);
                    exit;
                }

                $prefix = ($peticion === 'subir-avatar') ? 'avatar_' : 'cover_';
                $cleanCedula = str_replace('-', '', $cedula);
                $target_dir = BASE_PATH . DS . 'public' . DS . 'assets' . DS . 'img' . DS . 'perfiles';
                
                // Ensure target directory exists
                if (!is_dir($target_dir)) {
                    @mkdir($target_dir, 0777, true);
                }

                $nombre_archivo = $prefix . $cleanCedula . '_' . time();
                $final_extension = 'webp';
                $target_file = $target_dir . DS . $nombre_archivo . '.webp';
                $direccion_db = '/assets/img/perfiles/' . $nombre_archivo . '.webp';
                
                $upload_success = false;

                if (function_exists('imagewebp') && Helper::convertirAWebP($file['tmp_name'], $target_file)) {
                    $upload_success = true;
                } else {
                    // Fallback to original image format and standard upload
                    $final_extension = $extension;
                    $target_file = $target_dir . DS . $nombre_archivo . '.' . $final_extension;
                    $direccion_db = '/assets/img/perfiles/' . $nombre_archivo . '.' . $final_extension;
                    if (move_uploaded_file($file['tmp_name'], $target_file)) {
                        $upload_success = true;
                    }
                }

                if ($upload_success) {
                    try {
                        $db = Database::getConnection('security');
                        
                        // Check if entry already exists in the polymorphic image table
                        $stmtCheck = $db->prepare("SELECT id_imagen, direccion FROM imagen WHERE entidad_tipo = 'USUARIO' AND entidad_id = :cedula AND es_principal = :es_principal LIMIT 1");
                        $stmtCheck->execute(['cedula' => $cedula, 'es_principal' => $es_principal]);
                        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                        if ($existing) {
                            // Update entry
                            $stmtUpdate = $db->prepare("UPDATE imagen SET direccion = :direccion WHERE id_imagen = :id_imagen");
                            $stmtUpdate->execute(['direccion' => $direccion_db, 'id_imagen' => $existing['id_imagen']]);
                            
                            // Try deleting the old physical file to prevent storage bloat
                            $old_file_path = BASE_PATH . DS . 'public' . str_replace('/', DS, $existing['direccion']);
                            if (file_exists($old_file_path)) {
                                @unlink($old_file_path);
                            }
                        } else {
                            // Insert entry
                            $id_imagen = Helper::generarId('IMG', 'USR');
                            $stmtInsert = $db->prepare("INSERT INTO imagen (id_imagen, entidad_tipo, entidad_id, direccion, orden, es_principal) VALUES (:id_imagen, 'USUARIO', :cedula, :direccion, 1, :es_principal)");
                            $stmtInsert->execute([
                                'id_imagen' => $id_imagen,
                                'cedula' => $cedula,
                                'direccion' => $direccion_db,
                                'es_principal' => $es_principal
                            ]);
                        }

                        // Bitacora
                        $label = ($peticion === 'subir-avatar') ? 'foto de perfil' : 'foto de portada';
                        Helper::Bitacora('Modificar', 'Perfil', "Usuario actualizó su {$label}");

                        $img_full_url = BASE_URL . $direccion_db;

                        echo json_encode([
                            'resultado' => 200, 
                            'icon' => 'success', 
                            'mensaje' => 'Imagen cargada y procesada exitosamente', 
                            'url' => $img_full_url
                        ]);
                    } catch (Exception $e) {
                        Helper::ErrorLog("Error actualizando base de datos para imagen: " . $e->getMessage());
                        echo json_encode(['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error interno al guardar la imagen en base de datos']);
                    }
                } else {
                    echo json_encode(['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error al procesar y cargar la imagen en el servidor']);
                }
                exit;
            }

            if ($peticion === 'obtener-actividad') {
                try {
                    $db = Database::getConnection('security');
                    $stmt = $db->prepare("SELECT id_bitacora, modulo, accion, detalle, ip_address, fecha FROM bitacora WHERE cedula = :cedula ORDER BY fecha DESC LIMIT 15");
                    $stmt->execute(['cedula' => $cedula]);
                    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo json_encode(['resultado' => 200, 'datos' => $actividades]);
                } catch (Exception $e) {
                    Helper::ErrorLog("Error obteniendo actividad de bitácora: " . $e->getMessage());
                    echo json_encode(['resultado' => 500, 'mensaje' => 'Error interno al obtener actividad']);
                }
                exit;
            }

            echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Petición no válida']);
            exit;
        }

        // Render profile view
        try {
            $usuarioModel = new Usuario();
            $usuarioModel->setCedula($cedula);
            $datosPerfil = $usuarioModel->Transaccion(['peticion' => 'perfil']);
            
            $perfil = $datosPerfil['response']['datos'] ?? null;
            if (!$perfil) {
                // Fallback to session
                $perfil = $user;
            }

            // Get cover banner from the image table
            $portada = '';
            try {
                $db = Database::getConnection('security');
                $stmtCover = $db->prepare("SELECT direccion FROM imagen WHERE entidad_tipo = 'USUARIO' AND entidad_id = :cedula AND es_principal = 0 LIMIT 1");
                $stmtCover->execute(['cedula' => $cedula]);
                $cover = $stmtCover->fetch(PDO::FETCH_ASSOC);
                if ($cover && !empty($cover['direccion'])) {
                    $portada = BASE_URL . $cover['direccion'];
                }
            } catch (Exception $e) {
                // Fail silently
            }

            $vars = [
                'perfil' => $perfil,
                'portada' => $portada,
                'extra_css' => [
                    BASE_URL . 'assets/css/perfil.css'
                ]
            ];

            Helper::cargarVista('perfil/index', 'Mi Perfil - Good Vibes', $vars);
        } catch (Exception $e) {
            Helper::ErrorLog("Error cargando perfil vista: " . $e->getMessage());
            die("Error crítico al cargar perfil.");
        }
    }
}
