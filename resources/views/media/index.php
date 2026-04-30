<div class="container-fluid py-4 media-manager">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 fw-bold" style="color: var(--color-sidebar);">
                <i class="fas fa-images me-2" style="color: var(--color-acento);"></i>Gestor Multimedia
            </h1>
            <p class="small mb-0" style="color: var(--color-sidebar); opacity: 0.7;">Administra, filtra y reutiliza las imágenes del sistema</p>
        </div>
        <div class="col-auto">
            <button class="btn media-manager__upload-btn shadow fw-bold px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload me-2"></i>Subir Imagen
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card media-manager__filters border-0 mb-4 overflow-hidden rounded-4 shadow-sm">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase opacity-75" style="color: var(--color-sidebar);">Directorio</label>
                    <select id="filter-dir" class="form-select border-0 shadow-sm">
                        <option value="">Todos los directorios</option>
                        <option value="noticias">Noticias</option>
                        <option value="productos">Productos</option>
                        <option value="usuarios">Usuarios</option>
                        <option value="empleados">Empleados</option>
                        <option value="uploads">Cargas Generales</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-uppercase opacity-75" style="color: var(--color-sidebar);">Estado</label>
                    <select id="filter-status" class="form-select border-0 shadow-sm">
                        <option value="">Cualquier estado</option>
                        <option value="linked">Vinculada (En uso)</option>
                        <option value="orphan">Huérfana (Sin uso)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase opacity-75" style="color: var(--color-sidebar);">BÚSQUEDA RÁPIDA</label>
                    <div class="input-group shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text border-0" style="color: var(--color-sidebar); background-color: var(--bg-tarjetas);"><i class="fas fa-search"></i></span>
                        <input type="text" id="search-input" class="form-control border-0" placeholder="Nombre de archivo..." style="color: var(--color-sidebar); background-color: var(--bg-tarjetas);">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="btn-refresh" class="btn w-100 border-0 shadow-sm fw-bold" style="background-color: var(--color-bg-muted); color: var(--color-sidebar);">
                        <i class="fas fa-sync-alt me-2"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Imágenes -->
    <div id="media-grid" class="row g-4 media-manager__grid">
        <!-- Se cargará vía AJAX. Estructura esperada de los items devueltos:
        <div class="col-6 col-md-4 col-lg-3">
            <div class="media-manager__item rounded-4 overflow-hidden position-relative h-100 d-flex flex-column">
                <div class="media-manager__preview position-relative w-100 d-flex align-items-center justify-content-center">
                    <span class="badge media-manager__badge position-absolute top-0 end-0 m-2 rounded-pill z-2">Estado</span>
                    <img src="..." class="media-manager__image w-100 h-100 object-fit-cover">
                </div>
                ...
            </div>
        </div>
        -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border" style="color: var(--color-acento);" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3" style="color: var(--color-sidebar); opacity: 0.7;">Escaneando archivos...</p>
        </div>
    </div>
</div>

<!-- Modal Detalles de Imagen -->
<div class="modal fade media-detail" id="imageDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: var(--bg-tarjetas);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--color-sidebar);">Detalles de Archivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-7 text-center">
                        <div class="media-detail__preview-box rounded-4 p-3 h-100 d-flex align-items-center justify-content-center">
                            <img id="detail-preview" src="" class="img-fluid rounded shadow-sm" style="max-height: 400px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="col-md-5 d-flex flex-column">
                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase opacity-75 d-block mb-1" style="color: var(--color-sidebar);">Nombre de archivo</label>
                            <span id="detail-name" class="fw-bold" style="color: var(--color-sidebar);"></span>
                        </div>
                        <div class="mb-4">
                            <label class="small fw-bold text-uppercase opacity-75 d-block mb-1" style="color: var(--color-sidebar);">Ubicación</label>
                            <span id="detail-path" class="media-detail__path small font-monospace d-block text-start p-3 rounded-3"></span>
                        </div>
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="small fw-bold text-uppercase opacity-75 d-block mb-1" style="color: var(--color-sidebar);">Tamaño</label>
                                <span id="detail-size" class="fw-semibold" style="color: var(--color-sidebar);"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold text-uppercase opacity-75 d-block mb-1" style="color: var(--color-sidebar);">Tipo</label>
                                <span id="detail-type" class="badge rounded-pill" style="background-color: rgba(26, 28, 32, 0.85); color: var(--color-acento);"></span>
                            </div>
                        </div>
                        <div class="mb-4 flex-grow-1">
                            <label class="small fw-bold text-uppercase opacity-75 d-block mb-2" style="color: var(--color-sidebar);">Vinculaciones en BD</label>
                            <div id="detail-links" class="mt-1">
                                <!-- Listado de donde se usa -->
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-auto">
                            <button id="btn-copy-path" class="btn shadow-sm fw-bold" style="background-color: rgba(26, 28, 32, 0.05); color: var(--color-sidebar);">
                                <i class="fas fa-copy me-2"></i>Copiar Ruta
                            </button>
                            <button id="btn-delete-file" class="btn btn-danger shadow-sm fw-bold border-0">
                                <i class="fas fa-trash-alt me-2"></i>Eliminar Archivo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Subir Imagen -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: var(--bg-tarjetas);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: var(--color-sidebar);">Subir Nueva Imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="upload-form" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-uppercase small opacity-75" style="color: var(--color-sidebar);">Destino</label>
                        <select name="directorio" class="form-select bg-white border-0 shadow-sm p-3">
                            <option value="uploads">Cargas Generales</option>
                            <option value="noticias">Noticias</option>
                            <option value="productos">Productos</option>
                            <option value="usuarios">Usuarios</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-uppercase small opacity-75" style="color: var(--color-sidebar);">Seleccionar Archivo</label>
                        <input type="file" name="archivo" class="form-control bg-white border-0 shadow-sm p-3" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn fw-bold" style="background-color: transparent; color: var(--color-sidebar);" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn px-4 shadow-sm fw-bold" style="background-color: var(--color-acento); color: var(--color-sidebar);">Subir Ahora</button>
                </div>
            </form>
        </div>
    </div>
</div>
