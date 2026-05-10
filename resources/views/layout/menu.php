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

<?php if (!isset($hideSidebar) || !$hideSidebar): ?>
<!-- Sidebar -->
<aside class="sidebar d-flex flex-column flex-shrink-0 vh-100 position-fixed" id="sidebar">
    <!-- Cabecera con logo -->
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
        <div class="sidebar-header-content d-flex align-items-center gap-2 overflow-hidden">
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
    <nav class="nav nav-pills flex-nowrap flex-column gap-1 px-2 py-3 flex-grow-1 overflow-auto" aria-label="Menú principal">
        <!-- Dashboard -->
        <a href="<?php echo BASE_URL; ?>/?page=home"
            class="nav-link <?php echo ($page == 'home') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="bi bi-speedometer2 fs-5"></i>
            <span class="flex-grow-1">Dashboard</span>
            <?php if ($page == 'home'): ?>
                <span class="visually-hidden">(actual)</span>
            <?php endif; ?>
        </a>

        <!-- Usuario -->
        <a href="<?php echo BASE_URL; ?>/?page=usuario"
            class="nav-link <?php echo ($page == 'usuario') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="fa-solid fa-users"></i>
            <span class="flex-grow-1">Usuarios</span>
        </a>

        <!-- Clientes -->
        <a href="<?php echo BASE_URL; ?>/?page=clientes"
            class="nav-link <?php echo ($page == 'clientes') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="fas fa-users"></i>
            <span class="flex-grow-1">Clientes</span>
        </a>

        <!-- Reservaciones (Colapsable) -->
        <div class="nav-item w-100">
            <a class="nav-link d-flex align-items-center gap-2 <?php echo in_array($page, ['reservaciones', 'reservar']) ? '' : 'collapsed'; ?>" 
                data-bs-toggle="collapse" href="#reservas-submenu" role="button"
                aria-expanded="<?php echo in_array($page, ['reservaciones', 'reservar']) ? 'true' : 'false'; ?>">
                <i class="bi bi-calendar-check fs-5"></i>
                <span class="flex-grow-1">Reservaciones</span>
                <i class="bi bi-chevron-right transition-rotate"></i>
            </a>
            <div class="collapse <?php echo in_array($page, ['reservaciones', 'reservar']) ? 'show' : ''; ?>" id="reservas-submenu">
                <div class="d-flex flex-column gap-1 ps-4 mt-1">
                    <?php if (in_array($datos['rol'], ['ADMINISTRADOR', 'VENTAS'])): ?>
                    <a href="<?php echo BASE_URL; ?>/?page=reservaciones"
                        class="nav-link <?php echo ($page == 'reservaciones') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="bi bi-calendar3 fs-6"></i>
                        <span>Agenda Global</span>
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo BASE_URL; ?>/?page=reservar"
                        class="nav-link <?php echo ($page == 'reservar') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="bi bi-calendar-heart fs-6"></i>
                        <span>Mis Citas</span>
                    </a>
                </div>
            </div>
        </div>


        <!-- Pedidos / Mesas -->
        <div class="nav-item w-100">
            <a class="nav-link d-flex align-items-center gap-2 collapsed" data-bs-toggle="collapse"
                href="#pedidos-mesas-submenu" role="button"
                aria-expanded="<?php echo in_array($page, ['areas', 'mesas', 'pedidos']) ? 'true' : 'false'; ?>">
                <i class="fas fa-clipboard-list fs-5"></i>
                <span class="flex-grow-1">Pedidos / Mesas</span>
                <i class="bi bi-chevron-right transition-rotate"></i>
            </a>
            <div class="collapse <?php echo in_array($page, ['areas', 'mesas', 'pedidos']) ? 'show' : ''; ?>"
                id="pedidos-mesas-submenu">
                <div class="d-flex flex-column gap-1 ps-4 mt-1">
                    <a href="<?php echo BASE_URL; ?>/?page=areas"
                        class="nav-link <?php echo ($page == 'areas') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="fas fa-building fs-6"></i>
                        <span>Áreas</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/?page=mesas"
                        class="nav-link <?php echo ($page == 'mesas') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="fas fa-chair fs-6"></i>
                        <span>Mesas</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/?page=pedidos"
                        class="nav-link <?php echo ($page == 'pedidos') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="fas fa-receipt fs-6"></i>
                        <span>Pedidos</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Menú del Restaurante (colapsable) -->
        <div class="nav-item w-100">
            <a class="nav-link d-flex align-items-center gap-2 collapsed" data-bs-toggle="collapse"
                href="#menu-submenu" role="button"
                aria-expanded="<?php echo in_array($page, ['menu', 'categorias']) ? 'true' : 'false'; ?>">
                <i class="fas fa-utensils fs-5"></i>
                <span class="flex-grow-1">Menú del Restaurante</span>
                <i class="bi bi-chevron-right transition-rotate"></i>
            </a>
            <div class="collapse <?php echo in_array($page, ['menu', 'categorias']) ? 'show' : ''; ?>"
                id="menu-submenu">
                <div class="d-flex flex-column gap-1 ps-4 mt-1">
                    <a href="<?php echo BASE_URL; ?>/?page=menu"
                        class="nav-link <?php echo ($page == 'menu') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="fas fa-hamburger fs-6"></i>
                        <span>Productos</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/?page=categorias"
                        class="nav-link <?php echo ($page == 'categorias') ? 'active' : ''; ?> d-flex align-items-center gap-2 py-1">
                        <i class="fas fa-tags fs-6"></i>
                        <span>Categorías</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <a href="<?php echo BASE_URL; ?>/?page=productos"
            class="nav-link <?php echo ($page == 'productos') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="bi bi-box-seam fs-5"></i>
            <span class="flex-grow-1">Productos Varios</span>
        </a>

        <!-- Ingredientes -->
        <a href="?page=ingredientes"
            class="nav-link <?php echo ($page == 'ingredientes') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="fa-solid fa-jar"></i>
            <span class="flex-grow-1">Ingredientes</span>
        </a>

        <!-- Noticias -->
        <a href="?page=noticias-admin"
            class="nav-link <?php echo ($page == 'noticias-admin' || $page == 'noticias' || $page == 'noticias-detalle') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="fas fa-newspaper fs-5"></i>
            <span class="flex-grow-1">Noticias</span>
        </a>

        <!-- Multimedia -->
        <a href="?page=multimedia"
            class="nav-link <?php echo ($page == 'multimedia') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="fas fa-images fs-5"></i>
            <span class="flex-grow-1">Multimedia</span>
        </a>

        <!-- Separador -->
        <hr class="my-2 opacity-25">

        <!-- Gestión de Equipos (colapsable) -->
        <div class="nav-item w-100">
            <a class="nav-link d-flex align-items-center gap-2 collapsed" data-bs-toggle="collapse"
                href="#equipos-submenu" role="button"
                aria-expanded="<?php echo in_array($page, ['bien', 'equipo', 'material']) ? 'true' : 'false'; ?>">
                <i class="bi bi-pc-display fs-5"></i>
                <span class="flex-grow-1">Gestión de Equipos</span>
                <i class="bi bi-chevron-right transition-rotate"></i>
            </a>
            <div class="collapse <?php echo in_array($page, ['bien', 'equipo', 'material']) ? 'show' : ''; ?>"
                id="equipos-submenu">
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

        <!-- Reportes -->
        <a href="<?php echo BASE_URL; ?>/?page=reportes"
            class="nav-link <?php echo ($page == 'reportes') ? 'active' : ''; ?> d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-bar-graph fs-5"></i>
            <span class="flex-grow-1">Reportes</span>
        </a>

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

        <!-- Papelera de Reciclaje -->
        <a href="?page=papelera"
            class="nav-link <?php echo ($page == 'papelera') ? 'active' : ''; ?> d-flex align-items-center gap-2 text-danger">
            <i class="fas fa-trash-restore fs-5"></i>
            <span class="flex-grow-1">Papelera de Reciclaje</span>
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
        <a href="<?php echo BASE_URL; ?>/?page=logout"
            class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-right"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>
<?php endif; ?>

<!-- Contenido principal -->
<main class="main-content flex-grow-1 <?php echo (isset($hideSidebar) && $hideSidebar) ? 'ms-0 w-100' : ''; ?>" id="main-content">
    <!-- Barra superior -->
    <header class="bg-body-tertiary border-bottom sticky-top" id="top-nav" style="z-index: 1040;">
        <div class="d-flex align-items-center justify-content-between px-3" style="height: 60px;">
            <div class="d-flex align-items-center gap-3">
                <!-- Botón para móvil (abrir sidebar) -->
                <button class="btn btn-link d-lg-none p-0" id="sidebar-toggle" aria-label="Abrir menú">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <!-- Breadcrumbs removidos por solicitud del usuario -->

            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Notificaciones -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-2 position-relative" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" id="notificationDropdown"
                        aria-label="Notificaciones" <?php echo !isset($_SESSION['user']) ? 'disabled' : ''; ?>>
                        <i class="bi bi-bell fs-5"></i>
                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge"
                            id="notificationBadge" style="font-size: 0.6rem; display: none;">0</span>
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

                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" id="userDropdown">
                        <div class="user-avatar" style="width: 36px; height: 36px;">
                            <i class="bi bi-person-circle fs-5"></i>
                        </div>
                        <span class="d-none d-lg-inline"><?php echo $datos['nombres'] ?? 'Invitado'; ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <?php if (isset($_SESSION['user'])): ?>
                        <li>
                            <div class="dropdown-header">
                                <div class="fw-semibold">
                                    <?php echo ($datos['nombres'] ?? '') . ' ' . ($datos['apellidos'] ?? ''); ?></div>
                                <div class="small text-muted"><?php echo $datos['cedula'] ?? ''; ?></div>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="<?php echo BASE_URL; ?>/?page=perfil">
                                <i class="bi bi-person"></i> Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="<?php echo BASE_URL; ?>/?page=configuracion">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                href="<?php echo BASE_URL; ?>/?page=logout">
                                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                            </a>
                        </li>
                        <?php else: ?>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="<?php echo BASE_URL; ?>/?page=login">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenedor para el contenido dinámico -->
    <div class="content-wrapper">