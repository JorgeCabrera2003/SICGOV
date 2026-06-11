import * as AjaxHelper from "../Helpers/AjaxHelper.js";

const ENDPOINT = BASE_URL + '?page=asistencia';
let currentAsistenciaRow = null;

export function init() {
  bindValidationEvents();
  bindModalEvents();
  bindDatatableActions();
}

export function openRegisterModal() {
  resetForm();
  $('#modalTitleTextAsistencia').text('Marcar Asistencia');
  $('#btnAsistenciaForm').text('Registrar');
  $('#modalAsistencia').modal('show');
}

export async function submitAsistenciaForm() {
  if (!validarFormularioAsistencia()) {
    return { resultado: 400, mensaje: 'Complete los datos obligatorios.' };
  }

  const peticion = new FormData();
  peticion.append('peticion', 'registrar');
  peticion.append('tipo_doc', $('#tipo_doc').val());
  peticion.append('cedula_empleado', $('#cedula_empleado').val().trim());
  peticion.append('tipo_marcacion', $('#tipo_marcacion').val());
  peticion.append('observacion', $('#observacion').val().trim());

  return await AjaxHelper.enviaAjax(peticion, ENDPOINT);
}

export function renderDataTable(arreglo) {
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
        className: 'text-center',
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
          if (type === 'display' || type === 'filter') {
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
        render: function (data, type, row) {
          return `
            <div class="dropdown">
              <button class="btn btn-sm bg-body text-body border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v me-2"></i>Acciones
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <button type="button" class="dropdown-item btn-observacion text-primary" data-id="${row.id_asistencia}">
                    <i class="fa-solid fa-pen-to-square me-2"></i>Gestionar Observaciones
                  </button>
                </li>
              </ul>
            </div>
          `;
        }
      }
    ],
    responsive: true,
    autoWidth: false,
    order: [[0, 'desc']],
    language: { url: idiomaTabla }
  });
}

export function pending() {
  Swal.fire({
    title: 'Funcionalidad Pendiente',
    text: 'Esta función aún no está disponible. Por favor, inténtelo más tarde.',
    icon: 'info',
    confirmButtonText: 'Entendido',
    timer: 5000,
    timerProgressBar: true,
  });
}

function bindValidationEvents() {
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
}

function bindModalEvents() {
  $('#btnAgregarObservacion').on('click', async function () {
    await submitObservacion();
  });

  $('#observacionActual').on('click', '.btn-eliminar-observacion', async function () {
    const index = parseInt($(this).data('index'), 10);
    if (isNaN(index) || index < 0) {
      return mensajes('error', 5000, 'Índice de observación inválido.');
    }

    const confirmacion = await Swal.fire({
      title: 'Eliminar observación',
      text: '¿Deseas eliminar esta observación?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      reverseButtons: true
    });

    if (confirmacion.isConfirmed) {
      await eliminarObservacion(index);
    }
  });
}

function bindDatatableActions() {
  $(document).on('click', '.btn-observacion', function () {
    openObservacionModal(this);
  });
}

export function openObservacionModal(button) {
  const table = $('#tablaAsistencia').DataTable();
  const row = table.row($(button).closest('tr')).data();

  if (!row || !row.id_asistencia) {
    return mensajes('error', 5000, 'No se encontró la asistencia seleccionada.');
  }

  currentAsistenciaRow = row;
  const estado = formatoEstado(row.estado);
  const tipo = formatoTipoMarcacion(row.tipo_marcacion);

  $('#observacionEmpleado').text(row.primer_nombre ? `${row.primer_nombre} ${row.primer_apellido || ''}`.trim() : row.cedula_empleado);
  $('#observacionFechaHora').text(`${formatearFecha(row.fecha)} ${formatearHora(row.hora)}`);
  $('#observacionTipo').text(tipo.label);
  $('#observacionEstado').html(`<span class="badge rounded-pill ${estado.style}">${estado.label}</span>`);
  renderObservacionesPrevias(row.observacion);
  $('#observacionInput').val('').focus();
  $('#modalObservacion').modal('show');
}

async function submitObservacion() {
  const observacion = $('#observacionInput').val().trim();

  if (!observacion) {
    return mensajes('warning', 4000, 'Escribe una observación para continuar.');
  }

  if (!currentAsistenciaRow || !currentAsistenciaRow.id_asistencia) {
    return mensajes('error', 5000, 'No se encontró la asistencia seleccionada.');
  }

  const peticion = new FormData();
  peticion.append('peticion', 'agregar_observacion');
  peticion.append('id_asistencia', currentAsistenciaRow.id_asistencia);
  peticion.append('observacion', observacion);

  const json = await AjaxHelper.enviaAjax(peticion, ENDPOINT);

  if (json && json.resultado === 200) {
    currentAsistenciaRow.observacion = json.datos.observacion;
    renderObservacionesPrevias(currentAsistenciaRow.observacion);
    $('#observacionInput').val('').focus();
    mensajes('success', 4000, json.mensaje || 'Observación agregada correctamente');
    actualizarFilaActual();
  } else {
    mensajes('error', 5000, (json && json.mensaje) ? json.mensaje : 'No se pudo agregar la observación');
  }

  return json;
}

async function eliminarObservacion(index) {
  if (!currentAsistenciaRow || !currentAsistenciaRow.id_asistencia) {
    return mensajes('error', 5000, 'No se encontró la asistencia seleccionada.');
  }

  const peticion = new FormData();
  peticion.append('peticion', 'eliminar_observacion');
  peticion.append('id_asistencia', currentAsistenciaRow.id_asistencia);
  peticion.append('indice', index);

  const json = await AjaxHelper.enviaAjax(peticion, ENDPOINT);

  if (json && json.resultado === 200) {
    currentAsistenciaRow.observacion = json.datos.observacion;
    renderObservacionesPrevias(currentAsistenciaRow.observacion);
    mensajes('success', 4000, json.mensaje || 'Observación eliminada correctamente');
    actualizarFilaActual();
  } else {
    mensajes('error', 5000, (json && json.mensaje) ? json.mensaje : 'No se pudo eliminar la observación');
  }

  return json;
}

function actualizarFilaActual() {
  if ($.fn.DataTable.isDataTable('#tablaAsistencia')) {
    const table = $('#tablaAsistencia').DataTable();
    table.rows().every(function () {
      const rowData = this.data();
      if (rowData.id_asistencia === currentAsistenciaRow.id_asistencia) {
        this.data(currentAsistenciaRow).draw(false);
      }
    });
  }
}

function renderObservacionesPrevias(observaciones) {
  const $container = $('#observacionActual');
  const lines = observaciones ? observaciones.split(/\r\n|\r|\n/) : [];
  const filtered = lines
    .map(line => line.trim())
    .filter(line => line !== '')
    .map(line => line.startsWith('- ') ? line.substring(2) : line);

  if (filtered.length === 0) {
    $container.text('Sin observaciones previas.');
    return;
  }

  const $list = $('<ul>').addClass('list-group list-group-flush mb-0');

  filtered.forEach((line, index) => {
    const $item = $('<li>').addClass('list-group-item d-flex justify-content-between align-items-center py-2 px-3');
    const $text = $('<span>').addClass('text-body').text(line);
    const $button = $('<button>')
      .attr('type', 'button')
      .addClass('btn btn-sm btn-outline-danger btn-eliminar-observacion')
      .attr('data-index', index)
      .html('<i class="fas fa-trash-alt"></i>');

    $item.append($text, $button);
    $list.append($item);
  });

  $container.empty().append($list);
}

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
    ENTRADA: { label: 'Entrada' },
    DESCANSO_IN: { label: 'Descanso Iniciado' },
    DESCANSO_OUT: { label: 'Descanso Terminado' },
    SALIDA: { label: 'Salida' }
  };

  const item = mapa[tipo] || { label: tipo ? tipo.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, c => c.toUpperCase()) : '' };
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

export function resetForm() {
  $('#tipo_doc').val('default').prop('disabled', false);
  $('#cedula_empleado').val('').prop('readOnly', false);
  $('#tipo_marcacion').val('default').prop('disabled', false);
  $('#observacion').val('').prop('readOnly', false);

  SistemaValidacion.limpiarValidacion({
    tipo_doc: $('#tipo_doc'),
    cedula_empleado: $('#cedula_empleado'),
    tipo_marcacion: $('#tipo_marcacion'),
    observacion: $('#observacion')
  });
}
