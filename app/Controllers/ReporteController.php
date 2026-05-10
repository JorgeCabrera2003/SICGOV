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
            'Centro de Reportes - SICGOV'
        );
    }

    private function generarReporte(string $tipo)
    {
        $reportService = new ReportService();
        $datosUsuario = Helper::getDatosUsuario();
        
        $info = [
            'usuario' => $datosUsuario['nombres'] . ' ' . $datosUsuario['apellidos'],
            'titulo' => 'Reporte del Sistema',
            'subtitulo' => 'Información generada dinámicamente'
        ];

        $columns = [];
        $data = [];
        $config = ['orientation' => 'portrait', 'paper' => 'letter'];

        switch ($tipo) {
            case 'reservaciones':
                $model = new Reservacion();
                $res = $model->Transaccion(['peticion' => 'listar']);
                $info['titulo'] = 'Listado de Reservaciones';
                $info['subtitulo'] = 'Historial completo de citas registradas';
                $columns = ['ID', 'Fecha', 'Hora Inicio', 'Hora Fin', 'Cliente', 'Estado'];
                
                if ($res['estado'] == 1) {
                    foreach ($res['response']['datos'] as $r) {
                        $data[] = [
                            $r['id'],
                            date('d/m/Y', strtotime($r['start'])),
                            date('h:i A', strtotime($r['start'])),
                            date('h:i A', strtotime($r['end'])),
                            $r['title'],
                            $r['extendedProps']['estado'] ?? 'N/A'
                        ];
                    }
                }
                break;

            case 'usuarios':
                $model = new Usuario();
                $res = $model->Transaccion(['peticion' => 'consultar']);
                $info['titulo'] = 'Reporte de Usuarios del Sistema';
                $info['subtitulo'] = 'Personal con acceso administrativo';
                $columns = ['Cédula', 'Username', 'Nombres', 'Apellidos', 'Rol'];
                
                if ($res['estado'] == 1) {
                    foreach ($res['response']['datos'] as $u) {
                        $data[] = [
                            $u['cedula'],
                            $u['username'],
                            $u['nombres'],
                            $u['apellidos'],
                            $u['rol']
                        ];
                    }
                }
                break;

            case 'productos':
                $model = new Producto();
                $res = $model->ConsultarTodos();
                $info['titulo'] = 'Inventario de Productos (Menú)';
                $info['subtitulo'] = 'Listado de platos y productos disponibles';
                $columns = ['ID', 'Nombre', 'Categoría', 'Precio'];
                
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
                break;

            default:
                header("Location: " . BASE_URL . "/?page=reportes&error=tipo_invalido");
                return;
        }

        $reportService->setup($info, $columns, $data, $config)->render("Reporte_{$tipo}_" . date('Ymd') . ".pdf");
    }
}
