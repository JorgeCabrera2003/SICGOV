<!-- ==========================================
    MÓDULO DE NOTICIAS - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-newspaper me-2 text-primary"></i>
            Gestión de Noticias y Publicaciones
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de noticia">
            <button class="btn btn-primary fw-semibold shadow-sm" id="btnNuevaNoticia">
                <i class="fas fa-plus me-2"></i>Nueva Noticia
            </button>
            <a href="<?= BASE_URL ?>?page=noticias" target="_blank" class="btn btn-outline-primary shadow-sm" id="btnVerBlog">
                <i class="fas fa-external-link-alt me-2"></i>Ver Blog Público
            </a>
        </div>
    </header>

    <!-- Tabla de productos (section semántica) -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaNoticias" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Imágenes</th>
                            <th scope="col">Título</th>
                            <th scope="col">Autor</th>
                            <th scope="col">Publicación Programada</th>
                            <th scope="col">Estatus</th>
                            <th scope="col">Acciones</th>
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

<!-- Modales (incluidos como partials) -->
<?php 
    include 'partials/_modal_noticia.php'; 
    include __DIR__ . '/../partials/_media_picker.php'; 
?>

<!-- Recursos específicos de la página -->
<script src="<?= BASE_URL ?>/assets/js/media-picker.js" defer></script>
<script src="<?= BASE_URL ?>/assets/js/noticias.js" defer></script>
