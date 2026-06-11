document.addEventListener('DOMContentLoaded', () => {
    cargarPedidos();

    // Event listener para marcar como pagado desde el modal del comprobante
    document.getElementById('btnVerificarPago').addEventListener('click', async function() {
        const idPedido = this.dataset.idPedido;
        if(idPedido) {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
            const res = await PedidoAdminController.cambiarEstado(idPedido, 'PAGADO');
            if(res.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalComprobante'));
                modal.hide();
                cargarPedidos();
            } else {
                alert(res.message);
            }
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check me-2"></i>Marcar como Pagado';
        }
    });
});

async function cargarPedidos() {
    const res = await PedidoAdminController.listarPedidos();
    const tbody = document.getElementById('pedidosTbody');
    tbody.innerHTML = '';

    if (res.success && res.data.length > 0) {
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
                <td>${cliente}</td>
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
}

async function verDetalle(idPedido, estadoActual) {
    const res = await PedidoAdminController.buscarPedido(idPedido);
    if(res.success) {
        const p = res.data;
        const body = document.getElementById('detallePedidoBody');
        
        let html = `
            <div class="row mb-3">
                <div class="col-sm-6">
                    <strong>Cliente:</strong> ${p.nombre ? p.nombre + ' ' + (p.apellido||'') : 'Mostrador'}<br>
                    <strong>Teléfono:</strong> ${p.telefono || 'N/A'}<br>
                    <strong>Tipo:</strong> ${p.tipo_pedido}
                </div>
                <div class="col-sm-6 text-end">
                    <strong>Total:</strong> <span class="text-success fw-bold fs-5">$${parseFloat(p.total).toFixed(2)}</span><br>
                    <strong>Método de Pago:</strong> ${p.metodo_pago || 'N/A'}
                </div>
            </div>
            <h6 class="fw-bold border-bottom pb-2">Productos</h6>
            <ul class="list-group list-group-flush mb-3">
        `;

        if(p.detalles) {
            p.detalles.forEach(d => {
                html += `
                    <list-group-item class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        <div>
                            <span class="fw-bold">${d.cantidad}x</span> ${d.nombre_producto}
                            ${d.indicacion ? `<br><small class="text-muted ms-4"><i class="fas fa-info-circle me-1"></i>${d.indicacion}</small>` : ''}
                        </div>
                        <span>$${parseFloat(d.precio_unitario * d.cantidad).toFixed(2)}</span>
                    </list-group-item>
                `;
            });
        }

        html += `</ul>`;
        body.innerHTML = html;

        // Renderizar botones de estado dinámicos
        const group = document.getElementById('btnGroupEstados');
        group.innerHTML = '';
        
        const estados = ['PENDIENTE', 'COCINANDO', 'LISTO', 'ENTREGADO', 'CANCELADO'];
        estados.forEach(e => {
            if(e !== estadoActual) {
                const btn = document.createElement('button');
                btn.className = `btn btn-sm btn-outline-dark`;
                btn.innerText = `Pasar a ${e}`;
                btn.onclick = () => cambiarEstado(idPedido, e);
                group.appendChild(btn);
            }
        });

        const modal = new bootstrap.Modal(document.getElementById('modalDetallePedido'));
        modal.show();
    }
}

async function verComprobante(idPedido, estado) {
    const res = await PedidoAdminController.obtenerComprobante(idPedido);
    if(res.success && res.url) {
        document.getElementById('imgComprobante').src = res.url;
        const btnVerificar = document.getElementById('btnVerificarPago');
        
        if(estado !== 'PAGADO' && estado !== 'ENTREGADO') {
            btnVerificar.style.display = 'block';
            btnVerificar.dataset.idPedido = idPedido;
        } else {
            btnVerificar.style.display = 'none';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalComprobante'));
        modal.show();
    } else {
        alert(res.message || 'No se encontró el comprobante.');
    }
}

async function cambiarEstado(idPedido, nuevoEstado) {
    if(confirm(`¿Estás seguro de cambiar el estado a ${nuevoEstado}?`)) {
        const res = await PedidoAdminController.cambiarEstado(idPedido, nuevoEstado);
        if(res.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalDetallePedido'));
            modal.hide();
            cargarPedidos();
        } else {
            alert(res.message);
        }
    }
}
