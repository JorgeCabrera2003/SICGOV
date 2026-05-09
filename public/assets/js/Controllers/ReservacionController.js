import * as reservaciones from "../Handlers/ReservacionHandler.js";

$(document).ready(function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    // ── 1. Inicialización de Pickers de Hora ──────────────────────────────
    const pickers = reservaciones.inicializarPickers();

    // ── 2. Inicialización del Calendario ──────────────────────────────────
    const calendar = reservaciones.inicializarCalendario(calendarEl, pickers);

    // ── 3. Select2 para Clientes ─────────────────────────────────────────
    if ($('.select2-cliente').length) {
        $('.select2-cliente').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalReservacion'),
            width: '100%',
            placeholder: 'Buscar cliente por nombre o cédula...',
            language: { noResults: () => "No se encontraron resultados" },
            templateResult: reservaciones.formatarEstadoCliente,
            templateSelection: reservaciones.formatarEstadoCliente
        });
    }

    // ── 4. Eventos del Formulario ────────────────────────────────────────
    $('#formReservacion').on('submit', function (e) {
        e.preventDefault();
        reservaciones.GestionarEnvio(this, calendar);
    });

    // ── 5. Evento de Eliminación ─────────────────────────────────────────
    $('#btnEliminar').on('click', function (e) {
        e.preventDefault();
        const id = $('#id_reservacion').val();
        if (id) {
            reservaciones.EliminarReservacion(id, calendar);
        }
    });
});
