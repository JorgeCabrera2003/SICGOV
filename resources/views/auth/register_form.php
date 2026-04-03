<?php
require_once __DIR__ . '/../layout/head.php';
?>

<body class="bg-light" style="position: relative;">
    <style>
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('<?php echo BASE_URL; ?>/assets/img/gobernacion.jpg');
            background-size: cover;
            background-position: center;
            filter: blur(5px);
            z-index: -1;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
            padding: 20px;
        }
    </style>

    <div class="container col-md-6 mb-4 d-flex justify-content-center align-items-center vh-100">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title mb-0" style="font-weight: 700; color: #333;">Crear cuenta</h2>
                <img style="width: 25%;" class="img-logo" src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo">
            </div>
            <div class="card-body p-4">

                <?php if (isset($_SESSION['error_register']) && $_SESSION['error_register']): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <?php echo $_SESSION['error_register']; ?>
                        <?php unset($_SESSION['error_register']); ?>
                    </div>
                <?php endif; ?>

                <form action="?page=crear-cuenta" method="post" class="row g-3 needs-validation" id="register-form">

                    <div class="col-md-6">
                        <label for="nombres" class="form-label">Nombres</label>
                        <input type="text" name="nombres" class="form-control" placeholder="Ej: Juan" required>
                    </div>

                    <div class="col-md-6">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" placeholder="Ej: Pérez" required>
                    </div>

                    <div class="col-md-6">
                        <label for="cedula" class="form-label">Cédula de Identidad</label>
                        <div class="input-group">
                            <select class="form-select" name="particle" style="max-width: 70px;">
                                <option value="V">V</option>
                                <option value="E">E</option>
                                <option value="J">J</option>
                                <option value="G">G</option>
                            </select>
                            <input type="text" name="CI" class="form-control" placeholder="Ej: 12345678" required maxlength="8">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="username" class="form-label">Nombre de Usuario</label>
                        <input type="text" name="username" class="form-control" placeholder="Ej: juanperez" required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" placeholder="Ej: 04121234567" required>
                    </div>

                    <div class="col-md-6">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" name="correo" class="form-control" placeholder="Ej: juan@example.com" required>
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-center my-3">
                        <div class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>"></div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary w-100 py-2" type="submit" name="peticion" value="registrar" style="font-weight: 600;">
                            Crear Cuenta <i class="fa-solid fa-user-plus ms-2"></i>
                        </button>
                    </div>

                    <div class="col-12 text-center mt-3">
                        <a href="?page=login" class="text-decoration-none small text-muted">¿Ya tienes cuenta? Inicia sesión</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/jquery.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/login.js"></script>

    <script>
        $(document).ready(function() {
            $("#togglePassword").click(function() {
                const input = $("#password");
                const icon = $(this).find("i");
                if (input.attr("type") === "password") {
                    input.attr("type", "text");
                    icon.removeClass("fa-eye").addClass("fa-eye-slash");
                } else {
                    input.attr("type", "password");
                    icon.removeClass("fa-eye-slash").addClass("fa-eye");
                }
            });

            $("#toggleConfirmPassword").click(function() {
                const input = $("#confirm_password");
                const icon = $(this).find("i");
                if (input.attr("type") === "password") {
                    input.attr("type", "text");
                    icon.removeClass("fa-eye").addClass("fa-eye-slash");
                } else {
                    input.attr("type", "password");
                    icon.removeClass("fa-eye-slash").addClass("fa-eye");
                }
            });
        });
    </script>
</body>

</html>