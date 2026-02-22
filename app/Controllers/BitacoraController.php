<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Security\Bitacora;

class BitacoraController
{
    public function index()
    {
        // ===== 1. VERIFICAR SESIÓN CON HELPER =====
        Helper::verificarSesion();

        // ===== 2. CREAR MODELO =====
        $bitacora = new Bitacora();

        // ===== 3. DETECTAR TIPO DE PETICIÓN =====
        $esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        $accion = $_GET['action'] ?? $_POST['action'] ?? '';

        // ===== 4. MANEJAR PETICIONES AJAX =====
        if ($esAjax) {
            header('Content-Type: application/json');
            
            // Listar registros para DataTables
            if ($accion === 'listarJson') {
                $registros = $bitacora->Transaccion(['peticion' => 'listar']) ?: [];
                
                $data = [];
                foreach ($registros as $reg) {
                    $fecha = new \DateTime($reg['fecha'] ?? 'now');
                    $fechaFormateada = $fecha->format('d/m/Y H:i:s');
                    
                    $usuario = $reg['username'] ?? '';
                    if (!empty($reg['nombres'])) {
                        $usuario = $reg['nombres'] . ' ' . ($reg['apellidos'] ?? '');
                    }
                    
                    $data[] = [
                        'id' => $reg['id_bitacora'] ?? '',
                        'usuario' => !empty($usuario) ? $usuario : ($reg['cedula'] ?? 'Sistema'),
                        'modulo' => $reg['modulo'] ?? '',
                        'accion' => $reg['accion'] ?? '',
                        'detalles' => $reg['detalles'] ?? '',
                        'ip' => $reg['ip_address'] ?? '0.0.0.0',
                        'fecha' => $fechaFormateada,
                        'acciones' => $this->generarAccionesHtml($reg)
                    ];
                }
                
                echo json_encode(['data' => $data]);
                exit();
            }
            
            // Buscar registro por ID
            if ($accion === 'buscar') {
                $id = $_GET['id'] ?? '';
                if (empty($id)) {
                    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                    exit();
                }

                $todos = $bitacora->Transaccion(['peticion' => 'listar']);
                
                $registro = null;
                foreach ($todos as $r) {
                    if ($r['id_bitacora'] == $id) {
                        $registro = $r;
                        break;
                    }
                }

                if ($registro) {
                    echo json_encode(['success' => true, 'data' => $registro]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
                }
                exit();
            }
            
            echo json_encode(['error' => 'Acción no válida']);
            exit();
        }

        // ===== 5. PETICIÓN NORMAL - CARGAR VISTA CON HELPER =====
        Helper::cargarVista('bitacora/index', 'Bitácora del Sistema');
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
}