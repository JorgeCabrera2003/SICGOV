<!-- ==========================================
    MODAL DE USUARIO - GOOD VIBES
    ========================================== -->

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <!-- Header moderno -->
            <div class="modal-header bg-primary border-bottom-0 text-white">
                <h5 class="modal-title d-flex align-items-center gap-2 fw-bold" id="modalUsuarioLabel">
                    <i class="fas fa-user-shield"></i> <span id="modalTitleTextUsuario">Nuevo Usuario</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <form id="formUsuario" class="needs-validation" novalidate>
                    <!-- Campo Oculto para Petición -->
                    <input type="hidden" id="peticionUsuario" name="peticion" value="registrar">

                    <div class="row g-3">
                        <!-- SECCIÓN SELECCIÓN EMPLEADO (Solo visible al Registrar) -->
                        <div class="col-md-12" id="grupo-seleccion-empleado">
                            <label for="cedula" class="form-label fw-semibold text-secondary">
                                Seleccionar Empleado <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="cedula" name="cedula" required>
                                <option value="" selected disabled>Cargando empleados...</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione un empleado.</div>
                        </div>

                        <!-- SECCIÓN DETALLES EMPLEADO (Solo visible al Modificar, solo lectura) -->
                        <div class="col-md-12 d-none" id="grupo-detalle-empleado">
                            <label class="form-label fw-semibold text-secondary">Empleado Relacionado</label>
                            <div class="p-3 bg-light border rounded d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="fas fa-user-tie fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold" id="txt-empleado-nombre">Juan Pérez</h6>
                                    <small class="text-muted" id="txt-empleado-cedula">Cédula: V-12345678</small>
                                </div>
                            </div>
                            <!-- Campo oculto para enviar la cédula al actualizar -->
                            <input type="hidden" id="cedula_editar" name="cedula_editar">
                        </div>

                        <!-- Nombre de Usuario -->
                        <div class="col-md-6 position-relative">
                            <label for="username" class="form-label fw-semibold text-secondary">
                                Nombre de Usuario <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Ej. jperez" required>
                            </div>
                            <span id="feedback_username" style="width: fit-content;"></span>
                        </div>

                        <!-- Rol del Sistema -->
                        <div class="col-md-6">
                            <label for="rol" class="form-label fw-semibold text-secondary">
                                Rol del Sistema <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-tag text-muted"></i></span>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="" selected disabled>Cargando roles...</option>
                                </select>
                            </div>
                            <div class="invalid-feedback">Por favor seleccione un rol.</div>
                        </div>

                        <!-- Contraseña -->
                        <div class="col-md-6">
                            <label for="clave" class="form-label fw-semibold text-secondary">
                                Contraseña <span class="text-danger" id="req-clave">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" id="clave" name="clave" placeholder="Ej. 1234" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary btn-pwd-toggle" type="button" data-target="#clave">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <span id="feedback_clave" style="width: fit-content;"></span>
                            <small class="form-text text-muted d-none" id="help-clave">Dejar en blanco si no desea modificar la contraseña.</small>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="col-md-6">
                            <label for="rclave" class="form-label fw-semibold text-secondary">
                                Confirmar Contraseña <span class="text-danger" id="req-rclave">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-check-double text-muted"></i></span>
                                <input type="password" class="form-control" id="rclave" name="rclave" placeholder="Repita la contraseña" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary btn-pwd-toggle" type="button" data-target="#rclave">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <span id="feedback_rclave" style="width: fit-content;"></span>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4 fw-medium" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary px-4 fw-medium text-white" id="btnUsuarioForm">
                    <i class="fas fa-save me-2"></i>Guardar Usuario
                </button>
            </div>
        </div>
    </div>
</div>