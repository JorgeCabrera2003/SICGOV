// MODULO DE CATEGORÍAS DE MENÚ

// Interfaz de Acceso a Elementos del Formulario
function etiquetasFormulario() {
    return {
        peticion: $('#peticionCategoria'),
        id_categoria: $('#id_categoria'),
        nombre_categoria: $('#nombre_categoria'),
        descripcion: $('#descripcion_categoria'),
        estatus: $('#estatus_categoria')
    };
}

// Interfaz de Acceso al Modal Principal
function etiquetasModal() {
    return {
        modal: $('#modalCategoria'),
        titulo: $('#tituloModalCategoria'),
        boton: $('#btnGuardarCategoria'),
        divEstatus: $('#divEstatusCategoria'),
        formulario: $('#formCategoria')
    };
}

// Editar la configuración visual del Modal dependiendo de la operación
function editarModal(operacion) {
    let titulo = "";
    let boton = "";
    let eti = etiquetasModal();

    if (operacion === 'registrar') {
        titulo = "Nueva Categoría";
        boton = "Guardar Categoría";
        eti.divEstatus.hide();
    }
    
    if (operacion === 'modificar') {
        titulo = "Actualizar Categoría";
        boton = "Actualizar Categoría";
        eti.divEstatus.show();
    }

    if (operacion === 'eliminar') {
        titulo = "Eliminar Categoría";
        boton = "Confirmar Eliminación";
        eti.divEstatus.hide();
    }

    eti.titulo.text(titulo);
    eti.boton.html(`<i class="fas ${operacion === 'eliminar' ? 'fa-trash' : 'fa-save'} me-2"></i>${boton}`);
    eti.modal.modal("show");
}

function limpiar() {
    let input = etiquetasFormulario();
    let eti = etiquetasModal();
    
    eti.formulario[0].reset();
    input.id_categoria.val("");
    input.peticion.val("registrar");
    
    eti.formulario.removeClass('was-validated');
    input.nombre_categoria.prop("readOnly", false);
    input.descripcion.prop("readOnly", false);
}

// Enviar datos
async function enviarDatos(operacion) {
    let input = etiquetasFormulario();
    let modal = etiquetasModal();
    let form = modal.formulario[0];

    // Validación Básica
    if (operacion !== 'eliminar' && !form.checkValidity()) {
        form.classList.add('was-validated');
        mensajes("error", 5000, "Error de Validación", "Por favor completa correctamente los campos obligatorios.");
        return;
    }

    let confirmacion = false;
    let tituloAccion = "";
    if (operacion === "registrar") tituloAccion = "Se registrará una nueva Categoría";
    if (operacion === "modificar") tituloAccion = "Se actualizará la Categoría";

    confirmacion = await confirmarAccion(tituloAccion, "¿Está seguro de realizar la acción?", operacion === 'eliminar' ? 'warning' : 'question');

    if (confirmacion) {
        modal.boton.prop('disabled', true);
        
        let peticionData = new FormData(form);
        peticionData.set('peticion', operacion);
        
        if (operacion === "registrar" || operacion === "modificar") {
             if (operacion === "modificar") {
                 peticionData.set('estatus', input.estatus.is(':checked') ? 1 : 0);
             }
        }

        try {
            let json = await enviaAjax(peticionData);

            if (json.resultado >= 200 && json.resultado < 300) {
                modal.modal.modal("hide");
                crearDataTable();
                mensajes("success", 3000, "Éxito", json.mensaje);
            } else {
                mensajes("error", 5000, "Error", json.mensaje || "Ocurrió un error inesperado.");
            }
        } catch (error) {
            mensajes("error", 5000, "Error", "Error de comunicación con el servidor.");
        } finally {
            modal.boton.prop('disabled', false);
        }
    }
}

// Rellenar datos
function rellenar(pos, accion) {
    limpiar();
    
    let input = etiquetasFormulario();
    const linea = $(pos).closest('tr');
    const tabla = $('#tablaCategoria').DataTable();
    const datosFila = tabla.row(linea).data();
    
    input.id_categoria.val(datosFila.id_categoria);
    input.nombre_categoria.val(datosFila.nombre_categoria);
    input.descripcion.val(datosFila.descripcion);
    input.estatus.prop('checked', datosFila.estatus == 1);
    
    if (accion === 0) { // Editar
        input.peticion.val("modificar");
        editarModal("modificar");
    }
}

// Función exclusiva para Cambiar Estatus directamente sin Modal
async function cambiarEstatus(pos) {
    const linea = $(pos).closest('tr');
    const tabla = $('#tablaCategoria').DataTable();
    const datosFila = tabla.row(linea).data();
    
    let nuevoEstatus = datosFila.estatus == 1 ? 0 : 1;
    let textoAccion = datosFila.estatus == 1 ? "desactivará" : "reactivará";
    
    let confirmacion = await confirmarAccion(`Se ${textoAccion} la Categoría`, "¿Está seguro de realizar la acción?", "warning");
    
    if (confirmacion) {
        let peticionData = new FormData();
        peticionData.append('peticion', 'cambiar_estatus');
        peticionData.append('id_categoria', datosFila.id_categoria);
        peticionData.append('estatus', nuevoEstatus);
        
        try {
            let json = await enviaAjax(peticionData);

            if (json.resultado >= 200 && json.resultado < 300) {
                crearDataTable();
                mensajes("success", 3000, "Éxito", json.mensaje);
            } else {
                mensajes("error", 5000, "Error", json.mensaje || "Ocurrió un error inesperado.");
            }
        } catch (error) {
            mensajes("error", 5000, "Error", "Error de comunicación con el servidor.");
        }
    }
}

// Menú de acciones en DataTable
async function vistaPermisoCategoria() {
    const dropdown = $('<div>').addClass('dropdown');
    const boton = $('<button>').addClass('btn btn-sm bg-body text-body border dropdown-toggle')
      .attr('type', 'button')
      .attr('data-bs-toggle', 'dropdown')
      .html('<i class="fas fa-ellipsis-v me-2"></i>Acciones');
  
    const menu = $('<ul>').addClass('dropdown-menu dropdown-menu-end');
  
    const itemEditar = $('<li>');
    const linkEditar = $('<a>')
      .addClass('dropdown-item text-primary')
      .attr('href', '#')
      .attr('onclick', 'rellenar(this, 0)')
      .html('<i class="fa-solid fa-pen-to-square me-2"></i>Editar');
    itemEditar.append(linkEditar);
  
    const separador = $('<li>').html('<hr class="dropdown-divider">');
  
    const itemEliminar = $('<li>');
    const linkEliminar = $('<a>')
      .addClass('dropdown-item text-danger')
      .attr('href', '#')
      .attr('onclick', 'cambiarEstatus(this)')
      .html('<i class="fa-solid fa-power-off me-2"></i>Cambiar Estatus');
    itemEliminar.append(linkEliminar);
  
    menu.append(itemEditar, separador, itemEliminar);
    dropdown.append(boton, menu);
  
    return dropdown.prop('outerHTML');
}

// Crear DataTable
async function crearDataTable() {
    let peticion = new FormData();
    let arreglo = [];
    let botones = await vistaPermisoCategoria();
  
    try {
        peticion.append('peticion', 'consultar');
        let json = await enviaAjax(peticion);
        arreglo = json || [];
    } catch (error) {
        arreglo = [];
    }
  
    if ($.fn.DataTable.isDataTable('#tablaCategoria')) {
        $('#tablaCategoria').DataTable().destroy();
    }
  
    $('#tablaCategoria').DataTable({
        processing: true,
        data: arreglo,
        columns: [
            { 
                data: 'nombre_categoria',
                render: function (data) {
                    return `<strong>${data}</strong>`;
                }
            },
            { 
                data: 'descripcion',
                render: function (data) {
                    return data ? data : '<span class="text-muted">Sin descripción</span>';
                }
            },
            { 
                data: 'estatus',
                render: function(data) {
                    return data == 1 
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>';
                }
            },
            {
                data: null,
                className: 'text-end',
                render: function () {
                    return botones;
                }
            }
        ],
        order: [[0, 'asc']],
        language: { url: idiomaTabla } // Asumiento que idiomaTabla es una config global
    });
}

// Event Listeners
$(document).ready(function () {
    crearDataTable();
  
    $("#btnNuevaCategoria").on("click", function () {
        limpiar();
        editarModal("registrar");
    });
    
    $("#btnGuardarCategoria").on("click", function (e) {
        e.preventDefault();
        let peticion = $('#peticionCategoria').val();
        enviarDatos(peticion);
    });
});
