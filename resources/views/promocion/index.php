<!-- ==========================================
    MÓDULO DE PROMOCIONES - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-tags me-2 text-warning"></i>
            Gestión de Promoción
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de promociones">
            <?php if (($permisosPromocion['promocion']['registrar'] ?? 0) == 1): ?>
                <button class="btn btn-warning text-dark fw-semibold" id="btnNuevaPromocion">
                    <i class="fas fa-plus me-2"></i>Nueva Promoción
                </button>
            <?php endif; ?>
        </div>
    </header>

    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaPromocion"
                    data-puede-modificar="<?= (($permisosPromocion['promocion']['modificar'] ?? 0) == 1) ? '1' : '0' ?>"
                    data-puede-eliminar="<?= (($permisosPromocion['promocion']['eliminar'] ?? 0) == 1) ? '1' : '0' ?>"
                    style="width:100%">
                    <thead class="table-light">
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Tipo de Descuento</th>
                                    <th scope="col">Valor</th>
                                    <th scope="col">Inicio</th>
                                    <th scope="col">Fin</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col"></th>
                                </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables carga los datos aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<?php
include_once 'partials/_modal_promocion.php';
include_once __DIR__ . '/../partials/_media_picker.php';
?>

<script>
    const productosDB = <?= json_encode($productos ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="<?= BASE_URL ?>assets/js/media-picker.js" defer></script>
<script type="module" src="<?= BASE_URL ?>assets/js/Controllers/PromocionController.js" defer></script>
