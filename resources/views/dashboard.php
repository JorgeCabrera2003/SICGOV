<?php
$hora = (int)date('H');
$saludo = ($hora >= 5 && $hora < 12) ? 'Buenos días' : (($hora >= 12 && $hora < 19) ? 'Buenas tardes' : 'Buenas noches');

$nombreUsuario = htmlspecialchars($datosUsuario['nombre'] ?? $datos['nombre'] ?? 'Administrador');
$rolUsuario = htmlspecialchars($datosUsuario['rol'] ?? $datos['rol'] ?? 'Administrador');

$dias = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
$meses = ['January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'];
$diaSemana = $dias[date('l')] ?? date('l');
$mesNombre = $meses[date('F')] ?? date('F');
$fechaFormateada = $diaSemana . ', ' . date('d') . ' de ' . $mesNombre . ' de ' . date('Y');
?>

<div class="container-fluid py-3 px-md-4">
    <!-- 1. CABECERA LIMPIA CON RELOJ EN VIVO -->
    <div class="dash-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 fw-bold mb-1 text-body">
                    <?= $saludo ?>, <span class="dash-user-name"><?= $nombreUsuario ?></span>
                </h1>
                <p class="text-body-secondary small mb-0">
                    <i class="bi bi-calendar3 me-1"></i> <?= $fechaFormateada ?> &bull; <?= $rolUsuario ?>
                </p>
            </div>
            
            <!-- Reloj Digital en Vivo -->
            <div class="dash-clock-box d-flex align-items-center gap-2 px-3 py-2">
                <i class="bi bi-clock-history text-warning fs-5"></i>
                <div class="d-flex align-items-baseline">
                    <span id="dashboardClock" class="dash-clock-time fw-bold text-body">--:--:--</span>
                    <span class="dash-clock-zone text-body-secondary ms-1">VET</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. TARJETAS DE MÉTRICAS (KPIs Desaturados y Elegantes) -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Productos en Menú -->
        <div class="col-xl-3 col-sm-6">
            <div class="dash-kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="dash-kpi-label">Productos en Menú</div>
                        <div class="dash-kpi-value"><?= $totalProductos ?></div>
                    </div>
                    <div class="dash-kpi-icon kpi-icon-amber">
                        <i class="bi bi-egg-fried"></i>
                    </div>
                </div>
                <div class="dash-kpi-sub" title="<?= count($productosCategoria['labels'] ?? []) ?> categorías activas">
                    <?= count($productosCategoria['labels'] ?? []) ?> categorías activas
                </div>
            </div>
        </div>

        <!-- KPI 2: Mesas en Salón -->
        <div class="col-xl-3 col-sm-6">
            <div class="dash-kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="dash-kpi-label">Mesas en Salón</div>
                        <div class="dash-kpi-value"><?= $mesasOcupadas ?> <span class="fs-6 text-body-secondary fw-normal">/ <?= $totalMesas ?></span></div>
                    </div>
                    <div class="dash-kpi-icon kpi-icon-emerald">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                    </div>
                </div>
                <div class="dash-kpi-sub" title="<?= $mesasDisponibles ?> libres (<?= $porcentajeOcupacion ?>% ocupado)">
                    <?= $mesasDisponibles ?> libres (<?= $porcentajeOcupacion ?>% ocupado)
                </div>
            </div>
        </div>

        <!-- KPI 3: Pedidos de Hoy -->
        <div class="col-xl-3 col-sm-6">
            <div class="dash-kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="dash-kpi-label">Pedidos Hoy</div>
                        <div class="dash-kpi-value"><?= $pedidosHoy ?></div>
                    </div>
                    <div class="dash-kpi-icon kpi-icon-cyan">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                </div>
                <div class="dash-kpi-sub" title="<?= $pedidosPendientes ?> pendientes &bull; <?= $pedidosEntregados ?> entregados">
                    <?= $pedidosPendientes ?> pendientes &bull; <?= $pedidosEntregados ?> entregados
                </div>
            </div>
        </div>

        <!-- KPI 4: Ingresos del Día -->
        <div class="col-xl-3 col-sm-6">
            <div class="dash-kpi-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="dash-kpi-label">Ingresos Hoy</div>
                        <div class="dash-kpi-value">$<?= number_format($ingresosHoy, 2) ?></div>
                    </div>
                    <div class="dash-kpi-icon kpi-icon-wine">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
                <div class="dash-kpi-sub" title="Total histórico: $<?= number_format($totalIngresosHistorico, 2) ?>">
                    Total histórico: $<?= number_format($totalIngresosHistorico, 2) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. INFORMACIÓN OPERATIVA FILTRADA -->
    <div class="row g-4 mb-4">
        <!-- Reservaciones de Hoy -->
        <div class="col-lg-7">
            <div class="dash-card h-100">
                <div class="dash-card-header">
                    <div>
                        <h2 class="dash-card-title">Agenda de Reservaciones</h2>
                        <span class="dash-card-subtitle">Programadas para hoy</span>
                    </div>
                    <a href="<?= BASE_URL ?>?page=reservaciones" class="btn btn-sm btn-outline-secondary">
                        Ver Agenda
                    </a>
                </div>
                <div class="p-3">
                    <?php if (!empty($reservasHoy)): ?>
                        <?php foreach (array_slice($reservasHoy, 0, 4) as $reserva): 
                            $estadoR = strtoupper($reserva['estado'] ?? 'PENDIENTE');
                            $badgeColor = match($estadoR) {
                                'CONFIRMADA' => 'bg-success-subtle text-success border-success-subtle',
                                'CANCELADA'  => 'bg-danger-subtle text-danger border-danger-subtle',
                                'COMPLETADA' => 'bg-info-subtle text-info border-info-subtle',
                                default      => 'bg-warning-subtle text-warning border-warning-subtle'
                            };
                            $nombreCliente = trim(($reserva['nombre'] ?? '') . ' ' . ($reserva['apellido'] ?? ''));
                            if (empty($nombreCliente)) {
                                $nombreCliente = 'Cliente General';
                            }
                            $horaRaw = $reserva['hora'] ?? '';
                            $horaNum = !empty($horaRaw) ? date('h:i', strtotime($horaRaw)) : '--:--';
                            $ampm = !empty($horaRaw) ? date('A', strtotime($horaRaw)) : '';
                        ?>
                            <div class="dash-reserva-item">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="dash-time-pill text-nowrap">
                                        <span><?= htmlspecialchars($horaNum) ?></span>
                                        <span class="dash-time-ampm"><?= htmlspecialchars($ampm) ?></span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-body small">
                                            <?= htmlspecialchars($nombreCliente) ?>
                                        </div>
                                        <div class="text-body-secondary small">
                                            <?= htmlspecialchars($reserva['telefono'] ?? 'Sin contacto') ?>
                                            <?php if (!empty($reserva['numero_mesa'])): ?>
                                                &bull; Mesa #<?= htmlspecialchars($reserva['numero_mesa']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge rounded-pill border <?= $badgeColor ?> small px-2 py-1">
                                    <?= $estadoR ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-check text-body-secondary fs-3 d-block mb-2"></i>
                            <p class="text-body-secondary small mb-0">No hay reservaciones agendadas para hoy.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alertas de Stock Crítico -->
        <div class="col-lg-5">
            <div class="dash-card h-100">
                <div class="dash-card-header">
                    <div>
                        <h2 class="dash-card-title">Inventario Crítico</h2>
                        <span class="dash-card-subtitle">Insumos bajo el nivel mínimo</span>
                    </div>
                    <a href="<?= BASE_URL ?>?page=insumo" class="btn btn-sm btn-outline-secondary">
                        Insumos
                    </a>
                </div>
                <div class="p-3">
                    <?php 
                    $alertLabels = $ingredientesAlerta['labels'] ?? [];
                    $alertActual = $ingredientesAlerta['actual'] ?? [];
                    $alertMinimo = $ingredientesAlerta['minimo'] ?? [];
                    ?>
                    <?php if (!empty($alertLabels)): ?>
                        <?php foreach (array_slice($alertLabels, 0, 4) as $idx => $nombreInsumo): 
                            $actual = (float)($alertActual[$idx] ?? 0);
                            $minimo = (float)($alertMinimo[$idx] ?? 1);
                            $porcentaje = $minimo > 0 ? min(100, round(($actual / $minimo) * 100)) : 0;
                            $progColor = $porcentaje <= 50 ? 'bg-danger' : 'bg-warning';
                        ?>
                            <div class="dash-stock-item">
                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                    <span class="fw-semibold text-body"><?= htmlspecialchars($nombreInsumo) ?></span>
                                    <span class="text-body-secondary">
                                        <strong class="text-danger"><?= $actual ?></strong> / <?= $minimo ?> mín.
                                    </span>
                                </div>
                                <div class="dash-progress">
                                    <div class="progress-bar <?= $progColor ?>" role="progressbar" style="width: <?= $porcentaje ?>%;" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                            <p class="text-body-secondary small mb-0">Niveles de stock óptimos.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. GRÁFICOS ANALÍTICOS (Renderizados vía dashboard.js) -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="dash-card h-100">
                <div class="dash-card-header">
                    <div>
                        <h2 class="dash-card-title">Evolución Operativa</h2>
                        <span class="dash-card-subtitle">Actividad mensual del restaurante</span>
                    </div>
                </div>
                <div class="p-3">
                    <div class="dash-chart-container">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5">
            <div class="dash-card h-100">
                <div class="dash-card-header">
                    <div>
                        <h2 class="dash-card-title">Variedad del Menú</h2>
                        <span class="dash-card-subtitle">Distribución por categoría</span>
                    </div>
                </div>
                <div class="p-3">
                    <div class="dash-chart-container">
                        <canvas id="productosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. TABLA DE ACTIVIDAD RECIENTE -->
    <div class="dash-card">
        <div class="dash-card-header">
            <div>
                <h2 class="dash-card-title">Pedidos Recientes</h2>
                <span class="dash-card-subtitle">Últimas transacciones registradas</span>
            </div>
            <a href="<?= BASE_URL ?>?page=pedidos" class="btn btn-sm btn-outline-secondary">
                Ver Todos
            </a>
        </div>
        <div class="p-0">
            <?php if (!empty($pedidosRecientes)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">N° Pedido</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Pago</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php foreach ($pedidosRecientes as $pedido): 
                                $estP = strtoupper($pedido['estado'] ?? 'PENDIENTE');
                                $badgeP = match($estP) {
                                    'COMPLETADO', 'ENTREGADO' => 'bg-success-subtle text-success border-success-subtle',
                                    'EN PREPARACION', 'EN_PROCESO' => 'bg-info-subtle text-info border-info-subtle',
                                    'CANCELADO' => 'bg-danger-subtle text-danger border-danger-subtle',
                                    default => 'bg-warning-subtle text-warning border-warning-subtle'
                                };
                                $fPed = $pedido['fecha_pedido'] ?? '';
                                $fPedFmt = !empty($fPed) ? date('d/m/Y h:i A', strtotime($fPed)) : '';
                            ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-body">
                                        #<?= htmlspecialchars($pedido['numero_pedido'] ?? $pedido['id_pedido']) ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-body">
                                            <?= htmlspecialchars(($pedido['nombre'] ?? '') . ' ' . ($pedido['apellido'] ?? 'Cliente General')) ?>
                                        </div>
                                        <span class="text-body-secondary" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars($fPedFmt) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-body-tertiary text-body border">
                                            <?= htmlspecialchars($pedido['tipo_pedido'] ?? 'Mesa') ?>
                                        </span>
                                    </td>
                                    <td class="text-body-secondary">
                                        <?= htmlspecialchars($pedido['metodo_pago'] ?? 'Efectivo') ?>
                                    </td>
                                    <td class="fw-bold text-success">
                                        $<?= number_format((float)($pedido['total'] ?? 0), 2) ?>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill border <?= $badgeP ?> px-2 py-1">
                                            <?= $estP ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-body-secondary small mb-0">No se han registrado pedidos en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CONTENEDOR DE DATOS PARA JAVASCRIPT (Desacoplado) -->
    <div id="dashboardData"
         data-categorias="<?= htmlspecialchars(json_encode($productosCategoria ?? []), ENT_QUOTES, 'UTF-8') ?>"
         data-reservaciones="<?= htmlspecialchars(json_encode($reservacionesMes ?? []), ENT_QUOTES, 'UTF-8') ?>"
         class="d-none"></div>
</div>
