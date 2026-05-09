import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";

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
        timePickerInicio: flatpickr("#horaPublica", configBase),
        timePickerFin: flatpickr("#hora_finPublica", configBase)
    };
}

export function inicializarCalendario(calendarEl, pickers) {
    const { timePickerInicio, timePickerFin } = pickers;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        selectable: true,
        unselectAuto: false, // Mantener selección visual
        
        events: async function (fetchInfo, successCallback, failureCallback) {
            const formData = new FormData();
            formData.append('peticion', 'listar');

            try {
                const res = await AjaxHelper.enviaAjax(formData, '?page=reservar');
                if (res && res.datos) {
                    successCallback(res.datos);
                    // Actualizar lista de ocupados para el día seleccionado tras cargar eventos
                    const selectedDate = calendar.getDate().toISOString().split('T')[0];
                    renderizarOcupados(res.datos, selectedDate);
                } else {
                    failureCallback();
                }
            } catch (e) {
                failureCallback();
            }
        },

        select: function (info) {
            const selectedDate = info.startStr.split('T')[0];
            $('#fechaPublica').val(selectedDate);
            $('#selectedDateLabel').text(formatarFechaLegible(selectedDate));
            
            // Filtrar eventos cargados para este día y renderizar tarjetas
            const allEvents = calendar.getEvents().map(e => ({
                title: e.title,
                start: e.start,
                end: e.end,
                extendedProps: e.extendedProps
            }));
            renderizarOcupados(allEvents, selectedDate);
        },

        eventClick: function (info) {
            if (info.event.extendedProps.ocupado) return;
            abrirDetallePropio(info.event, timePickerInicio, timePickerFin);
        }
    });

    calendar.render();
    return calendar;
}

function renderizarOcupados(eventos, fecha) {
    const $container = $('#occupiedList');
    $container.empty();

    const eventosDelDia = eventos.filter(e => {
        const start = e.start instanceof Date ? e.start.toISOString() : e.start;
        return start.startsWith(fecha);
    });

    if (eventosDelDia.length === 0) {
        $container.append(`
            <div class="text-center py-4 text-muted opacity-50 border rounded-4 border-dashed">
                <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                <span class="small">Todo disponible para este día</span>
            </div>
        `);
        return;
    }

    eventosDelDia.sort((a, b) => new Date(a.start) - new Date(b.start));

    eventosDelDia.forEach(e => {
        const hInicio = formatarHora(e.start);
        const hFin = formatarHora(e.end);
        const esMio = !e.extendedProps.ocupado;

        $container.append(`
            <div class="occupied-card ${esMio ? 'is-own' : ''} animate__animated animate__fadeInUp">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark">${hInicio} - ${hFin}</div>
                        <div class="small ${esMio ? 'text-primary' : 'text-muted'}">
                            ${esMio ? '<i class="bi bi-person-check me-1"></i>Tu Reservación' : '<i class="bi bi-slash-circle me-1"></i>Ocupado'}
                        </div>
                    </div>
                    <i class="bi ${esMio ? 'bi-star-fill text-warning' : 'bi-lock-fill opacity-25'}"></i>
                </div>
            </div>
        `);
    });
}

function formatarFechaLegible(isoDate) {
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(isoDate + 'T00:00:00').toLocaleDateString('es-ES', opciones);
}

function formatarHora(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleTimeString('es-ES', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function abrirDetallePropio(event, timePickerInicio, timePickerFin) {
    $('#formReservarPublico')[0].reset();
    $('#fechaPublica').val(event.startStr.split('T')[0]);
    
    const hInicio = event.start.toTimeString().split(' ')[0].substring(0, 5);
    const hFin = event.end ? event.end.toTimeString().split(' ')[0].substring(0, 5) : '';
    
    timePickerInicio.setDate(hInicio);
    timePickerFin.setDate(hFin);

    $('#formReservarPublico input').prop('disabled', true);
    $('#formReservarPublico button[type="submit"]').hide();
    $('.modal-title').text('Detalle de mi Cita');
    $('#modalPublico').modal('show');
}

export function abrirModalNueva(info, timePickerInicio, timePickerFin) {
    // Si info viene del calendario
    const fecha = info.startStr ? info.startStr.split('T')[0] : info;

    $('#formReservarPublico')[0].reset();
    $('#formReservarPublico input').prop('disabled', false);
    $('#fechaPublica').val(fecha).prop('readonly', true);
    $('#formReservarPublico button[type="submit"]').show();
    $('.modal-title').text('Solicitar Reservación');

    timePickerInicio.clear();
    timePickerFin.clear();
    $('#modalPublico').modal('show');
}

export async function EnviarSolicitud(form, calendar) {
    const formData = new FormData(form);
    
    try {
        const res = await AjaxHelper.enviaAjax(formData, '?page=reservar');
        if (res && res.resultado == 200) {
            $('#modalPublico').modal('hide');
            MensajeriaHelper.GenerarMensaje('success', 3000, "¡Solicitud Enviada!", "Te contactaremos pronto para confirmar.");
            calendar.refetchEvents();
        } else {
            MensajeriaHelper.GenerarMensaje('error', 5000, "Error", res?.mensaje || "No se pudo enviar");
        }
    } catch (e) {
        MensajeriaHelper.GenerarMensaje('error', 5000, "Error", "Error de conexión");
    }
}

