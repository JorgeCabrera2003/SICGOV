<?php
$extra_css = [BASE_URL . '/assets/css/auth.css'];
require_once __DIR__ . '/../layout/head.php';
$openRegisterSlide = $openRegisterSlide ?? false;
?>

<main class="auth-shell d-flex align-items-center">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xxl-8">
                <div class="card border-0 shadow-lg auth-card overflow-hidden">
                    <div id="authCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="7000">
                        <div class="carousel-inner">
                            <div class="carousel-item active" data-auth-slide="login">
                                <div class="row g-0 align-items-center">
                                    <div class="col-lg-6 p-4 p-lg-5">
                                        <div class="text-center mb-4">
                                            <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Good Vibes" class="auth-brand mb-3">
                                            <h1 class="h3 fw-bold mb-2">Iniciar sesión</h1>
                                            <p class="text-muted mb-0">Ingresa con tu usuario y contraseña.</p>
                                        </div>

                                        <?php if (isset($_SESSION['error_login']) && $_SESSION['error_login']): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <i class="fas fa-circle-exclamation me-2"></i>
                                                <?php echo $_SESSION['error_login']; ?>
                                                <?php unset($_SESSION['error_login']); ?>
                                            </div>
                                        <?php endif; ?>

                                        <form action="?page=login" method="post" id="login-form" class="row g-3">
                                            <div class="col-12">
                                                <label for="particle" class="form-label">Cédula de identidad</label>
                                                <div class="input-group shadow-sm rounded">
                                                    <select class="form-select" id="particle" name="particle" required>
                                                        <option value="V">V</option>
                                                        <option value="E">E</option>
                                                        <option value="J">J</option>
                                                        <option value="G">G</option>
                                                    </select>
                                                    <input type="text" name="CI" class="form-control" placeholder="Ej: 12345678" required maxlength="8">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="password" class="form-label">Contraseña</label>
                                                <div class="input-group shadow-sm rounded">
                                                    <input type="password" name="password" id="password" class="form-control" required>
                                                    <button class="btn btn-outline-secondary" type="button" data-password-toggle="#password">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>"></div>
                                            </div>
                                            <div class="col-12 d-grid gap-3">
                                                <button class="btn btn-primary btn-lg" type="submit" name="peticion" value="sesion">
                                                    Ingresar al Sistema <i class="fa-solid fa-right-to-bracket ms-2"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-target="#authCarousel" data-bs-slide-to="1">
                                                    Crear Cuenta <i class="fa-solid fa-user-plus ms-2"></i>
                                                </button>
                                            </div>
                                            <div class="col-12 text-center">
                                                <a href="?page=recuperar" class="text-decoration-none small text-muted">¿Olvidaste tu contraseña?</a>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-lg-6 d-none d-lg-flex auth-carousel align-items-end" style="background-image: url('<?php echo BASE_URL; ?>/assets/img/gobernacion.jpg'); background-size: cover; background-position: center;">
                                        <div class="carousel-caption text-start text-white p-4">
                                            <h3 class="display-6 fw-bold">Buena vibra desde el inicio</h3>
                                            <p class="lead">Un sistema pensado para tu negocio y tus clientes.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-item" data-auth-slide="register">
                                <div class="row g-0 align-items-center">
                                    <div class="col-lg-6 p-4 p-lg-5">
                                        <div class="text-center mb-4">
                                            <h1 class="h3 fw-bold mb-2">Crear cuenta</h1>
                                            <p class="text-muted mb-0">Regístrate ahora y comienza a gestionar tu sistema.</p>
                                        </div>

                                        <?php if (isset($_SESSION['error_register']) && $_SESSION['error_register']): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <i class="fas fa-circle-exclamation me-2"></i>
                                                <?php echo $_SESSION['error_register']; ?>
                                                <?php unset($_SESSION['error_register']); ?>
                                            </div>
                                        <?php endif; ?>

                                        <form id="register-form" action="?page=crear-cuenta" method="post" class="row g-3 needs-validation" novalidate>
                                            <input type="hidden" name="peticion" value="registrar">
                                            <?php $formContext = 'auth'; include __DIR__ . '/../partials/_user_form.php'; ?>
                                            <div class="col-12 d-grid gap-3">
                                                <button type="submit" class="btn btn-warning btn-lg text-dark fw-semibold">
                                                    Crear cuenta <i class="fa-solid fa-user-plus ms-2"></i>
                                                </button>
                                                <button type="button" class="btn btn-link text-decoration-none" data-bs-target="#authCarousel" data-bs-slide-to="0">
                                                    Ya tengo cuenta
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-lg-6 d-none d-lg-flex auth-carousel align-items-end" style="background-image: url('<?php echo BASE_URL; ?>/assets/img/logo.png'); background-size: cover; background-position: center;">
                                        <div class="carousel-caption text-start text-white p-4">
                                            <h3 class="display-6 fw-bold">Registro rápido y seguro</h3>
                                            <p class="lead">La misma experiencia de usuario en login y registro.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-indicators mt-4 mb-3">
                            <button type="button" data-bs-target="#authCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Iniciar sesión"></button>
                            <button type="button" data-bs-target="#authCarousel" data-bs-slide-to="1" aria-label="Crear cuenta"></button>
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
    window.authCarouselSlide = <?php echo json_encode($openRegisterSlide); ?>;
</script>

</body>
</html>
