<!-- Modal Selector de Multimedia -->
<div class="modal fade" id="mediaPickerModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered shadow-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white p-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-images me-2 text-warning"></i>Seleccionar desde Galería</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="container-fluid py-3 bg-body-tertiary border-bottom">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select id="picker-dir" class="form-select bg-body text-body border-secondary-subtle">  
                                <option value="">Todos los directorios</option>
                                <option value="noticias">Noticias</option>
                                <option value="productos">Productos</option>
                                <option value="uploads">Cargas Generales</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <input type="text" id="picker-search" class="form-control bg-body text-body border-secondary-subtle" placeholder="Buscar por nombre...">
                        </div>
                    </div>
                </div>
                <div id="picker-grid" class="row g-2 p-3 overflow-auto" style="max-height: 500px;">
                    <!-- Se llena vía JS -->
                </div>
            </div>
            <div class="modal-footer bg-body-tertiary p-2">
                <span class="text-muted small me-auto" id="picker-selection-info">Cargando galería...</span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<style>
.picker-item {
    cursor: pointer;
    transition: all 0.2s;
}
.picker-item:hover {
    transform: scale(1.05);
    z-index: 5;
}
.picker-card {
    border: 3px solid transparent;
}
.picker-card.selected {
    border-color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}
.picker-preview {
    height: 100px;
    object-fit: cover;
}
</style>
