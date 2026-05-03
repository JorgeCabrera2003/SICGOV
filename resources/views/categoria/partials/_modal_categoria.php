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
                            <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" placeholder="Ej. Bebidas Calientes" required>
                            <div class="invalid-feedback" id="feedback_nombre_categoria">Por favor, ingresa el nombre de la categoría.</div>
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label for="descripcion_categoria" class="form-label fw-medium text-secondary">Descripción</label>
                            <textarea class="form-control" id="descripcion_categoria" name="descripcion" rows="3" placeholder="Descripción opcional..."></textarea>
                            <div class="invalid-feedback" id="feedback_descripcion_categoria">La descripción solo puede contener letras y espacios.</div>
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
