<?php
namespace App\Helpers;

use App\Models\Security\Bitacora;
use App\Models\Security\Usuario;

class Helper {

    /**
     * Registra un movimiento en la bitácora
     */
    public static function Bitacora($accion, $modulo) {
        $bitacora = new Bitacora();
        
        // Obtenemos la sesión actual (Namespace de tu sistema)
        if (isset($_SESSION['user'])) {
            $bitacora->set_usuario($_SESSION['user']['cedula']);
            $bitacora->set_modulo($modulo);
            $bitacora->set_accion($accion);
            
            // Enviamos fecha y hora actual juntas para el campo 'fecha' de tu DB
            $bitacora->set_fecha(date('Y-m-d'));
            $bitacora->set_hora(date('H:i:s')); 
            
            return $bitacora->Transaccion(['peticion' => 'registrar']);
        }
        return false;
    }

    /**
     * Función para enviar notificaciones a usuarios
     */
    public static function Notificar($msg, $modulo, $busqueda = 'todos') {
        // Aquí puedes adaptar la lógica de notificaciones que tenías
        // usando los nuevos modelos de tu sistema
        // ...
    }
}