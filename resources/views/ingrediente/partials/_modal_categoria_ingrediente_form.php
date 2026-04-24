<!-- ==========================================
    MODAL DEl FORMULARIO CATEGORÍA INGREDIENTE - REUTILIZABLE
    ========================================== -->

<div class="modal fade" id="modal-formcategoria" tabindex="-1" aria-labelledby="modal-CategoriaFormLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modal-CategoriaFormLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleText-Form-Categoria"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formIngrediente" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: ID -->
                    <div class="row g-3 mb-3 justify-content-center d-none">
                        <div class="col-md-6">
                            <input type="hidden" name="id_categoria" id="id_categoria">
                            <span class="form-label" id="sid_categoria"></span>
                        </div>
                    </div>

                    <!-- Fila: Nombre-->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-7">
                            <label for="categoria-nombre" class="form-label fw-semibold">
                                Nombre de la Categoría<span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-font"></i>
                                </span>
                                <input type="text" class="form-control" id="categoria-nombre" name="categoria-nombre"
                                    maxlength="100" required>
                            </div>
                            <span class="form-label" id="scategoria-nombre"></span>
                        </div>

                    </div>
                    <!-- Fila: Descripción-->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-7">
                            <label for="categoria-descripcion" class="form-label fw-semibold">Descripción de la Categoría</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-align-left"></i>
                                </span>
                                <textarea class="form-control" id="categoria-descripcion" rows="5"></textarea>
                            </div>
                            <span class="form-label" id="scategoria-descripcion"></span>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" id="btn-CategoriaCancel">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-warning text-dark fw-semibold" id="btn-CategoriaForm">

                        </button>
                    </div>
            </form>
        </div>
    </div>
</div>