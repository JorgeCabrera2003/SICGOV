<!-- ==========================================
    CENTRO DE REPORTES - SICGOV
    Refactorizado: Estructura Modular
========================================== -->

<main class="container-fluid py-4">
    <header class="mb-5 text-center">
        <h1 class="display-5 fw-bold text-gradient">Centro de Reportes</h1>
        <p class="text-muted fs-5">Genera documentos oficiales y análisis de datos en formato PDF.</p>
    </header>

    <div class="row g-4 justify-content-center">
        <!-- Reporte de Reservaciones -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-lg hover-transform card-report">
                <div class="card-body p-4 text-center">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary mb-4 mx-auto">
                        <i class="bi bi-calendar-check fs-2"></i>
                    </div>
                    <h5 class="fw-bold">Reservaciones</h5>
                    <p class="text-muted small">Listado completo de citas, estados y horarios de atención al cliente.</p>
                    <button type="button" class="btn btn-primary w-100 rounded-pill fw-bold py-2 btn-config-report" data-tipo="reservaciones">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>

        <!-- Reporte de Usuarios -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-lg hover-transform card-report">
                <div class="card-body p-4 text-center">
                    <div class="icon-shape bg-info bg-opacity-10 text-info mb-4 mx-auto">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                    <h5 class="fw-bold">Usuarios</h5>
                    <p class="text-muted small">Reporte administrativo del personal registrado y sus niveles de acceso.</p>
                    <button type="button" class="btn btn-info w-100 rounded-pill fw-bold py-2 text-white btn-config-report" data-tipo="usuarios">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>

        <!-- Reporte de Productos -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-lg hover-transform card-report">
                <div class="card-body p-4 text-center">
                    <div class="icon-shape bg-success bg-opacity-10 text-success mb-4 mx-auto">
                        <i class="bi bi-box-seam fs-2"></i>
                    </div>
                    <h5 class="fw-bold">Inventario / Menú</h5>
                    <p class="text-muted small">Análisis de productos disponibles, categorías y precios actuales.</p>
                    <button type="button" class="btn btn-success w-100 rounded-pill fw-bold py-2 text-white btn-config-report" data-tipo="productos">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>

        <!-- Reporte de Mesas -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-lg hover-transform card-report">
                <div class="card-body p-4 text-center">
                    <div class="icon-shape bg-warning bg-opacity-10 text-warning mb-4 mx-auto" style="color: #fd7e14 !important; background-color: rgba(253, 126, 20, 0.1) !important;">
                        <i class="bi bi-table fs-2"></i>
                    </div>
                    <h5 class="fw-bold">Mesas / Áreas</h5>
                    <p class="text-muted small">Distribución de mesas por áreas, capacidad y estado de ocupación.</p>
                    <button type="button" class="btn btn-warning w-100 rounded-pill fw-bold py-2 text-white btn-config-report" data-tipo="mesas" style="background-color: #fd7e14; border-color: #fd7e14;">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-5 pt-5 text-center">
        <div class="alert alert-light border-0 shadow-sm d-inline-block p-4 rounded-4">
            <i class="bi bi-info-circle text-primary me-2 fs-5"></i>
            Todos los reportes son generados bajo el estándar <strong>SICGOV-ECO</strong> para ahorro de tinta.
        </div>
    </div>
</main>

<!-- Inclusión de Partials -->
<?php require_once __DIR__ . '/partials/_modal_config.php'; ?>
