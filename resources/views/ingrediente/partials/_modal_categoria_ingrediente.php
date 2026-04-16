<!-- ==========================================
    MODAL DE CATEGORÍA INGREDIENTE - REUTILIZABLE
    ========================================== -->

<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalCategoriaLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextCategoria">Categorías de Ingrediente</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <section class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex row mb-4 justify-content-end">
                            <div class="col-md-4">
                                <button class="btn btn-warning text-dark fw-semibold" id="btnNuevaCategoria">
                                    <i class="fas fa-plus me-2"></i>Nueva Categoría
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="tablaCategoria" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Descripción</th>
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

            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btn-CategoriaPCancel">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>