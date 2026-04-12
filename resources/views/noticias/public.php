<main class="container-fluid bg-body min-vh-100 py-5">
    
    <div class="row justify-content-center mb-5">
        <div class="col-md-8 text-center">
            <h1 class="display-4 fw-bold text-primary mb-3">Últimas Noticias</h1>
            <p class="lead text-muted">Mantente al día con nuestras actualizaciones, eventos y novedades de Good Vibes.</p>
        </div>
    </div>

    <div class="container">
        <div class="row g-4">
            <?php if (!empty($noticias)): ?>
                <?php foreach ($noticias as $noti): 
                    // Imagen por defecto si no tiene
                    $img = !empty($noti['imagen_principal']) ? BASE_URL . $noti['imagen_principal'] : BASE_URL . '/assets/img/default-product.png';
                    
                    // Fechas
                    $fecha = new DateTime($noti['fecha_publicacion']);
                    $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                    $dia = $fecha->format('d');
                    $mes = $meses[$fecha->format('m') - 1];
                    
                    // Tipos y colores
                    $badgeArray = [
                        'INFO'   => ['bg' => 'bg-info', 'text' => 'Informativo'],
                        'ALERTA' => ['bg' => 'bg-warning text-dark', 'text' => 'Alerta'],
                        'EXITO'  => ['bg' => 'bg-success', 'text' => 'Logro']
                    ];
                    $badgeClass = $badgeArray[$noti['tipo']]['bg'] ?? 'bg-primary';
                    $badgeText  = $badgeArray[$noti['tipo']]['text'] ?? 'Aviso';
                ?>
                
                <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                    <div class="card blog-card shadow-sm w-100 position-relative">
                        
                        <!-- Fecha Badge -->
                        <div class="blog-date-badge">
                            <span class="date-day"><?= $dia ?></span>
                            <span class="date-month"><?= $mes ?></span>
                        </div>

                        <!-- Imagen -->
                        <div class="overflow-hidden">
                            <img src="<?= $img ?>" class="card-img-top blog-img-top" alt="<?= htmlspecialchars($noti['titulo']) ?>">
                        </div>
                        
                        <div class="card-body d-flex flex-column p-4">
                            <div class="mb-2">
                                <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                <small class="text-muted ms-2"><i class="fas fa-user me-1"></i><?= htmlspecialchars($noti['autor']) ?></small>
                            </div>
                            <h4 class="card-title fw-bold mt-2 mb-3">
                                <a href="<?= BASE_URL ?>?page=noticias-detalle&id=<?= $noti['id_noticia'] ?>" class="text-dark text-decoration-none stretched-link">
                                    <?= htmlspecialchars($noti['titulo']) ?>
                                </a>
                            </h4>
                            <p class="card-text text-muted mb-4 flex-grow-1">
                                <?= !empty($noti['subtitulo']) ? htmlspecialchars($noti['subtitulo']) : substr(strip_tags($noti['contenido']), 0, 100) . '...' ?>
                            </p>
                            <div class="mt-auto border-top pt-3">
                                <span class="text-primary fw-semibold">Leer más <i class="fas fa-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-newspaper text-muted mb-3" style="font-size: 4rem;"></i>
                    <h3 class="text-muted">No hay noticias publicadas en este momento.</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
