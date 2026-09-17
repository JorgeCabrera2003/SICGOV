<!-- Modal Selector de Multimedia -->
<div class="modal fade" id="mediaPickerModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered shadow-lg">
        <div class="modal-content border-0 rounded-4 overflow-hidden" style="background: var(--bg-tarjetas, #fff);">

            <!-- Header -->
            <div class="modal-header border-0 px-4 py-3" style="background: linear-gradient(135deg, #1a1c20, #2d2f35);">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="fas fa-images text-warning"></i> Galería de Imágenes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Barra de herramientas -->
            <div class="px-4 py-3 border-bottom" style="background: var(--color-bg-muted, rgba(0,0,0,0.04));">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <select id="picker-dir" class="form-select form-select-sm border-0 shadow-sm">
                            <option value="">Todos los directorios</option>
                            <option value="noticias">Noticias</option>
                            <option value="productos">Productos</option>
                            <option value="uploads">Cargas Generales</option>
                            <option value="usuarios">Usuarios</option>
                            <option value="empleados">Empleados</option>
                            <option value="promociones">Promociones</option>
                            <option value="perfiles">Perfiles</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="picker-search" class="form-control form-control-sm border-0 shadow-sm"
                               placeholder="🔍 Buscar por nombre de archivo...">
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="button" class="btn btn-sm fw-bold rounded-pill px-3 shadow-sm"
                                style="background: var(--color-acento, #ffc107); color: #1a1c20;"
                                onclick="$('#picker-upload-section').slideToggle()">
                            <i class="fas fa-cloud-upload-alt me-1"></i> Subir Nueva
                        </button>
                    </div>
                </div>

                <!-- Sección de subida (oculta por defecto) -->
                <div id="picker-upload-section" style="display:none;" class="mt-3">
                    <div id="picker-drop-zone"
                         class="border-2 border-dashed rounded-3 p-4 text-center picker-drop-zone position-relative"
                         style="border-color: var(--color-acento, #ffc107); cursor: pointer;"
                         onclick="$('#picker-upload-input').click()">
                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-warning"></i>
                        <p class="mb-0 small fw-semibold" style="color: var(--color-sidebar, #333);">
                            Arrastra una imagen aquí o haz clic para seleccionar
                        </p>
                    </div>
                    <form id="picker-upload-form" class="mt-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-uppercase opacity-75">Destino</label>
                                <select name="directorio" id="picker-upload-dir" class="form-select form-select-sm border-0 shadow-sm">
                                    <option value="uploads">Cargas Generales</option>
                                    <option value="noticias">Noticias</option>
                                    <option value="productos">Productos</option>
                                    <option value="usuarios">Usuarios</option>
                                    <option value="empleados">Empleados</option>
                                    <option value="promociones">Promociones</option>
                                    <option value="perfiles">Perfiles</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-uppercase opacity-75">Archivo</label>
                                <input type="file" id="picker-upload-input" name="archivo"
                                       class="form-control form-control-sm border-0 shadow-sm"
                                       accept="image/jpeg,image/png,image/gif,image/webp" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success btn-sm w-100 fw-bold rounded-pill shadow-sm">
                                    <i class="fas fa-upload me-1"></i> Subir y Seleccionar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grid de imágenes -->
            <div class="modal-body p-3" style="max-height: 55vh; overflow-y: auto;">
                <div id="picker-grid" class="row g-2">
                    <!-- Se llena vía JS -->
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 px-4 py-2" style="background: var(--color-bg-muted, rgba(0,0,0,0.04));">
                <span class="text-muted small me-auto" id="picker-selection-info">Cargando galería...</span>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.picker-item {
    cursor: pointer;
    transition: transform 0.18s, box-shadow 0.18s;
}
.picker-item:hover {
    transform: scale(1.05);
    z-index: 5;
}
.picker-card {
    border: 3px solid transparent !important;
    transition: border-color 0.18s, box-shadow 0.18s;
}
.picker-card:hover {
    border-color: var(--color-acento, #ffc107) !important;
    box-shadow: 0 4px 18px rgba(255, 193, 7, 0.3) !important;
}
.picker-card.selected {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}
.picker-preview {
    height: 90px;
    object-fit: cover;
}
.picker-drop-zone {
    transition: background 0.2s;
}
.picker-drop-zone:hover,
.picker-drop-hover {
    background: rgba(255, 193, 7, 0.08) !important;
}
/* Dark mode */
[data-bs-theme="dark"] .picker-card,
.dark .picker-card {
    background-color: #1e2025 !important;
    border-color: rgba(255,255,255,0.07) !important;
}
[data-bs-theme="dark"] .picker-card:hover,
.dark .picker-card:hover {
    border-color: #ffc107 !important;
    box-shadow: 0 4px 18px rgba(255,193,7,0.25) !important;
}
</style>
