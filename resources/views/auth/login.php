<?php
$extra_css = [BASE_URL . '/assets/css/auth.css'];
require_once __DIR__ . '/../layout/head.php';
$openRegisterSlide = $openRegisterSlide ?? false;
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
                                <h2 class="mb-4">Tu Portal <br><span class="text-primary-gradient">Good Vibes</span></h2>
                                <p class="text-white opacity-50 px-4 lead">
                                    Accede a la mejor plataforma de gestión gastronómica con seguridad y elegancia.
                                </p>
                                
                                <div class="mt-5 pt-4 border-top border-white border-opacity-10">
                                    <div class="d-flex justify-content-center gap-4">
                                        <div class="text-center">
                                            <div class="h4 fw-bold text-white mb-0">100%</div>
                                            <div class="small text-white-50">Seguro</div>
                                        </div>
                                        <div class="vr opacity-25"></div>
                                        <div class="text-center">
                                            <div class="h4 fw-bold text-white mb-0">24/7</div>
                                            <div class="small text-white-50">Soporte</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna de Formularios -->
                        <div class="col-12 col-lg-7 auth-form-column">
                            <div id="authCarousel" class="carousel slide carousel-swap" data-bs-interval="false">
                                <div class="carousel-inner">
                                    
                                    <!-- Slide de Login -->
                                    <div class="carousel-item active" data-auth-slide="login">
                                        <div class="auth-header mb-5">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h1 class="h2 fw-bold text-white mb-0">Bienvenido</h1>
                                                <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo" class="auth-brand d-lg-none" style="height: 40px;">
                                            </div>
                                            <p class="text-white-50">Ingresa tus credenciales para continuar al panel de control.</p>
                                        </div>

                                        <?php if (isset($_SESSION['error_login']) && $_SESSION['error_login']): ?>
                                            <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger shadow-sm mb-4" role="alert">
                                                <i class="fas fa-circle-exclamation me-2"></i>
                                                <?php echo $_SESSION['error_login']; ?>
                                                <?php unset($_SESSION['error_login']); ?>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                        <form action="?page=login" method="post" id="login-form">
                                            <div class="form-group">
                                                <label for="CI" class="form-label">Cédula de identidad</label>
                                                <div class="input-group">
                                                    <select class="form-select auth-sm-select" id="particle" name="particle" required style="max-width: 80px;">
                                                        <option value="V">V</option>
                                                        <option value="E">E</option>
                                                        <option value="J">J</option>
                                                        <option value="G">G</option>
                                                    </select>
                                                    <input type="text" name="CI" class="form-control" placeholder="Ej: 12345678" required maxlength="8">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="password" class="form-label">Contraseña</label>
                                                <div class="input-group">
                                                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                                                    <button class="btn btn-outline-secondary" type="button" data-password-toggle="#password">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="text-end mt-2">
                                                    <a href="?page=recuperar-password" class="text-decoration-none small text-white-50 hover-white">¿Olvidaste tu contraseña?</a>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-center my-4">
                                                <div class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>" data-theme="dark"></div>
                                            </div>

                                            <div class="d-grid gap-3">
                                                <button class="btn btn-primary btn-lg" type="submit" name="peticion" value="sesion">
                                                    Ingresar al Sistema <i class="fa-solid fa-right-to-bracket ms-2"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-dark btn-lg" data-bs-target="#authCarousel" data-bs-slide-to="1">
                                                    ¿No tienes cuenta? Regístrate
                                                </button>
                                            </div>

                                            <div class="text-center mt-5">
                                                <a href="<?= BASE_URL ?>" class="text-decoration-none small fw-bold text-primary hover-scale d-inline-block">
                                                    <i class="fas fa-arrow-left me-2"></i> Regresar al Inicio
                                                </a>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Slide de Registro -->
                                    <div class="carousel-item" data-auth-slide="register">
                                        <div class="auth-header mb-4">
                                            <h1 class="h2 fw-bold text-white mb-2">Crear Cuenta</h1>
                                            <p class="text-white-50">Únete a nuestra comunidad y comienza a gestionar tu negocio.</p>
                                        </div>

                                        <?php if (isset($_SESSION['error_register']) && $_SESSION['error_register']): ?>
                                            <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger shadow-sm" role="alert">
                                                <i class="fas fa-circle-exclamation me-2"></i>
                                                <?php echo $_SESSION['error_register']; ?>
                                                <?php unset($_SESSION['error_register']); ?>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                        <form id="register-form" action="?page=crear-cuenta" method="post" class="needs-validation" novalidate>
                                            <input type="hidden" name="peticion" value="registrar">
                                            
                                            <div class="auth-register-scroll pe-2 custom-scrollbar" style="max-height: 380px; overflow-y: auto;">
                                                <?php $formContext = 'auth'; include __DIR__ . '/../partials/_user_form.php'; ?>
                                            </div>

                                            <div class="d-flex justify-content-center mt-4 mb-2">
                                                <div class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>" data-theme="dark"></div>
                                            </div>

                                            <div class="d-grid gap-3">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    Registrarse ahora <i class="fa-solid fa-user-plus ms-2"></i>
                                                </button>
                                                <button type="button" class="btn btn-link text-decoration-none text-white-50 hover-white" data-bs-target="#authCarousel" data-bs-slide-to="0">
                                                    Ya tengo una cuenta, iniciar sesión
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="carousel-indicators position-relative mt-5 mb-0">
                                    <button type="button" data-bs-target="#authCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                                    <button type="button" data-bs-target="#authCarousel" data-bs-slide-to="1"></button>
                                </div>
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
    .hover-scale { transition: transform 0.3s; }
    .hover-scale:hover { transform: scale(1.05); }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--color-acento); border-radius: 10px; }
</style>

<script src="<?php echo BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/auth.js"></script>
<script>
    window.authOpenRegisterSlide = <?php echo json_encode($openRegisterSlide); ?>;
</script>
<?php if (isset($_SESSION['show_disabled_alert']) && $_SESSION['show_disabled_alert']): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Cuenta inhabilitada',
            text: 'Su cuenta de usuario ha sido inhabilitada o se encuentra inactiva.',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Entendido'
        });
    });
</script>
<?php unset($_SESSION['show_disabled_alert']); endif; ?>

</body>
</html>
