class PedidoPublicoController {
    static async buscarProducto(id) {
        try {
            const url = `${BASE_URL_PUBLIC}/?page=PedidoPublico&action=buscarProducto&id=${id}`;
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.error('Error al buscar producto:', error);
            return { success: false, message: 'Error de red.' };
        }
    }

    static async enviarPedido(formData) {
        try {
            const url = `${BASE_URL_PUBLIC}/?page=PedidoPublico`;
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('Error al enviar pedido:', error);
            return { success: false, message: 'Error de red.' };
        }
    }
}
