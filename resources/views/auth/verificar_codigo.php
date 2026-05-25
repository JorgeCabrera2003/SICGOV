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
                        <h1 class="h3 fw-bold text-white mb-2">Verificar Código</h1>
                        <p class="text-white-50">Hemos enviado un código a <strong><?php echo htmlspecialchars($_SESSION['recovery_correo'] ?? ''); ?></strong></p>
                    </div>

                    <?php if (isset($_SESSION['error_verificar']) && $_SESSION['error_verificar']): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger shadow-sm mb-4" role="alert">
                            <i class="fas fa-circle-exclamation me-2"></i>
                            <?php echo $_SESSION['error_verificar']; unset($_SESSION['error_verificar']); ?>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="?page=verificar-codigo" method="post">
                        <input type="hidden" name="peticion" value="verificar">
                        
                        <div class="form-group mb-4">
                            <label for="codigo" class="form-label text-white">Código de Verificación (6 dígitos)</label>
                            <input type="text" name="codigo" id="codigo" class="form-control text-center" placeholder="000000" maxlength="6" pattern="\d{6}" required>
                        </div>

                        <div class="d-grid gap-3">
                            <button class="btn btn-primary btn-lg" type="submit">
                                Verificar Código <i class="fa-solid fa-check-circle ms-2"></i>
                            </button>
                            <a href="?page=recuperar-password" class="btn btn-outline-dark btn-lg text-white">
                                Cambiar Correo
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="<?php echo BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
