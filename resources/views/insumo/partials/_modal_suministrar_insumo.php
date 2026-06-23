<!-- ==========================================
    MODAL DE SUMINISTRAR INSUMO - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalSuministrarInsumo" tabindex="-1" aria-labelledby="modalSuministrarInsumoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalSuministrarInsumoLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextSuministrarInsumo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formSuministrarInsumo" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: Nombre y Costo Unitario -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 position-relative">
                            <label for="suministrar-nombre" class="form-label fw-semibold">
                                Insumo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="suministrar-nombre" name="suministrar-nombre"
                                maxlength="100" required readOnly>
                            <div class="form-label" id="ssuministrar-nombre"></div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="suministrar-entrada" class="form-label fw-semibold">
                                Proveedor <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="suministrar-entrada" name="suministrar-entrada">
                                <!--Contenido Dinámico -->
                            </select>
                            <div class="form-label" id="ssuministrar-entrada"></div>
                        </div>
                    </div>

                    <!-- Fila: Costo Unidad de Medida y Categoría-->
                    <div class="row g-3 mb-3 justify-content-center">

                        <div class="col-md-6 position-relative">
                            <label for="suministrar-stock" class="form-label fw-semibold">
                                Stock a Agregar <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="suministrar-stock" name="suministrar-stock"
                                maxlength="100" required>
                            <div class="form-label" id="ssuministrar-stock"></div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="suministrar-unidad" class="form-label fw-semibold">
                                Unidad de Medida <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="suministrar-unidad" name="suministrar-unidad">
                                <!--Contenido Dinámico -->
                            </select>
                            <div class="form-label" id="ssuministrar-unidad"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnSuministrarInsumoForm">

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>