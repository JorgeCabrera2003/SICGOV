<!-- ==========================================
    MODAL DE CLIENTE - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary border-bottom-0 text-white">
                <h5 class="modal-title fw-bold" id="modalClienteLabel">
                    <i class="fas fa-user text-white me-2"></i>
                    <span id="modalTitleTextCliente"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formCliente" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: Cédula y Fecha de Nacimiento -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="cedula" class="form-label fw-semibold">
                                Cédula <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="cedula" name="cedula" maxlength="15" required placeholder="V12345678">
                            <span class="form-label text-danger" id="scedula"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                            <span class="form-label text-danger" id="sfecha_nacimiento"></span>
                        </div>
                    </div>

                    <!-- Fila: Nombre y Apellido -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="80" required>
                            <span class="form-label text-danger" id="snombre"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label fw-semibold">
                                Apellido <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="apellido" name="apellido" maxlength="80" required>
                            <span class="form-label text-danger" id="sapellido"></span>
                        </div>
                    </div>

                    <!-- Fila: Teléfono y Sexo -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="20" placeholder="04121234567">
                            <span class="form-label text-danger" id="stelefono"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="sexo" class="form-label fw-semibold">Sexo</label>
                            <select class="form-select" id="sexo" name="sexo">
                                <option value="default" selected disabled>Seleccionar</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                            <span class="form-label text-danger" id="ssexo"></span>
                        </div>
                    </div>

                    <!-- Fila: Correo y Dirección -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="correo" class="form-label fw-semibold">Correo</label>
                            <input type="email" class="form-control" id="correo" name="correo" maxlength="100" placeholder="cliente@correo.com">
                            <span class="form-label text-danger" id="scorreo"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="direccion" class="form-label fw-semibold">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" maxlength="255">
                            <span class="form-label text-danger" id="sdireccion"></span>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary fw-semibold" id="btnClienteForm">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
