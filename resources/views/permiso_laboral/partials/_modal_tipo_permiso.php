<div class="modal fade" id="modalTipoPermiso" tabindex="-1" aria-labelledby="modalTipoPermisoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalTipoPermisoLabel">
                    <i class="fas fa-tags text-warning me-2"></i>
                    <span id="modalTitleTextTipoPermiso">Tipos de Permiso</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoTipo">
                        <i class="fas fa-plus me-2"></i>Nuevo Tipo
                    </button>
                </div>
                <?php include_once $basePath . '/resources/views/tipo_permiso/partials/_tabla_tipo_permiso.php'; ?>
            </div>

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" id="btn-TipoPermisoPCancel" data-bs-dismiss="modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
