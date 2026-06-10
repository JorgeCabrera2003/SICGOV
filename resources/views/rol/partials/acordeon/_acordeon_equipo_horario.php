<!-- ==========================================
    ACORDEÓN EQUIPO Y HORARIO - Reutilizable
    ========================================== -->

<div class="accordion-item">
    <h2 class="accordion-header" id="headingEquipo-Horario">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseEquipo-Horario" aria-expanded="false" aria-controls="collapseEquipo-Horario">
            Equipo y Horario
        </button>
    </h2>
    <div id="collapseEquipo-Horario" class="accordion-collapse collapse" aria-labelledby="headingEquipo-Horario"
        data-bs-parent="#accordionPermisos">
        <div class="accordion-body">
            <div class="row mt-2 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Asistencias -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-asistencia"
                                    data-modulo="ASIST0001020260519200547232">
                                <label class="form-check-label" for="group-asistencia">Gestionar Asistencias</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="ASIST0001020260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="asistencia-registrar">
                                    <label class="form-check-label" for="asistencia-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="asistencia-ver">
                                    <label class="form-check-label" for="asistencia-ver">Ver</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <!-- Grupo Cargos -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-cargo"
                                    data-modulo="CARGO0001120260519200547232">
                                <label class="form-check-label" for="group-cargo">Gestionar Cargos</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="CARGO0001120260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="cargo-registrar">
                                    <label class="form-check-label" for="cargo-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="cargo-ver">
                                    <label class="form-check-label" for="cargo-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="cargo-modificar">
                                    <label class="form-check-label" for="cargo-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="cargo-eliminar">
                                    <label class="form-check-label" for="cargo-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="row mt-2 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Empleados -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-empleado"
                                    data-modulo="EMPLE0001220260519200547232">
                                <label class="form-check-label" for="group-empleado">Gestionar Empleados</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="EMPLE0001220260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="empleado-registrar">
                                    <label class="form-check-label" for="empleado-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="empleado-ver">
                                    <label class="form-check-label" for="empleado-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="empleado-modificar">
                                    <label class="form-check-label" for="empleado-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="empleado-eliminar">
                                    <label class="form-check-label" for="empleado-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <!-- Grupo Horarios -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-horario"
                                    data-modulo="HORAR0001320260519200547232">
                                <label class="form-check-label" for="group-horario">Gestionar Horarios</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="HORAR0001320260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="horario-registrar">
                                    <label class="form-check-label" for="horario-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="horario-ver">
                                    <label class="form-check-label" for="horario-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="horario-modificar">
                                    <label class="form-check-label" for="horario-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="horario-eliminar">
                                    <label class="form-check-label" for="horario-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="row mt-2 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Permisos Laborales -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox"
                                    id="group-permiso_laboral" data-modulo="PERML0011420260519200547232">
                                <label class="form-check-label" for="group-permiso_laboral">Gestionar Permisos
                                    Laborales</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="PERML0011420260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="permiso_laboral-registrar">
                                    <label class="form-check-label" for="permiso_laboral-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="permiso_laboral-ver">
                                    <label class="form-check-label" for="permiso_laboral-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="permiso_laboral-modificar">
                                    <label class="form-check-label" for="permiso_laboral-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="permiso_laboral-eliminar">
                                    <label class="form-check-label" for="permiso_laboral-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <!-- Grupo Turnos -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-turno"
                                    data-modulo="TURNO0001420260519200547232">
                                <label class="form-check-label" for="group-turno">Gestionar Turnos</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="TURNO0001420260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="turno-registrar">
                                    <label class="form-check-label" for="turno-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="turno-ver">
                                    <label class="form-check-label" for="turno-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="turno-modificar">
                                    <label class="form-check-label" for="turno-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="turno-eliminar">
                                    <label class="form-check-label" for="turno-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="row mt-2 mb-5">
                <div class="col-md-6">
                    <!-- Grupo Tipo Permiso -->
                    <fieldset class="permission-group">
                        <legend class="group-header">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input group-checkbox" type="checkbox" id="group-tipo_permiso"
                                    data-modulo="TIPOP0021420260519200547232">
                                <label class="form-check-label" for="group-tipo_permiso">Gestionar Tipos de Permisos</label>
                            </div>
                        </legend>
                        <div class="row permission-options" data-modulo-string="TIPOP0021420260519200547232">
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="registrar" id="tipo_permiso-registrar">
                                    <label class="form-check-label" for="tipo_permiso-registrar">Registrar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="ver" id="tipo_permiso-ver">
                                    <label class="form-check-label" for="tipo_permiso-ver">Ver</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="modificar" id="tipo_permiso-modificar">
                                    <label class="form-check-label" for="tipo_permiso-modificar">Modificar</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input permission-checkbox" data-id-permiso=""
                                        type="checkbox" role="switch" value="eliminar" id="tipo_permiso-eliminar">
                                    <label class="form-check-label" for="tipo_permiso-eliminar">Eliminar</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</div>