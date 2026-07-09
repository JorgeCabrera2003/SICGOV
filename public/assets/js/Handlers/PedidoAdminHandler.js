// assets/js/Handlers/PedidoAdminHandler.js

document.addEventListener('DOMContentLoaded', () => {
    cargarPedidos();

    


    const btnVerificarPago = document.getElementById('btnVerificarPago');
    if (btnVerificarPago) {
        btnVerificarPago.addEventListener('click', async function() {
            const idPedido = this.dataset.idPedido;
            if (idPedido) {
                // Confirmar acción con SweetAlert
                const confirmResult = await Swal.fire({
                    title: '¿Confirmar pago?',
                    text: '¿Estás seguro de marcar este pedido como pagado?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, marcar como pagado',
                    cancelButtonText: 'Cancelar'
                });

                if (confirmResult.isConfirmed) {
                    this.disabled = true;
                    Swal.fire({
                        title: 'Procesando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const res = await PedidoAdminController.cambiarEstado(idPedido, 'PAGADO');
                    
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Pago confirmado!',
                            text: res.message,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        });
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalComprobante'));
                        if (modal) modal.hide();
                        cargarPedidos();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                    
                    this.disabled = false;
                }
            }
        });
    }
});

async function cargarPedidos() {
    try {
        const res = await PedidoAdminController.listarPedidos();
        const tbody = document.getElementById('pedidosTbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        if (res.success && res.data && res.data.length > 0) {
            res.data.forEach(p => {
                const fecha = new Date(p.fecha_pedido).toLocaleString();
                const cliente = p.nombre ? `${p.nombre} ${p.apellido || ''}` : 'Mostrador';
                
                // Botones de acción
                let btnComprobante = '';
                if (p.metodo_pago === 'Pago Móvil') {
                    btnComprobante = `<button class="btn btn-sm btn-outline-info" onclick="verComprobante('${p.id_pedido}', '${p.estado}')" title="Ver Comprobante"><i class="fas fa-image"></i></button>`;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="fw-bold">#${p.numero_pedido || p.id_pedido}</td>
                    <td>${fecha}</td>
                    <td>${escapeHtml(cliente)}</td>
                    <td><span class="badge bg-secondary">${p.tipo_pedido}</span></td>
                    <td class="fw-bold text-success">$${parseFloat(p.total).toFixed(2)}</td>
                    <td><span class="estado-badge estado-${p.estado || 'PENDIENTE'}">${p.estado || 'PENDIENTE'}</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick="verDetalle('${p.id_pedido}', '${p.estado || 'PENDIENTE'}')" title="Ver Detalle"><i class="fas fa-eye"></i></button>
                            ${btnComprobante}
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No hay pedidos registrados</td></tr>';
        }
    } catch (error) {
        console.error('Error al cargar pedidos:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los pedidos',
            confirmButtonColor: '#d33'
        });
    }
}

async function verDetalle(idPedido, estadoActual) {
    try {
        if (!estadoActual || estadoActual === 'undefined') {
            estadoActual = 'PENDIENTE';
        }
        Swal.fire({
            title: 'Cargando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const res = await PedidoAdminController.buscarPedido(idPedido);
        
        // ============================================
        // OBTENER TASA DEL DÍA (API ExchangeRate)
        // ============================================
        let tasaCambio = 60; // Fallback por defecto
        
        try {
            const response = await fetch('https://api.exchangerate-api.com/v4/latest/USD');
            if (response.ok) {
                const data = await response.json();
                tasaCambio = data.rates.VES || 60;
                console.log('Tasa USD/VES obtenida:', tasaCambio);
            } else {
                console.warn('Error al obtener tasa, usando fallback:', tasaCambio);
            }
        } catch (error) {
            console.error('Error al obtener tasa:', error);
            // Mantener fallback
        }
        
        Swal.close();
        
        if (res.success && res.data) {
            const p = res.data;
            const body = document.getElementById('detallePedidoBody');
            
            if (!body) return;
            
            const totalBs = parseFloat(p.total) * tasaCambio;
            
            let html = `
                <!-- ========== CARD PRINCIPAL ========== -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        
                        <!-- HEADER DEL PEDIDO (compacto) -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0">
                                    <i class="fas fa-receipt text-primary me-1"></i>
                                    Pedido #${p.numero_pedido || p.id_pedido}
                                </h5>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge estado-${p.estado}">${p.estado}</span>
                                <span class="badge bg-secondary">${p.tipo_pedido}</span>
                                <small class="text-muted">${new Date(p.fecha_pedido).toLocaleString()}</small>
                            </div>
                        </div>
                        
                        <!-- INFORMACIÓN DEL CLIENTE Y PEDIDO (compacto) -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="bg-light p-2 rounded-3">
                                    <span class="fw-bold small">${escapeHtml(p.nombre ? p.nombre + ' ' + (p.apellido||'') : 'Mostrador')}</span>
                                    <span class="text-muted small ms-2">${escapeHtml(p.telefono || 'N/A')}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-2 rounded-3 d-flex gap-3 flex-wrap">
                                    ${p.id_mesa ? `<span class="small"><span class="text-muted">Mesa:</span> <strong>${p.numero_mesa || p.id_mesa}</strong></span>` : ''}
                                    ${p.metodo_pago ? `<span class="small"><span class="text-muted">Pago:</span> <strong>${escapeHtml(p.metodo_pago)}</strong></span>` : ''}
                                    ${p.referencia ? `<span class="small"><span class="text-muted">Ref:</span> <strong>${escapeHtml(p.referencia)}</strong></span>` : ''}
                                </div>
                            </div>
                        </div>
                        
                        <!-- PRODUCTOS (compacto) -->
                        <div class="mb-2">
                            <h6 class="fw-bold mb-2 small">
                                <i class="fas fa-hamburger text-warning me-1"></i>Productos
                            </h6>
            `;
            
            if (p.detalles && p.detalles.length > 0) {
                html += `<div class="list-group list-group-flush">`;
                
                p.detalles.forEach((d, index) => {
                    const subtotal = parseFloat(d.precio_unitario) * parseInt(d.cantidad);
                    
                    html += `
                        <div class="list-group-item px-0 py-1 ${index > 0 ? 'border-top' : ''}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-primary rounded-pill">${d.cantidad}x</span>
                                    <span class="fw-bold small">${escapeHtml(d.nombre_producto)}</span>
                                    <span class="text-muted small">$${parseFloat(d.precio_unitario).toFixed(2)}</span>
                    `;
                    
                    if (d.indicacion && d.indicacion.trim() !== '') {
                        html += `
                            <span class="badge bg-info text-white small">${escapeHtml(d.indicacion)}</span>
                        `;
                    }
                    
                    html += `
                                </div>
                                <span class="fw-bold text-success small">$${subtotal.toFixed(2)}</span>
                            </div>
                        </div>
                    `;
                });
                
                html += `</div>`;
            } else {
                html += `<div class="alert alert-warning py-1 small">No hay productos registrados</div>`;
            }
            
            // TOTAL (compacto)
            html += `
                        </div>
                        
                        <!-- TOTAL (compacto) -->
                        <div class="mt-2">
                            <div class="card bg-success text-white border-0">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold fs-6">$${parseFloat(p.total).toFixed(2)}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold fs-6">Bs ${totalBs.toFixed(2)}</span>
                                            <br>
                                            <small class="text-white-50">Tasa: 1$ = ${tasaCambio.toFixed(2)} Bs</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            `;
            
            body.innerHTML = html;

                        // ==============================================
            // BOTONES DE ESTADO DINÁMICOS (CON FLUJO)
            // ==============================================
            const group = document.getElementById('btnGroupEstados');
            if (group) {
                group.innerHTML = '';
                
                const botones = obtenerBotonesEstado(estadoActual);
                
                if (botones.length === 0) {
                    // Pedido finalizado
                    const span = document.createElement('span');
                    span.className = 'text-muted';
                    
                    // Mensaje según el estado final
                    if (estadoActual === 'ENTREGADO') {
                        span.innerText = '✅ Pedido entregado';
                    } else if (estadoActual === 'PAGADO') {
                        span.innerText = '💰 Pedido pagado';
                    } else if (estadoActual === 'CANCELADO') {
                        span.innerText = '❌ Pedido cancelado';
                    } else {
                        span.innerText = '✅ Pedido completado';
                    }
                    
                    group.appendChild(span);
                } else {
                    botones.forEach(btn => {
                        const btnElement = document.createElement('button');
                        btnElement.className = `btn btn-sm btn-outline-${btn.color} me-1`;
                        btnElement.innerHTML = btn.html;
                        btnElement.onclick = () => cambiarEstado(idPedido, btn.estado, estadoActual);
                        group.appendChild(btnElement);
                    });
                }
            }

            const modal = new bootstrap.Modal(document.getElementById('modalDetallePedido'));
            modal.show();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.message || 'No se encontró el pedido',
                confirmButtonColor: '#d33'
            });
        }
    } catch (error) {
        console.error('Error al ver detalle:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los detalles del pedido',
            confirmButtonColor: '#d33'
        });
    }
}

async function verComprobante(idPedido, estado) {
    Swal.fire({
        title: 'Cargando comprobante...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    const res = await PedidoAdminController.obtenerComprobante(idPedido);
    Swal.close();
    
    if (res.success && res.url) {
        document.getElementById('imgComprobante').src = res.url;
        const btnVerificar = document.getElementById('btnVerificarPago');
        
        if (btnVerificar) {
            if (estado !== 'PAGADO' && estado !== 'ENTREGADO') {
                btnVerificar.style.display = 'block';
                btnVerificar.dataset.idPedido = idPedido;
            } else {
                btnVerificar.style.display = 'none';
            }
        }
        
        const modal = new bootstrap.Modal(document.getElementById('modalComprobante'));
        modal.show();
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Comprobante no encontrado',
            text: res.message || 'No se encontró el comprobante de pago',
            confirmButtonColor: '#d33'
        });
    }
}

// ==============================================
// CONFIGURACIÓN DE FLUJO DE ESTADOS
// ==============================================
const FLUJO_ESTADOS = {
    'PENDIENTE': {
        siguientes: ['CONFIRMADO', 'CANCELADO'],
        label: 'Pendiente',
        color: 'warning'
    },
    'CONFIRMADO': {
        siguientes: ['PREPARANDO', 'CANCELADO'],
        label: 'Confirmado',
        color: 'primary'
    },
    'PREPARANDO': {
        siguientes: ['LISTO', 'CANCELADO'],
        label: 'Preparando',
        color: 'info'
    },
    'LISTO': {
        siguientes: ['ENTREGADO', 'PAGADO', 'CANCELADO'],  // <-- Agregar PAGADO
        label: 'Listo',
        color: 'success'
    },
    'ENTREGADO': {
        siguientes: ['PAGADO'],  // <-- Se puede pagar después de entregar
        label: 'Entregado',
        color: 'success'
    },
    'PAGADO': {
        siguientes: [],  // <-- Estado final
        label: 'Pagado',
        color: 'success'
    },
    'CANCELADO': {
        siguientes: [],
        label: 'Cancelado',
        color: 'danger'
    }
};

// Estados que NO se pueden cancelar
const ESTADOS_NO_CANCELABLES = ['ENTREGADO', 'PAGADO', 'CANCELADO'];


async function cambiarEstado(idPedido, nuevoEstado, estadoActual) {
    // Validar que el nuevo estado sea válido según el flujo
    const config = FLUJO_ESTADOS[estadoActual] || { siguientes: [] };
    if (!config.siguientes.includes(nuevoEstado)) {
        Swal.fire({
            icon: 'error',
            title: 'Transición no válida',
            text: `No se puede pasar de "${estadoActual}" a "${nuevoEstado}"`,
            confirmButtonColor: '#d33'
        });
        return;
    }
    
    const info = FLUJO_ESTADOS[nuevoEstado];
    
    const confirmResult = await Swal.fire({
        title: `¿Cambiar a ${info.label}?`,
        text: `El pedido pasará de "${FLUJO_ESTADOS[estadoActual]?.label || estadoActual}" a "${info.label}"`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: `Sí, pasar a ${info.label}`,
        cancelButtonText: 'Cancelar'
    });
    
    if (confirmResult.isConfirmed) {
        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        const res = await PedidoAdminController.cambiarEstado(idPedido, nuevoEstado);
        
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: 'Estado actualizado',
                text: `Pedido ahora está en "${info.label}"`,
                confirmButtonColor: '#28a745'
            });
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalDetallePedido'));
            if (modal) modal.hide();
            cargarPedidos();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.message,
                confirmButtonColor: '#d33'
            });
        }
    }
}

function obtenerBotonesEstado(estadoActual) {
    if (!FLUJO_ESTADOS[estadoActual]) {
        estadoActual = 'PENDIENTE';
    }

    const config = FLUJO_ESTADOS[estadoActual];
    if (!config) return [];
    
    if (config.siguientes.length === 0) {
        return [];
    }
    
    return config.siguientes.map(estado => {
        const info = FLUJO_ESTADOS[estado];
        let icono = '';
        let btnColor = 'secondary';
        
        switch (estado) {
            case 'CONFIRMADO':
                icono = '<i class="fas fa-check-circle me-1"></i>';
                btnColor = 'primary';
                break;
            case 'PREPARANDO':
                icono = '<i class="fas fa-utensils me-1"></i>';
                btnColor = 'info';
                break;
            case 'LISTO':
                icono = '<i class="fas fa-check-double me-1"></i>';
                btnColor = 'success';
                break;
            case 'ENTREGADO':
                icono = '<i class="fas fa-truck me-1"></i>';
                btnColor = 'success';
                break;
            case 'PAGADO':  // <-- Nuevo
                icono = '<i class="fas fa-money-bill-wave me-1"></i>';
                btnColor = 'success';
                break;
            case 'CANCELADO':
                icono = '<i class="fas fa-times-circle me-1"></i>';
                btnColor = 'danger';
                break;
        }
        
        return {
            estado: estado,
            label: info.label,
            icono: icono,
            color: btnColor,
            html: `${icono} ${info.label}`
        };
    });
}

// Cargar mesas disponibles
async function cargarMesasDisponibles() {
    try {
        const formData = new FormData();
        formData.append('action', 'listar_mesas_disponibles');
        
        const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
        const data = await res.json();
        
        const selectMesa = document.getElementById('posMesa');
        if (!selectMesa) return;
        
        // Limpiar select
        selectMesa.innerHTML = '<option value="">Seleccione una mesa disponible</option>';
        
        if (data.success && data.data && data.data.length > 0) {
            data.data.forEach(mesa => {
                const option = document.createElement('option');
                option.value = mesa.id_mesa;
                option.textContent = `Mesa ${mesa.numero_mesa} (Cap: ${mesa.capacidad})`;
                selectMesa.appendChild(option);
            });
        } else {
            // Si no hay mesas disponibles
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No hay mesas disponibles';
            option.disabled = true;
            selectMesa.appendChild(option);
        }
    } catch (error) {
        console.error('Error al cargar mesas disponibles:', error);
    }
}

// Tipo de Pedido change
const selectTipo = document.getElementById('posTipoPedido');
const boxMesa = document.getElementById('boxMesa');
if (selectTipo) {
    selectTipo.addEventListener('change', () => {
        if (selectTipo.value === 'MESA') {
            boxMesa.style.display = 'block';
            document.getElementById('posMesa').required = true;
            // Cargar mesas disponibles al seleccionar MESA
            cargarMesasDisponibles();
        } else {
            boxMesa.style.display = 'none';
            document.getElementById('posMesa').required = false;
            document.getElementById('posMesa').value = '';
        }
    });
}

// Al abrir modal POS, también cargamos mesas si es necesario
modalPOS.addEventListener('show.bs.modal', async () => {
    // Cargar productos...
    // Si el tipo por defecto es MESA, cargar mesas
    if (document.getElementById('posTipoPedido').value === 'MESA') {
        await cargarMesasDisponibles();
    }
});

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}