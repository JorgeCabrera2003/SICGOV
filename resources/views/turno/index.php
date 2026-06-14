<!-- ==========================================
    MÓDULO DE TURNOS
========================================== -->

<main class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-clock me-2 text-warning"></i>
            Gestión de Turnos
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de turnos">
            <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoTurno">
                <i class="fas fa-plus me-2"></i>Nuevo Turno
            </button>
        </div>
    </header>

    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaTurno" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Hora Inicio</th>
                            <th scope="col">Hora Fin</th>
                            <th scope="col">Margen de tolerancia</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables cargará los datos -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<?php include_once 'partials/_modal_turno.php'; ?>

<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/TurnoController.js" defer></script>
