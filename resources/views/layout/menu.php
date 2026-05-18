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
        <nav class="nav nav-pills flex-nowrap flex-column gap-1 px-2 py-3 flex-grow-1 overflow-auto"
            aria-label="Menú principal">
            <!-- Dashboard -->
            <a href="<?php echo BASE_URL; ?>/?page=home"
                class="nav-link <?php echo ($page == 'home') ? 'active' : ''; ?> d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-grid-1x2-fill fs-5"></i>
                <span class="fw-bold">Dashboard Principal</span>
            </a>

            <!-- CATEGORÍA: ATENCIÓN AL CLIENTE -->
            <div class="nav-item w-100 mb-1">
                <small class="text-muted text-uppercase fw-bold px-3 mb-2 d-block"
                    style="font-size: 0.65rem; letter-spacing: 1px;">Atención al Cliente</small>
                <a class="nav-link d-flex align-items-center gap-2 <?php echo in_array($page, ['clientes', 'pedidos', 'reservaciones', 'reservar']) ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#cliente-submenu" role="button">
                    <i class="bi bi-person-heart fs-5"></i>
                    <span class="flex-grow-1">Servicio y Citas</span>
                    <i class="bi bi-chevron-right transition-rotate"></i>
                </a>
                <div class="collapse <?php echo in_array($page, ['clientes', 'pedidos', 'reservaciones', 'reservar']) ? 'show' : ''; ?>"
                    id="cliente-submenu">
                    <div class="d-flex flex-column gap-1 ps-4 mt-1">
                        <a href="?page=clientes" class="nav-link <?php echo ($page == 'clientes') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-people me-2"></i>Clientes
                        </a>
                        <a href="?page=pedidos" class="nav-link <?php echo ($page == 'pedidos') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-receipt me-2"></i>Pedidos
                        </a>
                        <?php if (in_array($datos['rol'], ['ADMINISTRADOR', 'VENTAS'])): ?>
                            <a href="?page=reservaciones"
                                class="nav-link <?php echo ($page == 'reservaciones') ? 'active' : ''; ?> py-1">
                                <i class="bi bi-calendar-check me-2"></i>Agenda Global
                            </a>
                        <?php endif; ?>
                        <a href="?page=reservar" class="nav-link <?php echo ($page == 'reservar') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-calendar-plus me-2"></i>Nueva Reserva
                        </a>
                    </div>
                </div>
            </div>

            <!-- CATEGORÍA: GESTIÓN DEL SALÓN -->
            <div class="nav-item w-100 mb-1">
                <small class="text-muted text-uppercase fw-bold px-3 mb-2 d-block"
                    style="font-size: 0.65rem; letter-spacing: 1px;">Gestión del Salón</small>
                <a class="nav-link d-flex align-items-center gap-2 <?php echo in_array($page, ['areas', 'mesas']) ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#salon-submenu" role="button">
                    <i class="bi bi-houses fs-5"></i>
                    <span class="flex-grow-1">Áreas y Mesas</span>
                    <i class="bi bi-chevron-right transition-rotate"></i>
                </a>
                <div class="collapse <?php echo in_array($page, ['areas', 'mesas']) ? 'show' : ''; ?>" id="salon-submenu">
                    <div class="d-flex flex-column gap-1 ps-4 mt-1">
                        <a href="?page=areas" class="nav-link <?php echo ($page == 'areas') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-geo-alt me-2"></i>Áreas del Local
                        </a>
                        <a href="?page=mesas" class="nav-link <?php echo ($page == 'mesas') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-table me-2"></i>Control de Mesas
                        </a>
                    </div>
                </div>
            </div>

            <!-- CATEGORÍA: COCINA E INVENTARIO -->
            <div class="nav-item w-100 mb-1">
                <small class="text-muted text-uppercase fw-bold px-3 mb-2 d-block"
                    style="font-size: 0.65rem; letter-spacing: 1px;">Cocina e Inventario</small>
                <a class="nav-link d-flex align-items-center gap-2 <?php echo in_array($page, ['categorias', 'ingredientes', 'menu']) ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#cocina-submenu" role="button">
                    <i class="bi bi-egg-fried fs-5"></i>
                    <span class="flex-grow-1">Menú y Recetas</span>
                    <i class="bi bi-chevron-right transition-rotate"></i>
                </a>
                <div class="collapse <?php echo in_array($page, ['categorias', 'ingredientes', 'menu']) ? 'show' : ''; ?>"
                    id="cocina-submenu">
                    <div class="d-flex flex-column gap-1 ps-4 mt-1">
                        <a href="?page=categorias"
                            class="nav-link <?php echo ($page == 'categorias') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-tags me-2"></i>Categorías
                        </a>
                        <a href="?page=ingredientes"
                            class="nav-link <?php echo ($page == 'ingredientes') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-droplet me-2"></i>Ingredientes
                        </a>
                        <a href="?page=menu" class="nav-link <?php echo ($page == 'menu') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-journal-text me-2"></i>Carta / Menú
                        </a>
                    </div>
                </div>
            </div>

            <!-- CATEGORÍA: LOGÍSTICA Y ALMACÉN -->
            <div class="nav-item w-100 mb-1">
                <small class="text-muted text-uppercase fw-bold px-3 mb-2 d-block"
                    style="font-size: 0.65rem; letter-spacing: 1px;">Logística y Almacén</small>
                <a class="nav-link d-flex align-items-center gap-2 <?php echo in_array($page, ['bien', 'equipo', 'material', 'productos']) ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#logistica-submenu" role="button">
                    <i class="bi bi-truck fs-5"></i>
                    <span class="flex-grow-1">Suministros y Activos</span>
                    <i class="bi bi-chevron-right transition-rotate"></i>
                </a>
                <div class="collapse <?php echo in_array($page, ['productos', 'proveedores']) ? 'show' : ''; ?>"
                    id="logistica-submenu">
                    <div class="d-flex flex-column gap-1 ps-4 mt-1">
                        <a href="?page=productos"
                            class="nav-link <?php echo ($page == 'productos') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-box-seam me-2"></i>Otros Productos
                        </a>
                    </div>
                    <div class="d-flex flex-column gap-1 ps-4 mt-1">
                        <a href="?page=proveedores"
                            class="nav-link <?php echo ($page == 'proveedores') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-people me-2"></i>Proveedores
                        </a>
                    </div>
                </div>
            </div>

            <!-- CATEGORÍA: MARKETING Y CONTENIDO -->
            <div class="nav-item w-100 mb-1">
                <small class="text-muted text-uppercase fw-bold px-3 mb-2 d-block"
                    style="font-size: 0.65rem; letter-spacing: 1px;">Marketing y Contenido</small>
                <a class="nav-link d-flex align-items-center gap-2 <?php echo in_array($page, ['multimedia', 'noticias-admin']) ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#marketing-submenu" role="button">
                    <i class="bi bi-megaphone fs-5"></i>
                    <span class="flex-grow-1">Difusión Digital</span>
                    <i class="bi bi-chevron-right transition-rotate"></i>
                </a>
                <div class="collapse <?php echo in_array($page, ['multimedia', 'noticias-admin']) ? 'show' : ''; ?>"
                    id="marketing-submenu">
                    <div class="d-flex flex-column gap-1 ps-4 mt-1">
                        <a href="?page=multimedia"
                            class="nav-link <?php echo ($page == 'multimedia') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-images me-2"></i>Galería Multimedia
                        </a>
                        <a href="?page=noticias-admin"
                            class="nav-link <?php echo ($page == 'noticias-admin') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-newspaper me-2"></i>Blog / Noticias
                        </a>
                    </div>
                </div>
            </div>

            <!-- CATEGORÍA: GESTIÓN DEL PERSONAL -->
            <div class="nav-item w-100 mb-1">
                <small class="text-muted text-uppercase fw-bold px-3 mb-2 d-block"
                    style="font-size: 0.65rem; letter-spacing: 1px;">Gestión del Personal</small>
                <a class="nav-link d-flex align-items-center gap-2 <?php echo in_array($page, ['asistencia']) ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#personal-submenu" role="button">
                    <i class="bi bi-people fs-5"></i>
                    <span class="flex-grow-1">Equipo y Horario</span>
                    <i class="bi bi-chevron-right transition-rotate"></i>
                </a>
                <div class="collapse <?php echo in_array($page, ['asistencia']) ? 'show' : ''; ?>"
                    id="personal-submenu">
                    <div class="d-flex flex-column gap-1 ps-4 mt-1">
                        <a href="?page=asistencia"
                            class="nav-link <?php echo ($page == 'asistencia') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-check2-square me-2"></i>Asistencia
                        </a>
                    </div>
                </div>
            </div>

            <!-- CATEGORÍA: INTELIGENCIA DE NEGOCIO -->
            <div class="nav-item w-100 mb-1">
                <small class="text-muted text-uppercase fw-bold px-3 mb-2 d-block"
                    style="font-size: 0.65rem; letter-spacing: 1px;">Inteligencia de Negocio</small>
                <a class="nav-link d-flex align-items-center gap-2 <?php echo in_array($page, ['reportes', 'estadistica']) ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#reportes-submenu" role="button">
                    <i class="bi bi-graph-up-arrow fs-5"></i>
                    <span class="flex-grow-1">Análisis y PDF</span>
                    <i class="bi bi-chevron-right transition-rotate"></i>
                </a>
                <div class="collapse <?php echo in_array($page, ['reportes', 'estadistica']) ? 'show' : ''; ?>"
                    id="reportes-submenu">
                    <div class="d-flex flex-column gap-1 ps-4 mt-1">
                        <a href="?page=estadistica"
                            class="nav-link <?php echo ($page == 'estadistica') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-pie-chart me-2"></i>Estadísticas Reales
                        </a>
                        <a href="?page=reportes" class="nav-link <?php echo ($page == 'reportes') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Centro de Reportes
                        </a>
                    </div>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <!-- CATEGORÍA: SEGURIDAD Y AUDITORÍA -->
            <div class="nav-item w-100 mb-1">
                <small class="text-muted text-uppercase fw-bold px-3 mb-2 d-block"
                    style="font-size: 0.65rem; letter-spacing: 1px;">Seguridad y Auditoría</small>
                <a class="nav-link d-flex align-items-center gap-2 <?php echo in_array($page, ['bitacora', 'papelera', 'usuario']) ? '' : 'collapsed'; ?>"
                    data-bs-toggle="collapse" href="#seguridad-submenu" role="button">
                    <i class="bi bi-shield-lock fs-5"></i>
                    <span class="flex-grow-1">Control de Acceso</span>
                    <i class="bi bi-chevron-right transition-rotate"></i>
                </a>
                <div class="collapse <?php echo in_array($page, ['bitacora', 'papelera', 'usuario']) ? 'show' : ''; ?>"
                    id="seguridad-submenu">
                    <div class="d-flex flex-column gap-1 ps-4 mt-1">
                        <a href="?page=bitacora" class="nav-link <?php echo ($page == 'bitacora') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-shield-shaded me-2"></i>Bitácora de Acciones
                        </a>
                        <a href="?page=papelera"
                            class="nav-link <?php echo ($page == 'papelera') ? 'active text-danger' : 'text-danger'; ?> py-1">
                            <i class="bi bi-trash3 me-2"></i>Papelera (Recycle)
                        </a>
                        <a href="?page=usuario" class="nav-link <?php echo ($page == 'usuario') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-person-vcard me-2"></i>Gestión de Usuarios
                        </a>
                        <a href="?page=rol" class="nav-link <?php echo ($page == 'rol') ? 'active' : ''; ?> py-1">
                            <i class="bi bi-person-vcard me-2"></i>Gestión de Roles
                        </a>
                    </div>
                </div>
            </div>

            <!-- Soporte -->
            <a href="?page=ayuda"
                class="nav-link <?php echo ($page == 'ayuda') ? 'active' : ''; ?> d-flex align-items-center gap-2">
                <i class="bi bi-question-circle fs-5"></i>
                <span>Centro de Ayuda</span>
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
<main class="main-content flex-grow-1 <?php echo (isset($hideSidebar) && $hideSidebar) ? 'ms-0 w-100' : ''; ?>"
    id="main-content">
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
                                    <?php echo ($datos['nombres'] ?? '') . ' ' . ($datos['apellidos'] ?? ''); ?>
                                </div>
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