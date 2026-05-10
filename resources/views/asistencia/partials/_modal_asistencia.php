<!-- ==========================================
    MODAL DE ASISTENCIA
    ========================================== -->

<div class="modal fade" id="modalAsistencia" tabindex="-1" aria-labelledby="modalAsistenciaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered ">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalAsistenciaLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleTextAsistencia"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formAsistencia" enctype="multipart/form-data" >

                <div class="modal-body">

                    <!-- Fila: ID -->
                    <div class="row g-3 mb-3 justify-content-center d-none">
                        <div class="col-md-6">
                            <input type="hidden" name="id_asistencia" id="id_asistencia">
                            <span class="form-label" id="sid_asistencia"></span>
                        </div>
                    </div>

                    <!-- Fila: Cédula y Tipo Marcación -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="cedula_empleado" class="form-label fw-semibold">
                                Cédula del Empleado <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" style="width: 10%;">
                                    <i class="fa-solid fa-clock"></i>
                                </span>
                                <select class="form-select" id="tipo_doc" name="tipo_doc" style="width: 20%;" required>
                                    <option value="default" selected disabled>-</option>
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                </select>
                                <input type="text" class="form-control" id="cedula_empleado" name="cedula_empleado" maxlength="9" style="width: 60%;" required placeholder="12345678">
                            </div>
                            <span class="invalid-feedback" id="stipo_doc"></span>
                            <span class="invalid-feedback" id="scedula_empleado"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="tipo_marcacion" class="form-label fw-semibold">
                                Tipo de Marcación <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fa-solid fa-clock"></i>
                                </span>
                                <select class="form-select" id="tipo_marcacion" name="tipo_marcacion" required>
                                    <option value="default" selected disabled>-</option>
                                    <option value="ENTRADA">Entrada</option>
                                    <option value="DESCANSO_IN">Iniciar Descanso</option>
                                    <option value="DESCANSO_OUT">Terminar Descanso</option>
                                    <option value="SALIDA">Salida</option>
                                </select>
                                
                            </div>
                            <span class="invalid-feedback" id="stipo_marcacion"></span>
                        </div>
                    </div>

                    <!-- Fila: Observaciones-->
                    <div class="row g-3 mb-3 justify-content-center">
                        <div class="col-md-8">
                            <label for="observacion" class="form-label fw-semibold">
                                Observaciones 
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-align-left"></i>
                                </span>
                                <textarea class="form-control" id="observacion" name="observacion" rows="5"></textarea>
                                <div class="form-label" id="sobservacion"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnAsistenciaForm">

                    </button>
                </div>

            </form>
        </div>
    </div>
</div>