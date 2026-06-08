<!-- ==========================================
    MODAL DEl FORMULARIO CATEGORÍA INGREDIENTE - REUTILIZABLE
    ========================================== -->

<div class="modal fade" id="modal-formcargo" tabindex="-1" aria-labelledby="modal-CargoFormLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modal-CargoFormLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleText-Form-Cargo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formIngrediente" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: ID -->
                    <div class="row g-3 mb-3 justify-content-center d-none">
                        <div class="col-md-6">
                            <input type="hidden" name="id_cargo" id="id_cargo">
                            <span class="form-label" id="sid_cargo"></span>
                        </div>
                    </div>

                    <!-- Fila: Nombre-->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-7 position-relative">
                            <label for="cargo-nombre" class="form-label fw-semibold">
                                Nombre del Cargo<span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="cargo-nombre" name="cargo-nombre"
                                maxlength="100" required>
                            <div class="form-label" id="scargo-nombre"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" id="btn-CargoCancel">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-warning text-dark fw-semibold" id="btn-CargoForm">

                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>