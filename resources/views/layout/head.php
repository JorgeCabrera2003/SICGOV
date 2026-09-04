<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Título dinámico -->
    <title><?php echo $titulo ?? 'SICGOV - Good Vibes'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/assets/img/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/assets/img/logo.png">
    
    <!-- Bootstrap 5.3 + Iconos -->
    <link href="<?php echo BASE_URL; ?>/assets/css/lib/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/bootstrap-icons.css">
    
    <!-- Font Awesome 6 (opcional, como respaldo) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/all.min.css">
    
    <!-- DataTables + Bootstrap 5 -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/lib/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/lib/responsive.bootstrap5.min.css">
    
    <!-- Select2 + Bootstrap 5 -->
    <link href="<?php echo BASE_URL; ?>/assets/css/lib/select2.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/lib/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados (después de Bootstrap) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/notificaciones.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/temas/default.css?v=<?php echo time(); ?>" id="theme-stylesheet">
    
    <!-- Temas dinámicos -->
    <?php if (isset($tema_actual) && $tema_actual > 0): ?>
    <script>
        // Cambiar tema si es necesario
        const temas = {
            1: '<?php echo BASE_URL; ?>/assets/css/temas/rosa.css',
            2: '<?php echo BASE_URL; ?>/assets/css/temas/azul.css',
            3: '<?php echo BASE_URL; ?>/assets/css/temas/verde.css',
            4: '<?php echo BASE_URL; ?>/assets/css/temas/rojo.css',
            5: '<?php echo BASE_URL; ?>/assets/css/temas/morado.css'
        };
        if (temas[<?php echo $tema_actual; ?>]) {
            document.getElementById('theme-stylesheet').href = temas[<?php echo $tema_actual; ?>];
        }
    </script>
    <?php endif; ?>

    <?php if (!empty($extra_css) && is_array($extra_css)): ?>
        <?php foreach ($extra_css as $cssPath): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPath, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Variables globales para JavaScript -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const CURRENT_PAGE = '<?php echo $page ?? ''; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
        window.idiomaTabla = BASE_URL + '/assets/DataTables/espanol.json';
    </script>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- El contenido se carga después -->