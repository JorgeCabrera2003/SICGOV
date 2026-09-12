<!-- ==========================================
    ACORDEÓN MENÚ Y RECESTAS - Reutilizable
    ========================================== -->

<div class="accordion-item">
    <h2 class="accordion-header" id="headingMenu-Receta">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenu-Receta"
            aria-controls="collapseMenu-Receta">
            Menú y Recetas
        </button>
    </h2>
    <div id="collapseMenu-Receta" class="accordion-collapse collapse" aria-labelledby="headingMenu-Receta"
        data-bs-parent="#accordionPermisos">
        <div class="accordion-body">
            <div class="row mt-2 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Categorías de Insumos -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox"
                                    id="group-categoria_insumo" data-modulo="CATIN0001520260519200547232">
                                <label class="form-check-label" for="group-categoria_insumo">Gestionar Categorías de
                                    Insumos</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="CATIN0001520260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="categoria_insumo-registrar">
                                    <label class="form-check-label" for="categoria_insumo-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="categoria_insumo-ver">
                                    <label class="form-check-label" for="categoria_insumo-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="categoria_insumo-modificar">
                                    <label class="form-check-label" for="categoria_insumo-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="categoria_insumo-eliminar">
                                    <label class="form-check-label" for="categoria_insumo-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <!-- Grupo Bitácora -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-categoria_menu"
                                    data-modulo="CATME0001620260519200547232">
                                <label class="form-check-label" for="group-categoria_menu">Gestionar Categorías del
                                    Menú</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="CATME0001620260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="categoria_menu-registrar">
                                    <label class="form-check-label" for="categoria_menu-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="categoria_menu-ver">
                                    <label class="form-check-label" for="categoria_menu-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="categoria_menu-modificar">
                                    <label class="form-check-label" for="categoria_menu-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="categoria_menu-eliminar">
                                    <label class="form-check-label" for="categoria_menu-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="row mt-5 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Insumos -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-insumo"
                                    data-modulo="INSUM0001720260519200547232">
                                <label class="form-check-label" for="group-insumo">Gestionar Insumos</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="INSUM0001720260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="insumo-registrar">
                                    <label class="form-check-label" for="insumo-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="insumo-ver">
                                    <label class="form-check-label" for="insumo-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="suministrar" id="insumo-suministrar">
                                    <label class="form-check-label" for="insumo-suministrar">Suministrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="asociar" id="insumo-asociar">
                                    <label class="form-check-label" for="insumo-asociar">Asociar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="insumo-modificar">
                                    <label class="form-check-label" for="insumo-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="insumo-eliminar">
                                    <label class="form-check-label" for="insumo-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <!-- Grupo Productos (Menú) -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-producto"
                                    data-modulo="PRODU0001820260519200547232">
                                <label class="form-check-label" for="group-producto">Gestionar Productos (Menú)</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="PRODU0001820260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="producto-registrar">
                                    <label class="form-check-label" for="producto-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="producto-ver">
                                    <label class="form-check-label" for="producto-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="producto-modificar">
                                    <label class="form-check-label" for="producto-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="producto-eliminar">
                                    <label class="form-check-label" for="producto-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</div>