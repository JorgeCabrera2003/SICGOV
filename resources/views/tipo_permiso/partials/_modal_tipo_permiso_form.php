<!-- ==========================================
    MODAL DEl FORMULARIO CATEGORÍA INGREDIENTE - REUTILIZABLE
    ========================================== -->

<div class="modal fade" id="modal-formtipo_permiso" tabindex="-1" aria-labelledby="modal-TipoPermisoFormLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modal-TipoPermisoFormLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleText-Form-TipoPermiso"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formIngrediente" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: ID -->
                    <div class="row g-3 mb-3 justify-content-center d-none">
                        <div class="col-md-6">
                            <input type="hidden" name="id_tipo_permiso" id="id_tipo_permiso">
                            <span class="form-label" id="sid_tipo_permiso"></span>
                        </div>
                    </div>

                    <!-- Fila: Nombre-->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-7 position-relative">
                            <label for="tipo_permiso-nombre" class="form-label fw-semibold">
                                Nombre de la Categoría<span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="tipo_permiso-nombre" name="tipo_permiso-nombre"
                                maxlength="100" required>
                            <div class="form-label" id="stipo_permiso-nombre"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" id="btn-TipoPermisoCancel">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-warning text-dark fw-semibold" id="btn-TipoPermisoForm">

                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>