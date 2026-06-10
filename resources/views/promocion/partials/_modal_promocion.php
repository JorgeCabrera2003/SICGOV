<!-- ==========================================
    MODAL DE PROMOCIÓN - Reutilizable
========================================== -->

<div class="modal fade" id="modalPromocion" tabindex="-1" aria-labelledby="modalPromocionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content border-0 shadow">
            <style>
                #modalPromocion .is-valid {
                    border-color: #ced4da !important;
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
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="4"></textarea>
                                <div class="form-label text-danger" id="sdescripcion"></div>
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
                                        <div class="list-group list-group-flush" id="productosSeleccionadosLista" style="max-height: 180px; overflow:auto;">
                                            <div class="list-group-item bg-light text-center text-muted">No hay productos seleccionados</div>
                                        </div>
                                        <div class="form-label text-danger mt-2" id="sproducto"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnPromocionForm"></button>
                </div>
            </form>
        </div>
    </div>
</div>
