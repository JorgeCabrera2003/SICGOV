<!-- ==========================================
    MODAL DE SUMINISTRAR INSUMO - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalMovimientoInsumo" tabindex="-1" aria-labelledby="modalMovimientoInsumoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalMovimientoInsumoLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextMovimientoInsumo">Historial de Movimientos del Insumo</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formMovimientoInsumo" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: ID -->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-6 position-relative">
                                <input type="text" name="m-nombreInsumo" class="form-control" id="m-nombreInsumo" readOnly>
                                <span class="form-label" id="sm-nombreInsumo"></span>
                        </div>
                        <div class="col-md-4 position-relative">
                            <div class="input-group">
                                <input type="number" name="m-stockInsumo" class="form-control" id="m-stockInsumo" readOnly>
                                <span class="form-label" id="sm-stockInsumo"></span>

                                <input type="text" name="m-unidadmedida" class="form-control" id="m-unidadmedida" readOnly>
                                <span class="form-label" id="sm-unidadmedida"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Fila: Nombre y Costo Unitario -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-12 position-relative">
                            <section class="card shadow-sm border-0">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="tablaEntrada"
                                            style="width:100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Fecha</th>
                                                    <th scope="col">Proveedor</th>
                                                    <th scope="col">Descripción</th>
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
            </form>
        </div>
    </div>
</div>