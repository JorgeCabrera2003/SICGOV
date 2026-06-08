<?php

namespace App\Models\System;

use App\Core\Database;
use PDO;
use Exception;

class Estadistica extends Database
{
    public function ObtenerDatosDashboard(array $filtros): array
    {
        try {
            $db = $this->LlamarConexion();
            $dbSec = Database::getConnection('security');

            // Captura de filtros avanzados globales
            $fechaDesde = $filtros['fecha_desde'] ?? '';
            $fechaHasta = $filtros['fecha_hasta'] ?? '';
            $horaDesde = $filtros['hora_desde'] ?? '';
            $horaHasta = $filtros['hora_hasta'] ?? '';
            $reservaEstado = $filtros['reserva_estado'] ?? '';
            $reservaMesa = $filtros['reserva_mesa'] ?? '';
            $pedidoTipo = $filtros['pedido_tipo'] ?? '';
            $pedidoMetodoPago = $filtros['pedido_metodo_pago'] ?? '';
            $asistenciaEstado = $filtros['asistencia_estado'] ?? '';
            $asistenciaEmpleado = $filtros['asistencia_empleado'] ?? '';
            $bitacoraModulo = $filtros['bitacora_modulo'] ?? '';
            $bitacoraAccion = $filtros['bitacora_accion'] ?? '';

            // Captura de filtros locales específicos (con fallback a filtros globales)
            // 1. Tendencia de Reservas (tiempo)
            $tiempoFechaDesde = $filtros['tiempo_fecha_desde'] ?? $fechaDesde;
            $tiempoFechaHasta = $filtros['tiempo_fecha_hasta'] ?? $fechaHasta;
            $tiempoReservaMesa = $filtros['tiempo_reserva_mesa'] ?? $reservaMesa;

            // 2. Estados de Reservas (estado)
            $estadoFechaDesde = $filtros['estado_fecha_desde'] ?? $fechaDesde;
            $estadoFechaHasta = $filtros['estado_fecha_hasta'] ?? $fechaHasta;
            $estadoReservaMesa = $filtros['estado_reserva_mesa'] ?? $reservaMesa;

            // 3. Asistencia
            $asistenciaFechaDesde = $filtros['asistencia_fecha_desde'] ?? $fechaDesde;
            $asistenciaFechaHasta = $filtros['asistencia_fecha_hasta'] ?? $fechaHasta;
            $asistenciaEmpleadoLocal = $filtros['asistencia_empleado'] ?? $asistenciaEmpleado;
            $asistenciaEstadoLocal = $filtros['asistencia_estado'] ?? $asistenciaEstado;

            // 4. Variedad del menú
            $menuCategoriaLocal = $filtros['menu_categoria_producto'] ?? '';

            // 5. Actividad de Seguridad (bitacora)
            $bitacoraFechaDesde = $filtros['bitacora_fecha_desde'] ?? $fechaDesde;
            $bitacoraFechaHasta = $filtros['bitacora_fecha_hasta'] ?? $fechaHasta;
            $bitacoraModuloLocal = $filtros['bitacora_modulo'] ?? $bitacoraModulo;
            $bitacoraAccionLocal = $filtros['bitacora_accion'] ?? $bitacoraAccion;
            $bitacoraEmpleadoLocal = $filtros['bitacora_empleado'] ?? $asistenciaEmpleado;

            // 6. Top 5 Productos más vendidos
            $productosFechaDesde = $filtros['productos_fecha_desde'] ?? $fechaDesde;
            $productosFechaHasta = $filtros['productos_fecha_hasta'] ?? $fechaHasta;
            $productosPedidoTipo = $filtros['productos_pedido_tipo'] ?? $pedidoTipo;

            // 7. Métodos de Pago
            $pagosFechaDesde = $filtros['pagos_fecha_desde'] ?? $fechaDesde;
            $pagosFechaHasta = $filtros['pagos_fecha_hasta'] ?? $fechaHasta;
            $pagosPedidoTipo = $filtros['pagos_pedido_tipo'] ?? $pedidoTipo;

            // 8. Popularidad de Mesas
            $mesasFechaDesde = $filtros['mesas_fecha_desde'] ?? $fechaDesde;
            $mesasFechaHasta = $filtros['mesas_fecha_hasta'] ?? $fechaHasta;
            $mesasReservaEstado = $filtros['mesas_reserva_estado'] ?? $reservaEstado;

            // 9. Stock Crítico de Ingredientes
            $ingredientesRatioLimite = $filtros['ingredientes_ratio_limite'] ?? 1.0;

            // Catálogos para alimentar los selectores dinámicamente
            $catalogoMesas = $db->query("SELECT id_mesa, numero_mesa FROM mesa WHERE estatus = 1 ORDER BY numero_mesa ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $catalogoMetodosPago = $db->query("SELECT id_metodo_pago, nombre FROM metodo_pago ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $catalogoCategorias = $db->query("SELECT id_categoria, nombre_categoria AS nombre FROM categoria_producto ORDER BY nombre_categoria ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $catalogoEmpleados = $db->query("
                SELECT e.cedula, p.nombre, p.apellido 
                FROM empleado e 
                JOIN persona p ON e.cedula = p.cedula 
                ORDER BY p.nombre ASC, p.apellido ASC
            ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // ----------------------------------------------------
            // CONSTRUCCIÓN DE FILTROS DINÁMICOS - PEDIDOS Y PAGOS
            // ----------------------------------------------------
            $wherePedidos = " WHERE 1=1";
            $paramsPedidos = [];

            if (!empty($fechaDesde)) {
                $wherePedidos .= " AND ped.fecha_pedido >= :fecha_desde_ped";
                $paramsPedidos[':fecha_desde_ped'] = $fechaDesde . ' 00:00:00';
            }
            if (!empty($fechaHasta)) {
                $wherePedidos .= " AND ped.fecha_pedido <= :fecha_hasta_ped";
                $paramsPedidos[':fecha_hasta_ped'] = $fechaHasta . ' 23:59:59';
            }
            if (!empty($horaDesde)) {
                $wherePedidos .= " AND TIME(ped.fecha_pedido) >= :hora_desde_ped";
                $paramsPedidos[':hora_desde_ped'] = $horaDesde;
            }
            if (!empty($horaHasta)) {
                $wherePedidos .= " AND TIME(ped.fecha_pedido) <= :hora_hasta_ped";
                $paramsPedidos[':hora_hasta_ped'] = $horaHasta;
            }
            if (!empty($pedidoTipo)) {
                $wherePedidos .= " AND ped.tipo_pedido = :pedido_tipo";
                $paramsPedidos[':pedido_tipo'] = $pedidoTipo;
            }
            if (!empty($asistenciaEmpleado)) {
                $wherePedidos .= " AND ped.cedula_empleado = :pedido_empleado";
                $paramsPedidos[':pedido_empleado'] = $asistenciaEmpleado;
            }
            if (!empty($pedidoMetodoPago)) {
                $wherePedidos .= " AND EXISTS (SELECT 1 FROM pago pg WHERE pg.id_pedido = ped.id_pedido AND pg.id_metodo_pago = :pedido_metodo_pago)";
                $paramsPedidos[':pedido_metodo_pago'] = $pedidoMetodoPago;
            }

            // A. Ganancias Totales
            $stmtGanancias = $db->prepare("
                SELECT COALESCE(SUM(pag.monto), 0) as total 
                FROM pago pag
                JOIN pedido ped ON pag.id_pedido = ped.id_pedido
                $wherePedidos
            ");
            $stmtGanancias->execute($paramsPedidos);
            $gananciasTotales = (float) $stmtGanancias->fetchColumn();

            // B. Total Pedidos
            $stmtPedidos = $db->prepare("
                SELECT COUNT(*) 
                FROM pedido ped
                $wherePedidos
            ");
            $stmtPedidos->execute($paramsPedidos);
            $totalPedidos = (int) $stmtPedidos->fetchColumn();

            // C. Cantidad de Items Vendidos
            $stmtItems = $db->prepare("
                SELECT COALESCE(SUM(dp.cantidad), 0) 
                FROM detalle_pedido dp
                JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                $wherePedidos
            ");
            $stmtItems->execute($paramsPedidos);
            $totalItemsVendidos = (int) $stmtItems->fetchColumn();

            // D. Producto Estrella (Top 1)
            $stmtProdEstrella = $db->prepare("
                SELECT p.nombre_producto, SUM(dp.cantidad) as total_vendido
                FROM detalle_pedido dp
                JOIN producto p ON dp.id_producto = p.id_producto
                JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                $wherePedidos
                GROUP BY dp.id_producto, p.nombre_producto
                ORDER BY total_vendido DESC
                LIMIT 1
            ");
            $stmtProdEstrella->execute($paramsPedidos);
            $prodEstrellaRow = $stmtProdEstrella->fetch();
            $productoEstrella = $prodEstrellaRow ? [
                'nombre' => $prodEstrellaRow['nombre_producto'],
                'cantidad' => (int) $prodEstrellaRow['total_vendido']
            ] : [
                'nombre' => 'Ninguno',
                'cantidad' => 0
            ];

            // ----------------------------------------------------
            // CONSTRUCCIÓN DE FILTROS DINÁMICOS - RESERVACIONES
            // ----------------------------------------------------
            $whereReservas = " WHERE 1=1";
            $paramsReservas = [];

            if (!empty($fechaDesde)) {
                $whereReservas .= " AND r.fecha >= :fecha_desde_res";
                $paramsReservas[':fecha_desde_res'] = $fechaDesde;
            }
            if (!empty($fechaHasta)) {
                $whereReservas .= " AND r.fecha <= :fecha_hasta_res";
                $paramsReservas[':fecha_hasta_res'] = $fechaHasta;
            }
            if (!empty($horaDesde)) {
                $whereReservas .= " AND r.hora >= :hora_desde_res";
                $paramsReservas[':hora_desde_res'] = $horaDesde;
            }
            if (!empty($horaHasta)) {
                $whereReservas .= " AND r.hora <= :hora_hasta_res";
                $paramsReservas[':hora_hasta_res'] = $horaHasta;
            }
            if (!empty($reservaEstado)) {
                $whereReservas .= " AND r.estado = :reserva_estado";
                $paramsReservas[':reserva_estado'] = $reservaEstado;
            }
            if (!empty($reservaMesa)) {
                $whereReservas .= " AND EXISTS (SELECT 1 FROM asignacion_mesa am WHERE am.id_reservacion = r.id_reservacion AND am.id_mesa = :reserva_mesa)";
                $paramsReservas[':reserva_mesa'] = $reservaMesa;
            }

            // ----------------------------------------------------
            // CONSTRUCCIÓN DE FILTROS DINÁMICOS - REPORTES LOCALES
            // ----------------------------------------------------
            // 1. Tendencia de Reservas (tiempo)
            $whereTiempo = " WHERE 1=1";
            $paramsTiempo = [];
            if (!empty($tiempoFechaDesde)) {
                $whereTiempo .= " AND r.fecha >= :tiempo_fecha_desde";
                $paramsTiempo[':tiempo_fecha_desde'] = $tiempoFechaDesde;
            }
            if (!empty($tiempoFechaHasta)) {
                $whereTiempo .= " AND r.fecha <= :tiempo_fecha_hasta";
                $paramsTiempo[':tiempo_fecha_hasta'] = $tiempoFechaHasta;
            }
            if (!empty($tiempoReservaMesa)) {
                $whereTiempo .= " AND EXISTS (SELECT 1 FROM asignacion_mesa am WHERE am.id_reservacion = r.id_reservacion AND am.id_mesa = :tiempo_reserva_mesa)";
                $paramsTiempo[':tiempo_reserva_mesa'] = $tiempoReservaMesa;
            }

            // 2. Estados de Reservas (estado)
            $whereEstado = " WHERE 1=1";
            $paramsEstado = [];
            if (!empty($estadoFechaDesde)) {
                $whereEstado .= " AND r.fecha >= :estado_fecha_desde";
                $paramsEstado[':estado_fecha_desde'] = $estadoFechaDesde;
            }
            if (!empty($estadoFechaHasta)) {
                $whereEstado .= " AND r.fecha <= :estado_fecha_hasta";
                $paramsEstado[':estado_fecha_hasta'] = $estadoFechaHasta;
            }
            if (!empty($estadoReservaMesa)) {
                $whereEstado .= " AND EXISTS (SELECT 1 FROM asignacion_mesa am WHERE am.id_reservacion = r.id_reservacion AND am.id_mesa = :estado_reserva_mesa)";
                $paramsEstado[':estado_reserva_mesa'] = $estadoReservaMesa;
            }

            // 3. Asistencia
            $whereAsistenciaLocal = " WHERE 1=1";
            $paramsAsistenciaLocal = [];
            if (!empty($asistenciaFechaDesde)) {
                $whereAsistenciaLocal .= " AND a.fecha >= :as_fecha_desde";
                $paramsAsistenciaLocal[':as_fecha_desde'] = $asistenciaFechaDesde;
            }
            if (!empty($asistenciaFechaHasta)) {
                $whereAsistenciaLocal .= " AND a.fecha <= :as_fecha_hasta";
                $paramsAsistenciaLocal[':as_fecha_hasta'] = $asistenciaFechaHasta;
            }
            if (!empty($asistenciaEmpleadoLocal)) {
                $whereAsistenciaLocal .= " AND a.cedula_empleado = :as_empleado";
                $paramsAsistenciaLocal[':as_empleado'] = $asistenciaEmpleadoLocal;
            }
            if (!empty($asistenciaEstadoLocal)) {
                $whereAsistenciaLocal .= " AND a.estado = :as_estado";
                $paramsAsistenciaLocal[':as_estado'] = $asistenciaEstadoLocal;
            }

            // 4. Variedad del menú (menu)
            $whereMenuLocal = " WHERE p.estatus = 1";
            $paramsMenuLocal = [];
            if (!empty($menuCategoriaLocal)) {
                $whereMenuLocal .= " AND p.id_categoria = :menu_categoria";
                $paramsMenuLocal[':menu_categoria'] = $menuCategoriaLocal;
            }

            // 5. Actividad de Seguridad (bitacora)
            $whereBitacoraLocal = " WHERE 1=1";
            $paramsBitacoraLocal = [];
            if (!empty($bitacoraFechaDesde)) {
                $whereBitacoraLocal .= " AND b.fecha >= :bit_fecha_desde";
                $paramsBitacoraLocal[':bit_fecha_desde'] = $bitacoraFechaDesde . ' 00:00:00';
            }
            if (!empty($bitacoraFechaHasta)) {
                $whereBitacoraLocal .= " AND b.fecha <= :bit_fecha_hasta";
                $paramsBitacoraLocal[':bit_fecha_hasta'] = $bitacoraFechaHasta . ' 23:59:59';
            }
            if (!empty($bitacoraModuloLocal)) {
                $whereBitacoraLocal .= " AND b.modulo = :bit_modulo";
                $paramsBitacoraLocal[':bit_modulo'] = $bitacoraModuloLocal;
            }
            if (!empty($bitacoraAccionLocal)) {
                $whereBitacoraLocal .= " AND b.accion LIKE :bit_accion";
                $paramsBitacoraLocal[':bit_accion'] = '%' . $bitacoraAccionLocal . '%';
            }
            if (!empty($bitacoraEmpleadoLocal)) {
                $whereBitacoraLocal .= " AND b.cedula = :bit_empleado";
                $paramsBitacoraLocal[':bit_empleado'] = $bitacoraEmpleadoLocal;
            }

            // 6. Top Productos (productosTop)
            $whereProductosTopLocal = " WHERE 1=1";
            $paramsProductosTopLocal = [];
            if (!empty($productosFechaDesde)) {
                $whereProductosTopLocal .= " AND ped.fecha_pedido >= :prod_fecha_desde";
                $paramsProductosTopLocal[':prod_fecha_desde'] = $productosFechaDesde . ' 00:00:00';
            }
            if (!empty($productosFechaHasta)) {
                $whereProductosTopLocal .= " AND ped.fecha_pedido <= :prod_fecha_hasta";
                $paramsProductosTopLocal[':prod_fecha_hasta'] = $productosFechaHasta . ' 23:59:59';
            }
            if (!empty($productosPedidoTipo)) {
                $whereProductosTopLocal .= " AND ped.tipo_pedido = :prod_pedido_tipo";
                $paramsProductosTopLocal[':prod_pedido_tipo'] = $productosPedidoTipo;
            }

            // 7. Métodos de Pago (metodosPago)
            $whereMetodosPagoLocal = " WHERE 1=1";
            $paramsMetodosPagoLocal = [];
            if (!empty($pagosFechaDesde)) {
                $whereMetodosPagoLocal .= " AND ped.fecha_pedido >= :pag_fecha_desde";
                $paramsMetodosPagoLocal[':pag_fecha_desde'] = $pagosFechaDesde . ' 00:00:00';
            }
            if (!empty($pagosFechaHasta)) {
                $whereMetodosPagoLocal .= " AND ped.fecha_pedido <= :pag_fecha_hasta";
                $paramsMetodosPagoLocal[':pag_fecha_hasta'] = $pagosFechaHasta . ' 23:59:59';
            }
            if (!empty($pagosPedidoTipo)) {
                $whereMetodosPagoLocal .= " AND ped.tipo_pedido = :pag_pedido_tipo";
                $paramsMetodosPagoLocal[':pag_pedido_tipo'] = $pagosPedidoTipo;
            }

            // 8. Popularidad de Mesas (mesasPopularidad)
            $whereMesasPopularidadLocal = " WHERE 1=1";
            $paramsMesasPopularidadLocal = [];
            if (!empty($mesasFechaDesde)) {
                $whereMesasPopularidadLocal .= " AND r.fecha >= :mesas_fecha_desde";
                $paramsMesasPopularidadLocal[':mesas_fecha_desde'] = $mesasFechaDesde;
            }
            if (!empty($mesasFechaHasta)) {
                $whereMesasPopularidadLocal .= " AND r.fecha <= :mesas_fecha_hasta";
                $paramsMesasPopularidadLocal[':mesas_fecha_hasta'] = $mesasFechaHasta;
            }
            if (!empty($mesasReservaEstado)) {
                $whereMesasPopularidadLocal .= " AND r.estado = :mesas_reserva_estado";
                $paramsMesasPopularidadLocal[':mesas_reserva_estado'] = $mesasReservaEstado;
            }

            // 9. Stock Crítico (ingredientesAlerta)
            $whereIngredientesLocal = " WHERE stock_actual <= (stock_minimo * :ratio_limite) AND estatus = 1";
            $paramsIngredientesLocal = [
                ':ratio_limite' => (float)$ingredientesRatioLimite
            ];

            // E. Cliente del Mes (Top 1)
            $stmtClienteTop = $db->prepare("
                SELECT pe.nombre, pe.apellido, COUNT(ped.id_pedido) as total_pedidos
                FROM pedido ped
                JOIN persona pe ON ped.cedula_cliente = pe.cedula
                $wherePedidos
                GROUP BY pe.cedula, pe.nombre, pe.apellido
                ORDER BY total_pedidos DESC
                LIMIT 1
            ");
            $stmtClienteTop->execute($paramsPedidos);
            $clienteTopRow = $stmtClienteTop->fetch();
            if (!$clienteTopRow) {
                $stmtClienteTopRes = $db->prepare("
                    SELECT pe.nombre, pe.apellido, COUNT(r.id_reservacion) as total_reservas
                    FROM reservacion r
                    JOIN persona pe ON r.cedula_cliente = pe.cedula
                    $whereReservas
                    GROUP BY pe.cedula, pe.nombre, pe.apellido
                    ORDER BY total_reservas DESC
                    LIMIT 1
                ");
                $stmtClienteTopRes->execute($paramsReservas);
                $clienteTopRow = $stmtClienteTopRes->fetch();
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
            $whereMesaOcupacion = " WHERE estatus = 1";
            $paramsMesaOcupacion = [];
            if (!empty($reservaMesa)) {
                $whereMesaOcupacion .= " AND id_mesa = :reserva_mesa";
                $paramsMesaOcupacion[':reserva_mesa'] = $reservaMesa;
            }
            $stmtTotalMesas = $db->prepare("SELECT COUNT(*) FROM mesa $whereMesaOcupacion");
            $stmtTotalMesas->execute($paramsMesaOcupacion);
            $totalMesas = (int) $stmtTotalMesas->fetchColumn();

            $stmtMesasOcupadas = $db->prepare("SELECT COUNT(*) FROM mesa $whereMesaOcupacion AND estado = 'OCUPADA'");
            $stmtMesasOcupadas->execute($paramsMesaOcupacion);
            $mesasOcupadas = (int) $stmtMesasOcupadas->fetchColumn();
            $porcentajeOcupacion = $totalMesas > 0 ? round(($mesasOcupadas / $totalMesas) * 100, 1) : 0.0;

            // G. Top 5 Productos Más Vendidos
            $stmtTopProductos = $db->prepare("
                SELECT p.nombre_producto, SUM(dp.cantidad) as total_vendido
                FROM detalle_pedido dp
                JOIN producto p ON dp.id_producto = p.id_producto
                JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                $whereProductosTopLocal
                GROUP BY dp.id_producto, p.nombre_producto
                ORDER BY total_vendido DESC
                LIMIT 5
            ");
            $stmtTopProductos->execute($paramsProductosTopLocal);
            $topProductosRows = $stmtTopProductos->fetchAll() ?: [];
            $topProductosLabels = [];
            $topProductosValues = [];
            foreach ($topProductosRows as $row) {
                $topProductosLabels[] = $row['nombre_producto'];
                $topProductosValues[] = (int) $row['total_vendido'];
            }

            // H. Métodos de Pago
            $stmtMetodosPago = $db->prepare("
                SELECT mp.nombre, COUNT(pag.id_pago) as total_usos
                FROM pago pag
                JOIN metodo_pago mp ON pag.id_metodo_pago = mp.id_metodo_pago
                JOIN pedido ped ON pag.id_pedido = ped.id_pedido
                $whereMetodosPagoLocal
                GROUP BY mp.id_metodo_pago, mp.nombre
                ORDER BY total_usos DESC
            ");
            $stmtMetodosPago->execute($paramsMetodosPagoLocal);
            $metodosPagoRows = $stmtMetodosPago->fetchAll() ?: [];
            $metodosPagoLabels = [];
            $metodosPagoValues = [];
            foreach ($metodosPagoRows as $row) {
                $metodosPagoLabels[] = $row['nombre'];
                $metodosPagoValues[] = (int) $row['total_usos'];
            }

            // I. Popularidad de Mesas
            $stmtMesasPopularidad = $db->prepare("
                SELECT m.numero_mesa, COUNT(am.id_asignacion) as total_reservas
                FROM asignacion_mesa am
                JOIN mesa m ON am.id_mesa = m.id_mesa
                JOIN reservacion r ON am.id_reservacion = r.id_reservacion
                $whereMesasPopularidadLocal AND m.estatus = 1
                GROUP BY m.id_mesa, m.numero_mesa
                ORDER BY total_reservas DESC
                LIMIT 6
            ");
            $stmtMesasPopularidad->execute($paramsMesasPopularidadLocal);
            $mesasPopRows = $stmtMesasPopularidad->fetchAll() ?: [];
            $mesasPopLabels = [];
            $mesasPopValues = [];
            foreach ($mesasPopRows as $row) {
                $mesasPopLabels[] = "Mesa #" . $row['numero_mesa'];
                $mesasPopValues[] = (int) $row['total_reservas'];
            }

            // J. Ingredientes en Alerta
            $stmtIngredientes = $db->prepare("
                SELECT nombre_insumo, stock_actual, stock_minimo
                FROM insumo
                $whereIngredientesLocal
                ORDER BY (stock_actual / stock_minimo) ASC
                LIMIT 6
            ");
            $stmtIngredientes->execute($paramsIngredientesLocal);
            $ingAlertaRows = $stmtIngredientes->fetchAll() ?: [];
            $ingAlertaLabels = [];
            $ingAlertaActual = [];
            $ingAlertaMin = [];
            foreach ($ingAlertaRows as $row) {
                $ingAlertaLabels[] = $row['nombre_insumo'];
                $ingAlertaActual[] = (float) $row['stock_actual'];
                $ingAlertaMin[] = (float) $row['stock_minimo'];
            }

             // 1. Reservaciones - Tendencia Dinámica (tiempo)
             $stmtTiempoData = $db->prepare("
                 SELECT fecha
                 FROM reservacion r
                 $whereTiempo
             ");
             $stmtTiempoData->execute($paramsTiempo);
             $datosTiempo = $stmtTiempoData->fetchAll(PDO::FETCH_ASSOC) ?: [];
             
             // Determinar si agrupamos por día (rango <= 60 días) o por mes
             $agruparPorDia = false;
             if (!empty($tiempoFechaDesde) && !empty($tiempoFechaHasta)) {
                 try {
                     $d1 = new \DateTime($tiempoFechaDesde);
                     $d2 = new \DateTime($tiempoFechaHasta);
                     if ($d1->diff($d2)->days <= 60) {
                         $agruparPorDia = true;
                     }
                 } catch (\Exception $e) { }
             }
             
             $mesesReservaciones = [];
             foreach ($datosTiempo as $r) {
                 if ($agruparPorDia) {
                     $fecha = substr($r['fecha'] ?? '', 0, 10);
                     if (!empty($fecha) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                         $mesesReservaciones[$fecha] = ($mesesReservaciones[$fecha] ?? 0) + 1;
                     }
                 } else {
                     $fecha = substr($r['fecha'] ?? '', 0, 7);
                     if (!empty($fecha) && preg_match('/^\d{4}-\d{2}$/', $fecha)) {
                         $mesesReservaciones[$fecha] = ($mesesReservaciones[$fecha] ?? 0) + 1;
                     }
                 }
             }
             ksort($mesesReservaciones);
             if (!$agruparPorDia) {
                 $mesesReservaciones = array_slice($mesesReservaciones, -12, 12, true);
             }

             // 1.2 Reservaciones - Distribución por Estado (estado)
             $stmtEstadoData = $db->prepare("
                 SELECT estado
                 FROM reservacion r
                 $whereEstado
             ");
             $stmtEstadoData->execute($paramsEstado);
             $datosEstado = $stmtEstadoData->fetchAll(PDO::FETCH_ASSOC) ?: [];
             $estadosReservaciones = [
                 'PENDIENTE' => 0,
                 'CONFIRMADA' => 0,
                 'CANCELADA' => 0,
                 'COMPLETADA' => 0
             ];
             foreach ($datosEstado as $r) {
                 $est = strtoupper($r['estado'] ?? 'PENDIENTE');
                 if (isset($estadosReservaciones[$est])) {
                     $estadosReservaciones[$est]++;
                 } else {
                     $estadosReservaciones[$est] = 1;
                 }
             }

             // 2. Clientes
             $whereClientes = " WHERE 1=1";
             $paramsClientes = [];
             if (!empty($fechaDesde)) {
                 $whereClientes .= " AND c.fecha_registro >= :fecha_desde_cl";
                 $paramsClientes[':fecha_desde_cl'] = $fechaDesde . ' 00:00:00';
             }
             if (!empty($fechaHasta)) {
                 $whereClientes .= " AND c.fecha_registro <= :fecha_hasta_cl";
                 $paramsClientes[':fecha_hasta_cl'] = $fechaHasta . ' 23:59:59';
             }
             
             $stmtClientes = $db->prepare("
                 SELECT COUNT(*) 
                 FROM cliente c
                 $whereClientes
             ");
             $stmtClientes->execute($paramsClientes);
             $totalClientes = (int) $stmtClientes->fetchColumn();

             // 3. Productos (Menu Chart using local filters)
             $stmtProductosLocal = $db->prepare("
                 SELECT p.*, cp.nombre_categoria as categoria_nombre
                 FROM producto p
                 LEFT JOIN categoria_producto cp ON p.id_categoria = cp.id_categoria
                 $whereMenuLocal
             ");
             $stmtProductosLocal->execute($paramsMenuLocal);
             $datosProductosLocal = $stmtProductosLocal->fetchAll(PDO::FETCH_ASSOC) ?: [];

             $categoriasProductos = [];
             foreach ($datosProductosLocal as $p) {
                 $catName = $p['categoria_nombre'] ?? 'Sin Categoría';
                 $categoriasProductos[$catName] = ($categoriasProductos[$catName] ?? 0) + 1;
             }
             arsort($categoriasProductos);

             // Global Products count for KPI
             $stmtProductos = $db->prepare("
                 SELECT COUNT(*) FROM producto p WHERE p.estatus = 1
             ");
             $stmtProductos->execute();
             $totalProductos = (int) $stmtProductos->fetchColumn();

             // 4. Asistencia (Asistencia Chart using local filters)
             $stmtAsistenciasLocal = $db->prepare("
                 SELECT a.estado, a.fecha
                 FROM asistencia a
                 $whereAsistenciaLocal
             ");
             $stmtAsistenciasLocal->execute($paramsAsistenciaLocal);
             $datosAsistenciaLocal = $stmtAsistenciasLocal->fetchAll(PDO::FETCH_ASSOC) ?: [];

             $estadosAsistencia = [
                 'A_TIEMPO' => 0,
                 'TARDE' => 0,
                 'FALTA' => 0
             ];
             foreach ($datosAsistenciaLocal as $a) {
                 $est = strtoupper($a['estado'] ?? 'A_TIEMPO');
                 if (isset($estadosAsistencia[$est])) {
                     $estadosAsistencia[$est]++;
                 } else {
                     $estadosAsistencia[$est] = 1;
                 }
             }

             // Global/KPI asistencias hoy
             $stmtAsistenciasHoy = $db->prepare("
                 SELECT COUNT(*) FROM asistencia WHERE fecha = :hoy
             ");
             $stmtAsistenciasHoy->execute([':hoy' => date('Y-m-d')]);
             $asistenciasHoy = (int) $stmtAsistenciasHoy->fetchColumn();

             // 5. Bitácora (Seguridad Chart using local filters)
             $stmtBitacoraLocal = $dbSec->prepare("
                 SELECT b.modulo
                 FROM bitacora b
                 $whereBitacoraLocal
             ");
             $stmtBitacoraLocal->execute($paramsBitacoraLocal);
             $datosBitacoraLocal = $stmtBitacoraLocal->fetchAll(PDO::FETCH_ASSOC) ?: [];
             
             $actividadModulos = [];
             foreach ($datosBitacoraLocal as $b) {
                 $mod = strtoupper($b['modulo'] ?? 'GENERAL');
                 $actividadModulos[$mod] = ($actividadModulos[$mod] ?? 0) + 1;
             }
             arsort($actividadModulos);
             $actividadModulos = array_slice($actividadModulos, 0, 8, true);

             // Global Reservas count for KPI
             $stmtReservasGlobalCount = $db->prepare("
                 SELECT COUNT(*) FROM reservacion r $whereReservas
             ");
             $stmtReservasGlobalCount->execute($paramsReservas);
             $totalReservacionesGlobal = (int) $stmtReservasGlobalCount->fetchColumn();

             $this->DestruirConexion();

             return [
                 'catalogs' => [
                     'mesas' => $catalogoMesas,
                     'empleados' => $catalogoEmpleados,
                     'categorias' => $catalogoCategorias,
                     'metodosPago' => $catalogoMetodosPago
                 ],
                 'kpis' => [
                     'totalReservaciones' => $totalReservacionesGlobal,
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
             ];
        } catch (Exception $e) {
            $this->DestruirConexion();
            throw $e;
        }
    }
}
