<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Favicon y CSS con BASE_URL -->
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>/assets/img/favicon.png" type="image/x-icon">
    <link href="<?php echo BASE_URL; ?>/assets/Select2/css/select2.min.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>/assets/Select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $titulo ?></title>

    <!-- Bootstrap CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Temas -->
    <?php
    $theme_href = BASE_URL . '/assets/css/temas/default.css';
    if (isset($tema_actual)) {
        switch ($tema_actual) {
            case 1: $theme_href = BASE_URL . '/assets/css/temas/rosa.css'; break;
            case 2: $theme_href = BASE_URL . '/assets/css/temas/azul.css'; break;
            case 3: $theme_href = BASE_URL . '/assets/css/temas/verde.css'; break;
            case 4: $theme_href = BASE_URL . '/assets/css/temas/rojo.css'; break;
            case 5: $theme_href = BASE_URL . '/assets/css/temas/morado.css'; break;
            default: $theme_href = BASE_URL . '/assets/css/temas/default.css'; break;
        }
    }
    ?>
    <link id="theme-stylesheet" rel="stylesheet" href="<?php echo $theme_href; ?>" />

    <script>
        // Aplicar tema guardado en localStorage
        (function() {
            try {
                const map = {
                    0: '<?php echo BASE_URL; ?>/assets/css/temas/default.css',
                    1: '<?php echo BASE_URL; ?>/assets/css/temas/rosa.css',
                    2: '<?php echo BASE_URL; ?>/assets/css/temas/azul.css',
                    3: '<?php echo BASE_URL; ?>/assets/css/temas/verde.css',
                    4: '<?php echo BASE_URL; ?>/assets/css/temas/rojo.css',
                    5: '<?php echo BASE_URL; ?>/assets/css/temas/morado.css'
                };
                const sel = localStorage.getItem('selectedTheme');
                if (sel !== null && typeof sel !== 'undefined') {
                    const id = parseInt(sel, 10);
                    if (!isNaN(id) && map[id]) {
                        var link = document.getElementById('theme-stylesheet');
                        if (link) link.href = map[id];
                    }
                }
            } catch (e) {}
        })();
    </script>

    <!-- Estilos principales con BASE_URL -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/all.min.css" />

    <!-- DataTables -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/DataTables/datatables.css" />

    <!-- Tema oscuro -->
    <script>
        const htmlElement = document.documentElement;
        const savedTheme = localStorage.getItem("theme");
        if (
            savedTheme === "dark" ||
            (!savedTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)
        ) {
            htmlElement.classList.add("dark");
        }
    </script>

    <!-- ===== SCRIPTS ===== -->
    <script src="<?php echo BASE_URL; ?>/assets/js/jquery.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/utils.js"></script>

    <!-- Estilos adicionales -->
    <style>
        #tabla1 td, #tabla1 th { text-align: center; }
        .select2-container { width: 100% !important; }
    </style>

    <!-- Loader styles -->
    <style>
        html:not(.page-ready)>body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: #ffffff;
            z-index: 2000;
        }
        html:not(.page-ready)>body::after {
            content: "";
            position: fixed;
            left: 50%;
            top: 50%;
            width: 64px;
            height: 64px;
            margin-left: -32px;
            margin-top: -32px;
            border-radius: 50%;
            border: 8px solid #e9ecef;
            border-top-color: #7C1D21;
            animation: _sys_spin 1s linear infinite;
            z-index: 2001;
        }
        @keyframes _sys_spin { to { transform: rotate(360deg); } }
        html:not(.page-ready) body * { transition: none !important; }
    </style>
</head>