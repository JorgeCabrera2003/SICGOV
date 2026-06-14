<div class="modal fade" id="modalTurno" tabindex="-1" aria-labelledby="modalTurnoLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalTurnoLabel">
                    <i class="fas fa-clock text-warning me-2"></i>
                    <span id="modalTitleTextTurno"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formTurno">
                <input type="hidden" id="id_turno" name="id_turno">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold">Nombre del Turno <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" maxlength="60" required>
                        <div class="form-label text-danger" id="snombre"></div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="hora_inicio" class="form-label fw-semibold">Hora Inicio</label>
                            <input type="time" class="form-control" id="hora_inicio" name="hora_inicio">
                            <div class="form-label text-danger" id="shora_inicio"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="hora_fin" class="form-label fw-semibold">Hora Fin</label>
                            <input type="time" class="form-control" id="hora_fin" name="hora_fin">
                            <div class="form-label text-danger" id="shora_fin"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="minuto_tolerancia" class="form-label fw-semibold">Margen de tolerancia</label>
                        <div class="input-group input-group-sm" style="max-width:160px;">
                            <input type="number" class="form-control text-end" id="minuto_tolerancia" name="minuto_tolerancia" min="0" step="1" value="15" aria-label="Margen de tolerancia">
                            <span class="input-group-text">min.</span>
                        </div>
                        <div class="form-label text-danger" id="sminuto_tolerancia"></div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnTurnoForm"></button>
                </div>
            </form>
        </div>
    </div>
</div>
