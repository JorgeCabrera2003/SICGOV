document.addEventListener('DOMContentLoaded', async () => {
    let cart = JSON.parse(localStorage.getItem('gv_cart')) || [];
    let currentProduct = null;
    let qtyInput = document.getElementById('input-qty');
    const modalPersonalizar = new bootstrap.Modal(document.getElementById('modalPersonalizarPedido'));
    const modalCheckout = new bootstrap.Modal(document.getElementById('modalCheckout'));
    let tasaCambio = 60; // Fallback

    await obtenerTasaCambio();
    renderCart();

    async function obtenerTasaCambio() {
        try {
            const response = await fetch('https://pydolarve.org/api/v1/dollar?page=bcv');
            const data = await response.json();
            if (data && data.monitors && data.monitors.usd) {
                tasaCambio = data.monitors.usd.price || 60;
                console.log('Tasa USD/VES obtenida:', tasaCambio);
            }
        } catch (error) {
            console.warn('Error al obtener tasa, usando fallback:', tasaCambio);
        }
    }

    // Event Listeners para Añadir Producto
    document.querySelectorAll('.btn-add-product').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const res = await PedidoPublicoController.buscarProducto(id);
            if (res.success && res.data) {
                openCustomizationModal(res.data);
            } else {
                Swal.fire('Error', 'No se pudo cargar el producto.', 'error');
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

    document.getElementById('btn-qty-plus').addEventListener('click', async (e) => {
        let btn = e.currentTarget;
        btn.disabled = true;
        let q = parseInt(qtyInput.value);
        let newQty = q + 1;
        await validateAndSetModalQty(newQty, btn);
    });

    qtyInput.addEventListener('change', async (e) => {
        let q = parseInt(qtyInput.value) || 1;
        if (q < 1) q = 1;
        qtyInput.disabled = true;
        await validateAndSetModalQty(q, qtyInput, true);
    });

    async function validateAndSetModalQty(newQty, el, isManual = false) {
        // Verificar stock antes de permitir incrementar
        let cantidadEnCarrito = 0;
        cart.forEach(item => {
            if (item.id_producto === currentProduct.id_producto) {
                cantidadEnCarrito += item.cantidad;
            }
        });
        const cantidadAVerificar = cantidadEnCarrito + newQty;

        try {
            const formData = new FormData();
            formData.append('action', 'verificar_stock');
            formData.append('id_producto', currentProduct.id_producto);
            formData.append('cantidad', cantidadAVerificar);
            
            const res = await fetch('?page=PedidoPublico', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (!data.success) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock insuficiente',
                    text: `${data.message} (Stock disponible: ${data.stock_disponible || 0})`,
                    confirmButtonText: 'Entendido'
                });
                if (isManual) {
                    qtyInput.value = data.stock_disponible || 1; // max available
                }
                if (el) el.disabled = false;
                updateModalPrice();
                return;
            }
        } catch (error) {
            console.error('Error verificando stock:', error);
            Swal.fire('Error', 'No se pudo verificar el stock del producto.', 'error');
            if (isManual) qtyInput.value = 1;
            if (el) el.disabled = false;
            updateModalPrice();
            return;
        }

        qtyInput.value = newQty;
        updateModalPrice();
        if (el) el.disabled = false;
    }

    // Añadir al Carrito
    document.getElementById('btn-add-to-cart').addEventListener('click', async () => {
        const btn = document.getElementById('btn-add-to-cart');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Añadiendo...';
        
        const added = await addToCart();
        
        if (added) {
            modalPersonalizar.hide();
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style = '';
            }, 300);
        }
        
        btn.disabled = false;
        btn.innerHTML = originalHtml;
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

    async function addToCart() {
        let qty = parseInt(qtyInput.value) || 1;
        
        let cantidadEnCarrito = 0;
        cart.forEach(item => {
            if (item.id_producto === currentProduct.id_producto) {
                cantidadEnCarrito += item.cantidad;
            }
        });
        const cantidadAVerificar = cantidadEnCarrito + qty;

        try {
            const formData = new FormData();
            formData.append('action', 'verificar_stock');
            formData.append('id_producto', currentProduct.id_producto);
            formData.append('cantidad', cantidadAVerificar);
            
            const res = await fetch('?page=PedidoPublico', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (!data.success) {
                alert(`Stock insuficiente:\n${data.message}\nStock disponible: ${data.stock_disponible || 0}`);
                return false;
            }
        } catch (error) {
            console.error('Error verificando stock:', error);
            alert('No se pudo verificar el stock del producto.');
            return false;
        }
        
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
        return true;
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
                let subtotalBs = subtotal * tasaCambio;
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
                    <div class="cart-item position-relative mb-3 border-bottom pb-2">
                        <div class="cart-item-details w-100">
                            <h6 class="mb-1">${item.nombre}</h6>
                            <div class="cart-item-customizations mb-2">${customHtml}</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 shadow-sm border border-secondary-subtle">
                                    <button class="btn btn-sm btn-link text-warning btn-qty-change p-0 m-0 text-decoration-none d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 50%;" data-cartid="${item.cart_id}" data-action="minus">
                                        <i class="fas fa-minus-circle fs-5"></i>
                                    </button>
                                    <input type="number" class="form-control text-center px-1 fw-bold border-0 bg-transparent cart-qty-input text-dark" style="width: 45px; -moz-appearance: textfield; box-shadow: none;" data-cartid="${item.cart_id}" value="${item.cantidad}" min="1">
                                    <button class="btn btn-sm btn-link text-warning btn-qty-change p-0 m-0 text-decoration-none d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 50%;" data-cartid="${item.cart_id}" data-action="plus">
                                        <i class="fas fa-plus-circle fs-5"></i>
                                    </button>
                                </div>
                                <div class="text-end">
                                    <div class="text-primary fw-bold fs-5">$${subtotal.toFixed(2)}</div>
                                    <div class="text-muted small">Bs. ${subtotalBs.toFixed(2)}</div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger btn-remove-item position-absolute top-0 end-0 rounded-circle shadow-sm" style="padding: 4px 6px; transform: translate(25%, -25%); background: white;" data-cartid="${item.cart_id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
            });
        }

        cDesktop.innerHTML = cart.length === 0 ? '<div class="text-center text-muted mt-5"><i class="fas fa-cart-arrow-down fs-1 mb-3"></i><p>Tu carrito está vacío</p></div>' : html;
        cMobile.innerHTML = cDesktop.innerHTML;
        
        let totalBs = total * tasaCambio;
        let tStr = `<span class="fs-4">$${total.toFixed(2)}</span> <br><small class="text-muted" style="font-size:0.65em;">Bs. ${totalBs.toFixed(2)}</small>`;
        totalDesktop.innerHTML = tStr;
        totalMobile.innerHTML = tStr;
        badge.textContent = totalItems;

        document.querySelectorAll('.btn-remove-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                let cid = e.currentTarget.getAttribute('data-cartid');
                cart = cart.filter(i => i.cart_id != cid);
                saveCart();
                renderCart();
            });
        });

        document.querySelectorAll('.btn-qty-change').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                let btnEl = e.currentTarget;
                btnEl.disabled = true;
                let cid = btnEl.getAttribute('data-cartid');
                let action = btnEl.getAttribute('data-action');
                await changeCartItemQuantity(cid, action, btnEl);
            });
        });

        document.querySelectorAll('.cart-qty-input').forEach(input => {
            input.addEventListener('change', async (e) => {
                let inputEl = e.currentTarget;
                inputEl.disabled = true;
                let cid = inputEl.getAttribute('data-cartid');
                let q = parseInt(inputEl.value) || 1;
                if (q < 1) q = 1;
                await setManualCartItemQuantity(cid, q, inputEl);
            });
        });
    }

    async function setManualCartItemQuantity(cart_id, newQty, inputEl) {
        let itemIndex = cart.findIndex(i => i.cart_id == cart_id);
        if (itemIndex === -1) {
            if (inputEl) inputEl.disabled = false;
            return;
        }
        
        let item = cart[itemIndex];
        
        // Verificar stock de la diferencia si se está aumentando, 
        // o mejor, mandar el total que se quiere como cantidadAVerificar.
        let cantidadEnCarritoExcluyendoEste = 0;
        cart.forEach(i => {
            if (i.id_producto === item.id_producto && i.cart_id != cart_id) {
                cantidadEnCarritoExcluyendoEste += i.cantidad;
            }
        });
        const cantidadAVerificar = cantidadEnCarritoExcluyendoEste + newQty;

        try {
            const formData = new FormData();
            formData.append('action', 'verificar_stock');
            formData.append('id_producto', item.id_producto);
            formData.append('cantidad', cantidadAVerificar);
            
            const res = await fetch('?page=PedidoPublico', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (!data.success) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock insuficiente',
                    text: `${data.message} (Stock disponible: ${data.stock_disponible || 0})`,
                    confirmButtonText: 'Entendido'
                });
                // Revert or set max possible
                let maxAllowed = (data.stock_disponible || 0) - cantidadEnCarritoExcluyendoEste;
                if (maxAllowed < 1) {
                    cart.splice(itemIndex, 1);
                } else {
                    item.cantidad = maxAllowed;
                }
                saveCart();
                renderCart();
                return;
            }
        } catch (error) {
            console.error('Error verificando stock:', error);
            Swal.fire('Error', 'No se pudo verificar el stock del producto.', 'error');
            if (inputEl) inputEl.disabled = false;
            renderCart(); // revert visuals
            return;
        }

        item.cantidad = newQty;
        saveCart();
        renderCart();
    }

    async function changeCartItemQuantity(cart_id, action, btnEl) {
        let itemIndex = cart.findIndex(i => i.cart_id == cart_id);
        if (itemIndex === -1) {
            if(btnEl) btnEl.disabled = false;
            return;
        }
        
        let item = cart[itemIndex];
        let newQty = action === 'plus' ? item.cantidad + 1 : item.cantidad - 1;
        
        if (newQty < 1) {
            cart.splice(itemIndex, 1);
            saveCart();
            renderCart();
            return;
        }

        if (action === 'plus') {
            // Check stock again
            let cantidadEnCarrito = 0;
            cart.forEach(i => {
                if (i.id_producto === item.id_producto) {
                    cantidadEnCarrito += i.cantidad;
                }
            });
            const cantidadAVerificar = cantidadEnCarrito + 1;

            try {
                const formData = new FormData();
                formData.append('action', 'verificar_stock');
                formData.append('id_producto', item.id_producto);
                formData.append('cantidad', cantidadAVerificar);
                
                const res = await fetch('?page=PedidoPublico', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (!data.success) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock insuficiente',
                        text: `${data.message} (Stock disponible: ${data.stock_disponible || 0})`,
                        confirmButtonText: 'Entendido'
                    });
                    if(btnEl) btnEl.disabled = false;
                    return;
                }
            } catch (error) {
                console.error('Error verificando stock:', error);
                Swal.fire('Error', 'No se pudo verificar el stock del producto.', 'error');
                if(btnEl) btnEl.disabled = false;
                return;
            }
        }

        item.cantidad = newQty;
        saveCart();
        renderCart();
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
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '¡Pedido enviado exitosamente!',
                confirmButtonText: 'Aceptar'
            });
            cart = [];
            saveCart();
            renderCart();
            modalCheckout.hide();
            form.reset();
        } else {
            Swal.fire('Error', res.message || 'Error al procesar el pedido.', 'error');
        }
    }
});
