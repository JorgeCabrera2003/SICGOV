<!-- ==========================================
    MÓDULO DE TIPOS DE PERMISOS - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-box me-2 text-warning"></i>
            Gestión de Tipos de Permisos
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de tipos de permisos">
            <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoTipo">
                <i class="fas fa-plus me-2"></i>Nuevo Tipo de Permiso
            </button>
        </div>
    </header>

    <!-- Tabla de productos (section semántica) -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <?php
            include_once 'partials/_tabla_tipo_permiso.php';
            ?>
        </div>
    </section>
</main>

<!-- Modales (incluidos como partials) -->
<?php
include_once 'partials/_modal_tipo_permiso_form.php';
?>

<!-- Recursos específicos de la página -->
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/TipoPermisoController.js" defer></script>