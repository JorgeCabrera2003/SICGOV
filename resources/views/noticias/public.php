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

<!-- 1. Top Bar (Fecha, Redes y Login) -->
<div class="news-top-bar py-2 shadow-sm border-bottom">
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
                <a href="#" class="btn-social me-3" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/goodvibes_tapasbar/" target="_blank" class="btn-social me-3" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="btn-social" title="TikTok"><i class="fab fa-tiktok"></i></a>
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

<!-- 2. Logo Section -->
<div class="news-logo-section text-center py-4 bg-tarjetas border-bottom d-none">
    <div class="container">
        <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Good Vibes" style="height: 160px;" class="landing-logo py-2">
    </div>
</div>

<!-- 3. Ticker -->
<div class="news-ticker border-bottom bg-body-tertiary">
    <div class="container d-flex align-items-center py-1">
        <span class="ticker-label text-nowrap me-3 small fw-bold">LO ÚLTIMO</span>
        <div class="ticker-content-wrapper small">
            <div class="ticker-content">
                <?php foreach($topnoticias as $t): ?>
                    <span class="ticker-item fw-semibold text-secondary">• <?= htmlspecialchars($t['titulo']) ?></span>
                <?php endforeach; ?>
                <?php foreach($topnoticias as $t): ?>
                    <span class="ticker-item fw-semibold text-secondary">• <?= htmlspecialchars($t['titulo']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- 4. Navegación (Landing Page Link) -->
<nav class="sticky-top bg-tarjetas border-bottom shadow-sm py-2" id="landingNav" style="z-index: 1020;">
    <div class="container d-flex justify-content-center">
        <ul class="nav nav-pills nav-fill gap-2" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="<?= BASE_URL ?>" class="nav-link fw-bold text-uppercase px-4">
                    <i class="fas fa-home me-2"></i>Inicio
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-uppercase px-4" id="pills-news-tab" data-bs-toggle="pill" data-bs-target="#pills-news" type="button" role="tab">
                    <i class="fas fa-newspaper me-2"></i>Noticias
                </button>
            </li>
        </ul>
    </div>
</nav>

<main class="bg-body pb-5">
    <div class="tab-content" id="pills-tabContent">
        
        <!-- PESTAÑA 1: EXPLORA (NOTICIAS) -->
        <div class="tab-pane fade show active" id="pills-news" role="tabpanel">
            
            <!-- Featured Carousel (Solo en noticias) -->
            <?php if (!empty($topnoticias)): ?>
            <section class="news-carousel mb-5 shadow-lg">
                <div id="featuredCarousel" class="carousel slide" data-bs-ride="carousel">
                    <!-- ... (contenido del carousel igual) -->
            <div class="carousel-indicators">
                <?php foreach($topnoticias as $idx => $t): ?>
                    <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx==0?'active':'' ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach($topnoticias as $idx => $t): 
                    $img = !empty($t['imagen_principal']) ? BASE_URL . $t['imagen_principal'] : BASE_URL . '/assets/img/noticia-default.png';
                ?>
                <div class="carousel-item <?= $idx==0?'active':'' ?>">
                    <img src="<?= $img ?>" class="d-block w-100" alt="<?= htmlspecialchars($t['titulo']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/img/logo.png'; this.style.objectFit='contain';">
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
                            <a href="<?= BASE_URL ?>?page=Noticia&type=detalle&id=<?= $t['id_noticia'] ?>" class="btn btn-primary btn-lg fw-bold shadow">
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
        
        <!-- 5. Filtros con Accordion -->
        <section class="news-filters-accordion mb-4">
            <div class="accordion" id="accordionFiltros">
                <div class="accordion-item border-0 bg-transparent">
                    <div class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed bg-tarjetas shadow-sm rounded-3 border fw-bold text-uppercase py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltros" aria-expanded="false" aria-controls="collapseFiltros">
                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                <span><i class="fas fa-sliders-h me-2 text-primary"></i> Panel de Filtros</span>
                                <?php if(isset($_GET['tipo']) || isset($_GET['autor']) || isset($_GET['mes']) || isset($_GET['anio'])): ?>
                                    <span class="badge bg-primary rounded-pill small ms-2">Filtros Activos</span>
                                <?php endif; ?>
                            </div>
                        </button>
                    </div>
                    <div id="collapseFiltros" class="accordion-collapse collapse <?= (isset($_GET['tipo']) || isset($_GET['autor']) || isset($_GET['mes']) || isset($_GET['anio'])) ? 'show' : '' ?>" aria-labelledby="headingOne" data-bs-parent="#accordionFiltros">
                        <div class="accordion-body bg-tarjetas border rounded-3 mt-2 shadow-sm p-4">
                            <form action="<?= BASE_URL ?>" method="GET" class="row g-3 align-items-end">
                                <input type="hidden" name="page" value="noticias">
                                
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-uppercase">Categoría</label>
                                    <select name="tipo" class="form-select">
                                        <option value="">Todas las categorías</option>
                                        <option value="INFO" <?= ($_GET['tipo']??'')=='INFO'?'selected':'' ?>>Informativo</option>
                                        <option value="EXITO" <?= ($_GET['tipo']??'')=='EXITO'?'selected':'' ?>>Logros/Éxitos</option>
                                        <option value="ALERTA" <?= ($_GET['tipo']??'')=='ALERTA'?'selected':'' ?>>Importante/Alerta</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-uppercase">Publicador</label>
                                    <select name="autor" class="form-select">
                                        <option value="">Todos los autores</option>
                                        <?php foreach($autores as $autor): ?>
                                            <option value="<?= $autor ?>" <?= ($_GET['autor']??'')==$autor?'selected':'' ?>><?= htmlspecialchars($autor) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-uppercase">Mes</label>
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
                                    <label class="form-label small fw-bold text-uppercase">Año</label>
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
                            <?php if(isset($_GET['tipo']) || isset($_GET['autor']) || isset($_GET['mes']) || isset($_GET['anio'])): ?>
                                <div class="mt-3 text-end">
                                    <a href="<?= BASE_URL ?>?page=Noticia&type=publico" class="text-decoration-none small text-danger fw-bold">
                                        <i class="fas fa-times-circle"></i> Limpiar Filtros
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Noticias Grid Section -->
        <h3 class="news-section-title">Todas las Noticias</h3>
        <div class="row g-4 mt-2">
            <?php if (!empty($otrasnoticias)): ?>
                <?php foreach ($otrasnoticias as $noti): 
                    $img = !empty($noti['imagen_principal']) ? BASE_URL . $noti['imagen_principal'] : BASE_URL . '/assets/img/noticia-default.png';
                    $fecha = new DateTime($noti['fecha_publicacion']);
                    $badgeClass = $badgeArray[$noti['tipo']]['bg'] ?? 'bg-primary';
                    $badgeText  = $badgeArray[$noti['tipo']]['text'] ?? 'Nota';
                ?>
                
                <div class="col-md-6 col-lg-4">
                    <article class="editorial-card h-100 bg-tarjetas shadow-sm border">
                        <div class="editorial-img-container">
                            <span class="badge <?= $badgeClass ?> editorial-badge shadow"><?= $badgeText ?></span>
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($noti['titulo']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/img/logo.png'; this.style.objectFit='contain'; this.style.padding='20px';">
                            <a href="<?= BASE_URL ?>?page=Noticia&type=detalle&id=<?= $noti['id_noticia'] ?>" class="stretched-link"></a>
                        </div>
                        <div class="p-3">
                            <div class="editorial-meta">
                                <span class="me-3"><i class="far fa-user me-1"></i> <?= htmlspecialchars($noti['autor']) ?></span>
                                <span><i class="far fa-calendar me-1"></i> <?= $fecha->format('d/m/Y') ?></span>
                            </div>
                            <h4 class="editorial-title">
                                <a href="<?= BASE_URL ?>?page=Noticia&type=detalle&id=<?= $noti['id_noticia'] ?>" class="text-body text-decoration-none">
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
                    <a href="<?= BASE_URL ?>?page=Noticia&type=publico" class="btn btn-outline-primary mt-3">Ver todas las noticias</a>
                </div>
            <?php endif; ?>
        </div>

            </div> <!-- Fin container noticias -->
        </div> <!-- Fin tab-pane noticias -->
    </div> <!-- Fin tab-content -->
</main>
