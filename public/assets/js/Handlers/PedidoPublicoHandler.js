document.addEventListener('DOMContentLoaded', () => {
    let cart = JSON.parse(localStorage.getItem('gv_cart')) || [];
    let currentProduct = null;
    let qtyInput = document.getElementById('input-qty');
    const modalPersonalizar = new bootstrap.Modal(document.getElementById('modalPersonalizarPedido'));
    const modalCheckout = new bootstrap.Modal(document.getElementById('modalCheckout'));

    renderCart();

    // Event Listeners para Añadir Producto
    document.querySelectorAll('.btn-add-product').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const res = await PedidoPublicoController.buscarProducto(id);
            if (res.success && res.data) {
                openCustomizationModal(res.data);
            } else {
                alert('No se pudo cargar el producto.');
            }
        });
    });

    // Control de cantidad en modal
    document.getElementById('btn-qty-minus').addEventListener('click', () => {
        let q = parseInt(qtyInput.value);
        if (q > 1) {
            qtyInput.value = q - 1;
            updateModalPrice();
        }
    });

    document.getElementById('btn-qty-plus').addEventListener('click', () => {
        let q = parseInt(qtyInput.value);
        qtyInput.value = q + 1;
        updateModalPrice();
    });

    // Añadir al Carrito
    document.getElementById('btn-add-to-cart').addEventListener('click', () => {
        addToCart();
        modalPersonalizar.hide();
    });

    // Abrir Checkout
    document.getElementById('btn-checkout').addEventListener('click', () => {
        openCheckout();
    });
    document.getElementById('btn-mobile-checkout').addEventListener('click', () => {
        openCheckout();
    });

    // Enviar Pedido
    document.getElementById('btn-submit-order').addEventListener('click', submitOrder);

    // Dinamismo del Checkout
    const tipoPedidoSelect = document.getElementById('chk-tipo-pedido');
    const metodoPagoSelect = document.getElementById('chk-metodo-pago');
    const boxDireccion = document.getElementById('box-direccion');
    const chkDireccion = document.getElementById('chk-direccion');
    const boxPagoMovil = document.getElementById('box-pago-movil');
    const chkReferencia = document.getElementById('chk-referencia');
    const chkComprobante = document.getElementById('chk-comprobante');

    tipoPedidoSelect.addEventListener('change', () => {
        if (tipoPedidoSelect.value === 'DELIVERY') {
            boxDireccion.style.display = 'block';
            chkDireccion.required = true;
        } else {
            boxDireccion.style.display = 'none';
            chkDireccion.required = false;
        }
    });

    metodoPagoSelect.addEventListener('change', () => {
        // METOD00420260519200547232 es Pago Móvil
        if (metodoPagoSelect.value === 'METOD00420260519200547232') {
            boxPagoMovil.style.display = 'block';
            chkReferencia.required = true;
            chkComprobante.required = true;
        } else {
            boxPagoMovil.style.display = 'none';
            chkReferencia.required = false;
            chkComprobante.required = false;
            // Limpiar valores
            chkReferencia.value = '';
            chkComprobante.value = '';
        }
    });

    function openCustomizationModal(product) {
        currentProduct = product;
        qtyInput.value = 1;
        
        document.getElementById('modal-prod-name').textContent = product.nombre_producto;
        document.getElementById('modal-prod-desc').textContent = product.descripcion || '';
        document.getElementById('modal-prod-img').src = product.imagen && product.imagen !== 'default-product.png' 
            ? `${BASE_URL}/assets/img/productos/${product.imagen}` 
            : `${BASE_URL}/assets/img/placeholder.png`;

        // Ingredientes Principales
        const cPrincipales = document.getElementById('container-principales');
        const lPrincipales = document.getElementById('list-principales');
        lPrincipales.innerHTML = '';
        if (product.insumos_principales && product.insumos_principales.length > 0) {
            cPrincipales.classList.remove('d-none');
            product.insumos_principales.forEach(ins => {
                lPrincipales.innerHTML += `
                    <div class="form-check mb-2">
                        <input class="form-check-input principal-check" type="checkbox" value="${ins.id_insumo}" data-name="${ins.nombre_insumo}" checked id="princ_${ins.id_insumo}">
                        <label class="form-check-label" for="princ_${ins.id_insumo}">
                            ${ins.nombre_insumo}
                        </label>
                    </div>
                `;
            });
        } else {
            cPrincipales.classList.add('d-none');
        }

        // Extras
        const cExtras = document.getElementById('container-extras');
        const lExtras = document.getElementById('list-extras');
        lExtras.innerHTML = '';
        if (product.insumos_adicionales && product.insumos_adicionales.length > 0) {
            cExtras.classList.remove('d-none');
            product.insumos_adicionales.forEach(ins => {
                lExtras.innerHTML += `
                    <div class="form-check mb-2">
                        <input class="form-check-input extra-check" type="checkbox" value="${ins.id_insumo}" data-name="${ins.nombre_insumo}" data-price="${ins.precio_insumo}" id="extra_${ins.id_insumo}">
                        <label class="form-check-label d-flex justify-content-between w-100" for="extra_${ins.id_insumo}">
                            <span>${ins.nombre_insumo}</span>
                            <span class="text-primary fw-bold">+$${parseFloat(ins.precio_insumo).toFixed(2)}</span>
                        </label>
                    </div>
                `;
            });
        } else {
            cExtras.classList.add('d-none');
        }

        // Listeners for price update
        document.querySelectorAll('.extra-check').forEach(chk => {
            chk.addEventListener('change', updateModalPrice);
        });

        updateModalPrice();
        modalPersonalizar.show();
    }

    function updateModalPrice() {
        if (!currentProduct) return;
        let base = parseFloat(currentProduct.precio);
        let extras = 0;
        document.querySelectorAll('.extra-check:checked').forEach(chk => {
            extras += parseFloat(chk.getAttribute('data-price'));
        });
        
        let unitPrice = base + extras;
        let qty = parseInt(qtyInput.value) || 1;
        let total = unitPrice * qty;

        document.getElementById('modal-prod-base-price').textContent = `$${unitPrice.toFixed(2)} c/u`;
        document.getElementById('modal-total-price-btn').textContent = `$${total.toFixed(2)}`;
    }

    function addToCart() {
        let qty = parseInt(qtyInput.value) || 1;
        
        let removedPrincipales = [];
        document.querySelectorAll('.principal-check:not(:checked)').forEach(chk => {
            removedPrincipales.push({
                id_insumo: chk.value,
                nombre_insumo: chk.getAttribute('data-name')
            });
        });

        let addedAdicionales = [];
        let extraCost = 0;
        document.querySelectorAll('.extra-check:checked').forEach(chk => {
            let p = parseFloat(chk.getAttribute('data-price'));
            extraCost += p;
            addedAdicionales.push({
                id_insumo: chk.value,
                nombre_insumo: chk.getAttribute('data-name'),
                precio: p
            });
        });

        let unitPrice = parseFloat(currentProduct.precio) + extraCost;

        cart.push({
            cart_id: Date.now() + Math.random(),
            id_producto: currentProduct.id_producto,
            nombre: currentProduct.nombre_producto,
            cantidad: qty,
            precio_unitario: unitPrice,
            removedPrincipales: removedPrincipales,
            addedAdicionales: addedAdicionales
        });

        saveCart();
        renderCart();
    }

    function saveCart() {
        localStorage.setItem('gv_cart', JSON.stringify(cart));
    }

    function renderCart() {
        const cDesktop = document.getElementById('cart-items-container');
        const cMobile = document.getElementById('mobile-cart-items-container');
        const totalDesktop = document.getElementById('cart-total-price');
        const totalMobile = document.getElementById('mobile-cart-total-price');
        const btnDesktop = document.getElementById('btn-checkout');
        const btnMobile = document.getElementById('btn-mobile-checkout');
        const badge = document.getElementById('mobile-cart-badge');
        const emptyMsg = document.getElementById('empty-cart-msg');

        let html = '';
        let total = 0;
        let totalItems = 0;

        if (cart.length === 0) {
            if(emptyMsg) emptyMsg.style.display = 'block';
            btnDesktop.disabled = true;
            btnMobile.disabled = true;
        } else {
            if(emptyMsg) emptyMsg.style.display = 'none';
            btnDesktop.disabled = false;
            btnMobile.disabled = false;

            cart.forEach(item => {
                let subtotal = item.precio_unitario * item.cantidad;
                total += subtotal;
                totalItems += item.cantidad;

                let customHtml = '';
                if (item.removedPrincipales.length > 0) {
                    customHtml += `<div class="text-danger small">Sin: ${item.removedPrincipales.map(i=>i.nombre_insumo).join(', ')}</div>`;
                }
                if (item.addedAdicionales.length > 0) {
                    customHtml += `<div class="text-success small">Ext: ${item.addedAdicionales.map(i=>i.nombre_insumo).join(', ')}</div>`;
                }

                html += `
                    <div class="cart-item">
                        <div class="cart-item-details">
                            <h6>${item.cantidad}x ${item.nombre}</h6>
                            <div class="cart-item-customizations">${customHtml}</div>
                            <div class="cart-item-price">$${subtotal.toFixed(2)}</div>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-danger btn-remove-item" data-cartid="${item.cart_id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        cDesktop.innerHTML = cart.length === 0 ? '<div class="text-center text-muted mt-5"><i class="fas fa-cart-arrow-down fs-1 mb-3"></i><p>Tu carrito está vacío</p></div>' : html;
        cMobile.innerHTML = cDesktop.innerHTML;
        
        let tStr = `$${total.toFixed(2)}`;
        totalDesktop.textContent = tStr;
        totalMobile.textContent = tStr;
        badge.textContent = totalItems;

        document.querySelectorAll('.btn-remove-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                let cid = e.currentTarget.getAttribute('data-cartid');
                cart = cart.filter(i => i.cart_id != cid);
                saveCart();
                renderCart();
            });
        });
    }

    function openCheckout() {
        let total = cart.reduce((acc, item) => acc + (item.precio_unitario * item.cantidad), 0);
        document.getElementById('chk-total-display').textContent = `$${total.toFixed(2)}`;
        modalCheckout.show();
    }

    async function submitOrder() {
        const form = document.getElementById('formCheckout');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        let total = cart.reduce((acc, item) => acc + (item.precio_unitario * item.cantidad), 0);
        
        const formData = new FormData(form);
        formData.append('carrito', JSON.stringify({
            items: cart,
            total: total
        }));

        document.getElementById('btn-submit-order').disabled = true;
        document.getElementById('btn-submit-order').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando...';

        const res = await PedidoPublicoController.enviarPedido(formData);

        document.getElementById('btn-submit-order').disabled = false;
        document.getElementById('btn-submit-order').innerHTML = '<i class="fas fa-paper-plane me-2"></i> Enviar Pedido';

        if (res.success) {
            alert('¡Pedido enviado exitosamente!');
            cart = [];
            saveCart();
            renderCart();
            modalCheckout.hide();
            form.reset();
        } else {
            alert(res.message || 'Error al procesar el pedido.');
        }
    }
});
