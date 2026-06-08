<!-- ==============================================================================
    ESTADÍSTICAS REALES DEL SISTEMA - SICGOV
    Maquetación 100% Semántica y Soporte Premium para Chart.js
============================================================================== -->

<main class="container-fluid py-4">
    <!-- Cabecera Principal de la Página -->
    <header class="mb-5 text-center">
        <h1 class="display-5 fw-bold text-gradient d-inline-flex align-items-center justify-content-center gap-3">
            <span>Estadísticas del Sistema</span>
        </h1>
        
    </header>

    <!-- SECCIÓN 1: Indicadores Clave de Rendimiento (KPIs) -->
    <section aria-labelledby="kpi-section-title" class="mb-5">
        <h2 id="kpi-section-title" class="visually-hidden">Resumen de Indicadores Clave</h2>
        
        <div class="row g-4 justify-content-center">
            <!-- KPI 1: Reservaciones -->
            <article class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow hover-transform card-report text-center p-4">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary mb-3 mx-auto" aria-hidden="true">
                        <i class="bi bi-calendar-check fs-2"></i>
                    </div>
                    <h3 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Reservaciones</h3>
                    <div class="my-2">
                        <data class="kpi-value h2 fw-bold text-primary" id="kpi-reservaciones" value="0">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </data>
                    </div>
                    <p class="text-muted small mb-0">Total registradas históricamente</p>
                </div>
            </article>

            <!-- KPI 2: Clientes Activos -->
            <article class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow hover-transform card-report text-center p-4">
                    <div class="icon-shape bg-info bg-opacity-10 text-info mb-3 mx-auto" aria-hidden="true">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                    <h3 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Clientes Activos</h3>
                    <div class="my-2">
                        <data class="kpi-value h2 fw-bold text-info" id="kpi-clientes" value="0">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </data>
                    </div>
                    <p class="text-muted small mb-0">Clientes fidelizados en la base</p>
                </div>
            </article>

            <!-- KPI 3: Platos en Carta -->
            <article class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow hover-transform card-report text-center p-4">
                    <div class="icon-shape bg-success bg-opacity-10 text-success mb-3 mx-auto" aria-hidden="true">
                        <i class="bi bi-egg-fried fs-2"></i>
                    </div>
                    <h3 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Platos en Carta</h3>
                    <div class="my-2">
                        <data class="kpi-value h2 fw-bold text-success" id="kpi-productos" value="0">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </data>
                    </div>
                    <p class="text-muted small mb-0">Opciones activas en el menú</p>
                </div>
            </article>

            <!-- KPI 4: Asistencias de Hoy -->
            <article class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow hover-transform card-report text-center p-4">
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning mb-3 mx-auto" style="color: #fd7e14 !important; background-color: rgba(253, 126, 20, 0.1) !important;" aria-hidden="true">
                        <i class="bi bi-clock-history fs-2"></i>
                    </div>
                    <h3 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Asistencias Hoy</h3>
                    <div class="my-2">
                        <data class="kpi-value h2 fw-bold text-warning" id="kpi-asistencias" value="0" style="color: #fd7e14 !important;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </data>
                    </div>
                    <p class="text-muted small mb-0">Marcaciones completadas hoy</p>
                </div>
            </article>
        </div>

        <!-- SECCIÓN 1.5: KPIs Financieros y de Ventas Premium -->
        <div class="row g-4 justify-content-center mt-2">
            <!-- KPI 5: Ganancias Totales -->
            <article class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow hover-transform card-report card-kpi-ganancias text-center p-4">
                    <div class="icon-shape bg-success bg-opacity-10 text-success mb-3 mx-auto" aria-hidden="true">
                        <i class="bi bi-cash-stack fs-2"></i>
                    </div>
                    <h3 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Ganancias Totales</h3>
                    <div class="my-2">
                        <span class="h2 fw-bold text-success">Bs. </span>
                        <data class="kpi-value h2 fw-bold text-success" id="kpi-ganancias" value="0">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </data>
                    </div>
                    <p class="text-muted small mb-0">Total caja recaudado en facturación</p>
                </div>
            </article>

            <!-- KPI 6: Producto Estrella -->
            <article class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow hover-transform card-report card-kpi-estrella text-center p-4">
                    <div class="icon-shape bg-danger bg-opacity-10 text-danger mb-3 mx-auto" aria-hidden="true">
                        <i class="bi bi-star-fill fs-2"></i>
                    </div>
                    <h3 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Producto Estrella</h3>
                    <div class="my-2">
                        <div class="h5 fw-bold text-danger text-truncate px-2" id="kpi-producto-estrella-nombre">Cargando...</div>
                        <div class="text-muted small mt-1" id="kpi-producto-estrella-detalles">0 unidades vendidas</div>
                    </div>
                    <p class="text-muted small mb-0">Plato/bebida con más ventas</p>
                </div>
            </article>

            <!-- KPI 7: Cliente del Mes -->
            <article class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow hover-transform card-report card-kpi-top-client text-center p-4">
                    <div class="icon-shape bg-primary bg-opacity-10 mb-3 mx-auto" style="color: #6c5ce7; background-color: rgba(108, 92, 231, 0.1) !important;" aria-hidden="true">
                        <i class="bi bi-trophy fs-2" style="color: #6c5ce7;"></i>
                    </div>
                    <h3 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Cliente Top</h3>
                    <div class="my-2">
                        <div class="h5 fw-bold text-truncate px-2" style="color: #6c5ce7;" id="kpi-cliente-top-nombre">Cargando...</div>
                        <div class="text-muted small mt-1" id="kpi-cliente-top-detalles">0 consumos</div>
                    </div>
                    <p class="text-muted small mb-0">Cliente más recurrente</p>
                </div>
            </article>

            <!-- KPI 8: Ocupación de Mesas -->
            <article class="col-sm-6 col-lg-3">
                <div class="card h-100 border-0 shadow hover-transform card-report card-kpi-ocupacion text-center p-4">
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning mb-3 mx-auto" style="color: #fd7e14 !important; background-color: rgba(253, 126, 20, 0.1) !important;" aria-hidden="true">
                        <i class="bi bi-percent fs-2" style="color: #fd7e14;"></i>
                    </div>
                    <h3 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Ocupación de Mesas</h3>
                    <div class="my-2">
                        <data class="kpi-value h2 fw-bold text-warning" id="kpi-ocupacion-mesas" value="0" style="color: #fd7e14 !important;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </data>
                        <span class="h2 fw-bold text-warning" style="color: #fd7e14 !important;">%</span>
                    </div>
                    <p class="text-muted small mb-0">Ocupación en tiempo real</p>
                </div>
            </article>
        </div>
    </section>

    <!-- SECCIÓN 2: Tendencias y Estados de Reservaciones -->
    <section aria-labelledby="reservaciones-section-title" class="mb-5">
        <h2 id="reservaciones-section-title" class="visually-hidden">Análisis Gráfico de Reservaciones</h2>
        
        <div class="row g-4">
            <!-- Tendencia Mensual de Reservas -->
            <article class="col-lg-8">
                <div class="card border-0 shadow card-report h-100">
                    <header class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 fw-bold mb-0">Tendencia de Reservas</h3>
                            <small class="text-muted">Historial consolidado mensual o diario</small>
                        </div>
                        <div class="chart-actions d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-toggle-local-filter d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#local-filter-tiempo" aria-label="Filtrar" style="width: 28px; height: 28px;">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <select class="form-select form-select-sm chart-type-select shadow-sm border-0 px-2 py-1 rounded" style="width: auto; font-size: 0.8rem;" data-chart="tiempo">
                                <option value="line" selected>Línea</option>
                                <option value="bar">Barra</option>
                                <option value="radar">Radar</option>
                            </select>
                        </div>
                    </header>
                    <div class="collapse local-filter-panel px-4 py-3 border-top border-light" id="local-filter-tiempo">
                        <form class="form-local-filter" data-chart="tiempo">
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Desde</label>
                                    <input type="date" class="form-control form-control-sm" name="tiempo_fecha_desde">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="tiempo_fecha_hasta">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Mesa</label>
                                    <select class="form-select form-select-sm local-select-mesas" name="tiempo_reserva_mesa">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-local-filter rounded-pill px-3" data-chart="tiempo">
                                        <i class="bi bi-x-circle"></i> Limpiar
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <section class="card-body px-4 pb-4 position-relative" style="height: 360px;">
                        <!-- Loader con alineación absoluta correcta -->
                        <div class="chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-75" id="loader-reservas-tiempo" style="z-index: 10; border-radius: 24px;">
                            <div class="spinner-grow text-primary" role="status">
                                <span class="visually-hidden">Cargando datos...</span>
                            </div>
                        </div>
                        <!-- Contenedor adaptativo de Chart.js -->
                        <div class="chart-container" style="position: relative; height: 100%; width: 100%;">
                            <canvas id="chart-reservas-tiempo"></canvas>
                        </div>
                    </section>
                </div>
            </article>

            <!-- Proporciones por Estado -->
            <article class="col-lg-4">
                <div class="card border-0 shadow card-report h-100">
                    <header class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 fw-bold mb-0">Estados de Reservas</h3>
                            <small class="text-muted">Proporciones por estado actual</small>
                        </div>
                        <div class="chart-actions d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-toggle-local-filter d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#local-filter-estado" aria-label="Filtrar" style="width: 28px; height: 28px;">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <select class="form-select form-select-sm chart-type-select shadow-sm border-0 px-2 py-1 rounded" style="width: auto; font-size: 0.8rem;" data-chart="estado">
                                <option value="doughnut" selected>Dona</option>
                                <option value="pie">Torta</option>
                                <option value="polarArea">Área Polar</option>
                                <option value="bar">Barra</option>
                            </select>
                        </div>
                    </header>
                    <div class="collapse local-filter-panel px-4 py-3 border-top border-light" id="local-filter-estado">
                        <form class="form-local-filter" data-chart="estado">
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Desde</label>
                                    <input type="date" class="form-control form-control-sm" name="estado_fecha_desde">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="estado_fecha_hasta">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Mesa</label>
                                    <select class="form-select form-select-sm local-select-mesas" name="estado_reserva_mesa">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-local-filter rounded-pill px-3" data-chart="estado">
                                        <i class="bi bi-x-circle"></i> Limpiar
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <section class="card-body px-4 pb-4 position-relative" style="height: 360px;">
                        <!-- Loader con alineación absoluta correcta -->
                        <div class="chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-75" id="loader-reservas-estado" style="z-index: 10; border-radius: 24px;">
                            <div class="spinner-grow text-primary" role="status">
                                <span class="visually-hidden">Cargando datos...</span>
                            </div>
                        </div>
                        <!-- Contenedor adaptativo de Chart.js -->
                        <div class="chart-container" style="position: relative; height: 100%; width: 100%;">
                            <canvas id="chart-reservas-estado"></canvas>
                        </div>
                    </section>
                </div>
            </article>
        </div>
    </section>

    <!-- SECCIÓN 3: Variedad del Menú, Personal e Inteligencia de Seguridad -->
    <section aria-labelledby="operaciones-section-title" class="mb-4">
        <h2 id="operaciones-section-title" class="visually-hidden">Análisis de Menú, Personal y Seguridad</h2>
        
        <div class="row g-4">
            <!-- Asistencia de Personal (Pie) -->
            <article class="col-md-6 col-lg-4">
                <div class="card border-0 shadow card-report h-100">
                    <header class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 fw-bold mb-0">Asistencia del Personal</h3>
                            <small class="text-muted">Cumplimiento y puntualidad general</small>
                        </div>
                        <div class="chart-actions d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-toggle-local-filter d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#local-filter-asistencia" aria-label="Filtrar" style="width: 28px; height: 28px;">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <select class="form-select form-select-sm chart-type-select shadow-sm border-0 px-2 py-1 rounded" style="width: auto; font-size: 0.8rem;" data-chart="asistencia">
                                <option value="pie" selected>Torta</option>
                                <option value="doughnut">Dona</option>
                                <option value="polarArea">Área Polar</option>
                                <option value="bar">Barra</option>
                            </select>
                        </div>
                    </header>
                    <div class="collapse local-filter-panel px-4 py-3 border-top border-light" id="local-filter-asistencia">
                        <form class="form-local-filter" data-chart="asistencia">
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Desde</label>
                                    <input type="date" class="form-control form-control-sm" name="asistencia_fecha_desde">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="asistencia_fecha_hasta">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Empleado</label>
                                    <select class="form-select form-select-sm local-select-empleados" name="asistencia_empleado">
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Estado</label>
                                    <select class="form-select form-select-sm" name="asistencia_estado">
                                        <option value="">Todos</option>
                                        <option value="A_TIEMPO">A TIEMPO</option>
                                        <option value="TARDE">TARDE</option>
                                        <option value="FALTA">FALTA</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-local-filter rounded-pill px-3" data-chart="asistencia">
                                        <i class="bi bi-x-circle"></i> Limpiar
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <section class="card-body px-4 pb-4 position-relative" style="height: 320px;">
                        <!-- Loader con alineación absoluta correcta -->
                        <div class="chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-75" id="loader-asistencia" style="z-index: 10; border-radius: 24px;">
                            <div class="spinner-grow text-success" role="status">
                                <span class="visually-hidden">Cargando datos...</span>
                            </div>
                        </div>
                        <!-- Contenedor adaptativo de Chart.js -->
                        <div class="chart-container" style="position: relative; height: 100%; width: 100%;">
                            <canvas id="chart-asistencia"></canvas>
                        </div>
                    </section>
                </div>
            </article>

            <!-- Variedad de Menú por Categorías (Barra Vertical) -->
            <article class="col-md-6 col-lg-4">
                <div class="card border-0 shadow card-report h-100">
                    <header class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 fw-bold mb-0">Variedad del Menú</h3>
                            <small class="text-muted">Cantidad de platos por categoría</small>
                        </div>
                        <div class="chart-actions d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-toggle-local-filter d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#local-filter-menu" aria-label="Filtrar" style="width: 28px; height: 28px;">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <select class="form-select form-select-sm chart-type-select shadow-sm border-0 px-2 py-1 rounded" style="width: auto; font-size: 0.8rem;" data-chart="menu">
                                <option value="bar" selected>Barra</option>
                                <option value="line">Línea</option>
                                <option value="radar">Radar</option>
                                <option value="doughnut">Dona</option>
                            </select>
                        </div>
                    </header>
                    <div class="collapse local-filter-panel px-4 py-3 border-top border-light" id="local-filter-menu">
                        <form class="form-local-filter" data-chart="menu">
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-8">
                                    <label class="form-label small fw-semibold text-muted mb-1">Categoría</label>
                                    <select class="form-select form-select-sm local-select-categorias" name="menu_categoria_producto">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                                <div class="col-sm-4 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-local-filter rounded-pill px-3" data-chart="menu">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <section class="card-body px-4 pb-4 position-relative" style="height: 320px;">
                        <!-- Loader con alineación absoluta correcta -->
                        <div class="chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-75" id="loader-menu" style="z-index: 10; border-radius: 24px;">
                            <div class="spinner-grow text-info" role="status">
                                <span class="visually-hidden">Cargando datos...</span>
                            </div>
                        </div>
                        <!-- Contenedor adaptativo de Chart.js -->
                        <div class="chart-container" style="position: relative; height: 100%; width: 100%;">
                            <canvas id="chart-menu"></canvas>
                        </div>
                    </section>
                </div>
            </article>

            <!-- Actividad del Sistema por Módulo (Barra Horizontal) -->
            <article class="col-lg-4">
                <div class="card border-0 shadow card-report h-100">
                    <header class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 fw-bold mb-0">Actividad de Seguridad</h3>
                            <small class="text-muted">Módulos más auditados en Bitácora</small>
                        </div>
                        <div class="chart-actions d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-toggle-local-filter d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#local-filter-bitacora" aria-label="Filtrar" style="width: 28px; height: 28px;">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <select class="form-select form-select-sm chart-type-select shadow-sm border-0 px-2 py-1 rounded" style="width: auto; font-size: 0.8rem;" data-chart="bitacora">
                                <option value="bar" selected>Barra</option>
                                <option value="line">Línea</option>
                                <option value="pie">Torta</option>
                                <option value="radar">Radar</option>
                            </select>
                        </div>
                    </header>
                    <div class="collapse local-filter-panel px-4 py-3 border-top border-light" id="local-filter-bitacora">
                        <form class="form-local-filter" data-chart="bitacora">
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Desde</label>
                                    <input type="date" class="form-control form-control-sm" name="bitacora_fecha_desde">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="bitacora_fecha_hasta">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Módulo</label>
                                    <select class="form-select form-select-sm" name="bitacora_modulo">
                                        <option value="">Todos</option>
                                        <option value="SEGURIDAD">SEGURIDAD</option>
                                        <option value="RESERVACIONES">RESERVACIONES</option>
                                        <option value="COMPRAS">COMPRAS</option>
                                        <option value="VENTAS">VENTAS</option>
                                        <option value="INVENTARIO">INVENTARIO</option>
                                        <option value="CLIENTES">CLIENTES</option>
                                        <option value="EMPLEADOS">EMPLEADOS</option>
                                        <option value="ASISTENCIA">ASISTENCIA</option>
                                        <option value="PLATOS">PLATOS</option>
                                        <option value="BITACORA">BITACORA</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Empleado</label>
                                    <select class="form-select form-select-sm local-select-empleados" name="bitacora_empleado">
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                                <div class="col-sm-8">
                                    <label class="form-label small fw-semibold text-muted mb-1">Acción (Buscar)</label>
                                    <input type="text" class="form-control form-control-sm" name="bitacora_accion" placeholder="Buscar acción...">
                                </div>
                                <div class="col-sm-4 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-local-filter rounded-pill px-3" data-chart="bitacora">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <section class="card-body px-4 pb-4 position-relative" style="height: 320px;">
                        <!-- Loader con alineación absoluta correcta -->
                        <div class="chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-75" id="loader-bitacora" style="z-index: 10; border-radius: 24px;">
                            <div class="spinner-grow text-warning" role="status">
                                <span class="visually-hidden">Cargando datos...</span>
                            </div>
                        </div>
                        <!-- Contenedor adaptativo de Chart.js -->
                        <div class="chart-container" style="position: relative; height: 100%; width: 100%;">
                            <canvas id="chart-bitacora"></canvas>
                        </div>
                    </section>
                </div>
            </article>
        </div>
    </section>

    <!-- SECCIÓN 4: Rendimiento de Ventas y Métodos de Pago -->
    <section aria-labelledby="ventas-section-title" class="mb-5">
        <h2 id="ventas-section-title" class="visually-hidden">Análisis de Ventas y Métodos de Pago</h2>
        
        <div class="row g-4">
            <!-- Top 5 Productos Más Vendidos (Barra Horizontal) -->
            <article class="col-lg-8">
                <div class="card border-0 shadow card-report h-100">
                    <header class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 fw-bold mb-0">Top 5 Productos Más Vendidos</h3>
                            <small class="text-muted">Platos y bebidas con mayor volumen de salida</small>
                        </div>
                        <div class="chart-actions d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-toggle-local-filter d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#local-filter-productosTop" aria-label="Filtrar" style="width: 28px; height: 28px;">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <select class="form-select form-select-sm chart-type-select shadow-sm border-0 px-2 py-1 rounded" style="width: auto; font-size: 0.8rem;" data-chart="productosTop">
                                <option value="bar" selected>Barra</option>
                                <option value="line">Línea</option>
                                <option value="doughnut">Dona</option>
                                <option value="polarArea">Área Polar</option>
                            </select>
                        </div>
                    </header>
                    <div class="collapse local-filter-panel px-4 py-3 border-top border-light" id="local-filter-productosTop">
                        <form class="form-local-filter" data-chart="productosTop">
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Desde</label>
                                    <input type="date" class="form-control form-control-sm" name="productos_fecha_desde">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="productos_fecha_hasta">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Tipo Pedido</label>
                                    <select class="form-select form-select-sm" name="productos_pedido_tipo">
                                        <option value="">Todos</option>
                                        <option value="DELIVERY">DELIVERY</option>
                                        <option value="PICKUP">PICKUP</option>
                                        <option value="PRESENCIAL">PRESENCIAL</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-local-filter rounded-pill px-3" data-chart="productosTop">
                                        <i class="bi bi-x-circle"></i> Limpiar
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <section class="card-body px-4 pb-4 position-relative" style="height: 360px;">
                        <!-- Loader con alineación absoluta correcta -->
                        <div class="chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-75" id="loader-productos-top" style="z-index: 10; border-radius: 24px;">
                            <div class="spinner-grow text-danger" role="status">
                                <span class="visually-hidden">Cargando datos...</span>
                            </div>
                        </div>
                        <!-- Contenedor adaptativo de Chart.js -->
                        <div class="chart-container" style="position: relative; height: 100%; width: 100%;">
                            <canvas id="chart-productos-top"></canvas>
                        </div>
                    </section>
                </div>
            </article>

            <!-- Distribución de Métodos de Pago (Dona) -->
            <article class="col-lg-4">
                <div class="card border-0 shadow card-report h-100">
                    <header class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 fw-bold mb-0">Métodos de Pago</h3>
                            <small class="text-muted">Preferencia de pago de los clientes</small>
                        </div>
                        <div class="chart-actions d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-toggle-local-filter d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#local-filter-metodosPago" aria-label="Filtrar" style="width: 28px; height: 28px;">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <select class="form-select form-select-sm chart-type-select shadow-sm border-0 px-2 py-1 rounded" style="width: auto; font-size: 0.8rem;" data-chart="metodosPago">
                                <option value="doughnut" selected>Dona</option>
                                <option value="pie">Torta</option>
                                <option value="polarArea">Área Polar</option>
                                <option value="bar">Barra</option>
                            </select>
                        </div>
                    </header>
                    <div class="collapse local-filter-panel px-4 py-3 border-top border-light" id="local-filter-metodosPago">
                        <form class="form-local-filter" data-chart="metodosPago">
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Desde</label>
                                    <input type="date" class="form-control form-control-sm" name="pagos_fecha_desde">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="pagos_fecha_hasta">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Tipo Pedido</label>
                                    <select class="form-select form-select-sm" name="pagos_pedido_tipo">
                                        <option value="">Todos</option>
                                        <option value="DELIVERY">DELIVERY</option>
                                        <option value="PICKUP">PICKUP</option>
                                        <option value="PRESENCIAL">PRESENCIAL</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-local-filter rounded-pill px-3" data-chart="metodosPago">
                                        <i class="bi bi-x-circle"></i> Limpiar
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <section class="card-body px-4 pb-4 position-relative" style="height: 360px;">
                        <!-- Loader con alineación absoluta correcta -->
                        <div class="chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-75" id="loader-metodos-pago" style="z-index: 10; border-radius: 24px;">
                            <div class="spinner-grow text-success" role="status">
                                <span class="visually-hidden">Cargando datos...</span>
                            </div>
                        </div>
                        <!-- Contenedor adaptativo de Chart.js -->
                        <div class="chart-container" style="position: relative; height: 100%; width: 100%;">
                            <canvas id="chart-metodos-pago"></canvas>
                        </div>
                    </section>
                </div>
            </article>
        </div>
    </section>

    <!-- SECCIÓN 5: Distribución de Mesas e Inventario -->
    <section aria-labelledby="recursos-section-title" class="mb-5">
        <h2 id="recursos-section-title" class="visually-hidden">Análisis de Distribución de Mesas y Recursos de Inventario</h2>
        
        <div class="row g-4">
            <!-- Popularidad de Mesas (Radar) -->
            <article class="col-lg-6">
                <div class="card border-0 shadow card-report h-100">
                    <header class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 fw-bold mb-0">Popularidad de Mesas</h3>
                            <small class="text-muted">Mesas con mayor cantidad de reservaciones históricas</small>
                        </div>
                        <div class="chart-actions d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-toggle-local-filter d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#local-filter-mesasPopularidad" aria-label="Filtrar" style="width: 28px; height: 28px;">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <select class="form-select form-select-sm chart-type-select shadow-sm border-0 px-2 py-1 rounded" style="width: auto; font-size: 0.8rem;" data-chart="mesasPopularidad">
                                <option value="radar" selected>Radar</option>
                                <option value="polarArea">Área Polar</option>
                                <option value="bar">Barra</option>
                                <option value="doughnut">Dona</option>
                            </select>
                        </div>
                    </header>
                    <div class="collapse local-filter-panel px-4 py-3 border-top border-light" id="local-filter-mesasPopularidad">
                        <form class="form-local-filter" data-chart="mesasPopularidad">
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Desde</label>
                                    <input type="date" class="form-control form-control-sm" name="mesas_fecha_desde">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Hasta</label>
                                    <input type="date" class="form-control form-control-sm" name="mesas_fecha_hasta">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Estado Reserva</label>
                                    <select class="form-select form-select-sm" name="mesas_reserva_estado">
                                        <option value="">Todos</option>
                                        <option value="PENDIENTE">PENDIENTE</option>
                                        <option value="CONFIRMADA">CONFIRMADA</option>
                                        <option value="CANCELADA">CANCELADA</option>
                                        <option value="COMPLETADA">COMPLETADA</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-local-filter rounded-pill px-3" data-chart="mesasPopularidad">
                                        <i class="bi bi-x-circle"></i> Limpiar
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <section class="card-body px-4 pb-4 position-relative" style="height: 360px;">
                        <!-- Loader con alineación absoluta correcta -->
                        <div class="chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-75" id="loader-mesas-popularidad" style="z-index: 10; border-radius: 24px;">
                            <div class="spinner-grow text-primary" role="status">
                                <span class="visually-hidden">Cargando datos...</span>
                            </div>
                        </div>
                        <!-- Contenedor adaptativo de Chart.js -->
                        <div class="chart-container" style="position: relative; height: 100%; width: 100%;">
                            <canvas id="chart-mesas-popularidad"></canvas>
                        </div>
                    </section>
                </div>
            </article>

            <!-- Alertas de Stock de Ingredientes (Doble Barra: Actual vs Mínimo) -->
            <article class="col-lg-6">
                <div class="card border-0 shadow card-report h-100">
                    <header class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 fw-bold mb-0">Stock Crítico de Ingredientes</h3>
                            <small class="text-muted">Ingredientes con stock actual por debajo del mínimo</small>
                        </div>
                        <div class="chart-actions d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary border-0 rounded-circle btn-toggle-local-filter d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#local-filter-ingredientesAlerta" aria-label="Filtrar" style="width: 28px; height: 28px;">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <select class="form-select form-select-sm chart-type-select shadow-sm border-0 px-2 py-1 rounded" style="width: auto; font-size: 0.8rem;" data-chart="ingredientesAlerta">
                                <option value="bar" selected>Barra</option>
                                <option value="line">Línea</option>
                            </select>
                        </div>
                    </header>
                    <div class="collapse local-filter-panel px-4 py-3 border-top border-light" id="local-filter-ingredientesAlerta">
                        <form class="form-local-filter" data-chart="ingredientesAlerta">
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-8">
                                    <label class="form-label small fw-semibold text-muted mb-1">Umbral de Alerta</label>
                                    <select class="form-select form-select-sm" name="ingredientes_ratio_limite">
                                        <option value="1.0">Alerta de stock (<= 100% del mínimo)</option>
                                        <option value="0.5">Crítico severo (<= 50% del mínimo)</option>
                                        <option value="0.25">Alerta extrema (<= 25% del mínimo)</option>
                                    </select>
                                </div>
                                <div class="col-sm-4 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-local-filter rounded-pill px-3" data-chart="ingredientesAlerta">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <section class="card-body px-4 pb-4 position-relative" style="height: 360px;">
                        <!-- Loader con alineación absoluta correcta -->
                        <div class="chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-body bg-opacity-75" id="loader-ingredientes-alerta" style="z-index: 10; border-radius: 24px;">
                            <div class="spinner-grow text-warning" role="status" style="color: #fd7e14 !important;">
                                <span class="visually-hidden">Cargando datos...</span>
                            </div>
                        </div>
                        <!-- Contenedor adaptativo de Chart.js -->
                        <div class="chart-container" style="position: relative; height: 100%; width: 100%;">
                            <canvas id="chart-ingredientes-alerta"></canvas>
                        </div>
                    </section>
                </div>
            </article>
        </div>
    </section>
</main>
