import * as noticias from "../Handlers/NoticiasHandler.js";

//-------INICIALIZACIÓN-------

$(document).ready(function () {
    noticias.CargarNoticias();
    noticias.CapaValidar();
});

// EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$('#btnNuevaNoticia').on('click', function () {
    noticias.LimpiarFormulario();
    noticias.EditarModal('registrar');
});

$('#formNoticia').on('submit', async function (e) {
    e.preventDefault();
    await noticias.EnviarFormulario();
});

$('#imagenes').on('change', function(e) {
    noticias.ManejarCambioImagenes(e);
});

$('#btnAbrirGaleria').on('click', function() {
    MediaPicker.open({
        onSelect: function(ruta) {
            noticias.AgregarImagenGaleria(ruta);
        }
    });
});

// Delegación de eventos para la tabla
$(document).on('click', '.btn-editar', function () {
    const modulo = $(this).attr("data-modulo");
    if (modulo === 'Noticia') {
        const linea = $(this).closest('tr');
        const tabla = $('#tablaNoticias').DataTable();
        const datosFila = tabla.row(linea).data();
        noticias.EditarFormNoticia(datosFila);
    }
});

$(document).on('click', '.btn-ver-publico', function () {
    const id = $(this).attr("data-id");
    noticias.VerNoticiaPublica(id);
});

$(document).on('click', '.btn-eliminar', function () {
    const modulo = $(this).attr("data-modulo");
    if (modulo === 'Noticia') {
        const id = $(this).attr("data-id");
        noticias.EliminarNoticia(id);
    }
});

// Comportamiento de hover para el botón de acciones en la tabla
$(document).on({
    mouseenter: function() {
        const $toggle = $(this).find('.dropdown-toggle');
        if ($toggle.length) {
            const instance = bootstrap.Dropdown.getOrCreateInstance($toggle[0]);
            instance.show();
        }
    },
    mouseleave: function() {
        const $toggle = $(this).find('.dropdown-toggle');
        if ($toggle.length) {
            const instance = bootstrap.Dropdown.getOrCreateInstance($toggle[0]);
            instance.hide();
        }
    }
}, '#tablaNoticias .dropdown');
