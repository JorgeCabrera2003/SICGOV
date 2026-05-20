<div class="modal fade" id="modalObservacion" tabindex="-1" aria-labelledby="modalObservacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalObservacionLabel">
                    <i class="fas fa-clipboard-list text-warning me-2"></i>
                    <span id="observacionModalTitle">Agregar Observaciones</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Empleado</label>
                        <div id="observacionEmpleado" class="form-control-plaintext text-body"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha / Hora</label>
                        <div id="observacionFechaHora" class="form-control-plaintext text-body"></div>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo</label>
                        <div id="observacionTipo" class="form-control-plaintext text-body"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado</label>
                        <div id="observacionEstado" class="form-control-plaintext"></div>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label for="observacionInput" class="form-label fw-semibold">Nueva observación</label>
                        <textarea id="observacionInput" class="form-control" rows="4" placeholder="Escribe tu observación aquí..."></textarea>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones previas</label>
                        <div id="observacionActual" class="p-3 bg-body-tertiary rounded" style="min-height:120px; white-space: pre-wrap; word-break: break-word;">Sin observaciones previas.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnAgregarObservacion">Agregar</button>
            </div>
        </div>
    </div>
</div>
