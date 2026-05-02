<?php
$extra_css = [BASE_URL . '/assets/css/auth.css'];
require_once __DIR__ . '/../layout/head.php';
$openRegisterSlide = $openRegisterSlide ?? false;
?>

<main class="auth-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-10">
                <div class="card border-0 auth-card">
                    <div class="row g-0">
                        <!-- Columna de Branding (Visible solo en Desktop) -->
                        <div class="col-lg-5 d-none d-lg-flex auth-card__banner align-items-center justify-content-center text-center p-5">
                            <div class="auth-card__banner-content">
                                <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo" class="auth-card__banner-logo mb-4">
                                <h2 class="text-white fw-bold mb-3">¡Bienvenido de nuevo!</h2>
                                <p class="text-white opacity-75">Gestiona tu sistema con la mejor experiencia y seguridad.</p>
                            </div>
                        </div>

                        <!-- Columna de Formularios -->
                        <div class="col-12 col-lg-7 auth-form-column">
                            <div id="authCarousel" class="carousel slide carousel-swap" data-bs-interval="false">
                                <div class="carousel-inner">
                                    
                                    <!-- Slide de Login -->
                                    <div class="carousel-item active" data-auth-slide="login">
                                        <div class="auth-header mb-4">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo" class="auth-brand d-lg-none">
                                                <h1 class="h3 fw-bold mb-0">Iniciar sesión</h1>
                                            </div>
                                            <p class="text-muted">Ingresa tus credenciales para acceder al panel.</p>
                                        </div>

                                        <?php if (isset($_SESSION['error_login']) && $_SESSION['error_login']): ?>
                                            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                                                <i class="fas fa-circle-exclamation me-2"></i>
                                                <?php echo $_SESSION['error_login']; ?>
                                                <?php unset($_SESSION['error_login']); ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                        <form action="?page=login" method="post" id="login-form">
                                            <div class="form-group">
                                                <label for="particle" class="form-label">Cédula de identidad</label>
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
                                                    <a href="?page=recuperar" class="text-decoration-none small text-muted">¿Olvidaste tu contraseña?</a>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-center my-2">
                                                <div class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>"></div>
                                            </div>

                                            <div class="d-grid gap-3">
                                                <button class="btn btn-primary btn-lg py-3" type="submit" name="peticion" value="sesion">
                                                    Ingresar al Sistema <i class="fa-solid fa-right-to-bracket ms-2"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-dark btn-lg py-3" data-bs-target="#authCarousel" data-bs-slide-to="1">
                                                    Crear cuenta <i class="fa-solid fa-user-plus ms-2"></i>
                                                </button>
                                            </div>

                                            <div class="text-center mt-4">
                                                <a href="<?= BASE_URL ?>" class="text-decoration-none small fw-bold text-primary">
                                                    <i class="fas fa-arrow-left me-1"></i> Regresar al Inicio
                                                </a>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Slide de Registro -->
                                    <div class="carousel-item" data-auth-slide="register">
                                        <div class="auth-header mb-4">
                                            <h1 class="h3 fw-bold mb-2">Crear cuenta</h1>
                                            <p class="text-muted">Únete a nuestra plataforma y gestiona todo en un solo lugar.</p>
                                        </div>

                                        <?php if (isset($_SESSION['error_register']) && $_SESSION['error_register']): ?>
                                            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                                                <i class="fas fa-circle-exclamation me-2"></i>
                                                <?php echo $_SESSION['error_register']; ?>
                                                <?php unset($_SESSION['error_register']); ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                        <form id="register-form" action="?page=crear-cuenta" method="post" class="needs-validation" novalidate>
                                            <input type="hidden" name="peticion" value="registrar">
                                            
                                            <div class="auth-register-scroll pe-2" style="max-height: 400px; overflow-y: auto;">
                                                <?php $formContext = 'auth'; include __DIR__ . '/../partials/_user_form.php'; ?>
                                            </div>

                                            <div class="d-grid gap-3 mt-4">
                                                <button type="submit" class="btn btn-primary btn-lg py-3">
                                                    Registrarse ahora <i class="fa-solid fa-user-plus ms-2"></i>
                                                </button>
                                                <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-target="#authCarousel" data-bs-slide-to="0">
                                                    Ya tengo una cuenta, iniciar sesión
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="carousel-indicators position-relative mt-4 mb-0">
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

<script src="<?php echo BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/auth.js"></script>
<script>
    window.authOpenRegisterSlide = <?php echo json_encode($openRegisterSlide); ?>;
</script>

</body>
</html>
