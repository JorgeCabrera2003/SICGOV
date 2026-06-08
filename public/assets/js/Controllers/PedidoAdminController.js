class PedidoAdminController {
    static async listarPedidos() {
        const formData = new FormData();
        formData.append('action', 'listar');
        const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
        return await res.json();
    }

    static async buscarPedido(id_pedido) {
        const formData = new FormData();
        formData.append('action', 'buscar');
        formData.append('id_pedido', id_pedido);
        const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
        return await res.json();
    }

    static async cambiarEstado(id_pedido, estado) {
        const formData = new FormData();
        formData.append('action', 'cambiar_estado');
        formData.append('id_pedido', id_pedido);
        formData.append('estado', estado);
        const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
        return await res.json();
    }

    static async obtenerComprobante(id_pedido) {
        const formData = new FormData();
        formData.append('action', 'obtener_comprobante');
        formData.append('id_pedido', id_pedido);
        const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
        return await res.json();
    }

    static async crearPedidoPOS(datos) {
        const formData = new FormData();
        formData.append('action', 'crear_pos');
        formData.append('carrito', JSON.stringify(datos.carrito));
        formData.append('cedula', datos.cedula || '');
        formData.append('nombre', datos.nombre || '');
        formData.append('telefono', datos.telefono || '');
        formData.append('tipo_pedido', datos.tipo_pedido);
        formData.append('id_mesa', datos.id_mesa || '');
        formData.append('id_metodo_pago', datos.id_metodo_pago);
        const res = await fetch('?page=pedidos', { method: 'POST', body: formData });
        return await res.json();
    }
}
