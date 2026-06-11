<!-- ==========================================
    MODAL DE EMPLEADO - Reutilizable
    ========================================== -->

<?php
$cargoModel = new \App\Models\System\Cargo();
$cargosReq = $cargoModel->Transaccion(['peticion' => 'consultar']);
$cargos = isset($cargosReq['response']['datos']) ? $cargosReq['response']['datos'] : [];
?>

<div class="modal fade" id="modalEmpleado" tabindex="-1" aria-labelledby="modalEmpleadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary border-bottom-0 text-white">
                <h5 class="modal-title fw-bold" id="modalEmpleadoLabel">
                    <i class="fas fa-id-badge me-2"></i>
                    <span id="modalTitleTextEmpleado"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formEmpleado" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Fila: Cédula y Fecha de Nacimiento -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 position-relative">
                            <label for="cedula" class="form-label fw-semibold">
                                Cédula <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <select class="form-select" id="tipo_doc" name="tipo_doc" style="width: 30%;" required>
                                    <option value="default" selected disabled>Tipo</option>
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                    <option value="J">J</option>
                                </select>
                                <input type="text" class="form-control" id="cedula" name="cedula" maxlength="9" style="width: 70%;" required placeholder="12345678">
                            </div>
                            <span id="scedula" style="width: fit-content;"></span>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de Nacimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" max="<?= date('Y-m-d', strtotime('-1 day')) ?>">
                            <span id="sfecha_nacimiento" style="width: fit-content;"></span>
                        </div>
                    </div>

                    <!-- Fila: Nombre y Apellido -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 position-relative">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="80" required>
                            <span id="snombre" style="width: fit-content;"></span>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="apellido" class="form-label fw-semibold">
                                Apellido <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="apellido" name="apellido" maxlength="80" required>
                            <span id="sapellido" style="width: fit-content;"></span>
                        </div>
                    </div>

                    <!-- Fila: Cargo y Sexo -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 position-relative">
                            <label for="id_cargo" class="form-label fw-semibold">Cargo <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_cargo" name="id_cargo">
                                <option value="default" selected disabled>Seleccionar</option>
                                <?php foreach($cargos as $cargo): ?>
                                    <option value="<?= $cargo['id_cargo'] ?>"><?= $cargo['nombre_cargo'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span id="sid_cargo" style="width: fit-content;"></span>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="sexo" class="form-label fw-semibold">Sexo <span class="text-danger">*</span></label>
                            <select class="form-select" id="sexo" name="sexo">
                                <option value="default" selected disabled>Seleccionar</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                            <span id="ssexo" style="width: fit-content;"></span>
                        </div>
                    </div>

                    <!-- Fila: Teléfono y Correo -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 position-relative">
                            <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                            <div class="input-group">
                                <select class="form-select" id="prefijo_telefono" name="prefijo_telefono" style="width: 33%;">
                                    <option value="default" selected disabled>Cod</option>
                                    <option value="0414">0414</option>
                                    <option value="0424">0424</option>
                                    <option value="0412">0412</option>
                                    <option value="0422">0422</option>
                                    <option value="0416">0416</option>
                                    <option value="0426">0426</option>
                                </select>
                                <input type="text" class="form-control" id="telefono" name="telefono" maxlength="7" style="width: 67%;" placeholder="5539261">
                            </div>
                            <span id="stelefono" style="width: fit-content;"></span>
                        </div>
                        <div class="col-md-6 position-relative">
                            <label for="correo" class="form-label fw-semibold">Correo</label>
                            <input type="email" class="form-control" id="correo" name="correo" maxlength="100" placeholder="empleado@correo.com">
                            <span id="scorreo" style="width: fit-content;"></span>
                        </div>
                    </div>

                    <!-- Fila: Dirección -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-12 position-relative">
                            <label for="direccion" class="form-label fw-semibold">Dirección <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="direccion" name="direccion" maxlength="255">
                            <span id="sdireccion" style="width: fit-content;"></span>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary fw-semibold" id="btnEmpleadoForm">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
