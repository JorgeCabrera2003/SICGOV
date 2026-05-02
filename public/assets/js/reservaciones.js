// ES Module — Lógica del Módulo de Reservaciones
// Sigue el mismo patrón de ingrediente.js: exporta funciones puras de lógica.
// El orquestador de eventos vive en modulo_reservaciones.js

// ─────────────────────────────────────────────
// HELPERS PRIVADOS
// ─────────────────────────────────────────────

/**
 * Formatea el estado del select de Select2 con ícono de persona.
 * @param {object} state
 * @returns {jQuery|string}
 */
function formatarEstadoCliente(state) {
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
 * Guard: retorna null si el string no contiene la parte de tiempo.
 * @param {string} datetimeStr
 * @returns {string|null}
 */
function extraerHora(datetimeStr) {
    if (!datetimeStr || !datetimeStr.includes('T')) return null;
    return datetimeStr.split('T')[1].substring(0, 5);
}

// ─────────────────────────────────────────────
// EXPORTS PÚBLICOS
// ─────────────────────────────────────────────

/**
 * Crea y retorna un mixin Toast de SweetAlert2 reutilizable (DRY).
 * @returns {import('sweetalert2').SweetAlert}
 */
export function crearToast() {
    return Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

/**
 * Inicializa Select2 premium para el selector de clientes.
 * @param {string} selector  - Selector CSS del elemento.
 * @param {jQuery} $parent   - Elemento contenedor del dropdown.
 */
export function inicializarSelect2(selector, $parent) {
    $(selector).select2({
        theme: 'bootstrap-5',
        dropdownParent: $parent,
        width: '100%',
        placeholder: 'Buscar cliente por nombre o cédula...',
        language: {
            noResults: () => "No se encontraron resultados"
        },
        templateResult: formatarEstadoCliente,
        templateSelection: formatarEstadoCliente
    });
}

/**
 * Inicializa los pickers de hora (inicio y fin) con Flatpickr.
 * @returns {{ timePickerInicio: object, timePickerFin: object }}
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
 * Construye y renderiza el FullCalendar completo.
 * @param {HTMLElement} calendarEl - Elemento del DOM donde se montará el calendario.
 * @param {{ timePickerInicio: object, timePickerFin: object }} pickers
 * @returns {FullCalendar.Calendar} Instancia del calendario ya renderizada.
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
            meridiem: 'short'
        },
        slotLabelFormat: {
            hour: 'numeric',
            minute: '2-digit',
            omitZeroMinute: false,
            meridiem: 'short'
        },
        themeSystem: 'bootstrap5',
        editable: true,
        selectable: true,
        droppable: true,
        eventDisplay: 'block',

        // Cargar eventos desde el servidor
        events: function (fetchInfo, successCallback, failureCallback) {
            const formData = new FormData();
            formData.append('peticion', 'listar');
            formData.append('start', fetchInfo.startStr.split('T')[0]);
            formData.append('end', fetchInfo.endStr.split('T')[0]);

            enviaAjax(formData, BASE_URL + '/?page=reservaciones')
                .then(res => {
                    if (res && res.resultado == 200) {
                        successCallback(res.datos);
                    } else {
                        failureCallback();
                    }
                });
        },

        // Click en día vacío → Abrir modal para crear
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

            $('#btnEliminar').hide();
            $('#modalReservacion').modal('show');
        },

        // Click en evento existente → Abrir modal para editar
        eventClick: function (info) {
            const event = info.event;
            const props = event.extendedProps;

            $('#peticion').val('modificar');
            $('#id_reservacion').val(event.id);
            $('#cedula_cliente').val(props.cedula).trigger('change');
            $('#fecha').val(event.startStr.split('T')[0]);

            // Guard: extraerHora retorna null si no hay componente horario (eventos de día completo)
            const horaInicio = extraerHora(event.startStr);
            const horaFin = extraerHora(event.endStr);

            if (horaInicio) timePickerInicio.setDate(horaInicio);
            else timePickerInicio.clear();

            if (horaFin) timePickerFin.setDate(horaFin);
            else timePickerFin.clear();

            $('#estado').val(props.estado);
            $('#btnEliminar').show();
            $('#modalReservacion').modal('show');
        },

        // Drag & Drop → delegar a moverEvento (DRY)
        eventDrop: function (info) {
            moverEvento(info, calendar);
        },

        // Redimensionar evento → delegar a moverEvento (DRY)
        eventResize: function (info) {
            moverEvento(info, calendar, 'Duración actualizada con éxito');
        }
    });

    calendar.render();
    return calendar;
}

/**
 * Persiste el cambio de posición/duración de un evento (eventDrop + eventResize — DRY).
 * @param {object} info            - Objeto de evento de FullCalendar.
 * @param {object} calendar        - Instancia del calendario (para refetch si es necesario).
 * @param {string} mensajeExito    - Texto del toast de éxito.
 */
export async function moverEvento(info, calendar, mensajeExito = 'Reservación reprogramada con éxito') {
    const formData = new FormData();
    formData.append('peticion', 'mover');
    formData.append('id_reservacion', info.event.id);
    formData.append('fecha', info.event.startStr.split('T')[0]);
    formData.append('hora', extraerHora(info.event.startStr) ?? '');
    formData.append('hora_fin', extraerHora(info.event.endStr) ?? '');

    try {
        const res = await enviaAjax(formData, BASE_URL + '/?page=reservaciones');
        if (res && res.resultado == 200) {
            crearToast().fire({ icon: 'success', title: mensajeExito });
        } else {
            info.revert();
        }
    } catch {
        info.revert();
    }
}
