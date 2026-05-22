<?php
$extra_css = [BASE_URL . '/assets/css/auth.css'];
require_once __DIR__ . '/../layout/head.php';
?>

<main class="auth-shell">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-12 col-xxl-10">
                <div class="card border-0 auth-card">
                    <div class="row g-0">
                        <!-- Columna de Branding (Visible solo en Desktop) -->
                        <div class="col-lg-5 d-none d-lg-flex auth-card__banner align-items-center justify-content-center text-center p-5">
                            <div class="auth-card__banner-content">
                                <div class="mb-5">
                                    <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo" class="auth-card__banner-logo mb-4">
                                </div>
                                <h2 class="mb-4">Seguridad <br><span class="text-primary-gradient">Good Vibes</span></h2>
                                <p class="text-white opacity-50 px-4 lead">
                                    Tu seguridad es nuestra prioridad. Por favor, actualiza tu contraseña para continuar.
                                </p>
                            </div>
                        </div>

                        <!-- Columna de Formularios -->
                        <div class="col-12 col-lg-7 auth-form-column">
                            <div class="p-5">
                                <div class="auth-header mb-5">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h1 class="h2 fw-bold text-white mb-0">Actualiza tu contraseña</h1>
                                        <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo" class="auth-brand d-lg-none" style="height: 40px;">
                                    </div>
                                    <p class="text-white-50">Has iniciado sesión por primera vez o un administrador ha restablecido tu clave. Debes cambiarla obligatoriamente.</p>
                                </div>

                                <div id="alerta-error" class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger shadow-sm mb-4 d-none" role="alert">
                                    <i class="fas fa-circle-exclamation me-2"></i>
                                    <span id="mensaje-error"></span>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>

                                <form id="form-forzar-clave">
                                    <div class="form-group mb-4">
                                        <label for="clave_nueva" class="form-label">Nueva Contraseña</label>
                                        <div class="input-group">
                                            <input type="password" name="clave_nueva" id="clave_nueva" class="form-control" placeholder="••••••••" required>
                                            <button class="btn btn-outline-secondary" type="button" data-password-toggle="#clave_nueva">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2 small" id="password-requirements">
                                            <ul class="text-white-50 mb-0 ps-3">
                                                <li id="req-length">Al menos 8 caracteres</li>
                                                <li id="req-upper">Al menos una letra mayúscula</li>
                                                <li id="req-number">Al menos un número</li>
                                                <li id="req-special">Al menos un carácter especial (!@#$%^&*)</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="form-group mb-5">
                                        <label for="clave_confirmar" class="form-label">Confirmar Contraseña</label>
                                        <div class="input-group">
                                            <input type="password" name="clave_confirmar" id="clave_confirmar" class="form-control" placeholder="••••••••" required>
                                            <button class="btn btn-outline-secondary" type="button" data-password-toggle="#clave_confirmar">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2 small text-danger d-none" id="match-error">
                                            <i class="fas fa-times-circle me-1"></i> Las contraseñas no coinciden.
                                        </div>
                                    </div>

                                    <div class="d-grid gap-3">
                                        <button class="btn btn-primary btn-lg" type="submit" id="btn-submit" disabled>
                                            Guardar y Continuar <i class="fa-solid fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="text-center mt-4">
                                        <a href="?page=logout" class="text-decoration-none text-white-50 hover-white">
                                            <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                                        </a>
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

<style>
    .hover-white:hover { color: #fff !important; }
    .req-met { color: #198754 !important; list-style-type: '✓ '; }
    .req-unmet { color: var(--bs-danger) !important; list-style-type: '× '; }
</style>

<script src="<?php echo BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Import jQuery specifically for this view if it isn't in auth.js -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>var BASE_URL = "<?php echo BASE_URL; ?>";</script>
<script src="<?php echo BASE_URL; ?>/assets/js/forzar_cambio_clave.js"></script>

</body>
</html>
