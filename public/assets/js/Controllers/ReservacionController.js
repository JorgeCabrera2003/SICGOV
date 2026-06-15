import * as handler from "../Handlers/ReservacionHandler.js";

$(document).ready(function() {
    const calendarEl = document.getElementById('calendarPublico'); // ID compartido
    if (!calendarEl) return;

    
    const pickers = handler.inicializarPickers();

    
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

    
    const calendar = handler.inicializarCalendario(calendarEl, pickers);
    
    
    const esPublico = window.location.search.includes('type=publico');
    if (!esPublico) {
        calendar.setOption('editable', true);
        calendar.setOption('eventResizableFromStart', true);
    }

    
    $('#formReservacion, #formReservarPublico').on('submit', function(e) {
        e.preventDefault();
        handler.GestionarEnvio(this, calendar);
    });

    
    $('#btnEliminar').on('click', function() {
        const id = $('#id_reservacion').val();
        handler.EliminarReservacion(id, calendar);
    });

    
    $('#btnNuevaReservacion, #btnNuevaReservacionMobile').on('click', function() {
        const hoy = new Date().toISOString().split('T')[0];
        handler.abrirModalNueva ? handler.abrirModalNueva(hoy, pickers.timePickerInicio, pickers.timePickerFin) : 
        calendar.select(hoy);
    });
});
