<!-- ==========================================
    MODAL DE PRODUCTO - Reutilizable
    ========================================== -->

<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalProductoLabel">
                    <i class="fas fa-box text-warning me-2"></i>
                    <span id="modalTitleText">Nuevo Producto</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <form id="formProducto" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_producto" id="id_producto">

                    <!-- Fila: Nombre y Categoría -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   maxlength="100" required placeholder="Ej: Hamburguesa Clásica">
                        </div>
                        <div class="col-md-4">
                            <label for="id_categoria" class="form-label fw-semibold">Categoría</label>
                            <select class="form-select" id="id_categoria" name="id_categoria">
                                <option value="" selected disabled>Seleccionar</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>">
                                        <?= htmlspecialchars($cat['nombre_categoria']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                  rows="2" maxlength="255" placeholder="Breve descripción del producto"></textarea>
                    </div>

                    <!-- Fila: Precios y Stock -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="precio" class="form-label fw-semibold">
                                Precio Venta ($) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="precio" name="precio" 
                                   step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label for="costo" class="form-label fw-semibold">Costo ($)</label>
                            <input type="number" class="form-control" id="costo" name="costo" 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                            <label for="stock" class="form-label fw-semibold">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" 
                                   min="0" value="0">
                        </div>
                        <div class="col-md-2">
                            <label for="stock_minimo" class="form-label fw-semibold">Mín.</label>
                            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                                   min="0" value="5">
                        </div>
                    </div>

                    <!-- Fila: Imagen -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="imagen" class="form-label fw-semibold">Imagen del Producto</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" 
                                   accept="image/jpeg,image/png,image/gif">
                            <div class="form-text">Máx. 2MB (JPG, PNG, GIF)</div>
                        </div>
                        <div class="col-md-6" id="previewImagenContainer" style="display: none;">
                            <label class="form-label fw-semibold">Vista Previa:</label>
                            <div>
                                <img id="previewImagen" src="#" alt="Vista previa" 
                                     class="img-thumbnail" style="max-width: 120px; max-height: 120px;">
                            </div>
                        </div>
                    </div>

                    <!-- Switch: Activo/Inactivo -->
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="estatus" 
                               name="estatus" checked value="1" role="switch">
                        <label class="form-check-label fw-semibold" for="estatus">
                            Producto Activo
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning text-dark fw-semibold" id="btnGuardarProducto">
                        <i class="fas fa-save me-2"></i>Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>