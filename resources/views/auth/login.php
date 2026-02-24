<?php
// resources/views/auth/login.php
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

  <div class="container col-md-4 mb-4 d-flex justify-content-center align-items-center vh-100">
    <div class="card w-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title mb-0" style="font-weight: 700; color: #333;">Iniciar Sesión</h2>
        <img style="width: 25%;" class="img-logo" src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo">
      </div>
      <div class="card-body p-4">

        <?php if (isset($_SESSION['error_login']) && $_SESSION['error_login']): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <?php echo $_SESSION['error_login']; ?>
            <?php unset($_SESSION['error_login']); ?>
          </div>
        <?php endif; ?>

        <form action="?page=login" method="post" class="row g-3 needs-validation" id="login-form">

          <div class="col-12">
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

          <div class="col-12">
            <label for="password" class="form-label">Contraseña</label>
            <div class="input-group">
              <input type="password" name="password" id="password" class="form-control" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="fa fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="col-12 d-flex justify-content-center my-3">
            <div class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>"></div>
          </div>

          <div class="col-12">
            <button class="btn btn-primary w-100 py-2" type="submit" name="peticion" value="sesion" style="font-weight: 600;">
              Ingresar al Sistema <i class="fa-solid fa-right-to-bracket ms-2"></i>
            </button>
          </div>

          <div class="col-12 text-center mt-3">
            <a href="?page=recuperar" class="text-decoration-none small text-muted">¿Olvidaste tu contraseña?</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts usando BASE_URL -->
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
    });
  </script>
</body>

</html>