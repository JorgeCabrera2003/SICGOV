$(document).ready(function () {

  crearDataTable();
  //registrarEntrada();
  

});

$("#btnMarcarAsistencia").on("click", function () {
    pendiente();
  /*
   limpia();
   $('#modalTitleTextAsistencia').text('Marcar Asistencia');
    $('#btnAsistenciaForm').text('Registrar');
    $('#modalAsistencia').modal('show');
    */
 
});

async function crearDataTable() {

  let peticion = new FormData();
  let json = null;
  let arreglo = [];
  let botones = '';
  botones = await botonAcciones();

  try {
    peticion.append('peticion', 'consultar');
    json = await enviaAjax(peticion);
    arreglo = json.datos;
  } catch (error) {
    arreglo = [];
  }

  if ($.fn.DataTable.isDataTable('#tablaAsistencia')) {
    $('#tablaAsistencia').DataTable().destroy();
  }

  $('#tablaAsistencia').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'fecha' },
      { data: 'hora' },
      { 
        data: 'cedula_empleado',
        render: function (data, type) {
          if (!data) return data;
          const formatted = (data.length > 1) ? data.charAt(0) + '-' + data.slice(1) : data;
          if (type === 'display') return formatted;
          if (type === 'filter') return data + ' ' + formatted;
          return data;
        }
      },
      { data: 'tipo_marcacion' },
      { data: 'estado' },
      { data: 'observacion' },

      {
        data: null,
        render: function () {
          return botones;
        }
      }
    ],
    order: [[0, 'desc']],
    language: { url: idiomaTabla }
  });
}

async function botonAcciones() {
    
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
        .attr('onclick', 'pendiente()')
        .html('<i class="fa-solid fa-pen-to-square me-2"></i>Editar');
    itemEditar.append(linkEditar);

    menu.append(itemEditar);
    dropdown.append(boton, menu);

    return dropdown.prop('outerHTML');
}

function pendiente() {

    event.preventDefault();
    Swal.fire({
        title: 'Funcionalidad Pendiente',
        text: 'El marcado de la asistencia aún no está disponible. Por favor, inténtelo más tarde.',
        icon: 'info',
        confirmButtonText: 'Entendido',
        timer: 5000,
        timerProgressBar: true,
    });
    

}