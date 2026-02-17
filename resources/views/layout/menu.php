<!-- Sidebar -->
<aside id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <h1 class="logo">
            <span class="ms-2" id="logo-text">GOOD VIBES</span>
        </h1>
        <button id="collapse-btn" class="collapse-btn">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <div class="sidebar-content">
        <nav class="sidebar-menu">
            <ul>
                <li class="menu-item <?php echo ($page == "home") ? "active" : "" ?>" title="Dashboard">
                    <a href="<?php echo BASE_URL; ?>/?page=home">
                        <i class="fas fa-home"></i>
                        <span class="ms-2 me-2 menu-text">Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo ($page == "pedidos") ? "active" : "" ?>" title="Pedidos">
                    <a href="<?php echo BASE_URL; ?>/?page=pedidos">
                        <i class="fas fa-utensils"></i>
                        <span class="ms-2 me-2 menu-text">Pedidos / Mesas</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo ($page == "productos") ? "active" : "" ?>" title="Productos">
                    <a href="<?php echo BASE_URL; ?>/?page=productos">
                        <i class="fas fa-box"></i>
                        <span class="ms-2 me-2 menu-text">Productos</span>
                    </a>
                </li>
                
                <hr/>
                
                <li class="menu-item <?php echo ($page == "estadistica") ? "active" : "" ?>" title="Reportes Estadísticos">
                    <a href="<?php echo BASE_URL; ?>/?page=estadistica">
                        <i class="fa-solid fa-chart-line"></i>
                        <span class="ms-2 me-2 menu-text">Estadísticas</span>
                    </a>
                </li>
                
                <!-- Gestión de Equipos -->
                <li class="menu-item <?php echo in_array($page, ["bien", "equipo", "material"]) ? "active" : "" ?>" title="Gestión de Equipos">
                    <a class="nav-link collapsed" data-bs-target="#equipos-submenu" data-bs-toggle="collapse" href="#">
                        <i class="fas fa-laptop"></i>
                        <span class="ms-2 me-2 menu-text">Gestión de Equipos</span>
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                </li>
                <ul id="equipos-submenu" style="margin-left: 1em;" class="nav-content collapse<?php echo in_array($page, ["bien", "equipo", "material"]) ? " show" : "" ?>" data-bs-parent="#sidebar-nav">
                    <li class="menu-item <?php echo ($page == "bien") ? "active" : "" ?>" title="Bienes">
                        <a href="<?php echo BASE_URL; ?>/?page=bien">
                            <i class="fas fa-box"></i>
                            <span class="ms-2 me-2 menu-text">Bienes</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo ($page == "equipo") ? "active" : "" ?>" title="Equipos">
                        <a href="<?php echo BASE_URL; ?>/?page=equipo">
                            <i class="fa-solid fa-computer"></i>
                            <span class="ms-2 me-2 menu-text">Equipos</span>
                        </a>
                    </li>
                    <li class="menu-item <?php echo ($page == "material") ? "active" : "" ?>" title="Materiales">
                        <a href="<?php echo BASE_URL; ?>/?page=material">
                            <i class="fa-solid fa-toolbox"></i>
                            <span class="ms-2 me-2 menu-text">Materiales</span>
                        </a>
                    </li>
                </ul>
                
                <hr/>
                
                <li class="menu-item <?php echo ($page == "bitacora") ? "active" : "" ?>" title="Bitácora">
                    <a href="<?php echo BASE_URL; ?>/?page=bitacora">
                        <i class="fas fa-history"></i>
                        <span class="ms-2 me-2 menu-text">Bitácora</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo ($page == "ayuda") ? "active" : "" ?>" title="Ayuda">
                    <a href="<?php echo BASE_URL; ?>/?page=ayuda">
                        <i class="fas fa-question-circle"></i>
                        <span class="ms-2 me-2 menu-text">Ayuda</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="sidebar-footer">
        <ul>
            <li class="menu-item">
                <a href="<?php echo BASE_URL; ?>/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="ms-2 me-2 menu-text">Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </div>
</aside>

<!-- Main Content -->
<div class="main-content">
    <!-- Header/Top Navigation -->
    <header class="top-nav">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto d-lg-none">
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="fas fa-bars" style="pointer-events: none;"></i>
                    </button>
                </div>

                <div class="col d-none d-md-block">
                    <!-- Breadcrumbs -->
                </div>

                <div class="col-auto ms-auto">
                    <div class="top-nav-actions">
                        <!-- Notificaciones -->
                        <div class="action-item notification-dropdown">
                            <button class="notification-btn">
                                <i class="fas fa-bell"></i>
                                <span id="badge-notificacion" class="badge badge-notificacion position-absolute top-0 start-100 translate-middle bg-danger"></span>
                            </button>
                            <div class="dropdown-menu notification-menu">
                                <div class="dropdown-header d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-0">Notificaciones</h6>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <button class="close-dropdown">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="dropdown-body" id="notificaciones-container">
                                    <div class="text-center py-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-footer">
                                    <a href="<?php echo BASE_URL; ?>/?page=notificacion" class="btn btn-sm btn-primary w-100">Ver todas</a>
                                </div>
                            </div>
                        </div>

                        <!-- Theme Toggle -->
                        <div class="action-item">
                            <button id="theme-toggle" class="theme-toggle">
                                <i class="fas fa-moon dark-icon"></i>
                                <i class="fas fa-sun light-icon"></i>
                            </button>
                        </div>

                        <!-- User Dropdown -->
                        <div class="action-item user-dropdown">
                            <button class="user-dropdown-toggle">
                                <div class="avatar">
                                    <img src="<?php echo $datos['foto']; ?>" alt="User Avatar" />
                                </div>
                            </button>
                            <div class="dropdown-menu user-menu">
                                <div class="dropdown-header">
                                    <h6><?php echo $datos["nombres"] . " " . $datos["apellidos"]; ?></h6>
                                    <span><?php echo $datos["cedula"] . " / " . $datos["rol"]; ?></span>
                                </div>
                                <div class="dropdown-body">
                                    <ul>
                                        <li class="menu-item <?php echo ($page == "users-profile") ? "active" : "" ?>">
                                            <a href="<?php echo BASE_URL; ?>/?page=users-profile">
                                                <i class="menu-text-p fas fa-user"></i>
                                                <span class="menu-text-p">Perfil</span>
                                            </a>
                                        </li>
                                        <li class="menu-item">
                                            <a href="<?php echo BASE_URL; ?>/logout.php">
                                                <i class="menu-text-p fas fa-sign-out-alt"></i>
                                                <span class="menu-text-p">Cerrar Sesión</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content" style="flex: 1;">