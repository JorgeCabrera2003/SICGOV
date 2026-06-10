import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";

/**
 * ReservacionHandler - Manejador Universal para Reservaciones
 * Gestiona tanto la vista administrativa como la pública.
 */

// Detectar si estamos en vista pública
const ES_PUBLICO = window.location.search.includes('type=publico');
const BASE_URL_API = ES_PUBLICO ? '?page=Reservacion&type=publico' : '?page=Reservacion';

// IDs de elementos que cambian según la vista
const IDs = {
    form: ES_PUBLICO ? '#formReservarPublico' : '#formReservacion',
    modal: ES_PUBLICO ? '#modalPublico' : '#modalReservacion',
    fecha: ES_PUBLICO ? '#fechaPublica' : '#fecha',
    hora: ES_PUBLICO ? '#horaPublica' : '#hora',
    hora_fin: ES_PUBLICO ? '#hora_finPublica' : '#hora_fin',
    calendar: 'calendarPublico' // ID unificado en el DOM
};

/**
 * Helpers compartidos
 */
export function extraerHora(datetimeStr) {
    if (!datetimeStr || !datetimeStr.includes('T')) return null;
    return datetimeStr.split('T')[1].substring(0, 5);
}

export function formatarEstadoCliente(state) {
    if (!state.id) return state.text;
    
    // Obtener la URL del avatar desde el atributo data-avatar (si no existe, usa un icono por defecto o imagen genérica)
    const avatarUrl = (state.element && state.element.dataset.avatar) ? state.element.dataset.avatar : null;
    
    // Generar el HTML para el icono o la imagen
    const iconHtml = avatarUrl 
        ? `<img src="${avatarUrl}" alt="Avatar" class="rounded-circle me-3 border border-2 border-white shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">`
        : `<i class="bi bi-person-circle me-3 text-primary fs-3 shadow-sm rounded-circle"></i>`;

    return $(`
        <div class="d-flex align-items-center py-1">
            ${iconHtml}
            <div><span class="fw-medium">${state.text}</span></div>
        </div>
    `);
}

/**
 * Inicialización de Pickers (Flatpickr)
 */
export function inicializarPickers() {
    const configBase = {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        altInput: true,
        altFormat: "h:i K",
        time_24hr: false,
        locale: "es"
    };

    return {
        timePickerInicio: flatpickr(IDs.hora, configBase),
        timePickerFin: flatpickr(IDs.hora_fin, configBase)
    };
}

/**
 * Configuración Universal de FullCalendar
 */
export function inicializarCalendario(calendarEl, pickers) {
    const { timePickerInicio, timePickerFin } = pickers;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ES_PUBLICO ? '' : 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short',
            hour12: true
        },
        themeSystem: 'bootstrap5',
        selectable: true,
        unselectAuto: false,

        events: async function (fetchInfo, successCallback, failureCallback) {
            const formData = new FormData();
            formData.append('peticion', 'listar');
            if (!ES_PUBLICO) {
                formData.append('start', fetchInfo.startStr.split('T')[0]);
                formData.append('end', fetchInfo.endStr.split('T')[0]);
            }

            try {
                const res = await AjaxHelper.enviaAjax(formData, BASE_URL_API);
                if (res && (res.resultado == 200 || Array.isArray(res))) {
                    const datos = Array.isArray(res) ? res : res.datos;
                    successCallback(datos);
                } else {
                    failureCallback();
                }
            } catch (e) {
                failureCallback();
            }
        },

        select: function (info) {
            prepararNuevaReservacion(info, timePickerInicio, timePickerFin, calendar);
        },

        eventClick: function (info) {
            const event = info.event;
            const props = event.extendedProps;
            
            // Si es público y el evento es de otro (OCUPADO), no hacer nada
            if (ES_PUBLICO && props.ocupado) return;

            abrirDetalleReservacion(event, props, timePickerInicio, timePickerFin, calendar);
        },


        eventDrop: function (info) {
            MoverEvento(info, calendar);
        },

        eventResize: function (info) {
            MoverEvento(info, calendar, 'Duración actualizada');
        }
    });

    calendar.render();
    return calendar;
}

/**
 * Lógica Interna
 */

function obtenerRangosOcupados(fecha, calendar, idActual = null) {
    return calendar.getEvents()
        .filter(event => {
            const esMismaFecha = event.startStr.startsWith(fecha);
            const noEsMismaReservacion = event.id !== idActual;
            const estaOcupado = event.extendedProps.estado !== 'CANCELADA';
            return esMismaFecha && noEsMismaReservacion && estaOcupado;
        })
        .map(event => ({
            from: extraerHora(event.startStr),
            to: extraerHora(event.endStr)
        }));
}

function actualizarBloqueosPickers(fecha, calendar, tpInicio, tpFin, idActual = null) {
    const rangos = obtenerRangosOcupados(fecha, calendar, idActual);
    
    // Configuración para deshabilitar rangos en Flatpickr
    const configDisable = {
        disable: rangos.map(r => ({ from: r.from, to: r.to }))
    };

    tpInicio.set("disable", rangos);
    tpFin.set("disable", rangos);
}

function prepararNuevaReservacion(info, tpInicio, tpFin, calendar) {
    const $form = $(IDs.form);
    $form[0].reset();
    
    const fecha = info.startStr.split('T')[0];
    $('#peticion').val('registrar');
    $('#id_reservacion').val('');
    if (!ES_PUBLICO) {
        $('#cedula_cliente').val('').trigger('change');
        $('#id_mesa').val('');
    }
    
    $(IDs.fecha).val(fecha);

    // Actualizar bloqueos de tiempo para esta fecha
    actualizarBloqueosPickers(fecha, calendar, tpInicio, tpFin);

    if (info.view.type !== 'dayGridMonth') {
        tpInicio.setDate(info.start.toTimeString().split(' ')[0].substring(0, 5));
        tpFin.setDate(info.end.toTimeString().split(' ')[0].substring(0, 5));
    } else {
        tpInicio.clear();
        tpFin.clear();
    }

    $(`${IDs.form} input, ${IDs.form} select`).prop('disabled', false);
    $(`${IDs.form} button[type="submit"]`).show();
    $('#btnEliminar').hide();
    $(IDs.modal).modal('show');
}

function abrirDetalleReservacion(event, props, tpInicio, tpFin, calendar) {
    const esEditable = props.estado === 'PENDIENTE' && !ES_PUBLICO;
    const $form = $(IDs.form);
    const fecha = event.startStr.split('T')[0];

    $('#peticion').val('modificar');
    $('#id_reservacion').val(event.id);
    if (!ES_PUBLICO) {
        $('#cedula_cliente').val(props.cedula).trigger('change');
        $('#id_mesa').val(props.id_mesa || '');
    }
    
    $(IDs.fecha).val(fecha);

    // Actualizar bloqueos de tiempo (excluyendo la actual)
    actualizarBloqueosPickers(fecha, calendar, tpInicio, tpFin, event.id);

    const hInicio = extraerHora(event.startStr);
    const hFin = extraerHora(event.endStr);
    
    tpInicio.setDate(hInicio || '');
    tpFin.setDate(hFin || '');

    if (!ES_PUBLICO) $('#estado').val(props.estado);
    
    $(`${IDs.form} input, ${IDs.form} select`).prop('disabled', !esEditable && !ES_PUBLICO);
    
    if (ES_PUBLICO) {
        $(`${IDs.form} input`).prop('disabled', true);
        $(`${IDs.form} button[type="submit"]`).hide();
        $('.modal-title').text('Detalle de mi Cita');
    } else {
        $(`${IDs.form} button[type="submit"]`).toggle(esEditable);
        $('#btnEliminar').toggle(esEditable);
        $('.modal-title').text('Gestionar Reservación');
    }

    $(IDs.modal).modal('show');
}


export async function MoverEvento(info, calendar, mensaje = 'Reprogramado con éxito') {
    const formData = new FormData();
    formData.append('peticion', 'mover');
    formData.append('id_reservacion', info.event.id);
    formData.append('fecha', info.event.startStr.split('T')[0]);
    formData.append('hora', extraerHora(info.event.startStr) ?? '');
    formData.append('hora_fin', extraerHora(info.event.endStr) ?? '');

    const res = await AjaxHelper.enviaAjax(formData, BASE_URL_API);
    if (res && res.resultado == 200) {
        MensajeriaHelper.GenerarToast('success', mensaje);
    } else {
        info.revert();
        MensajeriaHelper.GenerarMensaje('error', 5000, 'Error', res?.mensaje || 'No se pudo mover');
    }
}

export async function GestionarEnvio(form, calendar) {
    const formData = new FormData(form);
    
    // Validación compartida
    const h1 = $(IDs.hora).val();
    const h2 = $(IDs.hora_fin).val();
    if (h1 && h2 && h2 <= h1) {
        MensajeriaHelper.GenerarMensaje('warning', 5000, "Rango inválido", "La hora de fin debe ser posterior.");
        return;
    }

    const res = await AjaxHelper.enviaAjax(formData, BASE_URL_API);
    if (res && res.resultado == 200) {
        $(IDs.modal).modal('hide');
        const msg = ES_PUBLICO ? "Solicitud enviada con éxito" : res.mensaje;
        MensajeriaHelper.GenerarMensaje('success', 2000, "¡Éxito!", msg);
        calendar.refetchEvents();
    } else {
        MensajeriaHelper.GenerarMensaje('error', 5000, "Error", res?.mensaje || "Error al procesar");
    }
}

export async function EliminarReservacion(id, calendar) {
    if (ES_PUBLICO) return;
    const confirmado = await MensajeriaHelper.MostrarConfirmacion('¿Eliminar?', 'Esta acción no se puede deshacer.');
    if (!confirmado) return;

    const formData = new FormData();
    formData.append('peticion', 'eliminar');
    formData.append('id_reservacion', id);

    const res = await AjaxHelper.enviaAjax(formData, BASE_URL_API);
    if (res && res.resultado == 200) {
        $(IDs.modal).modal('hide');
        MensajeriaHelper.GenerarMensaje('success', 2000, "Eliminado", "La reservación ha sido borrada.");
        calendar.refetchEvents();
    }
}
