<!-- ==========================================
    MODAL DE SUMINISTRAR INSUMO - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalAsociar" tabindex="-1" aria-labelledby="modalAsociarLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalAsociarLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextAsociar">Proveedores Asociados</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formAsociar" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: ID -->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-6 position-relative">
                                <input type="text" name="entrada-nombreInsumo" class="form-control" id="entrada-nombreInsumo" readOnly>
                                <span class="form-label" id="sentrada-nombreInsumo"></span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12 position-relative">
                            <section class="card shadow-sm border-0">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="tablaAsociar"
                                            style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Proveedor</th>
                                                    <th scope="col"></th>
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
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnAsociarForm">

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>