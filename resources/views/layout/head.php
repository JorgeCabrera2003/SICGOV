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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Font Awesome 6 (opcional, como respaldo) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- DataTables + Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <!-- Select2 + Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados (después de Bootstrap) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/temas/default.css" id="theme-stylesheet">
    
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
    
    <!-- Variables globales para JavaScript -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const CURRENT_PAGE = '<?php echo $page ?? ''; ?>';
        const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    </script>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- El contenido se carga después -->