<!-- ==========================================
    MODAL DE INGREDIENTE - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalIngrediente" tabindex="-1" aria-labelledby="modalIngredienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalIngredienteLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextIngrediente"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formIngrediente" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: ID -->
                    <div class="row g-3 mb-3 justify-content-center d-none">
                        <div class="col-md-6 position-relative">
                            <input type="hidden" name="id_ingrediente" id="id_ingrediente">
                            <span class="form-label" id="sid_ingrediente"></span>
                        </div>
                    </div>

                    <!-- Fila: Nombre y Costo Unitario -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 position-relative">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre del Ingrediente <span class="text-danger">*</span>
                            </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100"
                                    required>
                                <div class="form-label" id="snombre"></div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="costo_unitario" class="form-label fw-semibold">
                                Costo Unitario ($) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="costo_unitario" name="costo_unitario"
                                step="0.01" min="0" required placeholder="0,00">
                            <div class="form-label" id="scosto_unitario"></div>
                        </div>
                    </div>

                    <!-- Fila: Costo Unidad de Medida y Categoría-->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-6 position-relative">
                            <label for="clave_categoria" class="form-label fw-semibold">
                                Categoría del Ingrediente <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="clave_categoria" name="clave_categoria">
                                <!--Contenido Dinámico -->
                            </select>
                            <div class="form-label" id="sclave_categoria"></div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="unidad_medida" class="form-label fw-semibold">
                                Unidad de Medida <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="unidad_medida" name="unidad_medida">
                                <!--Contenido Dinámico -->
                            </select>
                            <div class="form-label" id="sunidad_medida"></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-6 position-relative">
                            <label for="id_proveedor" class="form-label fw-semibold">
                                Proveedor<span class="text-danger"></span>
                            </label>
                            <select class="form-select" id="id_proveedor" name="id_proveedor">
                                <!--Contenido Dinámico -->
                            </select>
                            <div class="form-label" id="sid_proveedor"></div>
                        </div>
                        <div class="col-md-6 position-relative" id="fila-stock-inicial">
                            <label for="stock_inicial" class="form-label fw-semibold">
                                Stock Inicial <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="stock_inicial" name="stock_inicial"
                                step="0.01" min="0" required placeholder="0,00">
                            <div class="form-label" id="sstock_inicial"></div>
                        </div>
                    </div>
                    <!-- Fila: Stock Inicial, Stock Máximo, Stock Mínimo-->
                    <div class="row g-3 mb-3 justify-content-center">

                        <div class="col-md-6 position-relative">
                            <label for="stock_minimo" class="form-label fw-semibold">
                                Stock Mínimo <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" step="0.01"
                                min="0" required placeholder="0,00">
                            <div class="form-label" id="sstock_minimo"></div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="stock_maximo" class="form-label fw-semibold">
                                Stock Máximo <span class="text-black"></span>
                            </label>
                            <input type="number" class="form-control" id="stock_maximo" name="stock_maximo" step="0.01"
                                min="0" required placeholder="0,00">
                            <div class="form-label" id="sstock_maximo"></div>
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