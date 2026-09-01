<!-- ==========================================
    MODAL DE SUMINISTRAR LOTE - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalSuministrarLote" tabindex="-1" aria-labelledby="modalSuministrarLoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalSuministrarLoteLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextSuministrarLote">Suministrar Insumos</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
                <div class="modal-body">

                    <!-- Fila: ID -->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-lg-4 position-relative">
                            <button class="btn btn-warning text-dark fw-semibold" id="btn-agregarInsumo">
                                <i class="fas fa-plus me-2"></i>Añadir Insumos
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12 position-relative">
                            <section class="card shadow-sm border-0">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="tablaSuministrarLote"
                                            style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col-3">Insumo</th>
                                                    <th scope="col-3">Unidad de Medida</th>
                                                    <th scope="col-2">Cantidad</th>
                                                    <th scope="col-3">Proveedor</th>
                                                    <th scope="col-1"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- DataTables carga los datos aquí -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnSuministrarLoteForm">
                        Suministrar Lote
                    </button>
                </div>
        </div>
    </div>
</div>