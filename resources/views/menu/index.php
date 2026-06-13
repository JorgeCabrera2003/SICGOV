<!-- ==========================================
    MÓDULO DE MENÚ - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h1 class="h3 mb-0">
            <i class="fas fa-utensils me-2 text-primary"></i>
            Gestión de Productos del Menú
        </h1>
        <div class="btn-group shadow-sm" role="group" aria-label="Acciones de menú">
            <a href="<?= BASE_URL ?>?page=nuestro-menu" target="_blank" class="btn btn-outline-primary fw-bold shadow-sm d-flex align-items-center">
                <i class="fas fa-external-link-alt me-2"></i>Ver Menú Público
            </a>
            <?php if (isset($permisos['producto']['registrar']) && $permisos['producto']['registrar'] == 1) { ?>
            <button class="btn btn-primary text-white fw-bold shadow-sm" id="btnNuevoMenu">
                <i class="fas fa-plus me-2"></i>Nuevo Producto al Menú
            </button>
            <?php } ?>
        </div>
    </header>

    <!-- Filtros visuales (opcional peros útil) -->
    <div class="d-flex gap-2 mb-4 overflow-auto pb-2" id="filtrosCategorias">
        <button class="btn btn-primary text-white rounded-pill px-4 btn-filtro fw-bold shadow-sm active" data-categoria="todas">Todas</button>
        <?php foreach ($categorias as $cat): ?>
            <button class="btn btn-primary text-white rounded-pill px-4 btn-filtro fw-bold shadow-sm" data-categoria="<?= $cat['id_categoria'] ?>">
                <?= htmlspecialchars($cat['nombre_categoria']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Galería de menú (section semántica) -->
    <section>
        <div id="loadingGallery" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando menú...</p>
        </div>
        
        <div id="galleryContainer" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4" style="display: none;">
            <!-- Tarjetas renderizadas por JS -->
        </div>

        <div id="emptyGallery" class="text-center py-5 text-muted" style="display: none;">
            <i class="fas fa-box-open fs-1 mb-3"></i>
            <h5>No hay productos en esta categoría</h5>
            <p>Intenta cambiar el filtro o agregar un nuevo producto.</p>
        </div>
    </section>
</main>

<!-- Scripts con los datos cargados desde PHP -->
<script>
    const categoriasDB = <?= json_encode($categorias) ?>;
    const insumosDB = <?= json_encode($insumos) ?>;
    const unidadesDB = <?= json_encode($unidades) ?>;
    const permisosDB = <?= isset($permisos) ? json_encode($permisos) : 'null' ?>;
</script>

<!-- Modales (incluidos como partials) -->
<?php 
    include 'partials/_modal_menu.php'; 
    include __DIR__ . '/../partials/_media_picker.php'; 
?>

<!-- Recursos específicos de la página -->
<script src="<?= BASE_URL ?>/assets/js/media-picker.js" defer></script>
