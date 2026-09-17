<!-- ==========================================
    MODAL DE PROMOCIÓN - Reutilizable
========================================== -->

<div class="modal fade" id="modalPromocion" tabindex="-1" aria-labelledby="modalPromocionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content border-0 shadow">
            <style>
                #modalPromocion .is-valid {
                    border-color: var(--bs-border-color) !important;
                    box-shadow: none !important;
                }
                #modalPromocion .valid-feedback {
                    display: none !important;
                }
                #modalPromocion input:valid,
                #modalPromocion textarea:valid,
                #modalPromocion select:valid {
                    background-image: none !important;
                    box-shadow: none !important;
                }
                /* Reglas para Modo Oscuro en Modal Promoción */
                html[data-bs-theme="dark"] #modalPromocion .modal-header,
                .dark #modalPromocion .modal-header {
                    background: linear-gradient(135deg, rgba(255, 193, 7, 0.16) 0%, rgba(255, 193, 7, 0.04) 100%) !important;
                    border-bottom: 1px solid rgba(255, 193, 7, 0.25) !important;
                }
                html[data-bs-theme="dark"] #modalPromocion #btnAbrirGaleriaPromocion,
                .dark #modalPromocion #btnAbrirGaleriaPromocion {
                    color: #ffc107 !important;
                    border-color: #ffc107 !important;
                    background-color: rgba(255, 193, 7, 0.1) !important;
                    transition: all 0.2s ease-in-out;
                }
                html[data-bs-theme="dark"] #modalPromocion #btnAbrirGaleriaPromocion:hover,
                .dark #modalPromocion #btnAbrirGaleriaPromocion:hover {
                    background-color: #ffc107 !important;
                    color: #111315 !important;
                    box-shadow: 0 0 16px rgba(255, 193, 7, 0.45) !important;
                }
                html[data-bs-theme="light"] #modalPromocion #btnAbrirGaleriaPromocion,
                :root:not(.dark) #modalPromocion #btnAbrirGaleriaPromocion {
                    color: #92400e;
                    border-color: #f59e0b;
                    background-color: rgba(245, 158, 11, 0.08);
                }
                html[data-bs-theme="light"] #modalPromocion #btnAbrirGaleriaPromocion:hover,
                :root:not(.dark) #modalPromocion #btnAbrirGaleriaPromocion:hover {
                    color: #ffffff;
                    background-color: #d97706;
                }
                html[data-bs-theme="dark"] #modalPromocion .promo-preview-container,
                .dark #modalPromocion .promo-preview-container {
                    background-color: rgba(255, 255, 255, 0.03) !important;
                    border-color: rgba(255, 255, 255, 0.12) !important;
                }
                html[data-bs-theme="dark"] #modalPromocion .border-end,
                .dark #modalPromocion .border-end {
                    border-color: rgba(255, 255, 255, 0.1) !important;
                }
            </style>
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalPromocionLabel">
                    <i class="fas fa-tags text-warning me-2"></i>
                    <span id="modalTitleTextPromocion"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formPromocion" enctype="multipart/form-data">
                <input type="hidden" id="id_promocion" name="id_promocion">
                <input type="hidden" id="productos" name="productos">
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-5 p-4 bg-body border-end">
                            <h6 class="fw-bold mb-3 text-warning"><i class="fas fa-info-circle me-2"></i>Datos de la Promoción</h6>

                            <div class="mb-3 position-relative">
                                <label for="nombre" class="form-label fw-semibold">
                                    Nombre de la Promoción <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required>
                                <div class="form-label text-danger" id="snombre"></div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6 position-relative">
                                    <label for="tipo_descuento" class="form-label fw-semibold">
                                        Tipo de descuento <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="tipo_descuento" name="tipo_descuento" required>
                                        <option value="default" selected disabled>Seleccione</option>
                                        <option value="PORCENTAJE">Porcentaje</option>
                                        <option value="MONTO_FIJO">Monto fijo</option>
                                    </select>
                                    <div class="form-label text-danger" id="stipo_descuento"></div>
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label for="valor_descuento" class="form-label fw-semibold">
                                        Valor del descuento <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="valorDescLabel">%</span>
                                        <input type="text" inputmode="decimal" pattern="[0-9,]*" class="form-control" id="valor_descuento" name="valor_descuento" placeholder="00,00" autocomplete="off" required>
                                    </div>
                                    <div class="form-label text-danger" id="svalor_descuento"></div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6 position-relative">
                                    <label for="fecha_inicio" class="form-label fw-semibold">
                                        Fecha de inicio <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                    <div class="form-label text-danger" id="sfecha_inicio"></div>
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label for="fecha_fin" class="form-label fw-semibold">
                                        Fecha de fin
                                    </label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                                    <div class="form-label text-danger" id="sfecha_fin"></div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6 position-relative">
                                    <label for="hora_inicio" class="form-label fw-semibold">
                                        Hora inicio
                                    </label>
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio">
                                    <div class="form-label text-danger" id="shora_inicio"></div>
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label for="hora_fin" class="form-label fw-semibold">
                                        Hora fin
                                    </label>
                                    <input type="time" class="form-control" id="hora_fin" name="hora_fin">
                                    <div class="form-label text-danger" id="shora_fin"></div>
                                </div>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="descripcion" class="form-label fw-semibold">
                                    Descripción
                                </label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                <div class="form-label text-danger" id="sdescripcion"></div>
                            </div>

                            <!-- Fila: Imagen de la Promoción (Galería) -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-image me-1 text-warning"></i> Imagen de la Promoción
                                </label>
                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-warning w-100 fw-semibold d-flex align-items-center justify-content-center" id="btnAbrirGaleriaPromocion">
                                        <i class="fas fa-images me-2"></i>Elegir de Galería
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="btnQuitarImagenPromocion" style="display: none;" title="Quitar imagen">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="imagen_galeria" name="imagen_galeria">
                                <div id="previewImagenPromocionContainer" style="display: none;" class="p-2 border rounded bg-body-tertiary text-center promo-preview-container">
                                    <img id="previewImagenPromocion" src="#" alt="Vista previa" class="img-fluid rounded shadow-sm" style="max-height: 130px; object-fit: contain;">
                                </div>
                                <small class="text-muted d-block mt-1">Selecciona una imagen del gestor de galería multimedia.</small>
                            </div>

                        </div>

                        <div class="col-lg-7 p-4">
                            <div id="seccionProductos">
                                <h6 class="fw-bold mb-3 text-warning"><i class="fas fa-list-check me-2"></i>Buscar producto</h6>
                                <p class="text-muted small">Filtra por nombre o categoría y selecciona el producto que recibirá la promoción.</p>

                                <div class="mb-3 position-relative">
                                    <div class="input-group mb-2 shadow-sm">
                                        <span class="input-group-text bg-body"><i class="fas fa-search"></i></span>
                                        <input type="text" id="buscar_producto_promocion" class="form-control" placeholder="Buscar productos..." autocomplete="off">
                                        <button class="btn btn-outline-secondary" type="button" id="btnVerCatalogoPromocion" data-bs-toggle="collapse" data-bs-target="#catalogoProductosPromocion" aria-expanded="false">
                                            Ver Catálogo
                                        </button>
                                    </div>

                                    <div class="collapse w-100" id="catalogoProductosPromocion">
                                        <div class="card card-body shadow-sm border-0 p-1" style="max-height: 300px; overflow-y: auto;">
                                            <div class="list-group list-group-flush" id="listaProductosPromocionUI">
                                                <div class="text-center text-muted py-3">No se encontraron productos</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="card card-body shadow-sm border p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 fw-semibold">Productos seleccionados</h6>
                                            <small class="text-muted" id="contadorProductosSeleccionados">0 productos</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="productosSeleccionadosLista" style="max-height: 320px; overflow:auto;">
                                            <div class="list-group-item bg-body-tertiary text-center text-body-secondary py-3">No hay productos seleccionados</div>
                                        </div>
                                        <div class="form-label text-danger mt-2" id="sproducto"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-info" id="btnCrearNoticiaModal" style="display: none;">
                        <i class="fas fa-bullhorn me-2"></i>Publicar como Noticia
                    </button>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnPromocionForm"></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
