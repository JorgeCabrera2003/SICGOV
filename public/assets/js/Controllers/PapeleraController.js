import * as papelera from "../Handlers/PapeleraHandler.js";

$(document).ready(function () {
    papelera.CargarPapelera();

    // Eventos delegados para la tabla
    $(document).on('click', '.btn-restaurar', function (e) {
        e.preventDefault();
        const id = $(this).attr('data-id');
        const modulo = $(this).attr('data-modulo');
        papelera.RestaurarRegistro(modulo, id);
    });

    $(document).on('click', '.btn-eliminar-permanente', function (e) {
        e.preventDefault();
        const id = $(this).attr('data-id');
        const modulo = $(this).attr('data-modulo');
        papelera.EliminarPermanente(modulo, id);
    });
});
