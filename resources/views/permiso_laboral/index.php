<!-- ==========================================
    MÓDULO DE PERMISOS LABORALES - GOOD VIBES
========================================== -->

<main class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-calendar-minus me-2 text-warning"></i>
            Gestión de Permisos Laborales
        </h1>
        <div class="btn-group" role="group">
            <?php if (($permisosPermisoLaboral['permiso_laboral']['registrar'] ?? 0) == 1): ?>
                <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoPermiso">
                    <i class="fas fa-plus me-2"></i>Solicitar Permiso
                </button>
            <?php endif; ?>
            <?php if (($permisosTipoPermiso['tipo_permiso']['ver'] ?? 0) == 1): ?>
                <button class="btn btn-outline-warning" id="btn-ModalTipos">
                    <i class="fas fa-tags me-2"></i>Tipo de Permiso
                </button>
            <?php endif; ?>
        </div>
    </header>

    <section class="card shadow-sm border-0">
        <div class="card-body">
            <?php include_once 'partials/_tabla_permiso_laboral.php'; ?>
        </div>
    </section>
</main>

<?php
include_once 'partials/_modal_permiso_laboral.php';
if (($permisosTipoPermiso['tipo_permiso']['ver'] ?? 0) == 1) {
    include_once 'partials/_modal_tipo_permiso.php';
    include_once $basePath . '/resources/views/tipo_permiso/partials/_modal_tipo_permiso_form.php';
}
?>

<script>
window.PERMISO_SESSION_USER = <?= json_encode($_SESSION['user'] ?? []) ?>;
</script>
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/PermisoLaboralController.js" defer></script>
<?php if (($permisosTipoPermiso['tipo_permiso']['ver'] ?? 0) == 1): ?>
    <script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/TipoPermisoController.js" defer></script>
<?php endif; ?>
