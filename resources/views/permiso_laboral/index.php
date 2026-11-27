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

    <div class="d-flex align-items-center gap-2 mb-3">
        <div class="btn-group" role="group" aria-label="Filtrar permisos laborales">
            <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnMisPermisos">
                <i class="fas fa-user me-2"></i>Mis Permisos
            </button>
            <?php if (($permisosPermisoLaboral['permiso_laboral']['ver_todos'] ?? 0) == 1): ?>
                <button type="button" class="btn btn-outline-warning" id="btnTodosPermisos">
                    <i class="fas fa-users me-2"></i>Todos los Permisos
                </button>
            <?php endif; ?>
        </div>
    </div>

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
<?php $usuarioSesionPermiso = $_SESSION['user'] ?? []; ?>
window.PERMISO_SESSION_USER = <?= json_encode([
    'cedula' => $usuarioSesionPermiso['cedula'] ?? '',
    'nombre' => $usuarioSesionPermiso['nombre'] ?? '',
    'apellido' => $usuarioSesionPermiso['apellido'] ?? '',
    'username' => $usuarioSesionPermiso['username'] ?? ''
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
window.PERMISO_PUEDE_VER_TODOS = <?= json_encode($puedeVerTodosPermisos) ?>;
</script>
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/PermisoLaboralController.js" defer></script>
<?php if (($permisosTipoPermiso['tipo_permiso']['ver'] ?? 0) == 1): ?>
    <script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/TipoPermisoController.js" defer></script>
<?php endif; ?>
