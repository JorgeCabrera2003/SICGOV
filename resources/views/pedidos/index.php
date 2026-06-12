<!-- ==========================================
    MÓDULO DE PEDIDOS - GOOD VIBES
    Gestión Administrativa
========================================== -->

<main class="container-fluid py-4 pedidos-admin">
    <header class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h1 class="h3 mb-0">
            <i class="fas fa-clipboard-list me-2 text-primary"></i>
            Gestión de Pedidos
        </h1>
        <div class="btn-group shadow-sm" role="group">
            <button type="button" class="btn btn-primary text-white fw-bold shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalPOS">
                <i class="fas fa-plus-circle me-2"></i>Nuevo Pedido (POS)
            </button>
        </div>
    </header>

    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-pedidos" id="pedidosTable">
                    <thead class="table-light">
                        <tr>
                            <th>Nro. Pedido</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pedidosTbody">
                        <!-- Llenado por JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<!-- Modal Detalles y Estado -->
<div class="modal fade" id="modalDetallePedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-receipt me-2"></i>Detalle de Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light" id="detallePedidoBody">
                <!-- Contenido Dinámico -->
            </div>
            <div class="modal-footer justify-content-between">
                <div class="btn-group" id="btnGroupEstados">
                    <!-- Botones dinámicos de estado -->
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Comprobante -->
<div class="modal fade" id="modalComprobante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-image me-2"></i>Comprobante de Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="comprobanteBody">
                <img src="" id="imgComprobante" class="img-fluid rounded" alt="Comprobante" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnVerificarPago" style="display:none;"><i class="fas fa-check me-2"></i>Marcar como Pagado</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal POS -->
<div class="modal fade" id="modalPOS" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
        <div class="modal-content border-0 shadow-lg" style="height: 90vh;">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fw-bold"><i class="fas fa-cash-register me-2"></i>Punto de Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light overflow-hidden">
                <div class="row h-100 g-0">
                    <!-- Panel Izquierdo: Productos -->
                    <div class="col-lg-8 d-flex flex-column h-100 border-end border-2">
                        <div class="p-3  shadow-sm z-index-1">
                            <div class="d-flex gap-2 overflow-auto pb-1" id="posFiltros">
                                <button class="btn btn-outline-primary btn-sm active text-nowrap" data-cat="todas">Todas</button>
                                <?php if(isset($categorias) && is_array($categorias)): foreach($categorias as $cat): ?>
                                    <button class="btn btn-outline-primary btn-sm text-nowrap" data-cat="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></button>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                        
                        <div class="pos-grid overflow-auto flex-grow-1 p-3" id="posProductos" style="align-content: flex-start;">
                            <!-- Productos cargados por JS -->
                        </div>
                    </div>

                    <!-- Panel Derecho: Ticket / Carrito -->
                    <div class="col-lg-4 h-100 d-flex flex-column pos-ticket bg-white">
                        <div class="bg-dark text-white p-2 d-flex justify-content-between align-items-center shadow-sm z-index-1">
                            <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Orden Actual</h6>
                            <span class="badge bg-primary rounded-pill fs-6" id="posCount">0</span>
                        </div>
                        
                        <div class="overflow-auto flex-grow-1 p-2 bg-light" id="posCartItems">
                            <!-- Items del carrito -->
                            <div class="text-center text-muted mt-5" id="posEmptyCart">
                                <i class="fas fa-shopping-basket fs-1 mb-2"></i>
                                <p>No hay productos en la orden</p>
                            </div>
                        </div>

                        <div class="bg-white border-top p-3 shadow-sm z-index-1">
                            <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                                <span>Total:</span>
                                <span id="posTotal" class="text-success">$0.00</span>
                            </div>

                            <form id="posForm">
                                <div class="mb-2">
                                    <select class="form-select form-select-sm" id="posTipoPedido" required>
                                        <option value="LLEVAR">Para Llevar</option>
                                        <option value="MESA">Para la Mesa</option>
                                        <option value="DELIVERY">Delivery</option>
                                    </select>
                                </div>
                                <div class="mb-2" id="boxMesa" style="display:none;">
                                    <input type="text" class="form-control form-control-sm" id="posMesa" placeholder="Nro Mesa">
                                </div>
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm" id="posClienteNombre" placeholder="Nombre de Cliente (Opcional)">
                                </div>
                                <div class="mb-3">
                                    <select class="form-select form-select-sm" id="posMetodoPago">
                                        <option value="METOD00120260519200547232">Efectivo</option>
                                        <option value="METOD00320260519200547232">Punto de Venta</option>
                                        <option value="METOD00420260519200547232">Pago Móvil</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary w-100 fw-bold shadow-sm" id="btnPosCobrar" disabled>
                                    <i class="fas fa-check-circle me-2"></i>Procesar y Cobrar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal de Personalización de Producto -->
<!-- Modal de Personalización de Producto -->
<div class="modal fade" id="modalPersonalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-sliders-h me-2"></i>
                    Personalizar Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 id="productoNombre" class="fw-bold mb-3"></h6>
                <input type="hidden" id="productoId">
                <input type="hidden" id="productoPrecioBase">
                
                <!-- Insumos Principales (opcionales de quitar) -->
                <div class="mb-3">
                    <label class="fw-bold mb-2">Ingredientes (puedes quitar los que no quieras):</label>
                    <div id="listaPrincipales" class="border rounded p-2 bg-light" style="max-height: 200px; overflow-y: auto;"></div>
                </div>
                
                <!-- Insumos Adicionales (extras con costo) -->
                <div class="mb-3">
                    <label class="fw-bold mb-2">Extras (costo adicional):</label>
                    <div id="listaAdicionales" class="border rounded p-2 bg-light" style="max-height: 200px; overflow-y: auto;"></div>
                </div>
                
                <div class="alert alert-info small">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Ingredientes:</strong> Desmarca los que no quieras.<br>
                    <strong>Extras:</strong> Tienen costo adicional y se suman al total.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarPersonalizar">
                    <i class="fas fa-check me-2"></i>Agregar al Carrito
                </button>
            </div>
        </div>
    </div>
</div>