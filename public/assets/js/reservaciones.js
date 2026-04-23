document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    // Helper para estilizar Select2 (Tipo Librería)
    const inicializarBuscadorPremium = (selector, parent) => {
        $(selector).select2({
            theme: 'bootstrap-5',
            dropdownParent: parent,
            width: '100%',
            placeholder: '🔍 Buscar cliente por nombre o cédula...',
            language: {
                noResults: () => "No se encontraron resultados"
            },
            templateResult: formatState, // Mejora visual en la lista
            templateSelection: formatState // Mejora visual al seleccionar
        });
    };

    function formatState (state) {
        if (!state.id) return state.text;
        return $(`<span><i class="bi bi-person-circle me-2 text-primary"></i>${state.text}</span>`);
    };

    if ($('.select2-cliente').length) {
        inicializarBuscadorPremium('.select2-cliente', $('#modalReservacion'));
    }

    // Inicializar FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventTimeFormat: { // Formato 12h (AM/PM)
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        themeSystem: 'bootstrap5',
        editable: true, // Habilitar Drag & Drop
        selectable: true,
        droppable: true,
        eventDisplay: 'block',
        
        // Cargar eventos desde el servidor
        events: function(fetchInfo, successCallback, failureCallback) {
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

        // Click en un día vacío (Crear)
        select: function(info) {
            $('#formReservacion')[0].reset();
            $('#peticion').val('registrar');
            $('#id_reservacion').val('');
            $('#cedula_cliente').val('').trigger('change'); // Reset Select2
            $('#fecha').val(info.startStr.split('T')[0]);
            
            if (info.view.type !== 'dayGridMonth') {
                const hora = info.start.toTimeString().split(' ')[0].substring(0, 5);
                $('#hora').val(hora);
            }

            $('#btnEliminar').hide();
            $('#modalReservacion').modal('show');
        },

        // Click en un evento existente (Editar)
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;

            $('#peticion').val('modificar');
            $('#id_reservacion').val(event.id);
            $('#cedula_cliente').val(props.cedula).trigger('change'); // Update Select2
            $('#fecha').val(event.startStr.split('T')[0]);
            $('#hora').val(event.startStr.split('T')[1].substring(0, 5));
            $('#estado').val(props.estado);

            $('#btnEliminar').show();
            $('#modalReservacion').modal('show');
        },

        // Drag & Drop (Mover)
        eventDrop: function(info) {
            const formData = new FormData();
            formData.append('peticion', 'mover');
            formData.append('id_reservacion', info.event.id);
            formData.append('fecha', info.event.startStr.split('T')[0]);
            formData.append('hora', info.event.startStr.split('T')[1].substring(0, 5));

            enviaAjax(formData, BASE_URL + '/?page=reservaciones')
                .then(res => {
                    if (res && res.resultado == 200) {
                        // Notificación tipo Toast (Superior Derecha)
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Reservación reprogramada con éxito'
                        });
                    } else {
                        info.revert();
                    }
                })
                .catch(() => info.revert());
        }
    });

    calendar.render();

    // Manejar envío del formulario
    $('#formReservacion').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const formData = new FormData(this);
        const url = $(this).attr('action') || (BASE_URL + '/?page=reservaciones');

        enviaAjax(formData, url)
            .then(res => {
                if (res && res.resultado == 200) {
                    $('#modalReservacion').modal('hide');
                    mensajes("success", 2000, "¡Éxito!", res.mensaje);
                    if (calendar) calendar.refetchEvents();
                } else {
                    const errorMsg = res ? res.mensaje : "Error desconocido";
                    mensajes("error", 5000, "Error de Validación", errorMsg);
                }
            })
            .catch(err => {
                console.error("Error en la petición:", err);
                mensajes("error", 5000, "Error del Servidor", "No se pudo procesar la reservación.");
            });
    });

    // Manejar eliminación
    $('#btnEliminar').on('click', function() {
        const id = $('#id_reservacion').val();
        confirmarAccion(
            "¿Estás seguro?",
            "Esta acción no se puede deshacer.",
            "warning"
        ).then(confirmado => {
            if (confirmado) {
                const formData = new FormData();
                formData.append('peticion', 'eliminar');
                formData.append('id_reservacion', id);

                enviaAjax(formData, BASE_URL + '/?page=reservaciones')
                    .then(res => {
                        if (res.resultado == 200) {
                            $('#modalReservacion').modal('hide');
                            mensajes("success", 2000, "Eliminado", "La reservación ha sido borrada.");
                            calendar.refetchEvents();
                        }
                    });
            }
        });
    });
});
