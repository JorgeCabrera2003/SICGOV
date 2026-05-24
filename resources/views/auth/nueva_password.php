<?php
$extra_css = [BASE_URL . '/assets/css/auth.css'];
require_once __DIR__ . '/../layout/head.php';
?>

<main class="auth-shell">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 auth-card p-4">
                    <div class="auth-header mb-4 text-center">
                        <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo" class="auth-brand mb-3" style="height: 60px;">
                        <h1 class="h3 fw-bold text-white mb-2">Nueva Contraseña</h1>
                        <p class="text-white-50">Ingresa tu nueva contraseña y confírmala.</p>
                    </div>

                    <?php if (isset($_SESSION['error_restablecer']) && $_SESSION['error_restablecer']): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger shadow-sm mb-4" role="alert">
                            <i class="fas fa-circle-exclamation me-2"></i>
                            <?php echo $_SESSION['error_restablecer']; unset($_SESSION['error_restablecer']); ?>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="?page=restablecer-password" method="post">
                        <input type="hidden" name="peticion" value="restablecer">
                        
                        <div class="form-group mb-3">
                            <label for="clave" class="form-label text-white">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="clave" id="clave" class="form-control" placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary" type="button" data-password-toggle="#clave">
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

                        <div class="form-group mb-4">
                            <label for="rclave" class="form-label text-white">Confirmar Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="rclave" id="rclave" class="form-control" placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary" type="button" data-password-toggle="#rclave">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            <div class="mt-2 small text-danger d-none" id="match-error">
                                <i class="fas fa-times-circle me-1"></i> Las contraseñas no coinciden.
                            </div>
                        </div>

                        <div class="d-grid gap-3">
                            <button class="btn btn-primary btn-lg" type="submit" id="btn-submit" disabled>
                                Actualizar Contraseña <i class="fa-solid fa-save ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="<?php echo BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/auth.js"></script>
</body>
</html>
