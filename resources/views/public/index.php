<!-- Estructura de la Landing Page de Good Vibes -->

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-background">
        <img src="<?= BASE_URL ?>assets/img/landing/hero_pizza.png" alt="Delicious Pizza Background">
    </div>
    <div class="container hero-content">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="hero-title">Siente las <br><span>Buenas Vibras</span> en cada bocado</h1>
                <p class="hero-subtitle">Descubre una experiencia gastronómica única con ingredientes frescos y el mejor ambiente. Tu restaurante favorito, ahora más cerca de ti.</p>
                <div class="hero-cta">
                    <a href="<?= BASE_URL ?>?page=nuestro-menu" class="btn btn-cta btn-cta-primary">
                        <i class="fas fa-utensils"></i> Ver menú y realizar pedido
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">Por qué elegir <span>Good Vibes</span></h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Ingredientes Frescos</h3>
                    <p>Seleccionamos cuidadosamente cada ingrediente para garantizar el mejor sabor y calidad en todos nuestros platos.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-fire-burner"></i>
                    </div>
                    <h3>Sabor Auténtico</h3>
                    <p>Nuestras recetas tradicionales combinadas con un toque moderno crean una explosión de sabor inigualable.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <h3>Entrega Rápida</h3>
                    <p>Llevamos tus platos favoritos calientes y listos para disfrutar directamente a la puerta de tu casa.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Banner Intermedio -->
<section class="banner-section">
    <div class="banner-background">
        <img src="<?= BASE_URL ?>assets/img/landing/ingredients.png" alt="Fresh Ingredients">
    </div>
    <div class="container banner-content">
        <h2 class="banner-title">CALIDAD QUE SE SIENTE</h2>
        <a href="<?= BASE_URL ?>?page=nuestro-menu" class="btn btn-cta btn-cta-primary mt-3">Descubre Nuestros Platos</a>
    </div>
</section>

<!-- Explorar Vistas Públicas -->
<section class="views-section">
    <div class="container">
        <h2 class="section-title">Explora <span>Nuestros Servicios</span></h2>
        <div class="row g-4">
            <!-- Menú -->
            <div class="col-md-4">
                <a href="<?= BASE_URL ?>?page=nuestro-menu" class="view-card">
                    <div class="view-img" style="background-image: url('<?= BASE_URL ?>assets/img/landing/hero_pizza.png');"></div>
                    <div class="view-content">
                        <h3 class="view-title">Nuestro Menú</h3>
                        <p class="view-desc">Conoce nuestra variedad de opciones.</p>
                    </div>
                </a>
            </div>
            <!-- Pedidos -->
            <div class="col-md-4">
                <a href="<?= isset($_SESSION['user']) ? BASE_URL . '?page=pedidos' : BASE_URL . '?page=login&msg=inicia-sesion' ?>" class="view-card">
                    <div class="view-img" style="background-image: url('<?= BASE_URL ?>assets/img/landing/ingredients.png');"></div>
                    <div class="view-content">
                        <h3 class="view-title">Hacer un Pedido</h3>
                        <p class="view-desc">
                            <?= isset($_SESSION['user']) ? 'Pide online y disfruta en casa.' : 'Debes iniciar sesión para pedir.' ?>
                        </p>
                    </div>
                </a>
            </div>
            <!-- Asistencia / Contacto -->
            <div class="col-md-4">
                <a href="<?= BASE_URL ?>?page=asistencia-publica" class="view-card">
                    <div class="view-img" style="background-color: var(--color-sidebar);"></div>
                    <div class="view-content">
                        <h3 class="view-title">Asistencia</h3>
                        <p class="view-desc">¿Necesitas ayuda o contactarnos?</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Nosotros Section -->
<section class="about-section py-5 bg-tarjetas">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="section-title text-start mb-4" style="font-size: 2.5rem;">Nuestra <span>Esencia</span></h2>
                <p class="lead text-body mb-4">
                    En <strong>Good Vibes Tapas & Bar</strong>, fusionamos la pasión por la gastronomía con un ambiente vibrante. Somos más que un local de comida; somos el punto de encuentro donde los sabores tradicionales, mexicanos y maracuchos se mezclan con la mejor música y energía de Barquisimeto.
                </p>
                <div class="p-4 bg-principal rounded-4 shadow-sm border-start border-4 mb-4" style="border-color: var(--color-acento) !important;">
                    <h4 class="fw-bold mb-3"><i class="fas fa-bullseye text-primary me-2"></i> Nuestra Misión</h4>
                    <p class="mb-0 text-muted">
                        Brindar una experiencia culinaria diversa y vibrante, ofreciendo sabores auténticos en un ambiente excepcional. Garantizamos calidad tanto en nuestro establecimiento como en cada entrega a domicilio.
                    </p>
                </div>
                <div class="p-4 bg-principal rounded-4 shadow-sm border-start border-4" style="border-color: var(--color-acento) !important;">
                    <h4 class="fw-bold mb-3"><i class="fas fa-eye text-primary me-2"></i> Nuestra Visión</h4>
                    <p class="mb-0 text-muted">
                        Consolidarnos como el referente gastronómico y de entretenimiento número uno en el este de Barquisimeto, siendo reconocidos por nuestra innovación constante y talento en vivo.
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-principal">
                    <img src="<?= BASE_URL ?>assets/img/landing/ingredients.png" alt="Nosotros" style="height: 300px; object-fit: cover;">
                    <div class="card-body p-5">
                        <h4 class="fw-bold mb-4 text-center">Lo que nos define</h4>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle fs-4 me-3" style="color: var(--color-acento);"></i> <span>Variedad única: Comida Mexicana, Maracucha y Tradicional.</span></li>
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle fs-4 me-3" style="color: var(--color-acento);"></i> <span>Eventos: Talento en vivo todos los viernes.</span></li>
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle fs-4 me-3" style="color: var(--color-acento);"></i> <span>Comodidad: Salón interno, Barra y Terraza.</span></li>
                            <li class="d-flex align-items-center"><i class="fas fa-check-circle fs-4 me-3" style="color: var(--color-acento);"></i> <span>Celebraciones: Cumpleaños con torta de regalo.</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contacto / Puntos de Venta Section -->
<section class="contact-section py-5">
    <div class="container py-4">
        <h2 class="section-title text-center mb-5">Visítanos y <span>Contáctanos</span></h2>
        <div class="row g-5">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm bg-tarjetas p-4 rounded-4 h-100">
                    <h3 class="fw-bold mb-4">¿Cómo encontrarnos?</h3>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-map-marker-alt fs-4 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Dirección Física</h5>
                            <p class="text-muted">Av. Los Leones, Centro Empresarial Barquisimeto, diagonal al C.C. París. Sector Este, Barquisimeto, Estado Lara.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-clock fs-4 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Horario de Atención</h5>
                            <p class="text-muted mb-0">Lunes a Sábado</p>
                            <p class="text-muted fw-bold">09:00 AM - 05:00 PM / 03:00 PM - 11:00 PM</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fab fa-whatsapp fs-4 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Reservaciones y Delivery</h5>
                            <p class="text-muted mb-2">¡Escríbenos por WhatsApp!</p>
                            <a href="https://wa.me/584126159308" target="_blank" class="btn btn-outline-primary fw-bold rounded-pill px-4">
                                Enviar Mensaje
                            </a>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-top">
                        <h5 class="fw-bold text-center mb-3">Síguenos en Redes</h5>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="https://www.instagram.com/goodvibes_tapasbar/" target="_blank" class="btn btn-primary rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                <i class="fab fa-instagram fs-5 text-dark"></i>
                            </a>
                            <a href="#" class="btn btn-primary rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                <i class="fab fa-tiktok fs-5 text-dark"></i>
                            </a>
                            <a href="#" class="btn btn-primary rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                <i class="fab fa-facebook-f fs-5 text-dark"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="map-container shadow-sm rounded-4 overflow-hidden border border-2 h-100" style="min-height: 400px; border-color: var(--color-border) !important;">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.312345!2d-69.284567!3d10.065432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDAzJzU1LjYiTiA2OcKwMTcnMDQuNCJX!5e0!3m2!1ses!2sve!4v1712950000000!5m2!1ses!2sve" 
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Noticias Integrada -->
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

<section id="noticias-seccion" class="py-0 bg-tarjetas border-top">
    <!-- Ticker -->
    <div class="news-ticker border-bottom bg-body-tertiary">
        <div class="container d-flex align-items-center py-1">
            <span class="ticker-label text-nowrap me-3 small fw-bold">LO ÚLTIMO</span>
            <div class="ticker-content-wrapper small" style="overflow: hidden; white-space: nowrap; width: 100%;">
                <div class="ticker-content d-inline-block" style="animation: ticker 25s linear infinite;">
                    <?php if(!empty($topnoticias)): foreach($topnoticias as $t): ?>
                        <span class="ticker-item fw-semibold text-secondary mx-3">• <?= htmlspecialchars($t['titulo']) ?></span>
                    <?php endforeach; else: ?>
                        <span class="ticker-item fw-semibold text-secondary mx-3">• Pronto compartiremos más novedades contigo.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes ticker {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .editorial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
            border-color: var(--color-acento) !important;
        }
    </style>

    <!-- Featured Carousel -->
    <?php if (!empty($topnoticias)): ?>
    <div class="news-carousel shadow-lg mb-0" style="position: relative;">
        <div id="featuredCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach($topnoticias as $idx => $t): ?>
                    <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx==0?'active':'' ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach($topnoticias as $idx => $t): 
                    $img = !empty($t['imagen_principal']) ? BASE_URL . $t['imagen_principal'] : BASE_URL . '/assets/img/noticia-default.png';
                ?>
                <div class="carousel-item <?= $idx==0?'active':'' ?>" style="height: 500px; background-color: #000;">
                    <img src="<?= $img ?>" class="d-block w-100 h-100" style="object-fit: cover; opacity: 0.6;" alt="<?= htmlspecialchars($t['titulo']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/img/logo.png'; this.style.objectFit='contain';">
                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center h-100" style="bottom: 0;">
                        <div class="container text-center">
                            <span class="badge <?= $badgeArray[$t['tipo']]['bg'] ?? 'bg-primary' ?> mb-3 p-2 px-3 fw-bold fs-6 shadow">
                                <?= $badgeArray[$t['tipo']]['text'] ?? 'Nota' ?>
                            </span>
                            <h2 class="text-white mb-3 fw-bold display-5" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);"><?= htmlspecialchars($t['titulo']) ?></h2>
                            <p class="lead text-white-50 mb-4 d-none d-md-block" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.8); max-width: 800px; margin: 0 auto;">
                                <?= !empty($t['subtitulo']) ? htmlspecialchars($t['subtitulo']) : substr(strip_tags($t['contenido']), 0, 160) . '...' ?>
                            </p>
                            <a href="<?= BASE_URL ?>?page=Noticia&type=detalle&id=<?= $t['id_noticia'] ?>" class="btn btn-primary btn-lg fw-bold shadow-lg rounded-pill px-4">
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
    </div>
    <?php endif; ?>

    <div class="container mt-5 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0" style="font-size: 2.5rem;">Últimas <span>Novedades</span></h2>
        </div>
        
        <!-- Filtros con Accordion -->
        <section class="news-filters-accordion mb-5">
            <div class="accordion shadow-sm rounded-4 border-0" id="accordionFiltros">
                <div class="accordion-item border-0 bg-transparent">
                    <div class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed bg-principal shadow-sm rounded-4 border fw-bold text-uppercase py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltros" aria-expanded="false" aria-controls="collapseFiltros">
                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                <span><i class="fas fa-sliders-h me-2 text-primary"></i> Panel de Filtros</span>
                                <?php if(isset($_GET['tipo']) || isset($_GET['autor']) || isset($_GET['mes']) || isset($_GET['anio'])): ?>
                                    <span class="badge bg-primary rounded-pill small ms-2 text-dark">Filtros Activos</span>
                                <?php endif; ?>
                            </div>
                        </button>
                    </div>
                    <div id="collapseFiltros" class="accordion-collapse collapse <?= (isset($_GET['tipo']) || isset($_GET['autor']) || isset($_GET['mes']) || isset($_GET['anio'])) ? 'show' : '' ?>" aria-labelledby="headingOne" data-bs-parent="#accordionFiltros">
                        <div class="accordion-body bg-principal border rounded-4 mt-2 shadow-sm p-4">
                            <form action="<?= BASE_URL ?>" method="GET" class="row g-3 align-items-end">
                                <input type="hidden" name="page" value="Home">
                                <input type="hidden" name="type" value="publico">
                                
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-uppercase">Categoría</label>
                                    <select name="tipo" class="form-select rounded-3">
                                        <option value="">Todas</option>
                                        <option value="INFO" <?= ($_GET['tipo']??'')=='INFO'?'selected':'' ?>>Informativo</option>
                                        <option value="EXITO" <?= ($_GET['tipo']??'')=='EXITO'?'selected':'' ?>>Logros/Éxitos</option>
                                        <option value="ALERTA" <?= ($_GET['tipo']??'')=='ALERTA'?'selected':'' ?>>Importante/Alerta</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-uppercase">Publicador</label>
                                    <select name="autor" class="form-select rounded-3">
                                        <option value="">Todos</option>
                                        <?php if(isset($autores)): foreach($autores as $autor): ?>
                                            <option value="<?= $autor ?>" <?= ($_GET['autor']??'')==$autor?'selected':'' ?>><?= htmlspecialchars($autor) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-uppercase">Mes</label>
                                    <select name="mes" class="form-select rounded-3">
                                        <option value="">Cualquiera</option>
                                        <?php 
                                        $mesesNombres = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                                        foreach($mesesNombres as $m => $nombre): 
                                            $val = $m + 1;
                                        ?>
                                            <option value="<?= $val ?>" <?= ($_GET['mes']??'')==$val?'selected':'' ?>><?= $nombre ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-uppercase">Año</label>
                                    <select name="anio" class="form-select rounded-3">
                                        <option value="">Cualquiera</option>
                                        <option value="2024" <?= ($_GET['anio']??'')=='2024'?'selected':'' ?>>2024</option>
                                        <option value="2025" <?= ($_GET['anio']??'')=='2025'?'selected':'' ?>>2025</option>
                                        <option value="2026" <?= ($_GET['anio']??'')=='2026'?'selected':'' ?>>2026</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">
                                        <i class="fas fa-search me-2"></i> BUSCAR
                                    </button>
                                </div>
                            </form>
                            <?php if(isset($_GET['tipo']) || isset($_GET['autor']) || isset($_GET['mes']) || isset($_GET['anio'])): ?>
                                <div class="mt-3 text-end">
                                    <a href="<?= BASE_URL ?>?page=Home&type=publico#noticias-seccion" class="text-decoration-none small text-danger fw-bold">
                                        <i class="fas fa-times-circle"></i> Limpiar Filtros
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Noticias Grid Section -->
        <div class="row g-4 mt-2">
            <?php if (!empty($otrasnoticias)): ?>
                <?php foreach ($otrasnoticias as $noti): 
                    $img = !empty($noti['imagen_principal']) ? BASE_URL . $noti['imagen_principal'] : BASE_URL . '/assets/img/noticia-default.png';
                    $fecha = new DateTime($noti['fecha_publicacion']);
                    $badgeClass = $badgeArray[$noti['tipo']]['bg'] ?? 'bg-primary';
                    $badgeText  = $badgeArray[$noti['tipo']]['text'] ?? 'Nota';
                ?>
                
                <div class="col-md-6 col-lg-4">
                    <article class="editorial-card h-100 bg-principal shadow-sm border" style="border-radius: 1rem; overflow: hidden; transition: all 0.3s ease;">
                        <div class="editorial-img-container" style="position: relative;">
                            <span class="badge <?= $badgeClass ?> shadow" style="position: absolute; top: 15px; left: 15px; z-index: 10;"><?= $badgeText ?></span>
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($noti['titulo']) ?>" style="width: 100%; height: 220px; object-fit: cover;" onerror="this.src='<?= BASE_URL ?>/assets/img/logo.png'; this.style.objectFit='contain'; this.style.padding='20px';">
                            <a href="<?= BASE_URL ?>?page=Noticia&type=detalle&id=<?= $noti['id_noticia'] ?>" class="stretched-link"></a>
                        </div>
                        <div class="p-4 d-flex flex-column" style="height: calc(100% - 220px);">
                            <div class="editorial-meta mb-3 text-muted small d-flex justify-content-between border-bottom pb-2">
                                <span><i class="far fa-user text-primary me-1"></i> <?= htmlspecialchars($noti['autor']) ?></span>
                                <span><i class="far fa-calendar text-primary me-1"></i> <?= $fecha->format('d/m/Y') ?></span>
                            </div>
                            <h4 class="editorial-title fw-bold mb-3" style="color: var(--color-sidebar);">
                                <a href="<?= BASE_URL ?>?page=Noticia&type=detalle&id=<?= $noti['id_noticia'] ?>" class="text-decoration-none" style="color: inherit;">
                                    <?= htmlspecialchars($noti['titulo']) ?>
                                </a>
                            </h4>
                            <p class="text-muted mb-4 flex-grow-1" style="font-size: 0.95rem;">
                                <?= !empty($noti['subtitulo']) ? htmlspecialchars($noti['subtitulo']) : substr(strip_tags($noti['contenido']), 0, 110) . '...' ?>
                            </p>
                            <div class="text-end mt-auto">
                                <span class="fw-bold text-uppercase" style="color: var(--color-acento); font-size: 0.85rem;">Leer más <i class="fas fa-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </article>
                </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="bg-principal shadow-sm rounded-4 p-5 d-inline-block border border-2 border-primary border-opacity-10">
                        <i class="fas fa-bullhorn fa-4x text-muted opacity-25 mb-4 d-block"></i>
                        <h4 class="fw-bold text-muted mb-2">Aún no hay novedades</h4>
                        <p class="text-muted mb-0">Pronto compartiremos más noticias contigo. Vuelve más tarde.</p>
                        <?php if(isset($_GET['tipo']) || isset($_GET['autor'])): ?>
                            <a href="<?= BASE_URL ?>?page=Home&type=publico#noticias-seccion" class="btn btn-primary mt-4 rounded-pill px-4">Ver todas las novedades</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Fix hover for noticia card inline style to avoid needing new CSS class just for this -->
<style>
    .noticia-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        border-color: var(--color-acento) !important;
    }
</style>

<!-- Public Footer -->
<footer class="bg-dark text-white pt-5 pb-4 mt-auto" style="border-top: 5px solid var(--color-acento);">
    <div class="container text-center text-md-start">
        <div class="row text-center text-md-start">
            <div class="col-md-4 col-lg-4 col-xl-4 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold" style="color: var(--color-acento);">Good Vibes</h5>
                <p>Tu punto de encuentro favorito en Barquisimeto donde la gastronomía y la buena música se unen para ofrecerte experiencias inolvidables.</p>
            </div>
            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold" style="color: var(--color-acento);">Enlaces Útiles</h5>
                <p><a href="<?= BASE_URL ?>?page=nuestro-menu" class="text-white text-decoration-none">Menú</a></p>
                <p><a href="<?= BASE_URL ?>?page=login" class="text-white text-decoration-none">Iniciar Sesión</a></p>
                <p><a href="<?= BASE_URL ?>?page=asistencia-publica" class="text-white text-decoration-none">Asistencia</a></p>
            </div>
            <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold" style="color: var(--color-acento);">Contacto</h5>
                <p><i class="fas fa-home mr-3"></i> Av. Los Leones, Barquisimeto, VE</p>
                <p><i class="fas fa-envelope mr-3"></i> contacto@goodvibes.com</p>
                <p><i class="fas fa-phone mr-3"></i> +58 412-6159308</p>
            </div>
        </div>
        <hr class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-7 col-lg-8">
                <p>Copyright © <?= date('Y') ?> Todos los derechos reservados por:
                    <a href="#" style="text-decoration: none;"><strong style="color: var(--color-acento);">Good Vibes Tapas & Bar</strong></a>
                </p>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="text-center text-md-end">
                    <ul class="list-unstyled list-inline">
                        <li class="list-inline-item">
                            <a href="https://www.instagram.com/goodvibes_tapasbar/" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="fab fa-instagram"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="fab fa-tiktok"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="btn-floating btn-sm text-white" style="font-size: 23px;"><i class="fab fa-facebook-f"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Ocultar el footer genérico del sistema si está presente */
    main.main-content > footer.bg-body-tertiary {
        display: none !important;
    }
</style>
