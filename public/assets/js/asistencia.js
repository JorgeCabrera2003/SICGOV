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

function formatearHora(hora) {
  if (!hora) return '';

  const partes = hora.split(':');
  if (partes.length < 2) return hora;

  let horas = parseInt(partes[0], 10);
  const minutos = partes[1].padStart(2, '0');
  const ampm = horas >= 12 ? 'pm' : 'am';

  if (horas === 0) {
    horas = 12;
  } else if (horas > 12) {
    horas -= 12;
  }

  return `${horas.toString().padStart(2, '0')}:${minutos} ${ampm}`;
}

function formatoTipoMarcacion(tipo) {
  const mapa = {
    ENTRADA: { label: 'Entrada'},
    DESCANSO_IN: { label: 'Descanso Iniciado'},
    DESCANSO_OUT: { label: 'Descanso Terminado' },
    SALIDA: { label: 'Salida'}
  };

  const item = mapa[tipo] || { label: tipo ? tipo.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, c => c.toUpperCase()) : ''};
  return { label: item.label, style: item.style };
}

function formatoEstado(estado) {
  const mapa = {
    A_TIEMPO: { label: 'A Tiempo', style: 'bg-success text-white' },
    TARDE: { label: 'Tarde', style: 'bg-warning text-dark' },
    FALTA: { label: 'Falta', style: 'bg-danger text-white' }
  };

  const item = mapa[estado] || { label: estado ? estado.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, c => c.toUpperCase()) : '', style: 'bg-secondary text-white' };
  return { label: item.label, style: item.style };
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
      {
        data: 'fecha',
        className: 'text-center',
        render: function (data, type) {
          if (type === 'display' || type === 'filter') {
            return formatearFecha(data);
          }
          return data;
        }
      },
      {
        data: 'hora',
        className: 'text-center',
        render: function (data, type) {
          if (type === 'display' || type === 'filter') {
            return formatearHora(data);
          }
          return data;
        }
      },
      {
        data: null,
         className: 'text-center' ,
        render: function (data, type, row) {
          const cedula = row.cedula_empleado || data.cedula_empleado || '';
          const formattedCedula = cedula.length > 1 ? cedula.charAt(0) + '-' + cedula.slice(1) : cedula;
          const nombre = row.primer_nombre || '';
          const apellido = row.primer_apellido || '';
          const nombreCompleto = `${nombre}${nombre && apellido ? ' ' : ''}${apellido}`.trim();

          if (type === 'display') {
            if (nombreCompleto) {
              return `
                <div style="line-height:1.2;">
                  <strong>${nombreCompleto}</strong><br>
                  <small class="text-muted">(${formattedCedula})</small>
                </div>
              `;
            }
            return `<div>${formattedCedula}</div>`;
          }

          if (type === 'filter') {
            return `${nombreCompleto} ${formattedCedula}`;
          }

          return cedula;
        }
      },
      {
        data: 'tipo_marcacion',
        className: 'text-center',
        render: function (data, type) {
          const tipo = formatoTipoMarcacion(data);
          if (type === 'display') {
            return tipo.label;
          }
          if (type === 'filter') {
            return tipo.label;
          }
          return data;
        }
      },
      {
        data: 'estado',
        className: 'text-center',
        render: function (data, type) {
          const estado = formatoEstado(data);
          if (type === 'display') {
            return `<span class="badge rounded-pill ${estado.style}">${estado.label}</span>`;
          }
          if (type === 'filter') {
            return estado.label;
          }
          return data;
        }
      },
      {
        data: 'observacion',
        className: 'text-center',
        render: function (data, type) {
          if (type === 'display' || type === 'filter') {
            const text = data || '';
            const safeText = $('<div>').text(text).html();
            return `<div style="white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere; max-width: 340px;">${safeText}</div>`;
          }
          return data;
        }
      },
      {
        data: null,
        className: 'text-center',
        render: function () {
          return botones;
        }
      }
    ],
    responsive: true,
    autoWidth: false,
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

function pendiente(event) {
    if (event && event.preventDefault) {
        event.preventDefault();
    }

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
