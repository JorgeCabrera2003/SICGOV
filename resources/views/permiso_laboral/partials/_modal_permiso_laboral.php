<div class="modal fade" id="modalPermisoLaboral" tabindex="-1" aria-labelledby="modalPermisoLaboralLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalPermisoLaboralLabel">
                    <i class="fas fa-calendar-minus text-warning me-2"></i>
                    <span id="modalTitleTextPermiso"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formPermiso" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="id_permiso" name="id_permiso">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Empleado <span class="text-danger">*</span></label>
                            <select id="permiso-cedula" class="form-select" required></select>
                            <div class="form-label" id="spermiso-cedula"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Permiso <span class="text-danger">*</span></label>
                            <select id="permiso-tipo" class="form-select" required></select>
                            <div class="form-label" id="spermiso-tipo"></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" id="permiso-fecha-inicio" class="form-control" required>
                            <div class="form-label" id="spermiso-fecha-inicio"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Fin <span class="text-danger">*</span></label>
                            <input type="date" id="permiso-fecha-fin" class="form-control" required>
                            <div class="form-label" id="spermiso-fecha-fin"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="permiso-comprobante" class="form-label">Comprobante o Justificativo</label>
                        <div id="permiso-comprobante-actual" class="mb-2 text-center" style="display: none;">
                            <a id="permiso-comprobante-enlace" target="_blank" rel="noopener noreferrer">
                                <img id="permiso-comprobante-preview" src="#" alt="Comprobante del permiso" class="img-thumbnail" style="max-height: 140px;">
                            </a>
                        </div>
                        <div class="card border border-2 border-dashed rounded-3">
                            <div class="card-body p-3 text-center">
                                <div id="permiso-comprobante-nuevo" class="mb-2" style="display: none;">
                                    <img id="permiso-comprobante-preview-nuevo" src="#" alt="Vista previa del comprobante" class="img-thumbnail" style="max-height: 140px;">
                                </div>
                                <input type="file" id="permiso-comprobante" name="comprobante" class="form-control d-none" accept="image/jpeg,image/png">
                                <label for="permiso-comprobante" class="btn btn-sm btn-outline-warning shadow-sm">
                                    <i class="fas fa-upload me-1"></i>Subir comprobante
                                </label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">Imagen JPG, PNG. máximo 10 MB.</small>
                    </div>

                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnPermisoForm"></button>
                </div>
            </form>
        </div>
    </div>
</div>
