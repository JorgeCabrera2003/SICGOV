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

            <form id="formPermiso">
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

                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnPermisoForm"></button>
                </div>
            </form>
        </div>
    </div>
</div>
