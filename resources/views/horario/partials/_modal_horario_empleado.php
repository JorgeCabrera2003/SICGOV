<!-- ==========================================
    MODAL DE HORARIO DEL EMPLEADO
    ========================================== -->

<div class="modal fade" id="modalHorarioEmpleado" tabindex="-1" aria-labelledby="modalHorarioEmpleadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalHorarioEmpleadoLabel">
                    <i class="fas fa-user-clock text-warning me-2"></i>
                    Horario de <span id="nombreEmpleadoTitulo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Calendario del empleado -->
                <div id="calendarEmpleado" style="min-height: 500px;"></div>
            </div>
            <div class="modal-footer border-top-0 d-flex justify-content-between">
                <div class="d-flex gap-2">
                    <span id="detalleTurno" class="badge fs-6 px-3 py-2"></span>
                    <span id="detalleDiasAsignados" class="badge bg-primary fs-6 px-3 py-2"></span>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>