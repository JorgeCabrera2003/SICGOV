import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";

export async function CargarPapelera() {
    const peticion = new FormData();
    peticion.append('peticion', 'consultar');

    try {
        const json = await AjaxHelper.enviaAjax(peticion, '?page=papelera');
        if (json && json.resultado === 200) {
            DataTablePrincipal(json.datos);
        } else {
            DataTablePrincipal([]);
        }
    } catch (e) {
        console.error("Error al cargar papelera", e);
        DataTablePrincipal([]);
    }
}

export function DataTablePrincipal(datos) {
    if ($.fn.DataTable.isDataTable('#tablaPapelera')) {
        $('#tablaPapelera').DataTable().destroy();
    }

    $('#tablaPapelera').DataTable({
        responsive: true,
        data: datos,
        order: [[0, 'asc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        columns: [
            {
                data: 'modulo',
                render: function (data) {
                    const colors = {
                        'NOTICIAS': 'bg-info',
                        'INGREDIENTES': 'bg-warning text-dark',
                        'USUARIOS': 'bg-primary',
                        'CLIENTES': 'bg-success',
                        'PRODUCTOS': 'bg-secondary'
                    };
                    const color = colors[data] || 'bg-dark';
                    return $('<span>', { class: `badge ${color}` }).text(data).prop('outerHTML');
                }
            },
            { data: 'descripcion' },
            {
                data: null,
                orderable: false,
                className: 'text-center',

                render: function (data, type, row) {
                    const $group = $('<div>', { class: 'btn-group' });
                    
                    const $btnRestaurar = $('<button>', {
                        class: 'btn btn-sm btn-outline-success btn-restaurar',
                        'data-id': row.id,
                        'data-modulo': row.modulo,
                        title: 'Restaurar Registro'
                    }).append($('<i>', { class: 'fas fa-undo me-1' }), 'Restaurar');

                    /* 
                    const $btnEliminar = $('<button>', {
                        class: 'btn btn-sm btn-outline-danger btn-eliminar-permanente',
                        'data-id': row.id,
                        'data-modulo': row.modulo,
                        title: 'Eliminar Permanentemente'
                    }).append($('<i>', { class: 'fas fa-skull-crossbones me-1' }), 'Eliminar');
                    */

                    return $group.append($btnRestaurar/*, $btnEliminar*/).prop('outerHTML');

                }
            }
        ]
    });
}

export async function RestaurarRegistro(modulo, id) {
    const confirmado = await MensajeriaHelper.MostrarConfirmacion(
        '¿Restaurar registro?',
        `El registro de ${modulo} volverá a estar activo en el sistema.`,
        'question'
    );

    if (confirmado) {
        const fd = new FormData();
        fd.append('peticion', 'restaurar');
        fd.append('modulo', modulo);
        fd.append('id', id);

        const res = await AjaxHelper.enviaAjax(fd, '?page=papelera');
        if (res && res.resultado === 200) {
            MensajeriaHelper.GenerarMensaje('success', 3000, 'Restaurado', res.mensaje);
            await CargarPapelera();
        } else {
            MensajeriaHelper.GenerarMensaje('error', 5000, 'Error', res?.mensaje || 'No se pudo restaurar');
        }
    }
}

export async function EliminarPermanente(modulo, id) {
    const confirmado = await MensajeriaHelper.MostrarConfirmacion(
        '¿ELIMINAR PERMANENTEMENTE?',
        `¡Esta acción es irreversible! Se borrará físicamente el registro de la base de datos.`,
        'warning'
    );

    if (confirmado) {
        const fd = new FormData();
        fd.append('peticion', 'eliminar_permanente');
        fd.append('modulo', modulo);
        fd.append('id', id);

        const res = await AjaxHelper.enviaAjax(fd, '?page=papelera');
        if (res && res.resultado === 200) {
            MensajeriaHelper.GenerarMensaje('success', 3000, 'Borrado', res.mensaje);
            await CargarPapelera();
        } else {
            MensajeriaHelper.GenerarMensaje('error', 5000, 'Error', res?.mensaje || 'No se pudo eliminar');
        }
    }
}
