<!-- ==========================================
     PORTAL PÚBLICO DEL MENÚ Y PEDIDOS
     ========================================== -->

<?php
$menusPorCategoria = [];
foreach ($menus as $menuItem) {
    $menusPorCategoria[$menuItem['id_categoria']][] = $menuItem;
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pedidos-publico.css?v=<?= time() ?>">

<!-- Top Bar Removida (Se usa el Header Global) -->

<!-- Header Promocional -->
<div class="menu-hero text-center py-5 bg-dark text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(10, 43, 43, 0.9) 0%, rgba(10, 43, 43, 0.4) 100%), url('<?= BASE_URL ?>/assets/img/hero-menu-new.png') center/cover;">
    <div class="container position-relative z-1 py-4">
        <h1 class="display-4 fw-bold mb-3 font-monospace text-primary">NUESTRO MENÚ</h1>
        <p class="lead mb-0 text-light">Pide en línea y disfruta del mejor sabor.</p>
    </div>
</div>

<!-- Categorías -->
<nav class="sticky-top bg-body border-bottom shadow-sm py-3" style="z-index: 1020;">
    <div class="container">
        <ul class="nav nav-pills gap-2 flex-nowrap overflow-x-auto categories-scroll pb-2" id="menu-categories-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-uppercase px-4 rounded-pill text-nowrap" id="cat-todas-tab" data-bs-toggle="pill" data-bs-target="#cat-todas" type="button" role="tab">
                    <i class="fas fa-star me-2"></i>Todo
                </button>
            </li>
            <?php foreach ($categorias as $index => $cat): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-uppercase px-4 rounded-pill text-nowrap" id="cat-<?= $cat['id_categoria'] ?>-tab" data-bs-toggle="pill" data-bs-target="#cat-<?= $cat['id_categoria'] ?>" type="button" role="tab">
                    <?= htmlspecialchars($cat['nombre_categoria']) ?>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<main class="bg-body-tertiary py-5 min-vh-100">
    <div class="container">
        <div class="row">
            <!-- Sección Izquierda: Grilla de Productos -->
            <div class="col-lg-8 col-xl-9">
                <div class="tab-content" id="menu-categories-tabContent">
                    
                    <!-- PESTAÑA: TODAS -->
                    <div class="tab-pane fade show active" id="cat-todas" role="tabpanel">
                        <?php foreach ($categorias as $cat): ?>
                            <?php if (isset($menusPorCategoria[$cat['id_categoria']]) && count($menusPorCategoria[$cat['id_categoria']]) > 0): ?>
                                <div class="category-section mb-5">
                                    <h3 class="fw-bold mb-4 border-bottom border-primary border-3 pb-2 d-inline-block">
                                        <?= htmlspecialchars($cat['nombre_categoria']) ?>
                                    </h3>
                                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                                        <?php foreach ($menusPorCategoria[$cat['id_categoria']] as $p): 
                                            $imgUrl = ($p['imagen'] && $p['imagen'] !== 'default-product.png') ? BASE_URL . '/assets/img/productos/' . $p['imagen'] : BASE_URL . '/assets/img/placeholder.png';
                                        ?>
                                        <div class="col">
                                            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden hover-lift transition-all">
                                                <div class="ratio ratio-4x3 overflow-hidden bg-body-tertiary">
                                                    <img src="<?= $imgUrl ?>" class="card-img-top object-fit-cover transition-scale" alt="<?= htmlspecialchars($p['nombre_producto']) ?>" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/img/placeholder.png'">
                                                </div>
                                                <div class="card-body d-flex flex-column">
                                                    <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($p['nombre_producto']) ?></h5>
                                                    <p class="text-primary fw-bold fs-5 mb-2">$<?= number_format($p['precio'], 2) ?></p>
                                                    <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($p['descripcion'] ?: 'Delicioso platillo preparado al momento.') ?></p>
                                                    <?php if(isset($_SESSION['user'])): ?>
                                                    <button class="btn btn-outline-primary mt-3 rounded-pill fw-bold btn-add-product" data-id="<?= $p['id_producto'] ?>">
                                                        <i class="fas fa-plus me-1"></i> Añadir
                                                    </button>
                                                    <?php else: ?>
                                                    <a href="<?= BASE_URL ?>?page=login&msg=inicia-sesion" class="btn btn-outline-secondary mt-3 rounded-pill fw-bold">
                                                        <i class="fas fa-lock me-1"></i> Iniciar Sesión
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- PESTAÑAS INDIVIDUALES -->
                    <?php foreach ($categorias as $cat): ?>
                    <div class="tab-pane fade" id="cat-<?= $cat['id_categoria'] ?>" role="tabpanel">
                        <div class="category-section mb-5">
                            <h3 class="fw-bold mb-4 border-bottom border-primary border-3 pb-2 d-inline-block">
                                <?= htmlspecialchars($cat['nombre_categoria']) ?>
                            </h3>
                            <?php if (isset($menusPorCategoria[$cat['id_categoria']]) && count($menusPorCategoria[$cat['id_categoria']]) > 0): ?>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                                <?php foreach ($menusPorCategoria[$cat['id_categoria']] as $p): 
                                    $imgUrl = ($p['imagen'] && $p['imagen'] !== 'default-product.png') ? BASE_URL . '/assets/img/productos/' . $p['imagen'] : BASE_URL . '/assets/img/placeholder.png';
                                ?>
                                <div class="col">
                                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden hover-lift transition-all">
                                        <div class="ratio ratio-4x3 overflow-hidden bg-body-tertiary">
                                            <img src="<?= $imgUrl ?>" class="card-img-top object-fit-cover transition-scale" alt="<?= htmlspecialchars($p['nombre_producto']) ?>">
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($p['nombre_producto']) ?></h5>
                                            <p class="text-primary fw-bold fs-5 mb-2">$<?= number_format($p['precio'], 2) ?></p>
                                            <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($p['descripcion'] ?: 'Delicioso platillo preparado al momento.') ?></p>
                                            <button class="btn btn-outline-primary mt-3 rounded-pill fw-bold btn-add-product" data-id="<?= $p['id_producto'] ?>">
                                                <i class="fas fa-plus me-1"></i> Añadir
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-utensils text-muted fs-1 mb-3"></i>
                                <h4 class="text-muted">No hay productos en esta categoría.</h4>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sección Derecha: Carrito de Compras (Desktop) -->
            <div class="col-lg-4 col-xl-3 d-none d-lg-block">
                <div class="cart-sidebar" id="desktop-cart">
                    <div class="cart-header">
                        <i class="fas fa-shopping-basket me-2 text-primary"></i> Mi Pedido
                    </div>
                    <?php if(isset($_SESSION['user'])): ?>
                    <div class="cart-items" id="cart-items-container">
                        <!-- Items rendered via JS -->
                        <div class="text-center text-muted mt-5" id="empty-cart-msg">
                            <i class="fas fa-cart-arrow-down fs-1 mb-3"></i>
                            <p>Tu carrito está vacío</p>
                        </div>
                    </div>
                    <div class="cart-footer">
                        <div class="cart-total">
                            <span>Total</span>
                            <span id="cart-total-price">$0.00</span>
                        </div>
                        <button class="btn btn-primary w-100 py-3 fw-bold rounded-pill" id="btn-checkout" disabled>
                            Proceder al Pago
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="cart-items d-flex flex-column align-items-center justify-content-center text-center p-4">
                        <i class="fas fa-lock text-muted fs-1 mb-3 opacity-50"></i>
                        <h5 class="fw-bold text-muted mb-2">Carrito Bloqueado</h5>
                        <p class="small text-muted mb-4">Debes iniciar sesión para comenzar a armar tu pedido.</p>
                        <a href="<?= BASE_URL ?>?page=login&msg=inicia-sesion" class="btn btn-primary rounded-pill fw-bold w-100">
                            Iniciar Sesión
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Botón Flotante Carrito (Mobile) -->
<div class="fab-cart d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart">
    <i class="fas fa-shopping-basket"></i>
    <span class="badge" id="mobile-cart-badge">0</span>
</div>

<!-- Offcanvas Carrito (Mobile) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
  <div class="offcanvas-header bg-primary text-white">
    <h5 class="offcanvas-title fw-bold" id="offcanvasCartLabel"><i class="fas fa-shopping-basket me-2"></i> Mi Pedido</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column p-0">
    <?php if(isset($_SESSION['user'])): ?>
    <div class="cart-items flex-grow-1 p-3" id="mobile-cart-items-container">
        <!-- Rendered via JS -->
    </div>
    <div class="cart-footer p-3 border-top bg-body-tertiary">
        <div class="cart-total">
            <span>Total</span>
            <span id="mobile-cart-total-price">$0.00</span>
        </div>
        <button class="btn btn-primary w-100 py-3 fw-bold rounded-pill" id="btn-mobile-checkout" disabled>
            Proceder al Pago
        </button>
    </div>
    <?php else: ?>
    <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center p-4">
        <i class="fas fa-lock text-muted fs-1 mb-3 opacity-50"></i>
        <h5 class="fw-bold text-muted mb-2">Carrito Bloqueado</h5>
        <p class="small text-muted mb-4">Debes iniciar sesión para comenzar a armar tu pedido.</p>
        <a href="<?= BASE_URL ?>?page=login&msg=inicia-sesion" class="btn btn-primary rounded-pill fw-bold w-100">
            Iniciar Sesión
        </a>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Personalizar Producto -->
<div class="modal fade" id="modalPersonalizarPedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 position-absolute w-100 z-1" style="justify-content: flex-end;">
                <button type="button" class="btn-close bg-white rounded-circle p-2 shadow-sm m-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <img src="" id="modal-prod-img" class="modal-product-img rounded-top-4" alt="Producto">
                
                <div class="px-4 pb-4">
                    <h3 class="fw-bold mb-1" id="modal-prod-name">Nombre</h3>
                    <p class="text-muted small mb-3" id="modal-prod-desc">Descripción</p>
                    <h4 class="text-primary fw-bold mb-4" id="modal-prod-base-price">$0.00</h4>

                    <!-- Ingredientes Principales (Quitar) -->
                    <div id="container-principales" class="mb-4 d-none">
                        <h6 class="fw-bold bg-body-tertiary p-2 rounded">Ingredientes <span class="fw-normal text-muted fs-7">(Desmarca para quitar)</span></h6>
                        <div class="ingredient-list px-2" id="list-principales"></div>
                    </div>

                    <!-- Extras (Añadir) -->
                    <div id="container-extras" class="mb-4 d-none">
                        <h6 class="fw-bold bg-body-tertiary p-2 rounded">Añadir Extras <span class="fw-normal text-muted fs-7">(Costo adicional)</span></h6>
                        <div class="ingredient-list px-2" id="list-extras"></div>
                    </div>

                    <!-- Cantidad -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <span class="fw-bold">Cantidad:</span>
                        <div class="quantity-selector">
                            <button id="btn-qty-minus"><i class="fas fa-minus"></i></button>
                            <input type="number" id="input-qty" value="1" min="1" readonly>
                            <button id="btn-qty-plus"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-primary w-100 py-3 rounded-pill fw-bold d-flex justify-content-between align-items-center" id="btn-add-to-cart">
                    <span>Añadir al Pedido</span>
                    <span id="modal-total-price-btn">$0.00</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Checkout -->
<div class="modal fade" id="modalCheckout" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i> Finalizar Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formCheckout">
                    <div class="row g-4">
                        <!-- Datos Personales -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary">Tus Datos</h6>
                            <div class="mb-3">
                                <label class="form-label">Cédula / Documento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="cedula" id="chk-cedula" required placeholder="V-12345678">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre y Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre" id="chk-nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="telefono" id="chk-telefono" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tipo de Pedido <span class="text-danger">*</span></label>
                                <select class="form-select" name="tipo_pedido" id="chk-tipo-pedido" required>
                                    <option value="DELIVERY">Delivery</option>
                                    <option value="RETIRO">Retiro en el Local</option>
                                </select>
                            </div>

                            <div class="mb-3" id="box-direccion">
                                <label class="form-label" id="lbl-direccion">Dirección de Entrega <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="direccion" id="chk-direccion" rows="2" required></textarea>
                            </div>
                            <div class="mb-3" id="box-observacion">
                                <label class="form-label">Observaciones (Opcional)</label>
                                <textarea class="form-control" name="observacion" id="chk-observacion" rows="1"></textarea>
                            </div>
                        </div>

                        <!-- Datos de Pago -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary">Pago</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_metodo_pago" id="chk-metodo-pago" required>
                                    <option value="METOD00420260519200547232">Pago Móvil</option>
                                    <option value="METOD00120260519200547232">Efectivo (al entregar)</option>
                                    <option value="METOD00320260519200547232">Punto de Venta (al entregar)</option>
                                </select>
                            </div>

                            <div id="box-pago-movil">
                                <div class="pago-movil-box">
                                    <h6 class="fw-bold text-info mb-2"><i class="fas fa-mobile-alt me-2"></i>Pago Móvil</h6>
                                    <p class="mb-1 d-flex justify-content-between align-items-center">
                                        <span><strong>Banco:</strong> Banesco (0134)</span>
                                        <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('0134')"><i class="far fa-copy"></i></button>
                                    </p>
                                    <p class="mb-1 d-flex justify-content-between align-items-center">
                                        <span><strong>Teléfono:</strong> 0414-1234567</span>
                                        <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('04141234567')"><i class="far fa-copy"></i></button>
                                    </p>
                                    <p class="mb-0 d-flex justify-content-between align-items-center">
                                        <span><strong>RIF:</strong> J-12345678-9</span>
                                        <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('J123456789')"><i class="far fa-copy"></i></button>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Referencia de Pago <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="referencia" id="chk-referencia" required placeholder="Últimos 4 o 6 dígitos">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Comprobante (Captura) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="comprobante" id="chk-comprobante" accept="image/*" required>
                                </div>
                            </div>
                            
                            <div class="alert alert-info py-2 mt-3 text-center">
                                <span class="fw-bold">Total a Pagar:</span> <span class="fs-4 fw-bold ms-2" id="chk-total-display">$0.00</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-body-tertiary rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success rounded-pill fw-bold px-4" id="btn-submit-order">
                    <i class="fas fa-paper-plane me-2"></i> Enviar Pedido
                </button>
            </div>
        </div>
    </div>
</div>




<style>
    /* Scrollbar horizontal personalizado para escritorio */
    .categories-scroll::-webkit-scrollbar {
        height: 8px;
    }
    .categories-scroll::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.05); 
        border-radius: 10px;
    }
    .categories-scroll::-webkit-scrollbar-thumb {
        background: var(--brand-orange); 
        border-radius: 10px;
    }
    .categories-scroll::-webkit-scrollbar-thumb:hover {
        background: var(--brand-dark-orange); 
    }
    
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }
    .transition-all { transition: all .3s ease; }
    .transition-scale { transition: transform .5s ease; }
    .hover-lift:hover .transition-scale { transform: scale(1.05); }
    .fs-7 { font-size: 0.85rem; }
</style>
