<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Services\ReportService;
use App\Models\System\Reservacion;
use App\Models\Security\Usuario;
use App\Models\System\Producto;
use App\Models\System\Cliente;

class ReporteController
{
    public function index()
    {
        Helper::verificarSesion();

        if (isset($_POST['peticion']) && $_POST['peticion'] === 'generar') {
            $this->generarReporte($_POST['tipo'] ?? '');
            exit;
        }

        Helper::cargarVista(
            'reports/index',
            'Centro de Reportes - SICGOV',
            [
                'extra_css' => [BASE_URL . '/assets/css/reportes.css'],
                'extra_js_modules' => [BASE_URL . '/assets/js/Controllers/ReporteController.js']
            ]
        );
    }


    private function generarReporte(string $tipo)
    {
        $reportService = new ReportService();
        $datosUsuario = Helper::getDatosUsuario();
        
        // Parámetros de configuración
        $paper = $_POST['paper'] ?? 'letter';
        $orientation = $_POST['orientation'] ?? 'portrait';
        $resumen = $_POST['resumen'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? null;
        $fecha_fin = $_POST['fecha_fin'] ?? null;

        $logoPath = BASE_PATH . '/public/assets/img/logo.png';
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $info = [
            'usuario' => $datosUsuario['nombres'] . ' ' . $datosUsuario['apellidos'],
            'titulo' => 'Reporte del Sistema',
            'subtitulo' => 'Información generada dinámicamente',
            'resumen' => $resumen,
            'logo' => $logoBase64
        ];



        if ($fecha_inicio && $fecha_fin) {
            $info['subtitulo'] .= " | Periodo: " . date('d/m/Y', strtotime($fecha_inicio)) . " al " . date('d/m/Y', strtotime($fecha_fin));
        }

        // Definición universal del reporte
        $configReporte = match ($tipo) {
            'reservaciones' => [
                'titulo' => 'Listado de Reservaciones',
                'columns' => ['Fecha', 'Hora Inicio', 'Hora Fin', 'Cliente', 'Estado'],
                'fetch' => fn() => (new Reservacion())->Transaccion([
                    'peticion' => 'listar', 
                    'filtros' => ($fecha_inicio && $fecha_fin) ? ['desde' => $fecha_inicio, 'hasta' => $fecha_fin] : []
                ]),
                'map' => fn($r) => [
                    date('d/m/Y', strtotime($r['start'])),
                    date('h:i A', strtotime($r['start'])),
                    date('h:i A', strtotime($r['end'])),
                    $r['title'],
                    $r['extendedProps']['estado'] ?? 'N/A'
                ]
            ],
            'usuarios' => [
                'titulo' => 'Reporte de Usuarios del Sistema',
                'columns' => ['Cédula', 'Nombre Completo', 'Username', 'Rol', 'Último Acceso'],
                'fetch' => fn() => (new Usuario())->Transaccion(['peticion' => 'consultar']),
                'map' => fn($u) => [
                    $u['cedula'],
                    ($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''),
                    $u['username'],
                    $u['rol'] ?? 'N/A',
                    $u['ultimo_acceso'] ? date('d/m/Y h:i A', strtotime($u['ultimo_acceso'])) : 'Nunca'
                ]
            ],
            'productos' => [
                'titulo' => 'Inventario de Productos (Menú)',
                'columns' => ['ID', 'Nombre', 'Categoría', 'Precio'],
                'fetch' => fn() => (new Producto())->ConsultarTodos(),
                'map' => fn($p) => [
                    $p['id_producto'],
                    $p['nombre_producto'],
                    $p['nombre_categoria'],
                    'Bs. ' . number_format($p['precio_producto'], 2)
                ]
            ],
            'mesas' => [
                'titulo' => 'Reporte de Distribución de Mesas',
                'columns' => ['Número', 'Área / Ubicación', 'Capacidad', 'Estado Actual'],
                'fetch' => fn() => (new \App\Models\System\Mesas())->Transaccion(['peticion' => 'consultar']),
                'map' => fn($m) => [
                    'Mesa #' . $m['numero_mesa'],
                    $m['area_nombre'] ?? 'Sin área asignada',
                    $m['capacidad'] . ' personas',
                    $m['estado'] ?? 'DISPONIBLE'
                ]
            ],
            default => null

        };

        if (!$configReporte) {
            header("Location: " . BASE_URL . "/?page=reportes&error=tipo_invalido");
            return;
        }

        // Ejecución Universal del Reporte (Principio de Eficiencia)
        $res = $configReporte['fetch']();
        $info['titulo'] = $configReporte['titulo'];
        $data = [];

        if ($res['estado'] == 1) {
            $raw_data = $res['response']['datos'] ?? [];
            foreach ($raw_data as $row) {
                $data[] = $configReporte['map']($row);
            }
        }

        $reportService->setup($info, $configReporte['columns'], $data, ['orientation' => $orientation, 'paper' => $paper])
                      ->render("Reporte_{$tipo}_" . date('Ymd') . ".pdf");
    }



}
