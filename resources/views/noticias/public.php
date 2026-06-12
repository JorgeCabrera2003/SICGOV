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

<!-- 4. Navegación por Pestañas (Landing Page) -->
<nav class="sticky-top bg-tarjetas border-bottom shadow-sm py-2" id="landingNav" style="z-index: 1020;">
    <div class="container d-flex justify-content-center">
        <ul class="nav nav-pills nav-fill gap-2" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-uppercase px-4" id="pills-news-tab" data-bs-toggle="pill" data-bs-target="#pills-news" type="button" role="tab">
                    <i class="fas fa-newspaper me-2"></i>Noticias
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-uppercase px-4" id="pills-about-tab" data-bs-toggle="pill" data-bs-target="#pills-about" type="button" role="tab">
                    <i class="fas fa-heart me-2"></i>Nosotros
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-uppercase px-4" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab">
                    <i class="fas fa-map-marker-alt me-2"></i>Puntos de Venta
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

        <!-- PESTAÑA 2: NOSOTROS (MISIÓN Y VISIÓN) -->
        <div class="tab-pane fade" id="pills-about" role="tabpanel">
            <div class="container py-5">
                <div class="row align-items-center mb-5 animate__animated animate__fadeIn">
                    <div class="col-lg-6">
                        <h2 class="display-5 fw-bold text-primary mb-4">Nuestra Esencia</h2>
                        <p class="lead text-body">
                            En <strong>Good Vibes Tapas & Bar</strong>, fusionamos la pasión por la gastronomía con un ambiente vibrante. 
                            Somos más que un local de comida; somos el punto de encuentro donde los sabores tradicionales, mexicanos y maracuchos se mezclan con la mejor música y energía de Barquisimeto.
                        </p>
                    </div>
                    <div class="col-lg-6">
                        <div class="p-4 bg-body-tertiary rounded shadow-sm border-start border-4 border-primary">
                            <h3 class="h4 fw-bold mb-3"><i class="fas fa-bullseye text-primary me-2"></i> Nuestra Misión</h3>
                            <p class="mb-0 text-body">
                                Brindar una experiencia culinaria diversa y vibrante, ofreciendo sabores auténticos en un ambiente 
                                excepcional. Nos dedicamos a satisfacer los paladares más exigentes con nuestro menú ejecutivo, 
                                infantil y especialidades de la casa, garantizando calidad tanto en nuestro establecimiento como en cada entrega a domicilio.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-md-6 animate__animated animate__fadeInUp">
                        <div class="card h-100 border-0 shadow-sm bg-tarjetas p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="fas fa-eye text-primary fs-3"></i>
                                </div>
                                <h3 class="h4 fw-bold mb-0">Nuestra Visión</h3>
                            </div>
                            <p class="text-muted">
                                Consolidarnos como el referente gastronómico y de entretenimiento número uno en el este de Barquisimeto, 
                                siendo reconocidos por nuestra innovación constante, talento en vivo los fines de semana y por ser el 
                                lugar preferido para celebraciones y reuniones inolvidables.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                        <div class="card h-100 border-0 shadow-sm bg-tarjetas p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="fas fa-star text-success fs-3"></i>
                                </div>
                                <h3 class="h4 fw-bold mb-0">Lo que nos define</h3>
                            </div>
                            <ul class="list-unstyled text-muted">
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Variedad única: Comida Mexicana, Maracucha y Tradicional.</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Eventos: Talento en vivo todos los viernes.</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Comodidad: Salón interno, Barra y Terraza.</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i> Celebraciones: Reservaciones para cumpleaños con torta de regalo.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 3: CONTACTO Y MAPA -->
        <div class="tab-pane fade" id="pills-contact" role="tabpanel">
            <div class="container py-5">
                <div class="row g-5">
                    <div class="col-lg-5">
                        <h2 class="h3 fw-bold mb-4">¿Cómo encontrarnos?</h2>
                        
                        <div class="mb-4 d-flex align-items-start">
                            <div class="icon-circle bg-primary me-3 mt-1">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Dirección Física</h5>
                                <p class="text-muted">Av. Los Leones, Centro Empresarial Barquisimeto, diagonal al C.C. París. Sector Este, Barquisimeto, Estado Lara.</p>
                            </div>
                        </div>

                        <div class="mb-4 d-flex align-items-start">
                            <div class="icon-circle bg-success text-white me-3 mt-1">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Horario de Atención</h5>
                                <p class="text-muted mb-0">Lunes a Sábado</p>
                                <p class="text-muted small">09:00 AM - 05:00 PM / 03:00 PM - 11:00 PM</p>
                            </div>
                        </div>

                        <div class="mb-4 d-flex align-items-start">
                            <div class="icon-circle bg-info text-white me-3 mt-1">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Reservaciones y Delivery</h5>
                                <p class="text-muted mb-0">¡Escríbenos por WhatsApp!</p>
                                <a href="https://wa.me/584126159308" target="_blank" class="btn btn-sm btn-outline-success mt-2 fw-bold rounded-pill px-3">
                                    Enviar Mensaje
                                </a>
                            </div>
                        </div>

                        <div class="p-4 bg-body-tertiary rounded-4 border dashed-border text-center">
                            <h5 class="fw-bold mb-3">Síguenos en Redes</h5>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="https://www.instagram.com/goodvibes_tapasbar/" target="_blank" class="btn btn-primary icon-circle shadow-sm">
                                    <i class="fab fa-instagram fs-4"></i>
                                </a>
                                <a href="#" class="btn btn-dark icon-circle shadow-sm">
                                    <i class="fab fa-tiktok fs-4"></i>
                                </a>
                                <a href="#" class="btn btn-info text-white icon-circle shadow-sm">
                                    <i class="fab fa-facebook-f fs-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="map-container shadow-lg rounded-4 overflow-hidden border" style="height: 450px;">
                            <!-- Iframe de Google Maps centrado en la zona indicada -->
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.312345!2d-69.284567!3d10.065432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDAzJzU1LjYiTiA2OcKwMTcnMDQuNCJX!5e0!3m2!1ses!2sve!4v1712950000000!5m2!1ses!2sve" 
                                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- Fin tab-content -->
</main>
