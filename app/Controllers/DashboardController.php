<?php
namespace App\Controllers;

use App\Helpers\Helper;
use App\Models\System\Estadistica;
use App\Models\System\Mesas;
use App\Models\System\Reservacion;
use App\Models\System\Pedido;
use Exception;

Helper::verificarSesion();
$type = $_REQUEST['type'] ?? 'index';

if ($type === 'index' || $type === 'variables') {
    $datosUsuario = Helper::getDatosUsuario();
    $fechaHoy = date('Y-m-d');

    // 1. Estadísticas del sistema (Modelo Estadistica)
    $kpis = [];
    $productosCategoria = ['labels' => [], 'values' => []];
    $ingredientesAlerta = ['labels' => [], 'actual' => [], 'minimo' => []];
    $topProductos = ['labels' => [], 'values' => []];
    $reservacionesMes = ['labels' => [], 'values' => []];

    try {
        $estadisticaModel = new Estadistica();
        $estadisticasCompletas = $estadisticaModel->ObtenerDatosDashboard([]);
        $kpis = $estadisticasCompletas['kpis'] ?? [];
        $productosCategoria = $estadisticasCompletas['productosCategoria'] ?? $productosCategoria;
        $ingredientesAlerta = $estadisticasCompletas['ingredientesAlerta'] ?? $ingredientesAlerta;
        $topProductos = $estadisticasCompletas['topProductos'] ?? $topProductos;
        $reservacionesMes = $estadisticasCompletas['reservacionesMes'] ?? $reservacionesMes;
    } catch (Exception $e) {
        error_log("Error cargando estadísticas en DashboardController: " . $e->getMessage());
    }

    // 2. Estado de Mesas (Modelo Mesas)
    $totalMesas = 0;
    $mesasOcupadas = 0;
    $mesasDisponibles = 0;
    $porcentajeOcupacion = 0;

    try {
        $mesaModel = new Mesas();
        $resMesas = $mesaModel->Transaccion(['peticion' => 'consultar']);
        $mesas = $resMesas['datos'] ?? $resMesas['response']['datos'] ?? [];
        $totalMesas = count($mesas);
        foreach ($mesas as $m) {
            $est = strtoupper($m['estado'] ?? 'DISPONIBLE');
            if ($est === 'OCUPADA') {
                $mesasOcupadas++;
            } else {
                $mesasDisponibles++;
            }
        }
        if ($totalMesas > 0) {
            $porcentajeOcupacion = round(($mesasOcupadas / $totalMesas) * 100);
        }
    } catch (Exception $e) {
        error_log("Error consultando mesas en DashboardController: " . $e->getMessage());
    }

    // 3. Reservaciones del Día (Modelo Reservacion)
    $reservasHoy = [];
    try {
        $reservaModel = new Reservacion();
        $resReservas = $reservaModel->Transaccion([
            'peticion' => 'listar',
            'filtros' => [
                'desde' => $fechaHoy,
                'hasta' => $fechaHoy
            ]
        ]);
        
        $listaReservas = $resReservas['response']['datos'] ?? $resReservas['datos'] ?? (isset($resReservas[0]) ? $resReservas : []);

        if (is_array($listaReservas)) {
            foreach ($listaReservas as $rev) {
                if (!is_array($rev)) continue;

                // Extraer hora
                $hora = $rev['hora'] ?? '';
                if (empty($hora) && !empty($rev['start'])) {
                    $partes = explode('T', $rev['start']);
                    $hora = $partes[1] ?? '';
                }

                // Extraer nombre y apellido
                $nombre = $rev['nombre'] ?? '';
                $apellido = $rev['apellido'] ?? '';
                if (empty($nombre) && !empty($rev['title'])) {
                    $limpio = preg_replace('/\s*\(Mesa.*?\)/i', '', $rev['title']);
                    $nombre = trim($limpio);
                }

                // Extraer telefono
                $telefono = $rev['telefono'] ?? ($rev['extendedProps']['telefono'] ?? 'Sin contacto');

                // Extraer estado
                $estado = $rev['estado'] ?? ($rev['extendedProps']['estado'] ?? 'PENDIENTE');

                // Extraer mesa
                $numeroMesa = $rev['numero_mesa'] ?? ($rev['extendedProps']['numero_mesa'] ?? null);

                $reservasHoy[] = [
                    'id_reservacion' => $rev['id'] ?? ($rev['id_reservacion'] ?? ''),
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'hora' => $hora,
                    'telefono' => $telefono,
                    'estado' => $estado,
                    'numero_mesa' => $numeroMesa
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Error consultando reservaciones en DashboardController: " . $e->getMessage());
    }

    // 4. Pedidos e Ingresos (Modelo Pedido)
    $pedidosHoy = 0;
    $ingresosHoy = 0.0;
    $pedidosPendientes = 0;
    $pedidosEntregados = 0;
    $pedidosRecientes = [];

    try {
        $pedidoModel = new Pedido();
        $todosPedidos = $pedidoModel->Transaccion(['peticion' => 'listar']);
        if (is_array($todosPedidos)) {
            $pedidosRecientes = array_slice($todosPedidos, 0, 5);

            foreach ($todosPedidos as $p) {
                $fPedido = substr($p['fecha_pedido'] ?? '', 0, 10);
                if ($fPedido === $fechaHoy) {
                    $pedidosHoy++;
                    $ingresosHoy += (float)($p['total'] ?? 0);
                    $est = strtoupper($p['estado'] ?? '');
                    if (in_array($est, ['PENDIENTE', 'EN PREPARACION', 'EN_PROCESO', 'LISTO'])) {
                        $pedidosPendientes++;
                    } elseif (in_array($est, ['ENTREGADO', 'COMPLETADO'])) {
                        $pedidosEntregados++;
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error consultando pedidos en DashboardController: " . $e->getMessage());
    }

    $totalIngresosHistorico = (float)($kpis['gananciasTotales'] ?? 0);
    $totalPedidosHistorico = (int)($kpis['totalPedidos'] ?? 0);

    $vars = [
        'kpis' => $kpis,
        'totalProductos' => (int)($kpis['totalProductos'] ?? 0),
        'totalMesas' => $totalMesas,
        'mesasOcupadas' => $mesasOcupadas,
        'mesasDisponibles' => $mesasDisponibles,
        'porcentajeOcupacion' => $porcentajeOcupacion,
        'pedidosHoy' => $pedidosHoy,
        'pedidosPendientes' => $pedidosPendientes,
        'pedidosEntregados' => $pedidosEntregados,
        'ingresosHoy' => $ingresosHoy,
        'totalIngresosHistorico' => $totalIngresosHistorico,
        'totalPedidosHistorico' => $totalPedidosHistorico,
        'reservasHoy' => $reservasHoy,
        'pedidosRecientes' => $pedidosRecientes,
        'productosCategoria' => $productosCategoria,
        'ingredientesAlerta' => $ingredientesAlerta,
        'topProductos' => $topProductos,
        'reservacionesMes' => $reservacionesMes,
        'datosUsuario' => $datosUsuario,
        'extra_css' => [BASE_URL . 'assets/css/dashboard.css'],
        'extra_js_modules' => [BASE_URL . 'assets/js/dashboard.js']
    ];

    Helper::cargarVista('dashboard', 'Dashboard - Good Vibes', $vars);
}
