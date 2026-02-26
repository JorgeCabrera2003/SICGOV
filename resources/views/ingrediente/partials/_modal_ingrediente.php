<!-- ==========================================
    MODAL DE INGREDIENTE - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalIngrediente" tabindex="-1" aria-labelledby="modalIngredienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalIngredienteLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextIngrediente">Nuevo Ingrediente</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <form id="formIngrediente" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_ingrediente" id="id_ingrediente">

                    <!-- Fila: Nombre y Unidad de Medida -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   maxlength="100" required>
                        </div>
                        <div class="col-md-5">
                            <label for="unidad_medida" class="form-label fw-semibold">Unidad de Medida</label>
                            <select class="form-select" id="unidad_medida" name="unidad_medida">
                                <option value="default" selected disabled>Seleccionar</option>
                                    <option value="Kg">
                                        Kilogramos - Kg
                                    </option>
                                    <option value="g">
                                        Gramos - g
                                    </option>
                                    <option value="L">
                                        Litros - L
                                    </option>
                                     <option value="ml">
                                        Militros - ml
                                    </option>
                                    <option value="U">
                                        Unidad - U
                                    </option>
                            </select>
                        </div>
                    </div>

                    <!-- Fila: Costo Unitario-->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="costo_unitario" class="form-label fw-semibold">
                                Costo Unitario ($) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="costo_unitario" name="costo_unitario" 
                                   step="0.01" min="0" required placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnIngredienteForm">

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>