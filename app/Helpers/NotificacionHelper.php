<?php

namespace App\Helpers;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;
use Exception;

class NotificacionHelper extends Database
{
    /**
     * Registra una notificación para un usuario específico en la base de datos de seguridad.
     * 
     * @param string $tipo Tipo de notificación: 'INFO', 'ALERTA', 'EXITO', 'ERROR'
     * @param string $mensaje El texto del mensaje de la notificación
     * @param string $id_usuario_destino La cédula del usuario destino
     * @param string|null $titulo Título de la notificación. Si es nulo, se genera uno por defecto.
     * @return string|bool Retorna el ID de la notificación registrada o false en caso de fallo
     */
    public static function registrarNotificacion($tipo, $mensaje, $id_usuario_destino, $titulo = null)
    {
        try {
            $id_usuario_destino = trim($id_usuario_destino);
            if (preg_match('/^[0-9]{7,15}$/', $id_usuario_destino)) {
                $id_usuario_destino = 'V-' . $id_usuario_destino;
            }
            if (preg_match('/^[VEJPGvejpg]{1}[0-9]{7,15}$/', $id_usuario_destino)) {
                $id_usuario_destino = strtoupper(substr($id_usuario_destino, 0, 1)) . '-' . substr($id_usuario_destino, 1);
            }

            $instancia = new self();
            $pdo = $instancia->LlamarConexion('security');

            $id = Helper::generarId('NOT');
            $tipoUpper = strtoupper($tipo);

            // Normalización de tipos permitidos por el ENUM
            if (!in_array($tipoUpper, ['INFO', 'ALERTA', 'EXITO', 'ERROR'])) {
                $tipoUpper = 'INFO';
            }

            // Título por defecto según el tipo
            if ($titulo === null) {
                $titulo = match ($tipoUpper) {
                    'INFO' => 'Información',
                    'ALERTA' => 'Advertencia',
                    'EXITO' => 'Éxito',
                    'ERROR' => 'Error de Sistema',
                    default => 'Notificación'
                };
            }

            $sql = "INSERT INTO notificacion (id_notificacion, cedula, titulo, mensaje, tipo, leido, estatus) 
                    VALUES (:id, :cedula, :titulo, :mensaje, :tipo, 0, 1)";

            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute([
                'id' => $id,
                'cedula' => $id_usuario_destino,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => $tipoUpper
            ]);

            $instancia->DestruirConexion();
            return $resultado ? $id : false;
        } catch (Exception $e) {
            Helper::ErrorLog("Error en NotificacionHelper::registrarNotificacion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las notificaciones no leídas de un usuario.
     * 
     * @param string $id_usuario La cédula del usuario
     * @return array Arreglo con las notificaciones
     */
    public static function obtenerNoLeidas($id_usuario)
    {
        try {
            $id_usuario = trim($id_usuario);
            if (preg_match('/^[0-9]{7,15}$/', $id_usuario)) {
                $id_usuario = 'V-' . $id_usuario;
            }
            if (preg_match('/^[VEJPGvejpg]{1}[0-9]{7,15}$/', $id_usuario)) {
                $id_usuario = strtoupper(substr($id_usuario, 0, 1)) . '-' . substr($id_usuario, 1);
            }

            $instancia = new self();
            $pdo = $instancia->LlamarConexion('security');

            $sql = "SELECT id_notificacion, titulo, mensaje, tipo, fecha_envio, leido 
                    FROM notificacion 
                    WHERE cedula = :cedula AND leido = 0 AND estatus = 1 
                    ORDER BY fecha_envio DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['cedula' => $id_usuario]);
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $instancia->DestruirConexion();
            return $resultado ?: [];
        } catch (Exception $e) {
            Helper::ErrorLog("Error en NotificacionHelper::obtenerNoLeidas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las notificaciones recientes (leídas o no) de un usuario.
     * 
     * @param string $id_usuario La cédula del usuario
     * @param int $limite Límite de registros a obtener
     * @return array Arreglo con las notificaciones
     */
    public static function obtenerRecientes($id_usuario, $limite = 10)
    {
        try {
            $id_usuario = trim($id_usuario);
            if (preg_match('/^[0-9]{7,15}$/', $id_usuario)) {
                $id_usuario = 'V-' . $id_usuario;
            }
            if (preg_match('/^[VEJPGvejpg]{1}[0-9]{7,15}$/', $id_usuario)) {
                $id_usuario = strtoupper(substr($id_usuario, 0, 1)) . '-' . substr($id_usuario, 1);
            }

            $instancia = new self();
            $pdo = $instancia->LlamarConexion('security');

            $sql = "SELECT id_notificacion, titulo, mensaje, tipo, fecha_envio, leido 
                    FROM notificacion 
                    WHERE cedula = :cedula AND estatus = 1 
                    ORDER BY fecha_envio DESC 
                    LIMIT :limite";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':cedula', $id_usuario, PDO::PARAM_STR);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $instancia->DestruirConexion();
            return $resultado ?: [];
        } catch (Exception $e) {
            Helper::ErrorLog("Error en NotificacionHelper::obtenerRecientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna la cantidad de notificaciones sin leer de un usuario.
     * 
     * @param string $id_usuario La cédula del usuario
     * @return int Cantidad de notificaciones no leídas
     */
    public static function obtenerCantidadNoLeidas($id_usuario)
    {
        try {
            $id_usuario = trim($id_usuario);
            if (preg_match('/^[0-9]{7,15}$/', $id_usuario)) {
                $id_usuario = 'V-' . $id_usuario;
            }
            if (preg_match('/^[VEJPGvejpg]{1}[0-9]{7,15}$/', $id_usuario)) {
                $id_usuario = strtoupper(substr($id_usuario, 0, 1)) . '-' . substr($id_usuario, 1);
            }

            $instancia = new self();
            $pdo = $instancia->LlamarConexion('security');

            $sql = "SELECT COUNT(*) as total FROM notificacion WHERE cedula = :cedula AND leido = 0 AND estatus = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['cedula' => $id_usuario]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            $instancia->DestruirConexion();
            return $fila ? (int)$fila['total'] : 0;
        } catch (Exception $e) {
            Helper::ErrorLog("Error en NotificacionHelper::obtenerCantidadNoLeidas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Marca una notificación individual como leída.
     * 
     * @param string $id_notificacion El ID de la notificación
     * @return bool Verdadero si la operación fue exitosa
     */
    public static function marcarComoLeida($id_notificacion)
    {
        try {
            $instancia = new self();
            $pdo = $instancia->LlamarConexion('security');

            $sql = "UPDATE notificacion 
                    SET leido = 1, fecha_leido = NOW() 
                    WHERE id_notificacion = :id AND estatus = 1";

            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute(['id' => $id_notificacion]);

            $instancia->DestruirConexion();
            return $resultado;
        } catch (Exception $e) {
            Helper::ErrorLog("Error en NotificacionHelper::marcarComoLeida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca todas las notificaciones pendientes de un usuario como leídas.
     * 
     * @param string $id_usuario La cédula del usuario
     * @return bool Verdadero si la operación fue exitosa
     */
    public static function marcarTodasComoLeidas($id_usuario)
    {
        try {
            $id_usuario = trim($id_usuario);
            if (preg_match('/^[0-9]{7,15}$/', $id_usuario)) {
                $id_usuario = 'V-' . $id_usuario;
            }
            if (preg_match('/^[VEJPGvejpg]{1}[0-9]{7,15}$/', $id_usuario)) {
                $id_usuario = strtoupper(substr($id_usuario, 0, 1)) . '-' . substr($id_usuario, 1);
            }

            $instancia = new self();
            $pdo = $instancia->LlamarConexion('security');

            $sql = "UPDATE notificacion 
                    SET leido = 1, fecha_leido = NOW() 
                    WHERE cedula = :cedula AND leido = 0 AND estatus = 1";

            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute(['cedula' => $id_usuario]);

            $instancia->DestruirConexion();
            return $resultado;
        } catch (Exception $e) {
            Helper::ErrorLog("Error en NotificacionHelper::marcarTodasComoLeidas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía una notificación a todos los usuarios activos en el sistema.
     * 
     * @param string $tipo Tipo de notificación: 'INFO', 'ALERTA', 'EXITO', 'ERROR'
     * @param string $mensaje El texto del mensaje de la notificación
     * @param string|null $titulo Título de la notificación.
     * @return bool Verdadero si se procesó correctamente
     */
    public static function notificarATodos($tipo, $mensaje, $titulo = null)
    {
        try {
            $instancia = new self();
            $pdo = $instancia->LlamarConexion('security');

            // 1. Obtener todas las cédulas de usuarios activos
            $sql = "SELECT cedula FROM usuario WHERE estatus = 1";
            $stmt = $pdo->query($sql);
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($usuarios)) {
                $instancia->DestruirConexion();
                return false;
            }

            $instancia->DestruirConexion();

            // 2. Registrar la notificación para cada usuario
            $exito = true;
            foreach ($usuarios as $u) {
                $resultado = self::registrarNotificacion($tipo, $mensaje, $u['cedula'], $titulo);
                if (!$resultado) {
                    $exito = false;
                }
            }

            return $exito;
        } catch (Exception $e) {
            Helper::ErrorLog("Error en NotificacionHelper::notificarATodos: " . $e->getMessage());
            return false;
        }
    }
}
