<?php
$extra_css = [BASE_URL . '/assets/css/auth.css'];
require_once __DIR__ . '/../layout/head.php';
$openRegisterModal = $openRegisterModal ?? false;
?>

<main class="auth-shell d-flex align-items-center">
    <div class="container py-5">
        <div class="row g-4 align-items-center">
            <section class="col-lg-5">
                <article class="auth-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Good Vibes" class="auth-brand mb-3">
                        <h1 class="h3 fw-bold mb-2">Bienvenido a Good Vibes</h1>
                        <p class="text-muted mb-0">Ingresa con tu usuario o crea tu cuenta en segundos.</p>
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
                            <div class="input-group input-group-lg shadow-sm rounded">
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
                            <div class="input-group input-group-lg shadow-sm rounded">
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
                            <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
                                Crear Cuenta <i class="fa-solid fa-user-plus ms-2"></i>
                            </button>
                        </div>

                        <div class="col-12 text-center">
                            <a href="?page=recuperar" class="text-decoration-none small text-muted">¿Olvidaste tu contraseña?</a>
                        </div>
                    </form>
                </article>
            </section>

            <aside class="col-lg-7">
                <div class="auth-carousel position-relative overflow-hidden">
                    <div id="loginCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner rounded-4">
                            <div class="carousel-item active" style="background-image: url('<?php echo BASE_URL; ?>/assets/img/gobernacion.jpg'); background-size: cover; background-position: center;">
                                <div class="carousel-caption text-start">
                                    <h3 class="display-6 fw-bold">Buena vibra desde el inicio</h3>
                                    <p class="lead">Un sistema pensado para tu negocio y tus clientes.</p>
                                </div>
                            </div>
                            <div class="carousel-item" style="background-image: url('<?php echo BASE_URL; ?>/assets/img/logo.png'); background-size: cover; background-position: center;">
                                <div class="carousel-caption text-start">
                                    <h3 class="display-6 fw-bold">Seguridad moderna</h3>
                                    <p class="lead">Acceso protegido con reCAPTCHA y validaciones inteligentes.</p>
                                </div>
                            </div>
                            <div class="carousel-item" style="background-image: url('<?php echo BASE_URL; ?>/assets/img/gobernacion.jpg'); background-size: cover; background-position: center;">
                                <div class="carousel-caption text-start">
                                    <h3 class="display-6 fw-bold">Registro y administración</h3>
                                    <p class="lead">El mismo formulario de usuario sirve en el login y en el módulo de usuarios.</p>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#loginCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#loginCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header auth-gradient-header text-dark">
                <h5 class="modal-title fw-bold" id="registerModalLabel">Crear nueva cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="register-form" action="?page=crear-cuenta" method="post" class="needs-validation" novalidate>
                <input type="hidden" name="peticion" value="registrar">
                <div class="modal-body p-4">
                    <?php $formContext = 'auth'; include __DIR__ . '/../partials/_user_form.php'; ?>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-semibold">Crear cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/auth.js"></script>
<script>
    window.authOpenRegisterModal = <?php echo json_encode($openRegisterModal); ?>;
</script>

</body>
</html>
