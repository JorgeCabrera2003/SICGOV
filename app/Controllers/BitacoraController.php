<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\Security\Bitacora;

// ===== 1. VERIFICAR SESIÓN Y PERMISOS CON HELPER =====
Helper::verificarSesion();

$permisosBitacora = Helper::TraerPermisos("bitacora");
$esAdmin = isset($_SESSION['user']['rol']) && in_array(strtolower($_SESSION['user']['rol']), ['superusuario', 'administrador']);

if (!$esAdmin && (!isset($permisosBitacora['bitacora']['ver']) || $permisosBitacora['bitacora']['ver'] != 1)) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        header("HTTP/1.1 403 Forbidden");
        echo json_encode(['error' => 'Acceso no autorizado al módulo de bitácora']);
        exit();
    } else {
        header('Location: ?page=Dashboard');
        exit();
    }
}

// ===== 2. CREAR MODELO =====
$bitacora = new Bitacora();

// ===== 3. DETECTAR TIPO DE PETICIÓN =====
$esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$accion = $_GET['action'] ?? $_POST['action'] ?? '';

// Función auxiliar para generar HTML de acciones
if (!function_exists('App\Controllers\generarAccionesHtmlBitacora')) {
    function generarAccionesHtmlBitacora($reg)
    {
        $id = $reg['id_bitacora'] ?? '';
        return sprintf(
            '<div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-primary btn-ver-detalle d-flex align-items-center gap-1" 
                        data-id="%s" 
                        title="Ver todo el registro"
                        data-bs-toggle="modal" 
                        data-bs-target="#modalDetalleBitacora">
                    <i class="fas fa-search-plus"></i>
                    <span>Ver Detalles</span>
                </button>
            </div>',
            $id
        );
    }
}

// ===== 4. MANEJAR PETICIONES AJAX =====
if ($esAjax) {
    header('Content-Type: application/json');
    
    // Listar registros para DataTables
    if ($accion === 'listarJson') {
        $filtros = [
            'modulo' => $_POST['modulo'] ?? $_GET['modulo'] ?? '',
            'desde' => $_POST['desde'] ?? $_GET['desde'] ?? '',
            'hasta' => $_POST['hasta'] ?? $_GET['hasta'] ?? '',
        ];
        $registros = $bitacora->Transaccion(['peticion' => 'listar', 'filtros' => $filtros]) ?: [];
        
        $data = [];
        foreach ($registros as $reg) {
            $fecha = new \DateTime($reg['fecha'] ?? 'now');
            $fechaFormateada = $fecha->format('d/m/Y H:i:s');
            
            $usuario = $reg['username'] ?? '';
            if (!empty($reg['nombre'])) {
                $usuario = $reg['nombre'] . ' ' . ($reg['apellido'] ?? '');
            }
            
            $data[] = [
                'id' => $reg['id_bitacora'] ?? '',
                'usuario' => !empty($usuario) ? $usuario : ($reg['cedula'] ?? 'Sistema'),
                'modulo' => $reg['modulo'] ?? '',
                'accion' => $reg['accion'] ?? '',
                'detalles' => $reg['detalles'] ?? '',
                'ip' => $reg['ip_address'] ?? '0.0.0.0',
                'fecha' => $fechaFormateada,
                'acciones' => \App\Controllers\generarAccionesHtmlBitacora($reg)
            ];
        }
        
        echo json_encode(['data' => $data]);
        exit();
    }
    
    // Buscar registro por ID
    if ($accion === 'buscar') {
        $id = $_POST['id'] ?? $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit();
        }

        $registro = $bitacora->Transaccion(['peticion' => 'buscar', 'id' => $id]);
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