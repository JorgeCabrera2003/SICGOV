<!-- ==========================================
    MARCADO DE ASISTENCIA PÚBLICO - GOOD VIBES
========================================== -->

<?php
    $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
?>

<div class="news-top-bar py-2 shadow-sm border-bottom">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="date-now small">
            <i class="far fa-calendar-alt me-2 text-primary"></i>
            <?= $dias[date('w')] . ", " . date('d') . " de " . $meses[date('n')] . " de " . date('Y'); ?>
        </div>
        <div class="d-flex align-items-center">
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="<?= BASE_URL ?>?page=login" class="btn btn-sm btn-outline-primary fw-bold text-uppercase px-3 rounded-pill">
                    <i class="fas fa-lock me-1"></i> Acceso
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>?page=dashboard" class="btn btn-sm btn-primary fw-bold text-uppercase px-3 rounded-pill shadow-sm">
                    <i class="fas fa-tachometer-alt me-1"></i> Panel Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<nav class="sticky-top bg-tarjetas border-bottom shadow-sm py-2" id="landingNav" style="z-index: 1020;">
    <div class="container d-flex justify-content-center">
        <a href="" class="btn btn-sm btn-primary fw-bold text-uppercase px-3 rounded-pill shadow-sm">
            <i class="fas fa-user-check me-2"></i>Asistencia
        </a>
    </div>
</nav>

<style>
    #stipo_marcacion.valid-feedback {
        display: none !important;
    }
</style>

<main class="bg-body pb-5">
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-asistencia" role="tabpanel">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-xl-8 col-lg-9">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start gap-3 mb-4">
                                    <div>
                                        
                                        
                                        <h2 class="h4 fw-bold mb-1">
                                        <i class="fas bi-list-check text-warning"></i>
                                        Marcar Asistencia</h2>
                                    </div>
                                </div>

                                <form id="formPublicAsistencia" class="row g-4 justify-content-center">
                                    <div class="col-12 col-md-8 mx-auto text-start">
                                        <label class="form-label fw-semibold">Documento del empleado <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-select rounded-end-0" id="tipo_doc" name="tipo_doc" style="max-width: 110px;" required>
                                                <option value="default" selected disabled>-</option>
                                                <option value="V">V</option>
                                                <option value="E">E</option>
                                            </select>
                                            <input type="text" class="form-control rounded-0" id="cedula_empleado" name="cedula_empleado" maxlength="9" inputmode="numeric" pattern="[0-9]*" autocomplete="off" placeholder="12345678" required>
                                            <button type="button" class="btn btn-primary rounded-start-0" id="btnVerifyEmployee">
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

                                    <div class="col-12 col-md-8 mx-auto text-start">
                                        <span id="employeeInfo" class="text-muted d-none border rounded-2 px-3 py-2" style="font-size: .95rem; display: inline-block;">
                                            Empleado: <strong id="employeeName" class="text-body"></strong>
                                        </span>
                                    </div>

                                    <div class="col-12 col-md-8 mx-auto text-start">
                                        <label for="tipo_marcacion" class="form-label fw-semibold">Tipo de marcación <span class="text-danger">*</span></label>
                                        <select class="form-select" id="tipo_marcacion" name="tipo_marcacion" disabled required>
                                            <option value="default" selected disabled>-</option>
                                            <option value="ENTRADA">Entrada</option>
                                            <option value="DESCANSO_IN">Iniciar Descanso</option>
                                            <option value="DESCANSO_OUT">Terminar Descanso</option>
                                            <option value="SALIDA">Salida</option>
                                        </select>
                                        <div class="invalid-feedback" id="stipo_marcacion"></div>
                                    </div>

                                    <!-- Campo 'Observaciones' eliminado en vista pública -->

                                    <div class="col-12 col-md-8 mx-auto text-end">
                                        <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnRegisterAttendance" disabled>
                                            <i class="fas fa-check me-2"></i>Registrar Asistencia
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
