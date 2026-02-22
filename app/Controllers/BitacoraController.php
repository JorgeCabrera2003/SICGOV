<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Security\Bitacora;

class BitacoraController
{
    public function index()
    {
        Helper::verificarSesion();

        $bitacoraModel = new Bitacora();
        $bitacoras = $bitacoraModel->Transaccion(['peticion' => 'listar']);

        Helper::cargarVista(
            'bitacora/index',
            'Bitácora - Good Vibes',
            compact('bitacoras')
        );
    }

    public function listarJson()
    {
        $this->responderJson(function() {
            Helper::verificarSesion();

            $bitacora = new Bitacora();
            $registros = $bitacora->Transaccion(['peticion' => 'listar']) ?: [];

            return [
                'data' => array_map([$this, 'formatearRegistro'], $registros)
            ];
        });
    }

    /**
     * Formatea un registro de bitácora para DataTables
     */
    private function formatearRegistro($reg)
    {
        // Formatear fecha
        $fecha = new \DateTime($reg['fecha'] ?? 'now');
        $fechaFormateada = $fecha->format('d/m/Y H:i:s');
        
        // Determinar usuario
        $usuario = $reg['username'] ?? '';
        if (!empty($reg['nombres'])) {
            $usuario = $reg['nombres'] . ' ' . ($reg['apellidos'] ?? '');
        }
        
        return [
            'id' => $reg['id_bitacora'] ?? '',
            'usuario' => !empty($usuario) ? $usuario : ($reg['cedula'] ?? 'Sistema'),
            'modulo' => $reg['modulo'] ?? '',
            'accion' => $reg['accion'] ?? '',
            'detalles' => $reg['detalles'] ?? '',
            'ip' => $reg['ip_address'] ?? '0.0.0.0',
            'fecha' => $fechaFormateada,
            'fecha_original' => $reg['fecha'] ?? '',
            'acciones' => $this->generarAccionesHtml($reg)
        ];
    }

    /**
     * Genera el HTML para las acciones (solo ver detalles)
     */
    private function generarAccionesHtml($reg)
    {
        $id = $reg['id_bitacora'] ?? '';
        
        return sprintf(
            '<div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-info border-0 btn-ver-detalle" 
                        data-id="%s" 
                        title="Ver detalles"
                        data-bs-toggle="modal" 
                        data-bs-target="#modalDetalleBitacora">
                    <i class="fas fa-eye"></i>
                </button>
            </div>',
            $id
        );
    }

    /**
     * Busca un registro específico para ver detalles
     */
    public function buscar()
    {
        $this->responderJson(function() {
            Helper::verificarSesion();

            if (empty($_GET['id'])) {
                return ['success' => false, 'message' => 'ID no proporcionado'];
            }

            $bitacora = new Bitacora();
            // Nota: Necesitarías implementar un método 'buscar' en el modelo
            // Por ahora, listamos todos y filtramos
            $todos = $bitacora->Transaccion(['peticion' => 'listar']);
            
            $registro = null;
            foreach ($todos as $r) {
                if ($r['id_bitacora'] == $_GET['id']) {
                    $registro = $r;
                    break;
                }
            }

            if ($registro) {
                return ['success' => true, 'data' => $registro];
            } else {
                return ['success' => false, 'message' => 'Registro no encontrado'];
            }
        });
    }

    private function responderJson(callable $callback)
    {
        header('Content-Type: application/json');

        try {
            $resultado = $callback();
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}