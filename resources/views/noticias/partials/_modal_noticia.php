<div class="modal fade" id="modalNoticia" tabindex="-1" aria-labelledby="modalNoticiaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalNoticiaLabel">
                    <i class="fas fa-newspaper me-2"></i>Nueva Noticia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formNoticia" enctype="multipart/form-data">
                <div class="modal-body p-4 bg-body-tertiary text-body">
                    <!-- Campo oculto para ID y Petición -->
                    <input type="hidden" id="id_noticia" name="id_noticia">
                    <input type="hidden" id="peticion" name="peticion" value="registrar">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="titulo" class="form-label fw-semibold">Título de la Publicación <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="titulo" name="titulo" placeholder="Ej: Nueva apertura del restaurante" required>
                        </div>
                        
                        <div class="col-md-12">
                            <label for="subtitulo" class="form-label fw-semibold">Subtítulo (Opcional)</label>
                            <input type="text" class="form-control" id="subtitulo" name="subtitulo" placeholder="Breve descripción o entradilla redondeando el título">
                        </div>

                        <div class="col-md-12">
                            <label for="contenido" class="form-label fw-semibold">Contenido <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="contenido" name="contenido" rows="6" placeholder="Escribe tu artículo aquí..." required style="resize: vertical;"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="tipo" class="form-label fw-semibold">Clasificación / Etiqueta</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="INFO" selected>Informativo</option>
                                <option value="EXITO">Logros/Éxitos</option>
                                <option value="ALERTA">Importante/Alerta</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_publicacion" class="form-label fw-semibold">Fecha de Publicación <span class="text-secondary">(Dejar vacío para publicar ahora)</span></label>
                            <input type="datetime-local" class="form-control" id="fecha_publicacion" name="fecha_publicacion">
                             <small class="text-muted">Si programas esto a futuro, no será visible hasta entonces.</small>
                        </div>
                        
                        <!-- Galería de Imágenes Actuales (Solo en edición) -->
                        <div class="col-12 mt-3" id="currentImagesSection" style="display: none;">
                            <label class="form-label fw-bold"><i class="fas fa-images me-2"></i>Imágenes Actuales</label>
                            <div id="currentImagesContainer" class="d-flex flex-wrap gap-2 p-3 bg-body border rounded shadow-sm">
                                <!-- Se llena dinámicamente -->
                            </div>
                        </div>

                        <!-- Subida de Múltiples Imágenes -->
                        <div class="col-12 mt-4">
                            <div class="card border border-2 border-dashed rounded-3">
                                <div class="card-body p-4 text-center">
                                    <i class="fas fa-cloud-upload-alt text-primary mb-3" style="font-size: 3rem;"></i>
                                    <h5>Sube imágenes para tu galería</h5>
                                    <p class="text-muted mb-3">La primera imagen será usada como Portada. Selecciona varias imágenes a la vez.</p>
                                    <input class="form-control d-none" type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple>
                                    <label for="imagenes" class="btn btn-outline-primary shadow-sm"><i class="fas fa-image me-2"></i>Seleccionar Imágenes</label>
                                    
                                    <div id="previewContainer" class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
                                        <!-- Vistas previas de imagenes aqui -->
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="modal-footer bg-body text-body">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm" id="btnGuardarNoticia">
                        <i class="fas fa-save me-2"></i>Guardar Publicación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
