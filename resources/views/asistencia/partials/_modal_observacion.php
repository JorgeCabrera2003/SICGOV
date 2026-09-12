<div class="modal fade" id="modalObservacion" tabindex="-1" aria-labelledby="modalObservacionLabel" aria-hidden="true"
    data-puede-agregar="<?= (($permisosAsistencia['asistencia']['agregar_observacion'] ?? 0) == 1) ? '1' : '0' ?>"
    data-puede-eliminar="<?= (($permisosAsistencia['asistencia']['eliminar_observacion'] ?? 0) == 1) ? '1' : '0' ?>">
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
                <!-- Información del empleado en una sola fila compacta -->
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted mb-0">Empleado</label>
                        <div id="observacionEmpleado" class="form-control-plaintext fw-bold"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-0">Tipo</label>
                        <div id="observacionTipo" class="form-control-plaintext"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted mb-0">Estado</label>
                        <div id="observacionEstado" class="form-control-plaintext"></div>
                    </div>
                </div>
                
                <!-- Fecha/Hora en una línea separada -->
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-muted mb-0">Fecha / Hora</label>
                        <div id="observacionFechaHora" class="form-control-plaintext"></div>
                    </div>
                </div>

                <!-- Nueva observación -->
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <label for="observacionInput" class="form-label fw-semibold">Nueva observación</label>
                        <textarea id="observacionInput" class="form-control" rows="3" placeholder="Escribe tu observación aquí..."></textarea>
                    </div>
                </div>

                <!-- Observaciones previas -->
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones previas</label>
                        <div id="observacionActual" class="p-3 bg-body-tertiary rounded" style="min-height:80px; max-height: 200px; overflow-y: auto; white-space: pre-wrap; word-break: break-word;"></div>
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