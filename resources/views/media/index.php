<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-images me-2 text-primary"></i>Gestor Multimedia
            </h1>
            <p class="text-muted small mb-0">Administra, filtra y reutiliza las imágenes del sistema</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload me-2"></i>Subir Imagen
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="card-body bg-body-tertiary p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Directorio</label>
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
                    <label class="form-label small fw-bold">Estado</label>
                    <select id="filter-status" class="form-select border-0 shadow-sm">
                        <option value="">Cualquier estado</option>
                        <option value="linked">Vinculada (En uso)</option>
                        <option value="orphan">Huérfana (Sin uso)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Búsqueda rápida</label>
                    <div class="input-group shadow-sm rounded overflow-hidden">
                        <span class="input-group-text border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="search-media" class="form-control border-0" placeholder="Nombre de archivo...">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="btn-refresh" class="btn btn-outline-secondary w-100 border-0 shadow-sm">
                        <i class="fas fa-sync-alt me-2"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Imágenes -->
    <div id="media-grid" class="row g-4 news-grid">
        <!-- Se cargará vía AJAX -->
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Escaneando archivos...</p>
        </div>
    </div>
</div>

<!-- Modal Detalles de Imagen -->
<div class="modal fade" id="imageDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Detalles de Archivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7 text-center">
                        <div class="bg-body-tertiary rounded p-2 mb-3">
                            <img id="detail-preview" src="" class="img-fluid rounded shadow-sm" style="max-height: 400px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-4">
                            <label class="text-muted small d-block">Nombre de archivo</label>
                            <span id="detail-name" class="fw-bold text-body"></span>
                        </div>
                        <div class="mb-4">
                            <label class="text-muted small d-block">Ubicación</label>
                            <span id="detail-path" class="small break-all font-monospace bg-body-tertiary text-body border border-secondary-subtle text-wrap d-block text-start p-2 rounded"></span>
                        </div>
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="text-muted small d-block">Tamaño</label>
                                <span id="detail-size" class="fw-semibold"></span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small d-block">Tipo</label>
                                <span id="detail-type" class="badge bg-primary"></span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="text-muted small d-block">Vinculaciones en BD</label>
                            <div id="detail-links" class="mt-1">
                                <!-- Listado de donde se usa -->
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button id="btn-copy-path" class="btn btn-outline-primary shadow-sm">
                                <i class="fas fa-copy me-2"></i>Copiar Ruta
                            </button>
                            <button id="btn-delete-file" class="btn btn-outline-danger shadow-sm">
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
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Subir Nueva Imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="upload-form" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Destino</label>
                        <select name="directorio" class="form-select">
                            <option value="uploads">Cargas Generales</option>
                            <option value="noticias">Noticias</option>
                            <option value="productos">Productos</option>
                            <option value="usuarios">Usuarios</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar Archivo</label>
                        <input type="file" name="archivo" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Subir Ahora</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.news-grid {
    display: flex;
    flex-wrap: wrap;
}
.media-item {
    transition: all 0.2s ease;
}
.media-item:hover {
    transform: translateY(-5px);
}
.media-card {
    height: 100%;
    cursor: pointer;
}
.media-preview-container {
    height: 150px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}
.media-preview-container img {
    height: 100%;
    width: 100%;
    object-fit: cover;
}
.media-badge {
    position: absolute;
    top: 5px;
    right: 5px;
}
.break-all {
    word-break: break-all;
}
</style>
