<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Helpers\RegexHelper;
use App\Models\Security\Usuario;
use App\Core\Database;
use Exception;
use PDO;

$type = $_REQUEST['type'] ?? 'index';

if ($type === 'forzar-cambiar-clave') {
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
    } elseif ($type === 'index') {
        Helper::verificarSesion();

        $user = $_SESSION['user'];
        $cedula = $user['cedula'] ?? '';

        // El superusuario admin_root V-00000000 no tiene permitido tener/acceder a "Mi perfil"
        if ($cedula === 'V-00000000') {
            header("Location: " . BASE_URL . "/?page=Dashboard");
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

                // Validación
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
                if (empty($direccion) || strlen($direccion) < 3) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Dirección inválida (mínimo 3 caracteres)']);
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
                    
                    // Obtener valores actuales para registrar en la bitácora
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

                    // Escribir en la Bitácora (registro de auditoría)
                    Helper::Bitacora('MODIFICAR', 'PERFIL', 'Usuario actualizó su información personal de perfil', $old_data, $new_data);

                    // Actualizar sesión
                    $_SESSION['user']['nombre'] = $nombre;
                    $_SESSION['user']['apellido'] = $apellido;
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

            if ($peticion === 'actualizar-username') {
                $username = trim($_POST['username'] ?? '');

                if (empty($username) || strlen($username) < 3) {
                    echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El nombre de usuario debe tener al menos 3 caracteres']);
                    exit;
                }

                try {
                    $db = Database::getConnection('security');
                    
                    // Verificar si el username ya existe en otro usuario
                    $stmtCheck = $db->prepare("SELECT cedula FROM usuario WHERE username = :username AND cedula != :cedula");
                    $stmtCheck->execute(['username' => $username, 'cedula' => $cedula]);
                    if ($stmtCheck->rowCount() > 0) {
                        echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'El nombre de usuario ya está en uso']);
                        exit;
                    }

                    // Actualizar username
                    $stmtUpdate = $db->prepare("UPDATE usuario SET username = :username WHERE cedula = :cedula");
                    $stmtUpdate->execute(['username' => $username, 'cedula' => $cedula]);

                    $_SESSION['user']['username'] = $username;

                    // Bitacora
                    Helper::Bitacora('MODIFICAR', 'USUARIOS', 'Usuario cambió su nombre de usuario');

                    echo json_encode(['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Nombre de usuario actualizado exitosamente']);
                } catch (Exception $e) {
                    Helper::ErrorLog("Error cambiando nombre de usuario: " . $e->getMessage());
                    echo json_encode(['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error interno al actualizar el nombre de usuario']);
                }
                exit;
            }

            if ($peticion === 'cambiar-clave') {
                $clave_nueva = $_POST['clave_nueva'] ?? '';
                $clave_confirmar = $_POST['clave_confirmar'] ?? '';

                if (empty($clave_nueva) || empty($clave_confirmar)) {
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

                    if (!$user_db) {
                        echo json_encode(['resultado' => 400, 'icon' => 'error', 'mensaje' => 'Usuario no encontrado']);
                        exit;
                    }

                    $hashed_clave = password_hash($clave_nueva, PASSWORD_DEFAULT);
                    $stmtUpdate = $db->prepare("UPDATE usuario SET clave = :clave WHERE cedula = :cedula");
                    $stmtUpdate->execute(['clave' => $hashed_clave, 'cedula' => $cedula]);

                    // Bitacora
                    Helper::Bitacora('MODIFICAR', 'SEGURIDAD', 'Usuario cambió su contraseña de acceso');

                    echo json_encode(['resultado' => 200, 'icon' => 'success', 'mensaje' => 'Contraseña cambiada exitosamente']);
                } catch (Exception $e) {
                    Helper::ErrorLog("Error cambiando clave: " . $e->getMessage());
                    echo json_encode(['resultado' => 500, 'icon' => 'error', 'mensaje' => 'Error interno al cambiar la contraseña']);
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

        // Renderizar la vista del perfil
        try {
            $usuarioModel = new Usuario();
            $usuarioModel->setCedula($cedula);
            $datosPerfil = $usuarioModel->Transaccion(['peticion' => 'perfil']);
            
            $perfil = $datosPerfil['response']['datos'] ?? null;
            if (!$perfil) {
                // Usar sesión como respaldo
                $perfil = $user;
            }

            // Obtener banner de portada de la tabla de imágenes
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
                // Fallar silenciosamente
            }

            $vars = [
                'perfil' => $perfil,
                'portada' => $portada,
                'hideSidebar' => (strtoupper($perfil['rol'] ?? '') === 'CLIENTE'),
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
