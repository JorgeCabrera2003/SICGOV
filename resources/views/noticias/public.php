<!-- ==========================================
     PORTAL DE NOTICIAS - GOOD VIBES
     Estilo Editorial Premium
     ========================================== -->

<?php
// Lógica de separación de noticias
$topnoticias = array_slice($noticias, 0, 5);
$otrasnoticias = array_slice($noticias, 0); // Todas para la grilla con filtrado aplicado

// Configuración de visualización
$badgeArray = [
    'INFO'   => ['bg' => 'bg-info', 'text' => 'Informativo'],
    'ALERTA' => ['bg' => 'bg-warning text-dark', 'text' => 'Alerta'],
    'EXITO'  => ['bg' => 'bg-success', 'text' => 'Logro']
];
?>

<!-- 1. Top Bar (Fecha y Redes) -->
<div class="news-top-bar py-2 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="date-now">
            <i class="far fa-calendar-alt me-2 text-primary"></i>
            <?php 
                $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
                $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                echo $dias[date('w')] . ", " . date('d') . " de " . $meses[date('n')] . " de " . date('Y');
            ?>
        </div>
        <div class="social-links d-none d-md-block">
            <a href="#" class="btn-social me-3"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="btn-social me-3"><i class="fab fa-instagram"></i></a>
            <a href="#" class="btn-social me-3"><i class="fab fa-twitter"></i></a>
            <a href="#" class="btn-social"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
</div>

<!-- 2. Logo Section -->
<div class="news-logo-section text-center py-4 bg-white border-bottom">
    <div class="container">
        <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Good Vibes" style="height: 80px;" class="mb-2">
        <p class="text-muted text-uppercase fw-bold letter-spacing-2" style="letter-spacing: 5px;">Portal de Noticias</p>
    </div>
</div>

<!-- 3. Ticker -->
<div class="news-ticker border-bottom">
    <div class="container d-flex align-items-center">
        <span class="ticker-label text-nowrap me-3">ÚLTIMAS NOTICIAS</span>
        <div class="ticker-content-wrapper">
            <div class="ticker-content">
                <?php foreach($topnoticias as $t): ?>
                    <span class="ticker-item fw-semibold">• <?= htmlspecialchars($t['titulo']) ?></span>
                <?php endforeach; ?>
                <!-- Duplicar para loop infinito fluido -->
                <?php foreach($topnoticias as $t): ?>
                    <span class="ticker-item fw-semibold">• <?= htmlspecialchars($t['titulo']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<main class="bg-light pb-5">
    
    <!-- 4. Featured Carousel (Top 5) -->
    <?php if (!empty($topnoticias)): ?>
    <section class="news-carousel mb-5 shadow-lg">
        <div id="featuredCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach($topnoticias as $idx => $t): ?>
                    <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx==0?'active':'' ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach($topnoticias as $idx => $t): 
                    $img = !empty($t['imagen_principal']) ? BASE_URL . $t['imagen_principal'] : BASE_URL . '/assets/img/default-product.png';
                ?>
                <div class="carousel-item <?= $idx==0?'active':'' ?>">
                    <img src="<?= $img ?>" class="d-block w-100" alt="<?= htmlspecialchars($t['titulo']) ?>">
                    <div class="news-carousel-caption">
                        <div class="container">
                            <span class="badge <?= $badgeArray[$t['tipo']]['bg'] ?? 'bg-primary' ?> mb-3 p-2 px-3 fw-bold">
                                <?= $badgeArray[$t['tipo']]['text'] ?? 'Nota' ?>
                            </span>
                            <h2 class="text-white mb-3 fw-bold"><?= htmlspecialchars($t['titulo']) ?></h2>
                            <div class="carousel-description mb-4">
                                <p class="lead text-white-50">
                                    <?= !empty($t['subtitulo']) ? htmlspecialchars($t['subtitulo']) : substr(strip_tags($t['contenido']), 0, 160) . '...' ?>
                                </p>
                            </div>
                            <a href="<?= BASE_URL ?>?page=noticias-detalle&id=<?= $t['id_noticia'] ?>" class="btn btn-primary btn-lg fw-bold shadow">
                                LEER NOTICIA <i class="fas fa-chevron-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>
    <?php endif; ?>

    <div class="container mt-4">
        
        <!-- 5. Filtros -->
        <section class="news-filters-bar mb-5">
            <form action="<?= BASE_URL ?>" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="noticias">
                
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase opacity-75">Categoría</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todas las categorías</option>
                        <option value="INFO" <?= ($_GET['tipo']??'')=='INFO'?'selected':'' ?>>Informativo</option>
                        <option value="EXITO" <?= ($_GET['tipo']??'')=='EXITO'?'selected':'' ?>>Logros/Éxitos</option>
                        <option value="ALERTA" <?= ($_GET['tipo']??'')=='ALERTA'?'selected':'' ?>>Importante/Alerta</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase opacity-75">Publicador</label>
                    <select name="autor" class="form-select">
                        <option value="">Todos los autores</option>
                        <?php foreach($autores as $autor): ?>
                            <option value="<?= $autor ?>" <?= ($_GET['autor']??'')==$autor?'selected':'' ?>><?= htmlspecialchars($autor) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-uppercase opacity-75">Mes</label>
                    <select name="mes" class="form-select">
                        <option value="">Cualquier mes</option>
                        <?php 
                        $mesesNombres = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                        foreach($mesesNombres as $m => $nombre): 
                            $val = $m + 1;
                        ?>
                            <option value="<?= $val ?>" <?= ($_GET['mes']??'')==$val?'selected':'' ?>><?= $nombre ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-uppercase opacity-75">Año</label>
                    <select name="anio" class="form-select">
                        <option value="">Cualquier año</option>
                        <option value="2024" <?= ($_GET['anio']??'')=='2024'?'selected':'' ?>>2024</option>
                        <option value="2025" <?= ($_GET['anio']??'')=='2025'?'selected':'' ?>>2025</option>
                        <option value="2026" <?= ($_GET['anio']??'')=='2026'?'selected':'' ?>>2026</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="fas fa-filter me-2"></i> FILTRAR
                    </button>
                </div>
            </form>
        </section>

        <!-- 6. Noticias Grid Section -->
        <h3 class="news-section-title">Todas las Noticias</h3>
        <div class="row g-4 mt-2">
            <?php if (!empty($otrasnoticias)): ?>
                <?php foreach ($otrasnoticias as $noti): 
                    $img = !empty($noti['imagen_principal']) ? BASE_URL . $noti['imagen_principal'] : BASE_URL . '/assets/img/default-product.png';
                    $fecha = new DateTime($noti['fecha_publicacion']);
                    $badgeClass = $badgeArray[$noti['tipo']]['bg'] ?? 'bg-primary';
                    $badgeText  = $badgeArray[$noti['tipo']]['text'] ?? 'Nota';
                ?>
                
                <div class="col-md-6 col-lg-4">
                    <article class="editorial-card h-100 bg-white">
                        <div class="editorial-img-container">
                            <span class="badge <?= $badgeClass ?> editorial-badge shadow"><?= $badgeText ?></span>
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($noti['titulo']) ?>">
                            <a href="<?= BASE_URL ?>?page=noticias-detalle&id=<?= $noti['id_noticia'] ?>" class="stretched-link"></a>
                        </div>
                        <div class="p-3">
                            <div class="editorial-meta">
                                <span class="me-3"><i class="far fa-user me-1"></i> <?= htmlspecialchars($noti['autor']) ?></span>
                                <span><i class="far fa-calendar me-1"></i> <?= $fecha->format('d/m/Y') ?></span>
                            </div>
                            <h4 class="editorial-title">
                                <a href="<?= BASE_URL ?>?page=noticias-detalle&id=<?= $noti['id_noticia'] ?>" class="text-dark text-decoration-none">
                                    <?= htmlspecialchars($noti['titulo']) ?>
                                </a>
                            </h4>
                            <p class="text-muted mt-2 mb-0" style="font-size: 0.95rem;">
                                <?= !empty($noti['subtitulo']) ? htmlspecialchars($noti['subtitulo']) : substr(strip_tags($noti['contenido']), 0, 110) . '...' ?>
                            </p>
                        </div>
                    </article>
                </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search text-muted mb-3" style="font-size: 3rem;"></i>
                    <h4 class="text-muted">No encontramos noticias con esos filtros.</h4>
                    <a href="<?= BASE_URL ?>?page=noticias" class="btn btn-outline-primary mt-3">Ver todas las noticias</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>
