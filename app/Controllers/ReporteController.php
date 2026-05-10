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

        $info = [
            'usuario' => $datosUsuario['nombres'] . ' ' . $datosUsuario['apellidos'],
            'titulo' => 'Reporte del Sistema',
            'subtitulo' => 'Información generada dinámicamente',
            'resumen' => $resumen
        ];

        // Construir subtítulo con filtros si existen
        if ($fecha_inicio && $fecha_fin) {
            $info['subtitulo'] .= " | Periodo: " . date('d/m/Y', strtotime($fecha_inicio)) . " al " . date('d/m/Y', strtotime($fecha_fin));
        }

        $columns = [];
        $data = [];
        $config = ['orientation' => $orientation, 'paper' => $paper];

        $resultado = match ($tipo) {
            'reservaciones' => (function() use ($info, $fecha_inicio, $fecha_fin) {
                $model = new Reservacion();
                $filtros = ($fecha_inicio && $fecha_fin) ? ['desde' => $fecha_inicio, 'hasta' => $fecha_fin] : [];
                $res = $model->Transaccion(['peticion' => 'listar', 'filtros' => $filtros]);
                
                $info['titulo'] = 'Listado de Reservaciones';
                $columns = ['Fecha', 'Hora Inicio', 'Hora Fin', 'Cliente', 'Estado'];
                $data = [];

                if ($res['estado'] == 1) {
                    foreach ($res['response']['datos'] as $r) {
                        $data[] = [
                            date('d/m/Y', strtotime($r['start'])),
                            date('h:i A', strtotime($r['start'])),
                            date('h:i A', strtotime($r['end'])),
                            $r['title'],
                            $r['extendedProps']['estado'] ?? 'N/A'
                        ];
                    }
                }
                return [$info, $columns, $data];
            })(),

            'usuarios' => (function() use ($info) {
                $model = new Usuario();
                $res = $model->Transaccion(['peticion' => 'consultar']);
                
                $info['titulo'] = 'Reporte de Usuarios del Sistema';
                $columns = ['Cédula', 'Username', 'Nombres', 'Apellidos', 'Rol'];
                $data = [];

                if ($res['estado'] == 1) {
                    foreach ($res['response']['datos'] as $u) {
                        $data[] = [$u['cedula'], $u['username'], $u['nombres'], $u['apellidos'], $u['rol']];
                    }
                }
                return [$info, $columns, $data];
            })(),

            'productos' => (function() use ($info) {
                $model = new Producto();
                $res = $model->ConsultarTodos();
                
                $info['titulo'] = 'Inventario de Productos (Menú)';
                $columns = ['ID', 'Nombre', 'Categoría', 'Precio'];
                $data = [];

                if ($res['estado'] == 1) {
                    foreach ($res['response']['datos'] as $p) {
                        $data[] = [
                            $p['id_producto'],
                            $p['nombre_producto'],
                            $p['nombre_categoria'],
                            'Bs. ' . number_format($p['precio_producto'], 2)
                        ];
                    }
                }
                return [$info, $columns, $data];
            })(),

            default => null
        };

        if (!$resultado) {
            header("Location: " . BASE_URL . "/?page=reportes&error=tipo_invalido");
            return;
        }

        [$info, $columns, $data] = $resultado;
        $reportService->setup($info, $columns, $data, $config)->render("Reporte_{$tipo}_" . date('Ymd') . ".pdf");
    }


}
