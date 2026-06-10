import * as handler from "../Handlers/ReservacionHandler.js";

$(document).ready(function() {
    const calendarEl = document.getElementById('calendarPublico'); // ID compartido
    if (!calendarEl) return;

    // Inicializar Pickers de hora
    const pickers = handler.inicializarPickers();

    // Configurar Select2 solo si no es público (vista admin)
    const $selectCliente = $('#cedula_cliente');
    if ($selectCliente.length) {
        $selectCliente.select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalReservacion'),
            placeholder: 'Seleccione un cliente',
            width: '100%',
            templateResult: handler.formatarEstadoCliente,
            templateSelection: handler.formatarEstadoCliente
        });
    }

    // Inicializar Calendario
    const calendar = handler.inicializarCalendario(calendarEl, pickers);

    // Eventos de formulario
    $('#formReservacion, #formReservarPublico').on('submit', function(e) {
        e.preventDefault();
        handler.GestionarEnvio(this, calendar);
    });

    // Botón eliminar (Admin)
    $('#btnEliminar').on('click', function() {
        const id = $('#id_reservacion').val();
        handler.EliminarReservacion(id, calendar);
    });

    // Botón Nueva Reservación (Manual)
    $('#btnNuevaReservacion, #btnNuevaReservacionMobile').on('click', function() {
        const hoy = new Date().toISOString().split('T')[0];
        handler.abrirModalNueva ? handler.abrirModalNueva(hoy, pickers.timePickerInicio, pickers.timePickerFin) : 
        calendar.select(hoy);
    });
});
