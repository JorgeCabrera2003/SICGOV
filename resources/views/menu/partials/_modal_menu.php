<!-- ==========================================
    MODAL DE MENÚ Y RECETA
========================================== -->
<style>
    /* Forzar texto negro en la pestaña activa del recetario para máxima legibilidad */
    #recetaTabs .nav-link.active,
    #recetaTabs .nav-link.active span,
    #recetaTabs .nav-link.active i {
        color: #1A1C20 !important;
    }
</style>

<div class="modal fade" id="modalMenu" tabindex="-1" aria-labelledby="modalMenuLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form id="formMenu" enctype="multipart/form-data" class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-dark">
                <h5 class="modal-title fw-bold" id="modalMenuLabel">
                    <i class="fas fa-utensils me-2"></i><span id="modalTitleText">Nuevo Producto al Menú</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0 bg-body-tertiary">
                    <input type="hidden" id="id_producto" name="id_producto">
                    
                    <div class="row g-0">
                        <!-- COLUMNA IZQUIERDA: DATOS BÁSICOS -->
                        <div class="col-lg-5 p-4 bg-body border-end">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-info-circle me-2"></i>Datos Requeridos</h6>
                            
                            <!-- Imagen -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Imagen del Producto <span class="text-danger">*</span></label>
                                <div class="card border border-2 border-dashed rounded-3">
                                    <div class="card-body p-3 text-center">
                                        <div id="previewImagenContainer" style="display: none;" class="mb-2">
                                            <img id="previewImagen" src="#" alt="Vista previa" class="img-thumbnail" style="max-height: 120px;">
                                        </div>
                                        <input class="form-control d-none" type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp">
                                        <input type="hidden" id="imagen_galeria" name="imagen_galeria">
                                        <div class="d-flex justify-content-center gap-2">
                                            <label for="imagen" class="btn btn-sm btn-outline-primary shadow-sm"><i class="fas fa-upload"></i> Subir</label>
                                            <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm" id="btnAbrirGaleria">
                                                <i class="fas fa-image"></i> Galería
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3 position-relative">
                                <label for="nombre" class="form-label fw-semibold">Nombre del Producto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Hamburguesa Clásica" required>
                                <span id="errorNombre" style="width: fit-content;"></span>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-md-6 position-relative">
                                    <label for="precio" class="form-label fw-semibold">Precio ($) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required placeholder="0.00">
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label for="tipo_producto" class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipo_producto" name="tipo_producto" required>
                                        <option value="COCINA">Cocina</option>
                                        <option value="BARRA">No Cocina (Barra)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3 position-relative">
                                <label for="id_categoria" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_categoria" name="id_categoria" required>
                                    <option value="" selected disabled>Seleccionar</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id_categoria'] ?>">
                                            <?= htmlspecialchars($cat['nombre_categoria']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span id="errorCategoria" style="width: fit-content;"></span>
                            </div>
                            
                            <div class="mb-3 position-relative">
                                <label for="descripcion" class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" placeholder="Breve descripción..." required></textarea>
                            </div>
                            
                        </div>

                        <!-- COLUMNA DERECHA: RECETAS / INSUMOS -->
                        <div class="col-lg-7 p-4">
                            <div id="seccionInsumos">
                                <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-list-check me-2"></i>Receta e Insumos</h6>
                            <p class="text-muted small">Selecciona los insumos y define sus cantidades.</p>
                            
                            <!-- Buscador de insumos -->
                            <div class="mb-3 position-relative">
                                <div class="input-group mb-2 shadow-sm">
                                    <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control select-insumo-input" placeholder="Escribe para buscar insumos..." aria-label="Buscar Insumo">
                                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#catalogoInsumos" aria-expanded="false">
                                        Ver Catálogo
                                    </button>
                                </div>
                                
                                <!-- Lista desplegable emergente -->
                                <div class="collapse position-absolute w-100 z-3" id="catalogoInsumos">
                                    <div class="card card-body shadow border-0 p-1" style="max-height: 200px; overflow-y: auto;">
                                        <div class="list-group list-group-flush" id="listaInsumosUI">
                                            <!-- Insumos renderizados por JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabs para separar Principales y Adicionales -->
                            <ul class="nav nav-tabs nav-fill mb-3" id="recetaTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold" id="principales-tab" data-bs-toggle="tab" data-bs-target="#tab-principales" type="button" role="tab" aria-selected="true">
                                        <i class="fas fa-star text-warning me-1"></i> Principales (<span id="contPrincipales">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold" id="adicionales-tab" data-bs-toggle="tab" data-bs-target="#tab-adicionales" type="button" role="tab" aria-selected="false">
                                        <i class="fas fa-plus-circle text-success me-1"></i> Adicionales (<span id="contAdicionales">0</span>)
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="recetaTabsContent" style="min-height: 250px;">
                                <!-- Tab Principales -->
                                <div class="tab-pane fade show active" id="tab-principales" role="tabpanel" tabindex="0">
                                    <div class="table-responsive bg-body rounded shadow-sm border p-2 h-100">
                                        <table class="table table-borderless table-sm align-middle w-100 m-0" id="tablaPrincipales">
                                            <thead class="border-bottom">
                                                <tr>
                                                    <th>Insumo</th>
                                                    <th style="width: 120px;">Cantidad</th>
                                                    <th style="width: 140px;">Unidad</th>
                                                    <th style="width: 50px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="empty-row text-center text-muted"><td colspan="4" class="py-4">No hay insumos principales</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Tab Adicionales -->
                                <div class="tab-pane fade" id="tab-adicionales" role="tabpanel" tabindex="0">
                                     <div class="table-responsive bg-body rounded shadow-sm border p-2 h-100">
                                        <table class="table table-borderless table-sm align-middle w-100 m-0" id="tablaAdicionales">
                                            <thead class="border-bottom">
                                                <tr>
                                                    <th>Insumo</th>
                                                    <th style="width: 100px;">Cantidad</th>
                                                    <th style="width: 120px;">Unidad</th>
                                                    <th style="width: 100px;">Precio ($)</th>
                                                    <th style="width: 50px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="empty-row text-center text-muted"><td colspan="5" class="py-4">No hay insumos adicionales</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-2 text-muted small"><i class="fas fa-info-circle"></i> Los insumos adicionales normalmente pueden ser solicitados extra por el cliente.</div>
                                </div>
                            </div>
                            </div>
                            
                            <!-- MENSAJE DE SIN INSUMOS -->
                            <div id="seccionSinInsumos" class="h-100 flex-column justify-content-center align-items-center text-center p-5" style="display: none;">
                                <div class="bg-body-tertiary rounded-circle p-4 mb-4 shadow-sm border">
                                    <i class="fas fa-glass-martini-alt fa-3x text-secondary"></i>
                                </div>
                                <h5 class="fw-bold text-body">No requiere insumos</h5>
                                <p class="text-muted">Este tipo de producto se prepara o despacha directamente, por lo que no lleva un control estricto de receta o insumos desde este módulo.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-body border-top p-3 text-body">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold text-white shadow-sm" id="btnGuardarMenu" disabled>
                        <i class="fas fa-save me-2"></i>Guardar Producto
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
