// assets/js/Handlers/PosHandler.js

let posCart = [];
let productosDB = [];
let productoActual = null;
let insumosPrincipales = [];
let insumosAdicionales = [];
let tasaCambioPos = 60; // Tasa de cambio para el POS (fallback)
window.extrasSeleccionados = [];
window.removidosSeleccionados = [];

async function obtenerTasaCambioPos() {
    try {
        const response = await fetch('https://api.exchangerate-api.com/v4/latest/USD');
        if (response.ok) {
            const data = await response.json();
            tasaCambioPos = data.rates.VES || 60;
            console.log('Tasa USD/VES para POS:', tasaCambioPos);
        } else {
            console.warn('Error al obtener tasa, usando fallback:', tasaCambioPos);
        }
    } catch (error) {
        console.error('Error al obtener tasa:', error);
    }
}


document.addEventListener('DOMContentLoaded', () => {
    cargarProductosPOS();

    // Filtros de categorías
    const filtros = document.getElementById('posFiltros').querySelectorAll('button');
    filtros.forEach(btn => {
        btn.addEventListener('click', (e) => {
            filtros.forEach(f => f.classList.remove('active'));
            e.target.classList.add('active');
            renderProductos(e.target.dataset.cat);
        });
    });

    // Tipo de Pedido change
    const selectTipo = document.getElementById('posTipoPedido');
    const boxMesa = document.getElementById('boxMesa');
    if (selectTipo) {
        selectTipo.addEventListener('change', () => {
            if (selectTipo.value === 'MESA') {
                boxMesa.style.display = 'block';
                document.getElementById('posMesa').required = true;
                cargarMesasDisponibles();
            } else {
                boxMesa.style.display = 'none';
                document.getElementById('posMesa').required = false;
                document.getElementById('posMesa').value = '';
            }
        });
    }

    // Confirmar personalización
    document.getElementById('btnConfirmarPersonalizar').addEventListener('click', confirmarPersonalizacion);

    // Cobrar
    document.getElementById('btnPosCobrar').addEventListener('click', procesarCobro);

    // ==============================================
    // AL ABRIR MODAL POS - OBTENER TASA
    // ==============================================
    const modalPOS = document.getElementById('modalPOS');
    if (modalPOS) {
        modalPOS.addEventListener('show.bs.modal', async () => {
            await obtenerTasaCambioPos();
            // Si el tipo por defecto es MESA, cargar mesas
            if (document.getElementById('posTipoPedido').value === 'MESA') {
                await cargarMesasDisponibles();
            }
        });
    }
});

async function cargarProductosPOS() {
    const formData = new FormData();
    formData.append('action', 'listar');
    
    try {
        const res = await fetch('?page=Menu', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.data) {
            productosDB = data.data;
            renderProductos('todas');
        }
    } catch (e) {
        console.error("Error cargando menú para POS:", e);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los productos del menú',
            confirmButtonColor: '#d33'
        });
    }
}

function actualizarPrecioPersonalizado() {
    let total = parseFloat(document.getElementById('productoPrecioBase').value) || 0;
    
    // Sumar el precio de los extras seleccionados
    document.querySelectorAll('#listaAdicionales .form-check-input:checked').forEach(cb => {
        total += parseFloat(cb.dataset.precio) || 0;
    });
    
    // Actualizar el total mostrado
    document.getElementById('precioPersonalizadoTotal').textContent = `$${total.toFixed(2)}`;
}

function renderProductos(categoriaId) {
    const grid = document.getElementById('posProductos');
    if (!grid) return;
    
    grid.innerHTML = '';
    
    let filtrados = productosDB;
    if (categoriaId !== 'todas') {
        filtrados = productosDB.filter(p => p.id_categoria == categoriaId);
    }

    if (filtrados.length === 0) {
        grid.innerHTML = '<div class="text-muted w-100 p-3">No hay productos en esta categoría.</div>';
        return;
    }

    filtrados.forEach(p => {
        const div = document.createElement('div');
        div.className = 'card pos-card';
        div.onclick = () => seleccionarProducto(p);
        
        div.innerHTML = `
                <img src="/SICGOV/public/assets/img/productos/${p.imagen || 'default-product.png'}" 
                    class="card-img-top pos-card__img" alt="${p.nombre_producto}" 
                    onerror="this.src='/SICGOV/public/assets/img/logo.png'">
                <div class="card-body p-2 text-center">
                    <h6 class="card-title text-truncate mb-1" style="font-size:0.9rem;">${escapeHtml(p.nombre_producto)}</h6>
                    <span class="text-success fw-bold">$${parseFloat(p.precio).toFixed(2)}</span>
                    
                    ${p.tipo_producto === 'BARRA' 
                        ? '<span class="badge bg-info text-white mt-1 d-block">🍸 Barra</span>' 
                        : p.tipo_producto === 'POSTRE' 
                            ? '<span class="badge bg-warning text-dark mt-1 d-block">🍰 Postre</span>'
                            : p.es_personalizable 
                                ? '<span class="badge bg-warning text-dark mt-1 d-block">Personalizable</span>' 
                                : ''}
                    
                </div>
            `;
        grid.appendChild(div);
    });
}

async function seleccionarProducto(producto) {
    productoActual = producto;
    
    // Si no es personalizable, verificar stock y agregar
    if (!producto.es_personalizable) {
        await agregarAlCarritoConVerificacion(productoActual, [], []);
        return;
    }
    
    if (producto.tipo_producto === 'BARRA') {
        await agregarAlCarritoConVerificacion(productoActual, [], [], parseFloat(productoActual.precio), '');
        return;
    }

    // Cargar insumos del producto
    Swal.fire({
        title: 'Cargando...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    try {
        const formData = new FormData();
        formData.append('action', 'obtener_insumos');
        formData.append('id_producto', producto.id_producto);
        
        const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
        const data = await res.json();
        
        Swal.close();
        
        if (data.success) {
            insumosPrincipales = data.data.principales || [];
            insumosAdicionales = data.data.adicionales || [];
            extrasSeleccionados = [];
            removidosSeleccionados = [];
            
            mostrarModalPersonalizacion();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar las opciones de personalización',
                confirmButtonColor: '#d33'
            });
        }
    } catch (error) {
        Swal.close();
        console.error('Error cargando insumos:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al cargar las opciones',
            confirmButtonColor: '#d33'
        });
    }
}

function mostrarModalPersonalizacion() {
    document.getElementById('productoNombre').innerHTML = `<i class="fas fa-utensils me-2"></i>${escapeHtml(productoActual.nombre_producto)}`;
    document.getElementById('productoId').value = productoActual.id_producto;
    document.getElementById('productoPrecioBase').value = productoActual.precio;
    
    // Inicializar precio total
    document.getElementById('precioPersonalizadoTotal').textContent = `$${parseFloat(productoActual.precio).toFixed(2)}`;
    
    extrasSeleccionados = [];
    removidosSeleccionados = [];
    
    // Mostrar insumos principales (con checkbox, marcados por defecto)
    const listaPrincipales = document.getElementById('listaPrincipales');
    if (insumosPrincipales.length > 0) {
        let html = '';
        insumosPrincipales.forEach((insumo, index) => {
            html += `
                <div class="form-check border-bottom py-1">
                    <input class="form-check-input" type="checkbox" id="principal_${index}" 
                           value="${insumo.id_insumo}" data-nombre="${escapeHtml(insumo.nombre_insumo)}" checked
                           onchange="actualizarPrecioPersonalizado()">
                    <label class="form-check-label" for="principal_${index}">
                        ${escapeHtml(insumo.nombre_insumo)} <small class="text-muted">(${insumo.cantidad} ${insumo.nombre_unidad})</small>
                    </label>
                </div>
            `;
        });
        listaPrincipales.innerHTML = html;
    } else {
        listaPrincipales.innerHTML = '<div class="text-muted text-center p-2">No hay ingredientes configurados</div>';
    }
    
    // Mostrar insumos adicionales (extras con checkbox sin marcar)
    const listaAdicionales = document.getElementById('listaAdicionales');
    if (insumosAdicionales.length > 0) {
        let html = '';
        insumosAdicionales.forEach((insumo, index) => {
            const precioExtra = parseFloat(insumo.precio_insumo || 0);
            html += `
                <div class="form-check border-bottom py-1">
                    <input class="form-check-input" type="checkbox" id="extra_${index}" 
                           value="${insumo.id_insumo}" data-precio="${precioExtra}"
                           data-nombre="${escapeHtml(insumo.nombre_insumo)}"
                           onchange="actualizarPrecioPersonalizado()">
                    <label class="form-check-label d-flex justify-content-between w-100" for="extra_${index}">
                        <span>${escapeHtml(insumo.nombre_insumo)} <small class="text-muted">(${insumo.cantidad} ${insumo.nombre_unidad})</small></span>
                        <span class="text-warning fw-bold">${precioExtra > 0 ? '+$' + precioExtra.toFixed(2) : 'Gratis'}</span>
                    </label>
                </div>
            `;
        });
        listaAdicionales.innerHTML = html;
    } else {
        listaAdicionales.innerHTML = '<div class="text-muted text-center p-2">No hay extras disponibles</div>';
    }
    
    // Inicializar precio total
    actualizarPrecioPersonalizado();
    
    const modal = new bootstrap.Modal(document.getElementById('modalPersonalizar'));
    modal.show();
}


function togglePrincipal(checkbox) {
    const idInsumo = checkbox.value;
    const nombreInsumo = checkbox.dataset.nombre;
    
    if (!checkbox.checked) {
        if (!window.removidosSeleccionados) window.removidosSeleccionados = [];
        if (!window.removidosSeleccionados.find(r => r.id_insumo === idInsumo)) {
            window.removidosSeleccionados.push({
                id_insumo: idInsumo,
                nombre: nombreInsumo
            });
        }
    } else {
        window.removidosSeleccionados = window.removidosSeleccionados.filter(r => r.id_insumo !== idInsumo);
    }
    
    // Actualizar precio al quitar/marcar ingredientes principales
    actualizarPrecioPersonalizado();
}

// SOLO UNA FUNCIÓN toggleExtra (elimina la duplicada)
function toggleExtra(checkbox) {
    const precio = parseFloat(checkbox.dataset.precio || 0);
    const idInsumo = checkbox.value;
    const nombreInsumo = checkbox.dataset.nombre;
    
    if (checkbox.checked) {
        if (!window.extrasSeleccionados) window.extrasSeleccionados = [];
        window.extrasSeleccionados.push({
            id_insumo: idInsumo,
            nombre: nombreInsumo,
            precio: precio
        });
    } else {
        window.extrasSeleccionados = window.extrasSeleccionados.filter(e => e.id_insumo !== idInsumo);
    }
    
    // Actualizar precio al marcar/desmarcar extras
    actualizarPrecioPersonalizado();
}

function confirmarPersonalizacion() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalPersonalizar'));
    modal.hide();
    
    // Obtener ingredientes removidos
    removidosSeleccionados = [];
    if (insumosPrincipales.length > 0) {
        for (let i = 0; i < insumosPrincipales.length; i++) {
            const checkbox = document.getElementById(`principal_${i}`);
            if (checkbox && !checkbox.checked) {
                removidosSeleccionados.push({
                    id_insumo: insumosPrincipales[i].id_insumo,
                    nombre: insumosPrincipales[i].nombre_insumo
                });
            }
        }
    }
    
    // Obtener extras seleccionados
    extrasSeleccionados = [];
    const checkboxesExtras = document.querySelectorAll('#listaAdicionales .form-check-input:checked');
    checkboxesExtras.forEach(checkbox => {
        const precio = parseFloat(checkbox.dataset.precio) || 0;
        extrasSeleccionados.push({
            id_insumo: checkbox.value,
            nombre: checkbox.dataset.nombre,
            precio: precio
        });
    });
    
    // Calcular precio final
    const precioFinal = parseFloat(document.getElementById('precioPersonalizadoTotal').textContent.replace('$', ''));
    const precioBase = parseFloat(document.getElementById('productoPrecioBase').value) || 0;
    
    // Construir indicación
    const extrasTexto = extrasSeleccionados.map(e => e.nombre);
    const sinTexto = removidosSeleccionados.map(r => r.nombre);
    
    let indicacion = '';
    if (sinTexto.length > 0) {
        indicacion += `Sin: ${sinTexto.join(', ')}. `;
    }
    if (extrasTexto.length > 0) {
        indicacion += `Extras: ${extrasTexto.join(', ')}. `;
    }
    indicacion = indicacion.trim();
    
    // Verificar stock antes de agregar
    agregarAlCarritoConVerificacion(productoActual, extrasSeleccionados, removidosSeleccionados, precioFinal, indicacion);
}


async function agregarAlCarritoConVerificacion(producto, extras = [], removidos = [], precioPersonalizado = null, indicacion = '') {
    // Verificar stock antes de agregar
    try {
        let cantidadEnCarrito = 0;
        posCart.forEach(item => {
            if (item.id_producto === producto.id_producto) {
                cantidadEnCarrito += item.cantidad;
            }
        });
        const cantidadAVerificar = cantidadEnCarrito + 1;

        const formData = new FormData();
        formData.append('action', 'verificar_stock');
        formData.append('id_producto', producto.id_producto);
        formData.append('cantidad', cantidadAVerificar);
        
        const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (!data.success) {
            Swal.fire({
                icon: 'error',
                title: 'Stock insuficiente',
                html: `
                    ${data.message}<br><br>
                    <strong>Stock disponible:</strong> ${data.stock_disponible} unidades<br>
                    <strong>Porcentaje de stock:</strong> ${Number(parseFloat(data.porcentaje || 0).toFixed(2))}%
                `,
                confirmButtonColor: '#d33'
            });
            return false;
        }
        
        // Si hay stock, agregar al carrito
        agregarAlCarrito(producto, extras, removidos, precioPersonalizado, indicacion);
        return true;
        
    } catch (error) {
        console.error('Error verificando stock:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo verificar el stock del producto',
            confirmButtonColor: '#d33'
        });
        return false;
    }
}


function agregarAlCarrito(producto, extras = [], removidos = [], precioPersonalizado = null, indicacion = '') {
    const index = posCart.findIndex(item => 
        item.id_producto === producto.id_producto && 
        JSON.stringify(item.extras) === JSON.stringify(extras) &&
        JSON.stringify(item.removidos) === JSON.stringify(removidos)
    );
    
    const precioUnitario = precioPersonalizado !== null ? precioPersonalizado : parseFloat(producto.precio);
    
    if (index >= 0) {
        posCart[index].cantidad += 1;
    } else {
        posCart.push({
            id_producto: producto.id_producto,
            nombre: producto.nombre_producto,
            precio_unitario: precioUnitario,
            precio_base: parseFloat(producto.precio),
            cantidad: 1,
            extras: extras,
            removidos: removidos,
            indicacion: indicacion,
            tipo_producto: producto.tipo_producto || 'COCINA' // <-- NUEVO
        });
    }
    
    renderCarrito();
}

async function actualizarCantidad(index, delta) {
    const item = posCart[index];
    if (!item) return;
    
    const nuevaCantidad = item.cantidad + delta;
    
    // Si es menor o igual a 0, eliminar
    if (nuevaCantidad <= 0) {
        posCart.splice(index, 1);
        renderCarrito();
        return;
    }
    
    // Si está sumando (+), verificar stock
    if (delta > 0) {
        try {
            let cantidadEnCarrito = 0;
            posCart.forEach((cartItem, i) => {
                if (cartItem.id_producto === item.id_producto) {
                    cantidadEnCarrito += (i === index) ? nuevaCantidad : cartItem.cantidad;
                }
            });

            const formData = new FormData();
            formData.append('action', 'verificar_stock');
            formData.append('id_producto', item.id_producto);
            formData.append('cantidad', cantidadEnCarrito);
            
            const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Stock insuficiente',
                    html: `${data.message}<br><br><strong>Stock disponible:</strong> ${data.stock_disponible} unidades`,
                    confirmButtonColor: '#d33'
                });
                return;
            }
        } catch (error) {
            console.error('Error verificando stock:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo verificar el stock',
                confirmButtonColor: '#d33'
            });
            return;
        }
    }
    
    // Si pasa la verificación, actualizar cantidad
    posCart[index].cantidad = nuevaCantidad;
    renderCarrito();
}

function renderCarrito() {
    const container = document.getElementById('posCartItems');
    const badge = document.getElementById('posCount');
    const labelTotal = document.getElementById('posTotal');
    const labelTotalBs = document.getElementById('posTotalBs');
    const btnCobrar = document.getElementById('btnPosCobrar');

    if (!container) return;
    
    container.innerHTML = '';
    let total = 0;
    let count = 0;

    if (posCart.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted mt-5" id="posEmptyCart">
                <i class="fas fa-shopping-basket fs-1 mb-2"></i>
                <p>No hay productos en la orden</p>
            </div>
        `;
        if (badge) badge.innerText = '0';
        if (labelTotal) labelTotal.innerText = '$0.00';
        if (labelTotalBs) labelTotalBs.innerText = 'Bs 0.00';
        if (btnCobrar) btnCobrar.disabled = true;
        return;
    }

    posCart.forEach((item, index) => {
        const subtotal = item.precio_unitario * item.cantidad;
        total += subtotal;
        count += item.cantidad;

        // Mostrar extras si tiene
        let extrasHtml = '';
        if (item.extras && item.extras.length > 0) {
            extrasHtml = `<div class="small text-muted mt-1">
                            <i class="fas fa-plus-circle"></i> Extras: ${item.extras.map(e => e.nombre).join(', ')}
                          </div>`;
        }

        // Mostrar removidos si tiene
        let removidosHtml = '';
        if (item.removidos && item.removidos.length > 0) {
            removidosHtml = `<div class="small text-danger mt-1">
                                <i class="fas fa-ban"></i> Sin: ${item.removidos.map(r => r.nombre).join(', ')}
                            </div>`;
        }

        const div = document.createElement('div');
        div.className = 'pos-item';
        div.innerHTML = `
            <div class="flex-grow-1">
                <div class="fw-bold text-truncate" style="max-width:180px;">${escapeHtml(item.nombre)}</div>
                ${extrasHtml}
                ${removidosHtml}
                <div class="text-success fw-semibold">$${subtotal.toFixed(2)}</div>
            </div>
            <div class="pos-item__controls">
                <button class="btn btn-outline-danger pos-item__btn" onclick="actualizarCantidad(${index}, -1)"><i class="fas fa-minus fs-7"></i></button>
                <span class="fw-bold px-1">${item.cantidad}</span>
                <button class="btn btn-outline-primary pos-item__btn" onclick="actualizarCantidad(${index}, 1)"><i class="fas fa-plus fs-7"></i></button>
            </div>
        `;
        container.appendChild(div);
    });

    if (badge) badge.innerText = count;
    if (labelTotal) labelTotal.innerText = `$${total.toFixed(2)}`;
    
    // ==============================================
    // CALCULAR Y MOSTRAR TOTAL EN BS
    // ==============================================
    if (labelTotalBs) {
        const totalBs = total * tasaCambioPos;
        labelTotalBs.innerText = `Bs ${totalBs.toFixed(2)}`;
    }
    
    if (btnCobrar) btnCobrar.disabled = false;
}

async function procesarCobro() {
    if (posCart.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Carrito vacío',
            text: 'No hay productos en el pedido',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

Swal.fire({
        title: 'Verificando stock...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    let stockOk = true;
    let erroresStock = [];
    
    // Agrupar cantidades por producto para validar correctamente
    const totalesPorProducto = {};
    posCart.forEach(item => {
        if (!totalesPorProducto[item.id_producto]) {
            totalesPorProducto[item.id_producto] = { nombre: item.nombre, cantidad: 0 };
        }
        totalesPorProducto[item.id_producto].cantidad += item.cantidad;
    });
    
    for (const [id_producto, prodData] of Object.entries(totalesPorProducto)) {
        try {
            const formData = new FormData();
            formData.append('action', 'verificar_stock');
            formData.append('id_producto', id_producto);
            formData.append('cantidad', prodData.cantidad);
            
            const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (!data.success) {
                stockOk = false;
                erroresStock.push(`- ${prodData.nombre}: ${data.message}`);
            }
        } catch (error) {
            console.error('Error verificando stock:', error);
            stockOk = false;
            erroresStock.push(`- ${prodData.nombre}: Error al verificar stock`);
        }
    }
    
    Swal.close();
    
    if (!stockOk) {
        Swal.fire({
            icon: 'error',
            title: 'Stock insuficiente',
            html: `
                <div class="text-start">
                    <p>No se puede procesar el pedido por los siguientes problemas:</p>
                    <ul class="mb-0">${erroresStock.map(e => `<li>${e}</li>`).join('')}</ul>
                </div>
            `,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    const form = document.getElementById('posForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // ============================================
    // 1. DECLARAR TODAS LAS VARIABLES PRIMERO
    // ============================================
    const tipo_pedido = document.getElementById('posTipoPedido').value;
    const id_mesa = document.getElementById('posMesa').value;
    const nombre_cliente = document.getElementById('posClienteNombre').value;
    const id_metodo_pago = document.getElementById('posMetodoPago').value;

    // ============================================
    // 2. VALIDAR (AHORA tipo_pedido YA ESTÁ DECLARADO)
    // ============================================
    if (tipo_pedido === 'MESA') {
        if (!id_mesa) {
            Swal.fire({
                icon: 'error',
                title: 'Mesa requerida',
                text: 'Debe seleccionar una mesa disponible para pedidos en el local',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
    }

    const btnCobrar = document.getElementById('btnPosCobrar');
    btnCobrar.disabled = true;
    btnCobrar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

    const totalCalculado = posCart.reduce((sum, item) => sum + (item.precio_unitario * item.cantidad), 0);

    const datos = {
        carrito: {
        items: posCart.map(item => ({
            id_producto: item.id_producto,
            cantidad: item.cantidad,
            precio_unitario: item.precio_unitario,
            indicacion: item.indicacion || '',
            tipo_producto: item.tipo_producto || 'COCINA' // <-- NUEVO
            })),
            total: totalCalculado
            },
        tipo_pedido: tipo_pedido,
        id_mesa: id_mesa,
        nombre: nombre_cliente,
        id_metodo_pago: id_metodo_pago
    };

    try {
        const res = await PedidoAdminController.crearPedidoPOS(datos);

        if (res.success) {
            Swal.fire({
            icon: 'success',
            title: '¡Pedido registrado!',
            html: `
                ${res.message}<br>
                <strong>Número de pedido: #${res.numero_pedido || res.id_pedido}</strong>
            `,
            confirmButtonColor: '#28a745'
        }).then(() => {
                posCart = [];
                document.getElementById('posClienteNombre').value = '';
                document.getElementById('posMesa').value = '';
                renderCarrito();

                const modalEl = document.getElementById('modalPOS');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.hide();
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style = '';
                }

                if (typeof cargarPedidos === 'function') {
                    cargarPedidos();
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error al registrar pedido',
                html: res.message,
                confirmButtonColor: '#d33'
            });
        }
    } catch (error) {
        console.error("Error al procesar el cobro:", error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al procesar el cobro. Por favor, intenta de nuevo.',
            confirmButtonColor: '#d33'
        });
    } finally {
        btnCobrar.disabled = posCart.length === 0;
        btnCobrar.innerHTML = '<i class="fas fa-check-circle me-2"></i>Procesar y Cobrar';
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}