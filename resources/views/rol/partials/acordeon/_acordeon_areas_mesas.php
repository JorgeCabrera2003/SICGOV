<!-- ==========================================
    ACORDEÓN AREAS Y MESAS - Reutilizable
    ========================================== -->

<div class="accordion-item">
    <h2 class="accordion-header" id="headingAreas-Mesas">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAreas-Mesas"
            aria-expanded="false" aria-controls="collapseAreas-Mesas">
            Áreas y Mesas
        </button>
    </h2>
    <div id="collapseAreas-Mesas" class="accordion-collapse collapse" aria-labelledby="headingAreas-Mesas"
        data-bs-parent="#accordionPermisos">
        <div class="accordion-body">
            <div class="row mt-2 mb-5">

                <div class="col-md-6">
                    <!-- Grupo Pedido -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-area_mesa"
                                    data-modulo="ROL0000220251001">
                                <label class="form-check-label" for="group-area_mesa">Gestionar Áreas de las
                                    Mesas</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="AREAM0000720260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="area_mesa-registrar">
                                    <label class="form-check-label" for="area_mesa-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="area_mesa-ver">
                                    <label class="form-check-label" for="area_mesa-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="area_mesa-modificar">
                                    <label class="form-check-label" for="area_mesa-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="area_mesa-eliminar">
                                    <label class="form-check-label" for="area_mesa-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <!-- Grupo Mesas -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-mesa"
                                    data-modulo="MESA00000620260519200547232">
                                <label class="form-check-label" for="group-mesa">Gestionar Mesas</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="MESA00000620260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="mesa-registrar">
                                    <label class="form-check-label" for="mesa-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="mesa-ver">
                                    <label class="form-check-label" for="mesa-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="mesa-modificar">
                                    <label class="form-check-label" for="mesa-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="mesa-eliminar">
                                    <label class="form-check-label" for="mesa-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</div>