<!-- ==========================================
     PORTAL PÚBLICO DEL MENÚ - GOOD VIBES
     Estilo Premium
     ========================================== -->

<?php
// Agrupar menús por categoría para facilitar la iteración
$menusPorCategoria = [];
foreach ($menus as $menuItem) {
    $menusPorCategoria[$menuItem['id_categoria']][] = $menuItem;
}
?>

<!-- 1. Top Bar (Fecha, Redes y Login) -->
<div class="news-top-bar py-2 shadow-sm border-bottom bg-body-tertiary">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="date-now small">
            <i class="far fa-calendar-alt me-2 text-primary"></i>
            <?php 
                $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
                $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                echo $dias[date('w')] . ", " . date('d') . " de " . $meses[date('n')] . " de " . date('Y');
            ?>
        </div>
        <div class="d-flex align-items-center">
            <div class="social-links d-none d-md-block me-3 border-end pe-3">
                <a href="#" class="btn-social me-3 text-secondary hover-primary" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/goodvibes_tapasbar/" target="_blank" class="btn-social me-3 text-secondary hover-primary" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="btn-social text-secondary hover-primary" title="TikTok"><i class="fab fa-tiktok"></i></a>
            </div>
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="<?= BASE_URL ?>?page=login" class="btn btn-sm btn-outline-primary fw-bold text-uppercase px-3 rounded-pill">
                    <i class="fas fa-lock me-1"></i> Acceso
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>?page=dashboard" class="btn btn-sm btn-primary fw-bold text-uppercase px-3 rounded-pill shadow-sm">
                    <i class="fas fa-tachometer-alt me-1"></i> Panel Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 2. Header Promocional -->
<div class="menu-hero text-center py-5 bg-dark text-white position-relative overflow-hidden" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?= BASE_URL ?>/assets/img/placeholder.png') center/cover;">
    <div class="container position-relative z-1 py-4">
        <h1 class="display-4 fw-bold mb-3 font-monospace text-primary">NUESTRO MENÚ</h1>
        <p class="lead mb-0 text-light">Descubre la fusión perfecta de sabores tradicionales y cocina moderna.</p>
    </div>
</div>

<!-- 3. Navegación por Categorías (Sticky) -->
<nav class="sticky-top bg-body border-bottom shadow-sm py-3" style="z-index: 1020;">
    <div class="container">
        <ul class="nav nav-pills nav-fill gap-2 flex-nowrap overflow-auto hide-scrollbar" id="menu-categories-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-uppercase px-4 rounded-pill text-nowrap" id="cat-todas-tab" data-bs-toggle="pill" data-bs-target="#cat-todas" type="button" role="tab">
                    <i class="fas fa-star me-2"></i>Todo
                </button>
            </li>
            <?php foreach ($categorias as $index => $cat): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-uppercase px-4 rounded-pill text-nowrap" id="cat-<?= $cat['id_categoria'] ?>-tab" data-bs-toggle="pill" data-bs-target="#cat-<?= $cat['id_categoria'] ?>" type="button" role="tab">
                    <?= htmlspecialchars($cat['nombre_categoria']) ?>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<main class="bg-body-tertiary py-5 min-vh-100">
    <div class="container">
        <div class="tab-content" id="menu-categories-tabContent">
            
            <!-- PESTAÑA: TODAS LAS CATEGORÍAS -->
            <div class="tab-pane fade show active" id="cat-todas" role="tabpanel">
                <?php foreach ($categorias as $cat): ?>
                    <?php if (isset($menusPorCategoria[$cat['id_categoria']]) && count($menusPorCategoria[$cat['id_categoria']]) > 0): ?>
                        <div class="category-section mb-5">
                            <h3 class="fw-bold mb-4 border-bottom border-primary border-3 pb-2 d-inline-block">
                                <?= htmlspecialchars($cat['nombre_categoria']) ?>
                            </h3>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                                <?php foreach ($menusPorCategoria[$cat['id_categoria']] as $p): 
                                    $imgUrl = ($p['imagen'] && $p['imagen'] !== 'default-product.png') ? BASE_URL . '/assets/img/productos/' . $p['imagen'] : BASE_URL . '/assets/img/placeholder.png';
                                ?>
                                <div class="col">
                                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden hover-lift transition-all">
                                        <div class="ratio ratio-4x3 overflow-hidden bg-body-tertiary">
                                            <img src="<?= $imgUrl ?>" class="card-img-top object-fit-cover transition-scale" alt="<?= htmlspecialchars($p['nombre_producto']) ?>" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/img/placeholder.png'">
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($p['nombre_producto']) ?></h5>
                                            <p class="text-primary fw-bold fs-5 mb-2">$<?= number_format($p['precio'], 2) ?></p>
                                            <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($p['descripcion'] ?: 'Sin descripción adicional') ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- PESTAÑAS INDIVIDUALES POR CATEGORÍA -->
            <?php foreach ($categorias as $cat): ?>
            <div class="tab-pane fade" id="cat-<?= $cat['id_categoria'] ?>" role="tabpanel">
                <div class="category-section mb-5">
                    <h3 class="fw-bold mb-4 border-bottom border-primary border-3 pb-2 d-inline-block">
                        <?= htmlspecialchars($cat['nombre_categoria']) ?>
                    </h3>
                    
                    <?php if (isset($menusPorCategoria[$cat['id_categoria']]) && count($menusPorCategoria[$cat['id_categoria']]) > 0): ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                        <?php foreach ($menusPorCategoria[$cat['id_categoria']] as $p): 
                            $imgUrl = ($p['imagen'] && $p['imagen'] !== 'default-product.png') ? BASE_URL . '/assets/img/productos/' . $p['imagen'] : BASE_URL . '/assets/img/placeholder.png';
                        ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden hover-lift transition-all">
                                <div class="ratio ratio-4x3 overflow-hidden bg-body-tertiary">
                                    <img src="<?= $imgUrl ?>" class="card-img-top object-fit-cover transition-scale" alt="<?= htmlspecialchars($p['nombre_producto']) ?>" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/img/placeholder.png'">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($p['nombre_producto']) ?></h5>
                                    <p class="text-primary fw-bold fs-5 mb-2">$<?= number_format($p['precio'], 2) ?></p>
                                    <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($p['descripcion'] ?: 'Sin descripción adicional') ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-utensils text-muted fs-1 mb-3"></i>
                        <h4 class="text-muted">No hay productos en esta categoría por el momento.</h4>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</main>

<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .transition-all {
        transition: all .3s ease;
    }
    .transition-scale {
        transition: transform .5s ease;
    }
    .hover-lift:hover .transition-scale {
        transform: scale(1.05);
    }
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
</style>
