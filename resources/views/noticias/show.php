<main class="bg-body pb-5 min-vh-100">
    
    <!-- Hero / Header -->
    <header class="article-header text-center px-3">
        <div class="container position-relative z-1">
            <span class="badge bg-body-tertiary text-primary fs-6 mb-3 px-3 py-2 rounded-pill"><?= htmlspecialchars($noticia['tipo']) ?></span>
            <h1 class="h1 fw-bold mb-3"><?= htmlspecialchars($noticia['titulo']) ?></h1>
            <p class="lead mb-3 mx-auto" style="max-width: 800px;">
                <?= htmlspecialchars($noticia['subtitulo']) ?>
            </p>
            
            <div class="d-flex justify-content-center align-items-center gap-4 small opacity-75">
                <span>
                    <i class="fas fa-user-circle me-2 fs-5"></i>
                    Escrito por <strong><?= htmlspecialchars($noticia['autor']) ?></strong>
                </span>
                <span>
                    <i class="fas fa-calendar-alt me-2 fs-5"></i>
                    <?php 
                        $f = new DateTime($noticia['fecha_publicacion']);
                        echo $f->format('d \d\e M, Y - h:i A');
                    ?>
                </span>
            </div>
        </div>
    </header>

    <div class="container mt-n2 position-relative z-2">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <article class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    
                    <!-- Carrusel de Imágenes si hay múltiples -->
                    <?php if (!empty($noticia['imagenes'])): ?>
                        <?php if (count($noticia['imagenes']) > 1): ?>
                            <div id="carouselNoticias" class="carousel slide carousel-fade carousel-news" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    <?php foreach ($noticia['imagenes'] as $index => $img): ?>
                                        <button type="button" data-bs-target="#carouselNoticias" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                                <div class="carousel-inner">
                                    <?php foreach ($noticia['imagenes'] as $index => $img): ?>
                                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" data-bs-interval="4000">
                                            <img src="<?= BASE_URL . $img['direccion'] ?>" class="d-block w-100" alt="News Image" onerror="this.src='<?= BASE_URL ?>/assets/img/logo.png'; this.style.objectFit='contain'; this.style.padding='40px';">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselNoticias" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon shadow-sm" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselNoticias" data-bs-slide="next">
                                    <span class="carousel-control-next-icon shadow-sm" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            </div>
                        <?php else: ?>
                            <!-- Mostrar una sola imagen de forma estática -->
                            <img src="<?= BASE_URL . $noticia['imagenes'][0]['direccion'] ?>" class="w-100 object-fit-cover" style="height: 500px;" alt="News Image" onerror="this.src='<?= BASE_URL ?>/assets/img/logo.png'; this.style.objectFit='contain'; this.style.padding='40px';">
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Imagen por defecto si no hay ninguna cargada -->
                        <img src="<?= BASE_URL ?>/assets/img/noticia-default.png" class="w-100 object-fit-cover" style="height: 500px;" alt="News Image" onerror="this.src='<?= BASE_URL ?>/assets/img/logo.png'; this.style.objectFit='contain';">
                    <?php endif; ?>


                    <div class="card-body p-5 article-content">
                        <!-- EL CONTENIDO -->
                        <?= nl2br(htmlspecialchars($noticia['contenido'])) ?>
                    </div>
                </article>

                <div class="text-center mt-5">
                    <a href="<?= BASE_URL ?>?page=Noticia&type=publico" class="btn btn-outline-primary btn-lg rounded-pill px-4 shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Noticias
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>
