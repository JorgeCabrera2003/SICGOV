// Orquestador de Eventos — Módulo de Reservaciones
// Patrón: igual que modulo_ingrediente.js
// Importa la lógica pura desde reservaciones.js y registra todos los listeners del DOM.

import * as reservaciones from "./reservaciones.js";

// Los módulos ES6 se ejecutan de forma diferida (defer) por el navegador.
// $(document).ready() garantiza que jQuery y el DOM estén disponibles.
$(document).ready(function () {

    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    // ── 1. Select2 para búsqueda de clientes ──────────────────────────────
    if ($('.select2-cliente').length) {
        reservaciones.inicializarSelect2('.select2-cliente', $('#modalReservacion'));
    }

    // ── 2. Flatpickr para hora inicio / fin ───────────────────────────────
    const pickers = reservaciones.inicializarPickers();

    // ── 3. FullCalendar ───────────────────────────────────────────────────
    const calendar = reservaciones.inicializarCalendario(calendarEl, pickers);

    // ── 4. Envío del formulario (Registrar / Modificar) ───────────────────
    $('#formReservacion').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const formData = new FormData(this);
        const url = $(this).attr('action') || (BASE_URL + '/?page=reservaciones');

        enviaAjax(formData, url)
            .then(res => {
                if (res && res.resultado == 200) {
                    $('#modalReservacion').modal('hide');
                    mensajes("success", 2000, "¡Éxito!", res.mensaje);
                    calendar.refetchEvents();
                } else {
                    mensajes("error", 5000, "Error de Validación", res?.mensaje ?? "Error desconocido");
                }
            })
            .catch(err => {
                console.error("Error en la petición:", err);
                mensajes("error", 5000, "Error del Servidor", "No se pudo procesar la reservación.");
            });
    });

    // ── 5. Eliminar reservación ───────────────────────────────────────────
    $('#btnEliminar').on('click', function () {
        const id = $('#id_reservacion').val();

        confirmarAccion(
            "¿Estás seguro?",
            "Esta acción no se puede deshacer.",
            "warning"
        ).then(confirmado => {
            if (!confirmado) return;

            const formData = new FormData();
            formData.append('peticion', 'eliminar');
            formData.append('id_reservacion', id);

            enviaAjax(formData, BASE_URL + '/?page=reservaciones')
                .then(res => {
                    if (res?.resultado == 200) {
                        $('#modalReservacion').modal('hide');
                        mensajes("success", 2000, "Eliminado", "La reservación ha sido borrada.");
                        calendar.refetchEvents();
                    }
                });
        });
    });

});
