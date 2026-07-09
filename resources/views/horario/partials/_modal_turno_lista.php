<!-- ==========================================
    MODAL DE LISTA DE TURNOS (para Horario)
    ========================================== -->

<div class="modal fade" id="modalTurnoLista" tabindex="-1" aria-labelledby="modalTurnoListaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalTurnoListaLabel">
                    <i class="fas fa-clock text-warning me-2"></i>
                    Gestión de Turnos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoTurnoLista">
                        <i class="fas fa-plus me-2"></i>Nuevo Turno
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablaTurnoLista" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Hora Inicio</th>
                                <th>Hora Fin</th>
                                <th>Tolerancia</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>