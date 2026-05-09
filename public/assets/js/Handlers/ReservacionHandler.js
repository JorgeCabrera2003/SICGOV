import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";

/**
 * Formatea el estado del select de Select2 con ícono de persona.
 */
export function formatarEstadoCliente(state) {
    if (!state.id) return state.text;
    return $(`
        <div class="d-flex align-items-center py-1">
            <i class="bi bi-person-circle me-3 text-primary fs-5"></i>
            <div>
                <span class="fw-medium">${state.text}</span>
            </div>
        </div>
    `);
}

/**
 * Extrae la hora en formato HH:MM desde un string datetime ISO.
 */
export function extraerHora(datetimeStr) {
    if (!datetimeStr || !datetimeStr.includes('T')) return null;
    return datetimeStr.split('T')[1].substring(0, 5);
}

/**
 * Inicializa los pickers de hora (inicio y fin) con Flatpickr.
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
        timePickerInicio: flatpickr("#hora", configBase),
        timePickerFin: flatpickr("#hora_fin", configBase)
    };
}

/**
 * Configura e inicializa FullCalendar.
 */
export function inicializarCalendario(calendarEl, pickers) {
    const { timePickerInicio, timePickerFin } = pickers;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short',
            hour12: true
        },

        slotLabelFormat: {
            hour: 'numeric',
            minute: '2-digit',
            omitZeroMinute: false,
            meridiem: 'short',
            hour12: true
        },

        themeSystem: 'bootstrap5',
        editable: true,
        selectable: true,
        droppable: true,
        eventDisplay: 'block',

        events: async function (fetchInfo, successCallback, failureCallback) {
            const formData = new FormData();
            formData.append('peticion', 'listar');
            formData.append('start', fetchInfo.startStr.split('T')[0]);
            formData.append('end', fetchInfo.endStr.split('T')[0]);

            try {
                const res = await AjaxHelper.enviaAjax(formData, '?page=reservaciones');
                if (res && res.resultado == 200) {
                    successCallback(res.datos);
                } else {
                    failureCallback();
                }
            } catch (e) {
                failureCallback();
            }
        },

        select: function (info) {
            $('#formReservacion')[0].reset();
            $('#peticion').val('registrar');
            $('#id_reservacion').val('');
            $('#cedula_cliente').val('').trigger('change');
            $('#fecha').val(info.startStr.split('T')[0]);

            if (info.view.type !== 'dayGridMonth') {
                const horaInicio = info.start.toTimeString().split(' ')[0].substring(0, 5);
                const horaFin = info.end.toTimeString().split(' ')[0].substring(0, 5);
                timePickerInicio.setDate(horaInicio);
                timePickerFin.setDate(horaFin);
            } else {
                timePickerInicio.clear();
                timePickerFin.clear();
            }

            // Habilitar campos para nueva reservación
            $('#formReservacion input, #formReservacion select').prop('disabled', false);
            $('#formReservacion button[type="submit"]').show();
            $('#btnEliminar').hide();
            $('#modalReservacion').modal('show');
        },


        eventClick: function (info) {
            const event = info.event;
            const props = event.extendedProps;
            const esEditable = props.estado === 'PENDIENTE';

            $('#peticion').val('modificar');
            $('#id_reservacion').val(event.id);
            $('#cedula_cliente').val(props.cedula).trigger('change');
            $('#fecha').val(event.startStr.split('T')[0]);

            const horaInicio = extraerHora(event.startStr);
            const horaFin = extraerHora(event.endStr);

            if (horaInicio) timePickerInicio.setDate(horaInicio);
            else timePickerInicio.clear();

            if (horaFin) timePickerFin.setDate(horaFin);
            else timePickerFin.clear();

            $('#estado').val(props.estado);
            
            // Bloquear campos si no es editable
            $('#formReservacion input, #formReservacion select').prop('disabled', !esEditable);
            $('#formReservacion button[type="submit"]').toggle(esEditable);
            $('#btnEliminar').toggle(esEditable);

            $('#modalReservacion').modal('show');
        },


        eventDrop: function (info) {
            MoverEvento(info, calendar);
        },

        eventResize: function (info) {
            MoverEvento(info, calendar, 'Duración actualizada con éxito');
        }
    });

    calendar.render();
    return calendar;
}

/**
 * Persiste cambios de drag & drop o resize.
 */
export async function MoverEvento(info, calendar, mensajeExito = 'Reservación reprogramada con éxito') {
    const formData = new FormData();
    formData.append('peticion', 'mover');
    formData.append('id_reservacion', info.event.id);
    formData.append('fecha', info.event.startStr.split('T')[0]);
    formData.append('hora', extraerHora(info.event.startStr) ?? '');
    formData.append('hora_fin', extraerHora(info.event.endStr) ?? '');

    try {
        const res = await AjaxHelper.enviaAjax(formData, '?page=reservaciones');
        if (res && res.resultado == 200) {
            MensajeriaHelper.GenerarMensaje('success', 3000, '¡Éxito!', mensajeExito);
        } else {
            info.revert();
            MensajeriaHelper.GenerarMensaje('error', 5000, 'Error', res?.mensaje || 'No se pudo mover');
        }
    } catch {
        info.revert();
    }
}

/**
 * Procesa el envío del formulario.
 */
export async function GestionarEnvio(form, calendar) {
    const formData = new FormData(form);
    
    // Validación básica de tiempo
    const horaInicio = $('#hora').val();
    const horaFin = $('#hora_fin').val();
    
    if (horaInicio && horaFin && horaFin <= horaInicio) {
        MensajeriaHelper.GenerarMensaje('warning', 5000, "Rango inválido", "La hora de fin debe ser posterior a la de inicio.");
        return;
    }

    try {
        const res = await AjaxHelper.enviaAjax(formData, '?page=reservaciones');
        if (res && res.resultado == 200) {
            $('#modalReservacion').modal('hide');
            MensajeriaHelper.GenerarMensaje('success', 2000, "¡Éxito!", res.mensaje);
            calendar.refetchEvents();
        } else {
            MensajeriaHelper.GenerarMensaje('error', 5000, "Error", res?.mensaje || "Error desconocido");
        }
    } catch (e) {
        MensajeriaHelper.GenerarMensaje('error', 5000, "Error crítico", "No se pudo procesar la solicitud");
    }
}

/**
 * Elimina una reservación.
 */
export async function EliminarReservacion(id, calendar) {
    const confirmado = await MensajeriaHelper.MostrarConfirmacion(
        '¿Eliminar reservación?',
        'Esta acción no se puede deshacer.',
        'warning'
    );

    if (confirmado) {
        const formData = new FormData();
        formData.append('peticion', 'eliminar');
        formData.append('id_reservacion', id);

        try {
            const res = await AjaxHelper.enviaAjax(formData, '?page=reservaciones');
            if (res && res.resultado == 200) {
                $('#modalReservacion').modal('hide');
                MensajeriaHelper.GenerarMensaje('success', 2000, "Eliminado", "La reservación ha sido borrada.");
                calendar.refetchEvents();
            } else {
                MensajeriaHelper.GenerarMensaje('error', 5000, "Error", res?.mensaje || "No se pudo eliminar");
            }
        } catch (e) {
            MensajeriaHelper.GenerarMensaje('error', 5000, "Error", "Error al intentar eliminar");
        }
    }
}
