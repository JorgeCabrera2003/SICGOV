<!-- ==========================================
    MÓDULO DE ASISTENCIA - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">

    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="h3 mb-0">
            <i class="fas bi-list-check me-2 text-warning"></i>
            Gestión de Asistencia
        </h1>

        <div class="btn-group" role="group" aria-label="Acciones de asistencia">
            <button class="btn btn-warning text-dark fw-semibold" id="btnMarcarAsistencia">
                <i class="fas fa-plus me-2"></i>Marcar Asistencia
            </button>
            <a href="<?= BASE_URL ?>?page=asistencia-publica" target="_blank" class="btn btn-outline-warning text-dark fw-semibold shadow-sm">
                <i class="fas fa-external-link-alt me-2"></i>Ver Página Pública
            </a>
        </div>

    </header>

    <div class="d-flex align-items-center gap-2 mb-3">
        <div class="btn-group" role="group" aria-label="Filtros de asistencia">
            <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnHistorial">
                <i class="fas fa-history me-2"></i>Histórico
            </button>
            <button type="button" class="btn btn-outline-warning" id="btnMiAsistencia">
                <i class="fas fa-user-clock me-2"></i>Mi Asistencia
            </button>
        </div>
    </div>

    <!-- Tabla de productos (section semántica) -->
    <section class="card shadow-sm border-0">

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaAsistencia" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Hora</th>
                            <th scope="col">Empleado</th>
                            <th scope="col">Tipo de Marcación</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Observación</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </section>
</main>

<!-- Modales (incluidos como partials) -->
<?php
include_once 'partials/_modal_asistencia.php';
include_once 'partials/_modal_observacion.php';
?>

<!-- Recursos específicos de la página -->
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/AsistenciaController.js" defer></script>