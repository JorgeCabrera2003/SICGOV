<!-- ==========================================
    ACORDEÓN SEGURIDAD - Reutilizable
    ========================================== -->

<div class="accordion-item">
    <h2 class="accordion-header" id="headingSeguridad">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeguridad"
            aria-expanded="true" aria-controls="collapseSeguridad">
            Control de Acceso
        </button>
    </h2>
    <div id="collapseSeguridad" class="accordion-collapse collapse show" aria-labelledby="headingSeguridad"
        data-bs-parent="#accordionPermisos">
        <div class="accordion-body">
            <div class="row mt-2 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Usuario -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-usuario"
                                    data-modulo="USUAR00120251001">
                                <label class="form-check-label" for="group-usuario">Gestionar Usuarios</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="USUAR00120251001">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="usuario-registrar">
                                    <label class="form-check-label" for="usuario-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="usuario-ver">
                                    <label class="form-check-label" for="usuario-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="usuario-modificar">
                                    <label class="form-check-label" for="usuario-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="usuario-eliminar">
                                    <label class="form-check-label" for="usuario-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <!-- Grupo Rol -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-rol"
                                    data-modulo="ROL0000220251001">
                                <label class="form-check-label" for="group-rol">Gestionar Roles</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="ROL0000220251001">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="rol-registrar">
                                    <label class="form-check-label" for="rol-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="rol-ver">
                                    <label class="form-check-label" for="rol-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="rol-modificar">
                                    <label class="form-check-label" for="rol-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="rol-eliminar">
                                    <label class="form-check-label" for="rol-eliminar">Eliminar</label>
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
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-bitacora"
                                    data-modulo="BITAC00320251001">
                                <label class="form-check-label" for="group-bitacora">Gestionar Bitácora</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="BITAC00320251001">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="bitacora-ver">
                                    <label class="form-check-label" for="bitacora-ver">Ver</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <!-- Grupo Mantenimiento -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-mantenimiento"
                                    data-modulo="MANTE00420251001">
                                <label class="form-check-label" for="group-mantenimiento">Gestionar
                                    Mantenimiento</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="MANTE00420251001">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="mantenimiento-ver">
                                    <label class="form-check-label" for="mantenimiento-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="exportar" id="mantenimiento-exportar">
                                    <label class="form-check-label" for="mantenimiento-exportar">Exportar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="importar" id="mantenimiento-importar">
                                    <label class="form-check-label" for="mantenimiento-importar">Importar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="mantenimiento-eliminar">
                                    <label class="form-check-label" for="mantenimiento-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

            </div>
            <div class="row mt-5 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Modulo del Sistema -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-modulo_sistema"
                                    data-modulo="MODSI02520251001">
                                <label class="form-check-label" for="group-mantenimiento">Módulos del Sistema</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="MODSI02520251001">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="modulo_sistema-ver">
                                    <label class="form-check-label" for="modulo_sistema-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="cargar" id="modulo_sistema-cargar">
                                    <label class="form-check-label" for="modulo_sistema-cargar">Cargar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="comprobar" id="modulo_sistema-comprobar">
                                    <label class="form-check-label" for="modulo_sistema-comprobar">Comprobar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</div>