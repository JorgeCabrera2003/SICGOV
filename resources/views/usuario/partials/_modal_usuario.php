<!-- ==========================================
    MODAL DE USUARIO - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalUsuarioLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextUsuario"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formUsuario" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: Nombre de Usuario -->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-6">
                            <label for="username" class="form-label fw-semibold">
                                Nombre de Usuario <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="username" name="username" maxlength="100"
                                required>
                            <span class="form-label" id="susername"></span>
                        </div>
                    </div>

                    <!-- Fila: Cédula y Nacionalidad -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="nacionalidad" class="form-label fw-semibold">Nacionalidad</label>
                            <select class="form-select" id="nacionalidad" name="nacionalidad">
                                <option value="default" selected disabled>Seleccionar</option>
                                <option value="V">
                                    V
                                </option>
                                <option value="E">
                                    E
                                </option>
                            </select>
                            <span class="form-label" id="snacionalidad"></span>
                        </div>
                        <div class="col-md-8">
                            <label for="cedula" class="form-label fw-semibold">
                                Cédula <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="cedula" name="cedula" maxlength="100" required>
                            <span class="form-label" id="scedula"></span>
                        </div>
                    </div>

                    <!-- Fila: Nombre y Apellido -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required>
                            <span class="form-label" id="snombre"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label fw-semibold">
                                Apellido <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="apellido" name="apellido" maxlength="100" required>
                            <span class="form-label" id="sapellido"></span>
                        </div>
                    </div>

                    <!-- Fila: Teléfono y Correo-->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="correo" class="form-label fw-semibold">
                                Correo <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="correo" name="correo" maxlength="100" required>
                            <span class="form-label" id="scorreo"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label fw-semibold">
                                Teléfono <span class="text-danger"></span>
                            </label>
                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="100">
                            <span class="form-label" id="stelefono"></span>
                        </div>
                    </div>

                    <!-- Fila: Contraseña-->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="clave" class="form-label fw-semibold">
                                Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="clave" name="clave" maxlength="100" required>
                            <span class="form-label" id="sclave"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="rclave" class="form-label fw-semibold">
                                Confirmar Constraseña <span class="text-danger"></span>
                            </label>
                            <input type="text" class="form-control" id="rclave" name="rclave" maxlength="100" required>
                            <span class="form-label" id="srclave"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnUsuarioForm">

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>