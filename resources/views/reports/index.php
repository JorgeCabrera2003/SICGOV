<!-- ==========================================
    CENTRO DE REPORTES - SICGOV
    Diseño: Premium Glassmorphism
========================================== -->

<main class="container-fluid py-4">
    <header class="mb-5 text-center">
        <h1 class="display-5 fw-bold text-gradient">Centro de Inteligencia y Reportes</h1>
        <p class="text-muted fs-5">Genera documentos oficiales y análisis de datos en formato PDF con un solo clic.</p>
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
                    <form method="POST" target="_blank">
                        <input type="hidden" name="peticion" value="generar">
                        <input type="hidden" name="tipo" value="reservaciones">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Generar PDF
                        </button>
                    </form>
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
                    <form method="POST" target="_blank">
                        <input type="hidden" name="peticion" value="generar">
                        <input type="hidden" name="tipo" value="usuarios">
                        <button type="submit" class="btn btn-info w-100 rounded-pill fw-bold py-2 text-white">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Generar PDF
                        </button>
                    </form>
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
                    <form method="POST" target="_blank">
                        <input type="hidden" name="peticion" value="generar">
                        <input type="hidden" name="tipo" value="productos">
                        <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold py-2 text-white">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Generar PDF
                        </button>
                    </form>
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

<style>
    .text-gradient {
        background: linear-gradient(135deg, #1A1C20 0%, #444 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .card-report {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 24px !important;
    }
    
    .hover-transform:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }
    
    .icon-shape {
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
    }

    body.dark-mode .text-gradient {
        background: linear-gradient(135deg, #fff 0%, #888 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    body.dark-mode .card-report {
        background-color: #1A1C20 !important;
        border: 1px solid rgba(255,255,255,0.05) !important;
    }
</style>
