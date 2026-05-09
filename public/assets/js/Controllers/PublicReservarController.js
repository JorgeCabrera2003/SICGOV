import * as handler from "../Handlers/PublicReservarHandler.js";

$(document).ready(function () {
    const calendarEl = document.getElementById('calendarPublico');
    if (!calendarEl) return;

    const pickers = handler.inicializarPickers();
    const calendar = handler.inicializarCalendario(calendarEl, pickers);

    $('#formReservarPublico').on('submit', function (e) {
        e.preventDefault();
        handler.EnviarSolicitud(this, calendar);
    });

    $('#btnNuevaReservacion, #btnNuevaReservacionMobile').on('click', function() {
        const selectedDate = $('#fechaPublica').val() || new Date().toISOString().split('T')[0];
        handler.abrirModalNueva(selectedDate, pickers.timePickerInicio, pickers.timePickerFin);
    });

});
