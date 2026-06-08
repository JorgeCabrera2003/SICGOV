let posCart = [];
let productosDB = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarProductosPOS();

    // Filtros
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
    selectTipo.addEventListener('change', () => {
        if(selectTipo.value === 'MESA') {
            boxMesa.style.display = 'block';
            document.getElementById('posMesa').required = true;
        } else {
            boxMesa.style.display = 'none';
            document.getElementById('posMesa').required = false;
        }
    });

    // Cobrar
    document.getElementById('btnPosCobrar').addEventListener('click', procesarCobro);
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
    }
}

function renderProductos(categoriaId) {
    const grid = document.getElementById('posProductos');
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
        div.onclick = () => agregarAlCarrito(p);
        
        div.innerHTML = `
            <img src="/good-vibes/public/assets/img/productos/${p.imagen || 'default-product.png'}" class="card-img-top pos-card__img" alt="${p.nombre_producto}" onerror="this.src='/good-vibes/public/assets/img/default-product.png'">
            <div class="card-body p-2 text-center">
                <h6 class="card-title text-truncate mb-1" style="font-size:0.9rem;">${p.nombre_producto}</h6>
                <span class="text-success fw-bold">$${parseFloat(p.precio).toFixed(2)}</span>
            </div>
        `;
        grid.appendChild(div);
    });
}

function agregarAlCarrito(producto) {
    // Si ya existe, incrementar cantidad
    const index = posCart.findIndex(item => item.id_producto === producto.id_producto);
    
    if (index >= 0) {
        posCart[index].cantidad += 1;
    } else {
        posCart.push({
            id_producto: producto.id_producto,
            nombre: producto.nombre_producto,
            precio_unitario: parseFloat(producto.precio),
            cantidad: 1,
            removedPrincipales: [],
            addedAdicionales: []
        });
    }
    
    renderCarrito();
}

function actualizarCantidad(index, delta) {
    posCart[index].cantidad += delta;
    if (posCart[index].cantidad <= 0) {
        posCart.splice(index, 1);
    }
    renderCarrito();
}

function renderCarrito() {
    const container = document.getElementById('posCartItems');
    const badge = document.getElementById('posCount');
    const labelTotal = document.getElementById('posTotal');
    const btnCobrar = document.getElementById('btnPosCobrar');

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
        badge.innerText = '0';
        labelTotal.innerText = '$0.00';
        btnCobrar.disabled = true;
        return;
    }

    posCart.forEach((item, index) => {
        const subtotal = item.precio_unitario * item.cantidad;
        total += subtotal;
        count += item.cantidad;

        const div = document.createElement('div');
        div.className = 'pos-item';
        div.innerHTML = `
            <div class="flex-grow-1">
                <div class="fw-bold text-truncate" style="max-width:180px;">${item.nombre}</div>
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

    badge.innerText = count;
    labelTotal.innerText = `$${total.toFixed(2)}`;
    btnCobrar.disabled = false;
}

async function procesarCobro() {
    if (posCart.length === 0) return;

    const form = document.getElementById('posForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const tipo_pedido = document.getElementById('posTipoPedido').value;
    const id_mesa = document.getElementById('posMesa').value;
    const nombre_cliente = document.getElementById('posClienteNombre').value;
    const id_metodo_pago = document.getElementById('posMetodoPago').value;

    const btnCobrar = document.getElementById('btnPosCobrar');
    btnCobrar.disabled = true;
    btnCobrar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Cobrando...';

    const totalCalculado = posCart.reduce((sum, item) => sum + (item.precio_unitario * item.cantidad), 0);

    const datos = {
        carrito: {
            items: posCart,
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
            alert("Cobro exitoso. " + res.message);
            posCart = [];
            document.getElementById('posClienteNombre').value = '';
            document.getElementById('posMesa').value = '';
            renderCarrito();
            
            // Cerrar modal
            const modalEl = document.getElementById('modalPOS');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                // Limpiar backdrop si queda pegado
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style = '';
            }
            
            // Recargar tabla principal de pedidos
            if (typeof cargarPedidos === 'function') {
                cargarPedidos();
            }
        } else {
            alert(res.message);
        }
    } catch (error) {
        console.error("Error al procesar el cobro:", error);
        alert("Ocurrió un error al procesar el cobro. Por favor, intenta de nuevo.");
    } finally {
        btnCobrar.disabled = posCart.length === 0;
        btnCobrar.innerHTML = '<i class="fas fa-check-circle me-2"></i>Procesar y Cobrar';
    }
}
