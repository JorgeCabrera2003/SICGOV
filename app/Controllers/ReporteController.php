<?php

namespace App\Controllers;

use App\Helpers\Helper;
use App\Services\ReportService;
use App\Models\System\Reservacion;
use App\Models\Security\Usuario;
use App\Models\System\Producto;
use App\Models\System\Cliente;
use App\Models\System\Asistencia;
use App\Models\Security\Bitacora;
use App\Core\Database;

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

    public function indexEstadistica()
    {
        Helper::verificarSesion();

        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        if ($action === 'data') {
            header('Content-Type: application/json');
            try {
                $db = Database::getConnection('business');

                // A. Ganancias Totales
                $queryGanancias = $db->query("SELECT COALESCE(SUM(monto), 0) as total FROM pago");
                $gananciasTotales = (float) $queryGanancias->fetchColumn();

                // B. Total Pedidos
                $queryPedidos = $db->query("SELECT COUNT(*) FROM pedido");
                $totalPedidos = (int) $queryPedidos->fetchColumn();

                // C. Cantidad de Items Vendidos
                $queryItems = $db->query("SELECT COALESCE(SUM(cantidad), 0) FROM detalle_pedido");
                $totalItemsVendidos = (int) $queryItems->fetchColumn();

                // D. Producto Estrella (Top 1)
                $queryProdEstrella = $db->query("
                    SELECT p.nombre_producto, SUM(dp.cantidad) as total_vendido
                    FROM detalle_pedido dp
                    JOIN producto p ON dp.id_producto = p.id_producto
                    GROUP BY dp.id_producto, p.nombre_producto
                    ORDER BY total_vendido DESC
                    LIMIT 1
                ");
                $prodEstrellaRow = $queryProdEstrella->fetch();
                $productoEstrella = $prodEstrellaRow ? [
                    'nombre' => $prodEstrellaRow['nombre_producto'],
                    'cantidad' => (int) $prodEstrellaRow['total_vendido']
                ] : [
                    'nombre' => 'Ninguno',
                    'cantidad' => 0
                ];

                // E. Cliente del Mes (Top 1)
                $queryClienteTop = $db->query("
                    SELECT pe.nombre, pe.apellido, COUNT(ped.id_pedido) as total_pedidos
                    FROM pedido ped
                    JOIN persona pe ON ped.cedula_cliente = pe.cedula
                    GROUP BY pe.cedula, pe.nombre, pe.apellido
                    ORDER BY total_pedidos DESC
                    LIMIT 1
                ");
                $clienteTopRow = $queryClienteTop->fetch();
                if (!$clienteTopRow) {
                    $queryClienteTopRes = $db->query("
                        SELECT pe.nombre, pe.apellido, COUNT(r.id_reservacion) as total_reservas
                        FROM reservacion r
                        JOIN persona pe ON r.cedula_cliente = pe.cedula
                        GROUP BY pe.cedula, pe.nombre, pe.apellido
                        ORDER BY total_reservas DESC
                        LIMIT 1
                    ");
                    $clienteTopRow = $queryClienteTopRes->fetch();
                    $clienteTop = $clienteTopRow ? [
                        'nombre' => $clienteTopRow['nombre'] . ' ' . $clienteTopRow['apellido'],
                        'cantidad' => (int) $clienteTopRow['total_reservas'],
                        'tipo' => 'reservas'
                    ] : [
                        'nombre' => 'Ninguno',
                        'cantidad' => 0,
                        'tipo' => 'pedidos'
                    ];
                } else {
                    $clienteTop = [
                        'nombre' => $clienteTopRow['nombre'] . ' ' . $clienteTopRow['apellido'],
                        'cantidad' => (int) $clienteTopRow['total_pedidos'],
                        'tipo' => 'pedidos'
                    ];
                }

                // F. Ocupación de Mesas
                $totalMesasQuery = $db->query("SELECT COUNT(*) FROM mesa WHERE estatus = 1");
                $totalMesas = (int) $totalMesasQuery->fetchColumn();
                $mesasOcupadasQuery = $db->query("SELECT COUNT(*) FROM mesa WHERE estado = 'OCUPADA' AND estatus = 1");
                $mesasOcupadas = (int) $mesasOcupadasQuery->fetchColumn();
                $porcentajeOcupacion = $totalMesas > 0 ? round(($mesasOcupadas / $totalMesas) * 100, 1) : 0.0;

                // G. Top 5 Productos Más Vendidos
                $queryTopProductos = $db->query("
                    SELECT p.nombre_producto, SUM(dp.cantidad) as total_vendido
                    FROM detalle_pedido dp
                    JOIN producto p ON dp.id_producto = p.id_producto
                    GROUP BY dp.id_producto, p.nombre_producto
                    ORDER BY total_vendido DESC
                    LIMIT 5
                ");
                $topProductosRows = $queryTopProductos->fetchAll() ?: [];
                $topProductosLabels = [];
                $topProductosValues = [];
                foreach ($topProductosRows as $row) {
                    $topProductosLabels[] = $row['nombre_producto'];
                    $topProductosValues[] = (int) $row['total_vendido'];
                }

                // H. Métodos de Pago
                $queryMetodosPago = $db->query("
                    SELECT mp.nombre, COUNT(pag.id_pago) as total_usos
                    FROM pago pag
                    JOIN metodo_pago mp ON pag.id_metodo_pago = mp.id_metodo_pago
                    GROUP BY mp.id_metodo_pago, mp.nombre
                    ORDER BY total_usos DESC
                ");
                $metodosPagoRows = $queryMetodosPago->fetchAll() ?: [];
                $metodosPagoLabels = [];
                $metodosPagoValues = [];
                foreach ($metodosPagoRows as $row) {
                    $metodosPagoLabels[] = $row['nombre'];
                    $metodosPagoValues[] = (int) $row['total_usos'];
                }

                // I. Popularidad de Mesas
                $queryMesasPopularidad = $db->query("
                    SELECT m.numero_mesa, COUNT(am.id_asignacion) as total_reservas
                    FROM asignacion_mesa am
                    JOIN mesa m ON am.id_mesa = m.id_mesa
                    WHERE m.estatus = 1
                    GROUP BY m.id_mesa, m.numero_mesa
                    ORDER BY total_reservas DESC
                    LIMIT 6
                ");
                $mesasPopRows = $queryMesasPopularidad->fetchAll() ?: [];
                $mesasPopLabels = [];
                $mesasPopValues = [];
                foreach ($mesasPopRows as $row) {
                    $mesasPopLabels[] = "Mesa #" . $row['numero_mesa'];
                    $mesasPopValues[] = (int) $row['total_reservas'];
                }

                // J. Ingredientes en Alerta
                $queryIngredientesAlerta = $db->query("
                    SELECT nombre_ingrediente, stock_actual, stock_minimo
                    FROM ingrediente
                    WHERE stock_actual <= stock_minimo AND estatus = 1
                    ORDER BY (stock_actual / stock_minimo) ASC
                    LIMIT 6
                ");
                $ingAlertaRows = $queryIngredientesAlerta->fetchAll() ?: [];
                $ingAlertaLabels = [];
                $ingAlertaActual = [];
                $ingAlertaMin = [];
                foreach ($ingAlertaRows as $row) {
                    $ingAlertaLabels[] = $row['nombre_ingrediente'];
                    $ingAlertaActual[] = (float) $row['stock_actual'];
                    $ingAlertaMin[] = (float) $row['stock_minimo'];
                }

                // 1. Reservaciones
                $reservacionModel = new Reservacion();
                $resReservaciones = $reservacionModel->Transaccion(['peticion' => 'listar']) ?: [];
                $datosReservaciones = $resReservaciones['response']['datos'] ?? [];

                $estadosReservaciones = [
                    'PENDIENTE' => 0,
                    'CONFIRMADA' => 0,
                    'CANCELADA' => 0,
                    'COMPLETADA' => 0
                ];
                $mesesReservaciones = [];

                foreach ($datosReservaciones as $r) {
                    $est = strtoupper($r['extendedProps']['estado'] ?? 'PENDIENTE');
                    if (isset($estadosReservaciones[$est])) {
                        $estadosReservaciones[$est]++;
                    } else {
                        $estadosReservaciones[$est] = 1;
                    }

                    $fecha = substr($r['start'] ?? '', 0, 7);
                    if (!empty($fecha) && preg_match('/^\d{4}-\d{2}$/', $fecha)) {
                        $mesesReservaciones[$fecha] = ($mesesReservaciones[$fecha] ?? 0) + 1;
                    }
                }
                ksort($mesesReservaciones);
                $mesesReservaciones = array_slice($mesesReservaciones, -12, 12, true);

                // 2. Clientes
                $clienteModel = new Cliente();
                $resClientes = $clienteModel->Transaccion(['peticion' => 'consultar']) ?: [];
                $datosClientes = $resClientes['response']['datos'] ?? [];
                $totalClientes = count($datosClientes);

                // 3. Productos
                $productoModel = new Producto();
                $datosProductos = $productoModel->Transaccion(['peticion' => 'listar']) ?: [];
                $totalProductos = count($datosProductos);

                $categoriasProductos = [];
                foreach ($datosProductos as $p) {
                    $catName = $p['categoria_nombre'] ?? 'Sin Categoría';
                    $categoriasProductos[$catName] = ($categoriasProductos[$catName] ?? 0) + 1;
                }
                arsort($categoriasProductos);

                // 4. Asistencia
                $asistenciaModel = new Asistencia();
                $resAsistencia = $asistenciaModel->Transaccion(['peticion' => 'consultar']) ?: [];
                $datosAsistencia = $resAsistencia['response']['datos'] ?? [];

                $hoy = date('Y-m-d');
                $asistenciasHoy = 0;
                $estadosAsistencia = [
                    'A_TIEMPO' => 0,
                    'TARDE' => 0,
                    'FALTA' => 0
                ];
                foreach ($datosAsistencia as $a) {
                    if (($a['fecha'] ?? '') === $hoy) {
                        $asistenciasHoy++;
                    }
                    $est = strtoupper($a['estado'] ?? 'A_TIEMPO');
                    if (isset($estadosAsistencia[$est])) {
                        $estadosAsistencia[$est]++;
                    } else {
                        $estadosAsistencia[$est] = 1;
                    }
                }

                // 5. Bitácora (Seguridad)
                $bitacoraModel = new Bitacora();
                $datosBitacora = $bitacoraModel->Transaccion(['peticion' => 'listar']) ?: [];
                
                $actividadModulos = [];
                foreach ($datosBitacora as $b) {
                    $mod = strtoupper($b['modulo'] ?? 'GENERAL');
                    $actividadModulos[$mod] = ($actividadModulos[$mod] ?? 0) + 1;
                }
                arsort($actividadModulos);
                $actividadModulos = array_slice($actividadModulos, 0, 8, true);

                echo json_encode([
                    'success' => true,
                    'kpis' => [
                        'totalReservaciones' => count($datosReservaciones),
                        'totalClientes' => $totalClientes,
                        'totalProductos' => $totalProductos,
                        'asistenciasHoy' => $asistenciasHoy,
                        'gananciasTotales' => $gananciasTotales,
                        'totalPedidos' => $totalPedidos,
                        'totalItemsVendidos' => $totalItemsVendidos,
                        'productoEstrella' => $productoEstrella,
                        'clienteTop' => $clienteTop,
                        'porcentajeOcupacion' => $porcentajeOcupacion
                    ],
                    'reservacionesEstado' => [
                        'labels' => array_keys($estadosReservaciones),
                        'values' => array_values($estadosReservaciones)
                    ],
                    'reservacionesMes' => [
                        'labels' => array_keys($mesesReservaciones),
                        'values' => array_values($mesesReservaciones)
                    ],
                    'productosCategoria' => [
                        'labels' => array_keys($categoriasProductos),
                        'values' => array_values($categoriasProductos)
                    ],
                    'asistenciasEstado' => [
                        'labels' => array_keys($estadosAsistencia),
                        'values' => array_values($estadosAsistencia)
                    ],
                    'bitacoraActividad' => [
                        'labels' => array_keys($actividadModulos),
                        'values' => array_values($actividadModulos)
                    ],
                    'topProductos' => [
                        'labels' => $topProductosLabels,
                        'values' => $topProductosValues
                    ],
                    'metodosPago' => [
                        'labels' => $metodosPagoLabels,
                        'values' => $metodosPagoValues
                    ],
                    'mesasPopularidad' => [
                        'labels' => $mesasPopLabels,
                        'values' => $mesasPopValues
                    ],
                    'ingredientesAlerta' => [
                        'labels' => $ingAlertaLabels,
                        'actual' => $ingAlertaActual,
                        'minimo' => $ingAlertaMin
                    ]
                ]);
            } catch (\Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al recopilar estadísticas: ' . $e->getMessage()
                ]);
            }
            exit;
        }

        Helper::cargarVista(
            'reports/estadistica',
            'Estadísticas del Sistema - SICGOV',
            [
                'extra_css' => [
                    BASE_URL . '/assets/css/reportes.css',
                    BASE_URL . '/assets/css/estadistica.css'
                ],
                'extra_js' => [
                    BASE_URL . '/assets/js/Controllers/EstadisticaController.js'
                ]
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
