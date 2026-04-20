<!-- Modal Categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow">
            <!-- Header moderno -->
            <div class="modal-header bg-primary border-bottom-0 text-white">
                <h5 class="modal-title d-flex align-items-center gap-2 fw-bold" id="modalCategoriaLabel">
                    <i class="fas fa-tag"></i> <span id="tituloModalCategoria">Nueva Categoría</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <form id="formCategoria" class="needs-validation" novalidate>
                    <!-- Campo Oculto para Petición y ID (Edición) -->
                    <input type="hidden" id="peticionCategoria" name="peticion" value="registrar">
                    <input type="hidden" id="id_categoria" name="id_categoria">

                    <div class="row g-3">
                        <!-- Nombre de la Categoría -->
                        <div class="col-12">
                            <label for="nombre_categoria" class="form-label fw-medium text-secondary">
                                Nombre de la Categoría <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-font"></i></span>
                                <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" placeholder="Ej. Bebidas Calientes" required>
                                <div class="invalid-feedback">Por favor, ingresa el nombre de la categoría.</div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label for="descripcion_categoria" class="form-label fw-medium text-secondary">Descripción</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-align-left"></i></span>
                                <textarea class="form-control" id="descripcion_categoria" name="descripcion" rows="3" placeholder="Descripción opcional..."></textarea>
                            </div>
                        </div>

                        <!-- Estatus (Sólo visible en edición) -->
                        <div class="col-12" id="divEstatusCategoria" style="display: none;">
                            <label class="form-label fw-medium text-secondary">Estatus</label>
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" role="switch" id="estatus_categoria" name="estatus" value="1" checked>
                                <label class="form-check-label fs-6 ms-2 mt-1" for="estatus_categoria">Activo</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4 fw-medium" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="submit" form="formCategoria" class="btn btn-primary px-4 fw-medium text-white" id="btnGuardarCategoria">
                    <i class="fas fa-save me-2"></i>Guardar Categoría
                </button>
            </div>
        </div>
    </div>
</div>
