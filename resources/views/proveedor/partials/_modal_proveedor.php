<!-- ==========================================
    MODAL DE PROVEEDOR - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalProveedorLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextProveedor"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formProveedor" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: Nombre y Costo Unitario -->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-10 position-relative">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre del Proveedor <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required>
                            <div class="form-label" id="snombre"></div>
                        </div>
                    </div>
                    <!-- Fila: Documento Legal -->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-3 position-relative">
                            <label for="nombre" class="form-label fw-semibold">
                                Tipo de Documento <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo_doc" name="tipo_doc" required>
                                <option value="default" selected disabled>Tipo</option>
                                <option value="V">V</option>
                                <option value="E">E</option>
                                <option value="J">J</option>
                            </select>
                            <div class="form-label" id="stipo_doc"></div>
                        </div>
                        <div class="col-md-7 position-relative">
                            <label for="nombre" class="form-label fw-semibold">
                                Documento Legal <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="documento_legal" name="documento_legal"
                                maxlength="65" required>
                            <div class="form-label" id="sdocumento_legal"></div>
                        </div>
                    </div>

                    <!-- Fila: Costo Unidad de Medida y Categoría-->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-6 position-relative">
                            <label for="telefono" class="form-label fw-semibold">
                                Teléfono <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <select class="form-select" id="prefijo_telefono" name="prefijo_telefono">
                                    <option value="default" selected disabled>Prefijo</option>
                                    <option value="0414">0414</option>
                                    <option value="0424">0424</option>
                                    <option value="0412">0412</option>
                                    <option value="0422">0422</option>
                                    <option value="0416">0416</option>
                                    <option value="0426">0426</option>
                                </select>
                                <input type="text" class="form-control" id="telefono" name="telefono" maxlength="7"
                                    placeholder="5539261">
                                <div class="form-label" id="stelefono"></div>
                            </div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="correo" class="form-label fw-semibold">
                                Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="correo" name="correo" maxlength="100" required>
                            <div class="form-label" id="scorreo"></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3 justify-content-center">

                        <div class="col-md-12 position-relative">
                            <label for="direccion" class="form-label fw-semibold">
                                Dirección <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="direccion" rows="5"></textarea>
                            <div class="form-label" id="sdireccion"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnProveedorForm">

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>