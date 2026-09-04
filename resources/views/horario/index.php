<!-- ==========================================
    MÓDULO DE HORARIOS - GOOD VIBES
========================================== -->

<?php
    $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    $hoy = $dias[date('w')] . ", " . date('d') . " de " . $meses[date('n')] . " de " . date('Y');
?>

<main class="container-fluid py-4">

    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-calendar-alt me-2 text-warning"></i>
            Gestión de Horarios
        </h1>
        <div class="btn-group" role="group">
            <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoHorario">
                <i class="fas fa-plus me-2"></i>Asignar Turno
            </button>
            <button class="btn btn-outline-warning text-dark fw-semibold" id="btnGestionarTurnos">
                <i class="fas fa-clock me-2"></i>Turnos
            </button>
        </div>
    </header>

    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnAgendaGlobal">
                <i class="fas fa-globe me-2"></i>Todos los Horarios
            </button>
            <button type="button" class="btn btn-outline-warning" id="btnEmpleados">
                <i class="fas fa-users me-2"></i>Empleados
            </button>
        </div>
        <div class="text-muted small">
            <i class="far fa-calendar-alt me-2 text-primary"></i><?= $hoy ?>
        </div>
    </div>

    <!-- Vista: Todos los Horarios (FullCalendar) -->
    <div id="agendaGlobalContainer" class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div id="calendarHorarios" style="min-height: 650px;"></div>
        </div>
    </div>

    <!-- Vista: Empleados (DataTable) -->
    <section id="empleadosSection" class="card shadow-sm border-0 d-none">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaEmpleados" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Cédula</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Turno Actual</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<!-- Modales -->
<?php
include_once 'partials/_modal_horario.php';
include_once 'partials/_modal_turno_lista.php';
include_once 'partials/_modal_horario_empleado.php';
include_once $basePath . '/resources/views/turno/partials/_modal_turno.php';
?>

<!-- CSS y JS de FullCalendar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/es.global.min.js"></script>
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/HorarioController.js" defer></script>
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/TurnoController.js" defer></script>

<style>
    .fc-event {
        border-radius: 6px;
        font-size: 0.85rem;
        padding: 4px 6px;
        cursor: pointer;
        border: none;
    }
    .fc .fc-toolbar-title { font-size: 1.2rem; }
    .fc .fc-button-primary { background-color: #ffc107; border-color: #ffc107; color: #000; }
    .fc .fc-button-primary:hover { background-color: #e0a800; border-color: #e0a800; }
    .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #d39e00; border-color: #d39e00; }
</style>