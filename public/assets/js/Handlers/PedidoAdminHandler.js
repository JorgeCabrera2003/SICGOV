// assets/js/Handlers/PedidoAdminHandler.js

document.addEventListener('DOMContentLoaded', () => {
    cargarPedidos();

    // Event listener para marcar como pagado desde el modal del comprobante
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
                    <td class="fw-bold">${p.id_pedido}</td>
                    <td>${fecha}</td>
                    <td>${escapeHtml(cliente)}</td>
                    <td><span class="badge bg-secondary">${p.tipo_pedido}</span></td>
                    <td class="fw-bold text-success">$${parseFloat(p.total).toFixed(2)}</td>
                    <td><span class="estado-badge estado-${p.estado}">${p.estado}</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick="verDetalle('${p.id_pedido}', '${p.estado}')" title="Ver Detalle"><i class="fas fa-eye"></i></button>
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
        Swal.fire({
            title: 'Cargando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const res = await PedidoAdminController.buscarPedido(idPedido);
        Swal.close();
        
        if (res.success && res.data) {
            const p = res.data;
            const body = document.getElementById('detallePedidoBody');
            
            if (!body) return;
            
            let html = `
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <strong>Cliente:</strong> ${p.nombre ? p.nombre + ' ' + (p.apellido||'') : 'Mostrador'}<br>
                        <strong>Teléfono:</strong> ${p.telefono || 'N/A'}<br>
                        <strong>Tipo:</strong> ${p.tipo_pedido}
                        ${p.id_mesa ? `<br><strong>Mesa:</strong> ${p.id_mesa}` : ''}
                    </div>
                    <div class="col-sm-6 text-end">
                        <strong>Total:</strong> <span class="text-success fw-bold">$${parseFloat(p.total).toFixed(2)}</span><br>
                        <strong>Método de Pago:</strong> ${p.metodo_pago || 'N/A'}
                    </div>
                </div>
                <h6 class="fw-bold border-bottom pb-2">Productos</h6>
                <ul class="list-group list-group-flush mb-3">
            `;

            if (p.detalles && p.detalles.length > 0) {
                p.detalles.forEach(d => {
                    const subtotal = parseFloat(d.precio_unitario) * parseInt(d.cantidad);
                    
                    html += `
                        <li class="list-group-item bg-transparent px-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="fw-bold">${d.cantidad}x</span> ${d.nombre_producto}
                                </div>
                                <span class="fw-bold">$${subtotal.toFixed(2)}</span>
                            </div>
                    `;
                    
                    // Mostrar extras si existen
                    if (d.indicacion && d.indicacion.trim() !== '') {
                        html += `
                            <div class="small text-muted mt-1">
                                <i class="fas fa-info-circle me-1"></i> ${d.indicacion}
                            </div>
                        `;
                    }
                    
                    html += `</li>`;
                });
            } else {
                html += `<li class="list-group-item text-muted">No hay productos registrados</li>`;
            }

            html += `</ul>`;
            body.innerHTML = html;

            // Botones de estado
            const group = document.getElementById('btnGroupEstados');
            if (group) {
                group.innerHTML = '';
                const estados = ['PENDIENTE', 'PREPARACION', 'LISTO', 'ENTREGADO', 'CANCELADO'];
                estados.forEach(e => {
                    if (e !== estadoActual) {
                        const btn = document.createElement('button');
                        btn.className = `btn btn-sm btn-outline-primary`;
                        btn.innerText = `Pasar a ${e}`;
                        btn.onclick = () => cambiarEstado(idPedido, e);
                        group.appendChild(btn);
                    }
                });
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

async function cambiarEstado(idPedido, nuevoEstado) {
    const confirmResult = await Swal.fire({
        title: '¿Cambiar estado?',
        text: `¿Estás seguro de cambiar el estado a ${nuevoEstado}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
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
                text: res.message,
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

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}