import { enviaAjax } from './Helpers/AjaxHelper.js';

import * as AjaxHelper from "./Helpers/AjaxHelper.js"

/**
 * Módulo de Notificaciones para SICGOV
 * Estilo BEM + jQuery Seguro + ES6 Modules
 */

'use strict';

const ENDPOINT = BASE_URL + '?page=Notification';

// Selectores BEM
const SELECTORS = {
    badge: '#notificationBadge',
    list: '#notificationList',
    markAllRead: '#markAllRead',
    pageList: '#pageNotificationList',
    pageMarkAllRead: '#btnPageMarkAllRead'
};

// Iconos por tipo de notificación
const ICONS = {
    exito: 'bi-check-circle-fill',
    info: 'bi-info-circle-fill',
    alerta: 'bi-exclamation-triangle-fill',
    error: 'bi-x-circle-fill',
    default: 'bi-bell-fill'
};

/**
 * Carga las notificaciones desde el servidor
 */
async function cargarNotificaciones() {
    try {
        const formData = new FormData();
        formData.append('peticion', 'listar');

        const response = await AjaxHelper.enviaAjax(formData, ENDPOINT);

        if (response && response.success) {
            renderNotificaciones(response.data);
            actualizarBadge(response.noLeidas);
        } else {
            console.error('Error al obtener notificaciones:', response?.mensaje || 'Error desconocido');
        }
    } catch (error) {
        console.error('Excepción al cargar notificaciones:', error);
    }
}

/**
 * Renderiza la lista de notificaciones en el DOM de forma segura
 * @param {Array} notificaciones 
 */
function renderNotificaciones(notificaciones) {
    const $list = $(SELECTORS.list);
    const $pageList = $(SELECTORS.pageList);
    
    if (!$list.length && !$pageList.length) return;

    if ($list.length) {
        $list.empty();
        if (!notificaciones || notificaciones.length === 0) {
            const $vacio = $('<div/>', { class: 'notificacion__vacio' })
                .append($('<i/>', { class: 'bi bi-inbox notificacion__vacio-icono' }))
                .append($('<span/>').text('No tienes notificaciones pendientes'));
            $list.append($vacio);
        }
    }

    if ($pageList.length) {
        $pageList.empty();
        if (!notificaciones || notificaciones.length === 0) {
            const $vacio = $('<div/>', { class: 'notificacion__vacio py-5' })
                .append($('<i/>', { class: 'bi bi-inbox notificacion__vacio-icono fs-1' }))
                .append($('<span/>', { class: 'd-block mt-2 fs-5 text-muted fw-bold' }).text('Bandeja de entrada limpia'))
                .append($('<p/>', { class: 'text-muted small mb-0' }).text('No tienes ninguna notificación en este momento.'));
            $pageList.append($vacio);
        }
    }

    if (!notificaciones || notificaciones.length === 0) return;

    notificaciones.forEach(notif => {
        // Generar para ambas vistas si están presentes en la página actual
        [
            { el: $list, isPage: false },
            { el: $pageList, isPage: true }
        ].forEach(target => {
            const $container = target.el;
            if (!$container.length) return;

            const $item = $('<div/>', {
                class: 'notificacion__item' + (notif.leida ? '' : ' notificacion__item--no-leida'),
                'data-id': notif.id
            });

            // Configuración de icono
            const tipo = ['info', 'alerta', 'exito', 'error'].includes(notif.tipo) ? notif.tipo : 'info';
            const iconClass = `notificacion__icono notificacion__icono--${tipo}`;
            const iconName = ICONS[tipo] || ICONS.default;

            const $iconoContainer = $('<div/>', { class: iconClass })
                .append($('<i/>', { class: `bi ${iconName}` }));

            // Contenido de la notificación
            const $contenido = $('<div/>', { class: 'notificacion__contenido' });
            
            const $encabezado = $('<div/>', { class: 'notificacion__encabezado' })
                .append($('<h6/>', { class: 'notificacion__asunto' }).text(notif.titulo || 'Notificación'))
                .append($('<span/>', { class: 'notificacion__fecha' }).text(notif.hace || ''));

            const $mensaje = $('<p/>', { class: 'notificacion__mensaje' }).text(notif.mensaje || '');

            $contenido.append($encabezado).append($mensaje);

            // Opciones (marcar como leída)
            const $opciones = $('<div/>', { class: 'notificacion__opciones' });
            if (!notif.leida) {
                const $btnMarcar = $('<button/>', {
                    class: 'notificacion__marcar-leida',
                    'data-id': notif.id,
                    title: 'Marcar como leída'
                }).append($('<i/>', { class: 'bi bi-check2' }));

                // Manejador de evento click para marcar una sola
                $btnMarcar.on('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    await marcarComoLeida(notif.id, $item);
                });

                $opciones.append($btnMarcar);
            }

            $item.append($iconoContainer).append($contenido).append($opciones);
            
            // Al hacer click en el item completo, marcar como leída
            $item.on('click', async function(e) {
                if ($(e.target).closest('.notificacion__marcar-leida').length) return;
                
                if (!notif.leida) {
                    await marcarComoLeida(notif.id, $item);
                }
            });

            $container.append($item);
        });
    });
}

/**
 * Actualiza el contador de notificaciones no leídas
 * @param {Number} noLeidas 
 */
function actualizarBadge(noLeidas) {
    const $badge = $(SELECTORS.badge);
    if (!$badge.length) return;

    if (noLeidas > 0) {
        $badge.text(noLeidas).show();
    } else {
        $badge.hide();
    }
}

/**
 * Marca una notificación individual como leída
 * @param {String} id 
 * @param {jQuery} $itemElement 
 */
async function marcarComoLeida(id, $itemElement) {
    try {
        const formData = new FormData();
        formData.append('peticion', 'marcar-leida');
        formData.append('id_notificacion', id);

        const response = await AjaxHelper.enviaAjax(formData, ENDPOINT);

        if (response && response.success) {
            // Transición visual elegante
            $itemElement.removeClass('notificacion__item--no-leida');
            $itemElement.find('.notificacion__marcar-leida').fadeOut(200, function() {
                $(this).remove();
            });
            // Recargar datos para actualizar badge y listas de forma sincronizada
            await cargarNotificaciones();
        } else {
            console.error('Error al marcar como leída:', response?.mensaje || 'Error desconocido');
        }
    } catch (error) {
        console.error('Excepción al marcar como leída:', error);
    }
}

/**
 * Marca todas las notificaciones como leídas
 */
async function marcarTodasComoLeidas() {
    try {
        const formData = new FormData();
        formData.append('peticion', 'marcar-todas');

        const response = await AjaxHelper.enviaAjax(formData, ENDPOINT);

        if (response && response.success) {
            await cargarNotificaciones();
            
            // SweetAlert de confirmación opcional/amigable
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Completado!',
                    text: 'Todas las notificaciones han sido marcadas como leídas.',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        } else {
            console.error('Error al marcar todas como leídas:', response?.mensaje || 'Error desconocido');
        }
    } catch (error) {
        console.error('Excepción al marcar todas como leídas:', error);
    }
}

// Inicialización del módulo
$(document).ready(function() {
    // Carga inicial
    cargarNotificaciones();

    // Intervalo de actualización cada 30 segundos
    const pollInterval = setInterval(cargarNotificaciones, 30000);

    // Evento para marcar todas como leídas (navbar)
    $(SELECTORS.markAllRead).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        marcarTodasComoLeidas();
    });

    // Evento para marcar todas como leídas (página principal)
    $(SELECTORS.pageMarkAllRead).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        marcarTodasComoLeidas();
    });

    // Exponer API pública del módulo en el objeto global SICGOV por retrocompatibilidad
    if (typeof window.SICGOV === 'undefined') {
        window.SICGOV = {};
    }
    window.SICGOV.notificaciones = {
        recargar: cargarNotificaciones,
        marcarTodas: marcarTodasComoLeidas
    };
});

// Exportaciones ES6
export {
    cargarNotificaciones,
    marcarTodasComoLeidas,
    marcarComoLeida
};
