<!-- ==========================================
    MARCADO DE ASISTENCIA PÚBLICO - GOOD VIBES
========================================== -->

<?php
    $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
?>

<style>
    #stipo_marcacion.valid-feedback {
        display: none !important;
    }
    
    .asistencia-portal-section {
        position: relative;
        min-height: 80vh;
        display: flex;
        align-items: center;
        background-image: url('<?= BASE_URL ?>assets/img/landing/hero_pizza.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        margin-top: -80px; /* Para que quede por detrás del Header */
        padding-top: 80px; /* Para compensar el espacio del Header en el contenido */
    }

    .asistencia-portal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(10, 43, 43, 0.9) 0%, rgba(10, 43, 43, 0.4) 100%);
        opacity: 1;
        z-index: 1;
    }

    .asistencia-portal-content {
        position: relative;
        z-index: 2;
        width: 100%;
    }
    
    .asistencia-card {
        background-color: var(--bg-tarjetas);
        border: 1px solid var(--color-border);
        border-radius: 1rem;
        box-shadow: 0 15px 40px rgba(0,0,0,0.3) !important;
    }
</style>

<section class="asistencia-portal-section">
    <div class="asistencia-portal-overlay"></div>
    <div class="container asistencia-portal-content py-5">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10">
                
                <div class="text-center mb-4">
                    <h2 class="display-6 fw-bold text-white mb-2">Portal de <span style="color: var(--color-acento);">Empleados</span></h2>
                    <p class="text-white-50">Control de Asistencia del Personal</p>
                </div>

                <div class="card asistencia-card mb-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="h4 fw-bold mb-1" style="color: var(--color-sidebar);">
                                <i class="fas fa-user-clock me-2" style="color: var(--color-acento);"></i>
                                Marcar Asistencia
                            </h3>
                            <hr style="border-color: var(--color-border);">
                        </div>

                        <form id="formPublicAsistencia" class="row g-4 justify-content-center">
                            <div class="col-12 text-start">
                                <label class="form-label fw-semibold" style="color: var(--color-texto);">Documento del empleado <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select rounded-end-0" id="tipo_doc" name="tipo_doc" style="max-width: 110px;" required>
                                        <option value="default" selected disabled>-</option>
                                        <option value="V">V</option>
                                        <option value="E">E</option>
                                    </select>
                                    <input type="text" class="form-control rounded-0" id="cedula_empleado" name="cedula_empleado" maxlength="9" inputmode="numeric" pattern="[0-9]*" autocomplete="off" placeholder="12345678" required>
                                    <button type="button" class="btn btn-primary rounded-start-0" id="btnVerifyEmployee" style="background-color: var(--color-acento); color: var(--color-dark); border-color: var(--color-acento);">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div class="row mt-2 gx-2">
                                    <div class="col-6">
                                        <div class="invalid-feedback" id="stipo_doc"></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="invalid-feedback" id="scedula_empleado"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-start">
                                <div id="employeeInfo" class="d-none border rounded-2 px-3 py-2" style="border-color: var(--color-border) !important; background-color: var(--color-principal);">
                                    <span style="font-size: .95rem; color: var(--color-texto);">
                                        Empleado: <strong id="employeeName" style="color: var(--color-sidebar);"></strong>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 text-start">
                                <label for="tipo_marcacion" class="form-label fw-semibold" style="color: var(--color-texto);">Tipo de marcación <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipo_marcacion" name="tipo_marcacion" disabled required>
                                    <option value="default" selected disabled>-</option>
                                    <option value="ENTRADA">Entrada</option>
                                    <option value="DESCANSO_IN">Iniciar Descanso</option>
                                    <option value="DESCANSO_OUT">Terminar Descanso</option>
                                    <option value="SALIDA">Salida</option>
                                </select>
                                <div class="invalid-feedback" id="stipo_marcacion"></div>
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="button" class="btn btn-lg w-100 fw-bold shadow-sm" id="btnRegisterAttendance" disabled style="background-color: var(--color-acento); color: var(--color-dark); border-color: var(--color-acento);">
                                    <i class="fas fa-check-circle me-2"></i>Registrar Asistencia
                                </button>
                            </div>
                            
                            <div class="col-12 text-center mt-3">
                                <a href="<?= BASE_URL ?>?page=Home" class="text-decoration-none" style="color: var(--color-acento); font-size: 0.9rem;">
                                    <i class="fas fa-arrow-left me-1"></i> Volver al inicio
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
