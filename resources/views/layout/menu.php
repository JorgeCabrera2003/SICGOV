<?php

/**
 * MENÚ PRINCIPAL - SICGOV
 * 
 * Características:
 * - Sidebar colapsable con botón visible
 * - Bandeja de notificaciones interactiva
 * - Perfil de usuario con menú
 * - Sin color de fondo fijo (se adapta al tema)
 */
?>

<!-- Sidebar -->
<aside class="sidebar d-flex flex-column flex-shrink-0 vh-100 position-fixed" id="sidebar">
    <!-- Cabecera con logo -->
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
            <button class="btn btn-link p-0 text-decoration-none" id="collapse-btn" aria-label="Colapsar menú">
                <i class="bi bi-chevron-left fs-5" id="collapse-icon"></i>
            </button>

            <a href="<?php echo BASE_URL; ?>/?page=home" class="d-flex align-items-center gap-2 text-decoration-none">
                <img src="<?php echo BASE_URL; ?>/assets/img/favicon.ico" alt="logo" class="logo-img" id="logo-img">
                <span class="h5 mb-0 fw-bold" id="logo-text">GOOD VIBES</span>
            </a>
        </div>

        <button class="btn btn-link d-lg-none p-0" id="sidebar-close" aria-label="Cerrar menú">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <!-- Perfil de usuario -->
    <div class="user-profile d-flex align-items-center gap-3 px-3 py-2 border-bottom">
        <div class="user-avatar">
            <i class="bi bi-person-circle fs-4"></i>
        </div>
        <div class="user-info">
            <div class="user-name fw-semibold"><?php echo $datos['nombres'] ?? 'Usuario'; ?></div>
            <div class="user-role small text-muted"><?php echo $datos['rol'] ?? 'Sin rol'; ?></div>
        </div>
    </div>

    <!-- Navegación principal -->
    <nav class="nav nav-pills flex-column gap-1 px-2 py-3 flex-grow-1 overflow-auto" aria-label="Menú principal">
        <!-- Dashboard -->
        <a href="<?php echo BASE_URL; ?>/?page=home"
            class="nav-link <?php echo ($page == 'home') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="bi bi-speedometer2 fs-5"></i>
            <span class="flex-grow-1">Dashboard</span>
            <?php if ($page == 'home'): ?>
                <span class="visually-hidden">(actual)</span>
            <?php endif; ?>
        </a>

        <!-- Pedidos / Mesas -->
        <a href="<?php echo BASE_URL; ?>/?page=pedidos"
            class="nav-link <?php echo ($page == 'pedidos') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="bi bi-egg-fried fs-5"></i>
            <span class="flex-grow-1">Pedidos / Mesas</span>
        </a>

        <!-- Productos -->
        <a href="<?php echo BASE_URL; ?>/?page=productos"
            class="nav-link <?php echo ($page == 'productos') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="bi bi-box-seam fs-5"></i>
            <span class="flex-grow-1">Productos</span>
        </a>

        <!-- Ingredientes -->
        <a href="?page=ingredientes" 
           class="nav-link <?php echo ($page == 'ingredientes') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="fa-solid fa-jar"></i>
            <span class="flex-grow-1">Ingredientes</span>
        </a>

        <!-- Separador -->
        <hr class="my-2 opacity-25">

        <!-- Gestión de Equipos (colapsable) -->
        <div class="nav-item w-100">
            <a class="nav-link d-flex align-items-center gap-2 collapsed"
                data-bs-toggle="collapse"
                href="#equipos-submenu"
                role="button"
                aria-expanded="<?php echo in_array($page, ['bien', 'equipo', 'material']) ? 'true' : 'false'; ?>">
                <i class="bi bi-pc-display fs-5"></i>
                <span class="flex-grow-1">Gestión de Equipos</span>
                <i class="bi bi-chevron-right transition-rotate"></i>
            </a>
            <div class="collapse <?php echo in_array($page, ['bien', 'equipo', 'material']) ? 'show' : ''; ?>" id="equipos-submenu">
                <div class="d-flex flex-column gap-1 ps-4 mt-1">
                    <a href="<?php echo BASE_URL; ?>/?page=bien"
                        class="nav-link <?php echo ($page == 'bien') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="bi bi-box fs-6"></i>
                        <span>Bienes</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/?page=equipo"
                        class="nav-link <?php echo ($page == 'equipo') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="bi bi-cpu fs-6"></i>
                        <span>Equipos</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/?page=material"
                        class="nav-link <?php echo ($page == 'material') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="bi bi-tools fs-6"></i>
                        <span>Materiales</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <a href="<?php echo BASE_URL; ?>/?page=estadistica"
            class="nav-link <?php echo ($page == 'estadistica') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="bi bi-bar-chart-steps fs-5"></i>
            <span class="flex-grow-1">Estadísticas</span>
        </a>

        <!-- Bitácora -->
        <a href="<?php echo BASE_URL; ?>/?page=bitacora"
            class="nav-link <?php echo ($page == 'bitacora') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="bi bi-journal-bookmark-fill fs-5"></i>
            <span class="flex-grow-1">Bitácora</span>
        </a>

        <!-- Ayuda -->
        <a href="<?php echo BASE_URL; ?>/?page=ayuda"
            class="nav-link <?php echo ($page == 'ayuda') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="bi bi-question-circle fs-5"></i>
            <span class="flex-grow-1">Ayuda</span>
        </a>
    </nav>

    <!-- Cerrar sesión (siempre visible) -->
    <div class="p-3 border-top">
        <a href="<?php echo BASE_URL; ?>/logout.php"
            class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-right"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>

<!-- Contenido principal -->
<main class="main-content flex-grow-1" id="main-content">
    <!-- Barra superior -->
    <header class="bg-body-tertiary border-bottom sticky-top" id="top-nav">
        <div class="d-flex align-items-center justify-content-between px-3" style="height: 60px;">
            <div class="d-flex align-items-center gap-3">
                <!-- Botón para móvil (abrir sidebar) -->
                <button class="btn btn-link d-lg-none p-0" id="sidebar-toggle" aria-label="Abrir menú">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/?page=home" class="text-decoration-none">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo ucfirst($page ?? 'Dashboard'); ?></li>
                    </ol>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Notificaciones -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-2 position-relative"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        id="notificationDropdown"
                        aria-label="Notificaciones">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge"
                            id="notificationBadge"
                            style="font-size: 0.6rem; display: none;">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Notificaciones</h6>
                            <button class="btn btn-sm btn-link p-0" id="markAllRead" title="Marcar todas como leídas">
                                <i class="bi bi-check2-all"></i>
                            </button>
                        </div>
                        <div class="notification-list" id="notificationList">
                            <!-- Las notificaciones se cargarán aquí vía JavaScript -->
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                <span>Cargando notificaciones...</span>
                            </div>
                        </div>
                        <div class="dropdown-footer text-center">
                            <a href="<?php echo BASE_URL; ?>/?page=notificaciones" class="btn btn-sm btn-primary w-100">
                                Ver todas
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Toggle de tema -->
                <button class="btn btn-link text-decoration-none p-2" id="theme-toggle" aria-label="Cambiar tema">
                    <i class="bi bi-moon-stars fs-5" id="theme-icon"></i>
                </button>

                <!-- Perfil de usuario (menú) -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        id="userDropdown">
                        <div class="user-avatar" style="width: 36px; height: 36px;">
                            <i class="bi bi-person-circle fs-5"></i>
                        </div>
                        <span class="d-none d-lg-inline"><?php echo $datos['nombres'] ?? 'Usuario'; ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <div class="dropdown-header">
                                <div class="fw-semibold"><?php echo $datos['nombres'] . ' ' . ($datos['apellidos'] ?? ''); ?></div>
                                <div class="small text-muted"><?php echo $datos['cedula'] ?? ''; ?></div>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>/?page=perfil">
                                <i class="bi bi-person"></i> Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>/?page=configuracion">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="<?php echo BASE_URL; ?>/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenedor para el contenido dinámico -->
    <div class="content-wrapper">