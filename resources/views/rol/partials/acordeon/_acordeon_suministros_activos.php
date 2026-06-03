<!-- ==========================================
    ACORDEÓN SUMINISTROS Y ACTIVOS - Reutilizable
    ========================================== -->

<div class="accordion-item">
    <h2 class="accordion-header" id="headingSuministros-Activos">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseSuministros-Activos" aria-controls="collapseSuministros-Activos">
            Suministros y Activos
        </button>
    </h2>
    <div id="collapseSuministros-Activos" class="accordion-collapse collapse"
        aria-labelledby="headingSuministros-Activos" data-bs-parent="#accordionPermisos">
        <div class="accordion-body">
            <div class="row mt-2 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Proveedores -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-proveedores"
                                    data-modulo="BITAC00320251001">
                                <label class="form-check-label" for="group-proveedores">Gestionar Proveedores</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="BITAC00320251001">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="proveedor-registrar">
                                    <label class="form-check-label" for="proveedor-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="proveedor-ver">
                                    <label class="form-check-label" for="proveedor-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="proveedor-modificar">
                                    <label class="form-check-label" for="proveedor-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="proveedor-eliminar">
                                    <label class="form-check-label" for="proveedor-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</div>