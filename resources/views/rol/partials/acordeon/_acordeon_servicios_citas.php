<!-- ==========================================
    ACORDEÓN SERVICIOS Y CITAS - Reutilizable
    ========================================== -->

<div class="accordion-item">
    <h2 class="accordion-header" id="headingServicios-Citas">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseServicios-Citas" aria-controls="collapseServicios-Citas">
            Servicios y Citas
        </button>
    </h2>
    <div id="collapseServicios-Citas" class="accordion-collapse collapse" aria-labelledby="headingServicios-Citas"
        data-bs-parent="#accordionPermisos">
        <div class="accordion-body">
            <div class="row mt-2 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Clientes -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-cliente"
                                    data-modulo="USUAR00120251001">
                                <label class="form-check-label" for="group-cliente">Gestionar Clientes</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="USUAR00120251001">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="cliente-registrar">
                                    <label class="form-check-label" for="cliente-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="cliente-ver">
                                    <label class="form-check-label" for="cliente-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="cliente-modificar">
                                    <label class="form-check-label" for="cliente-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="cliente-eliminar">
                                    <label class="form-check-label" for="cliente-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <!-- Grupo Pedido -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-pedido"
                                    data-modulo="ROL0000220251001">
                                <label class="form-check-label" for="group-pedido">Gestionar Pedidos</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="ROL0000220251001">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="pedido-registrar">
                                    <label class="form-check-label" for="pedido-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="pedido-ver">
                                    <label class="form-check-label" for="pedido-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="pedido-modificar">
                                    <label class="form-check-label" for="pedido-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="pedido-eliminar">
                                    <label class="form-check-label" for="pedido-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="row mt-5 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Bitácora -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-reservacion"
                                    data-modulo="BITAC00320251001">
                                <label class="form-check-label" for="group-reservacion">Gestionar Reservaciones</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="BITAC00320251001">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="reservacion-registrar">
                                    <label class="form-check-label" for="reservacion-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="reservacion-agenda">
                                    <label class="form-check-label" for="reservacion-agenda">Ver Agenda Global</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="reservacion-ver">
                                    <label class="form-check-label" for="reservacion-ver">Ver Reservación</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="reservacion-modificar">
                                    <label class="form-check-label" for="reservacion-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="reservacion-eliminar">
                                    <label class="form-check-label" for="reservacion-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</div>