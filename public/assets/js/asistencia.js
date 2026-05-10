$(document).ready(function () {
  crearDataTable();
  inicializarValidacionAsistencia();
});

function inicializarValidacionAsistencia() {
  const $tipoDoc = $('#tipo_doc');
  const $cedulaEmpleado = $('#cedula_empleado');
  const $tipoMarcacion = $('#tipo_marcacion');

  $tipoDoc.on('focus', function () {
    $(this).data('touched', true);
  });
  $tipoDoc.on('change blur', validarTipoDoc);

  $cedulaEmpleado.on('focus', function () {
    $(this).data('touched', true);
  });
  $cedulaEmpleado.on('keypress', function (e) {
    validarKeyPress(/\d/, e);
  });
  $cedulaEmpleado.on('keyup blur', validarCedulaEmpleado);

  $tipoMarcacion.on('focus', function () {
    $(this).data('touched', true);
  });
  $tipoMarcacion.on('change blur', validarTipoMarcacion);

  $('#btnAsistenciaForm').on('click', async function () {
    if (!validarFormularioAsistencia()) {
      return;
    }

    const peticion = new FormData();
    peticion.append('peticion', 'registrar');
    peticion.append('tipo_doc', $('#tipo_doc').val());
    peticion.append('cedula_empleado', $('#cedula_empleado').val().trim());
    peticion.append('tipo_marcacion', $('#tipo_marcacion').val());
    peticion.append('observacion', $('#observacion').val().trim());

    const json = await enviaAjax(peticion, BASE_URL + '?page=asistencia');

    if (json && json.resultado === 200) {
      mensajes('success', 5000, json.mensaje || 'Asistencia registrada correctamente');
      $('#modalAsistencia').modal('hide');
      crearDataTable();
    } else {
      mensajes('error', 5000, (json && json.mensaje) ? json.mensaje : 'No se pudo registrar la asistencia');
    }
  });
}

$("#btnMarcarAsistencia").on("click", function () {
  limpia();
  $('#modalTitleTextAsistencia').text('Marcar Asistencia');
  $('#btnAsistenciaForm').text('Registrar');
  $('#modalAsistencia').modal('show');
});

function validarTipoDoc() {
  const $tipoDoc = $('#tipo_doc');
  const valor = $tipoDoc.val();
  const valido = valor && valor !== 'default';

  if (valor !== 'default' || $tipoDoc.data('touched')) {
    SistemaValidacion.aplicarEstilos($tipoDoc, valido, 'Selecciona el tipo de documento.');
  } else {
    SistemaValidacion.limpiarEstilosCampo($tipoDoc);
  }

  return valido;
}

function validarCedulaEmpleado() {
  const $cedulaEmpleado = $('#cedula_empleado');
  const valor = $cedulaEmpleado.val() ? $cedulaEmpleado.val().trim() : '';
  const valido = /^\d{7,9}$/.test(valor);

  if (valor !== '' || $cedulaEmpleado.data('touched')) {
    SistemaValidacion.aplicarEstilos($cedulaEmpleado, valido, 'La cédula debe contener entre 7 y 9 dígitos.');
  } else {
    SistemaValidacion.limpiarEstilosCampo($cedulaEmpleado);
  }

  return valido;
}

function validarTipoMarcacion() {
  const $tipoMarcacion = $('#tipo_marcacion');
  const valor = $tipoMarcacion.val();
  const valido = valor && valor !== 'default';

  if (valor !== 'default' || $tipoMarcacion.data('touched')) {
    SistemaValidacion.aplicarEstilos($tipoMarcacion, valido, 'Selecciona el tipo de marcación.');
  } else {
    SistemaValidacion.limpiarEstilosCampo($tipoMarcacion);
  }

  return valido;
}

function validarFormularioAsistencia() {
  const validoTipoDoc = validarTipoDoc();
  const validoCedula = validarCedulaEmpleado();
  const validoTipoMarcacion = validarTipoMarcacion();

  return validoTipoDoc && validoCedula && validoTipoMarcacion;
}

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

function limpia() {
  // Limpiar campos específicos de asistencia
  $('#tipo_doc').val("default").prop("disabled", false);
  $('#cedula_empleado').val("").prop("readOnly", false);
  $('#tipo_marcacion').val("default").prop("disabled", false);
  $('#observacion').val("").prop("readOnly", false);

  // Resetear estado visual de validación
  SistemaValidacion.limpiarValidacion({
    tipo_doc: $('#tipo_doc'),
    cedula_empleado: $('#cedula_empleado'),
    tipo_marcacion: $('#tipo_marcacion'),
    observacion: $('#observacion')
  });
}
