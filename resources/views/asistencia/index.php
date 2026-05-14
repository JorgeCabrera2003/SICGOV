<!-- ==========================================
    MÓDULO DE ASISTENCIA - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">

    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="h3 mb-0">
            <i class="fas fa-box me-2 text-warning"></i>
            Gestión de Asistencia
        </h1>

        <div class="btn-group" role="group" aria-label="Acciones de asistencia">
            <button class="btn btn-warning text-dark fw-semibold" id="btnMarcarAsistencia">
                <i class="fas fa-plus me-2"></i>Marcar Asistencia
            </button>
        </div>

    </header>

    <div class="d-flex align-items-center gap-2 mb-3">
        <div class="btn-group" role="group" aria-label="Filtros de asistencia">
            <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnHistorial">
                <i class="fas fa-history me-2"></i>Historial
            </button>
            <button type="button" class="btn btn-outline-warning" id="btnMiAsistencia" onclick="pendiente()">
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
?>

<!-- Recursos específicos de la página -->
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/AsistenciaController.js" defer></script>