import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import { formatearFecha } from "../Helpers/FormatHelper.js";
import { SistemaValidacion } from "../Helpers/ValidationHelper.js";
import { mensajes } from "../Helpers/UIHelper.js";

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
            // Mostrar un resumen de las observaciones activas
            let observaciones = [];
            try {
              const parsed = JSON.parse(data);
              if (Array.isArray(parsed)) {
                observaciones = parsed.filter(obs => !obs.eliminada).map(obs => obs.texto);
              }
            } catch (e) {
              // Si no es JSON, mostrar el texto plano
              observaciones = [data];
            }
            const text = observaciones.join('\n');
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
          const puedeAgregar = $('#tablaAsistencia').attr('data-puede-agregar-observacion') === '1';
          const puedeEliminar = $('#tablaAsistencia').attr('data-puede-eliminar-observacion') === '1';
          if (!puedeAgregar && !puedeEliminar) {
            return '';
          }

          const botonGestionar = puedeAgregar
            ? `<button type="button" class="dropdown-item btn-observacion text-primary" data-id="${row.id_asistencia}">
                    <i class="fa-solid fa-pen-to-square me-2"></i>Gestionar Observaciones
                  </button>`
            : `<button type="button" class="dropdown-item btn-observacion text-primary" data-id="${row.id_asistencia}">
                    <i class="fa-solid fa-eye me-2"></i>Ver Observaciones
                  </button>`;

          return `
            <div class="dropdown">
              <button class="btn btn-sm bg-body text-body border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v me-2"></i>Acciones
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  ${botonGestionar}
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
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
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

  // Delegación de eventos para eliminar observaciones
  $('#observacionActual').on('click', '.btn-eliminar-observacion', async function () {
    const idObservacion = $(this).data('id');
    if (!idObservacion) {
      return mensajes('error', 5000, 'ID de observación inválido.');
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
      await eliminarObservacion(idObservacion);
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
  const puedeAgregar = $('#modalObservacion').attr('data-puede-agregar') === '1';
  const puedeEliminar = $('#modalObservacion').attr('data-puede-eliminar') === '1';

  if (!row || !row.id_asistencia) {
    return mensajes('error', 5000, 'No se encontró la asistencia seleccionada.');
  }

  currentAsistenciaRow = row;
  const estado = formatoEstado(row.estado);
  const tipo = formatoTipoMarcacion(row.tipo_marcacion);

  // Decodificar observaciones
  let observaciones = [];
  try {
    const parsed = JSON.parse(row.observacion);
    if (Array.isArray(parsed)) {
      observaciones = parsed;
    }
  } catch (e) {
    // Si no es JSON, mostrar como una sola observación
    if (row.observacion) {
      observaciones = [{
        id: 'OBS-LEGACY',
        texto: row.observacion,
        autor: 'Sistema',
        fecha: row.fecha + ' ' + row.hora,
        eliminada: false
      }];
    }
  }

  // Formatear la cédula para mostrar
  const cedula = row.cedula_empleado || '';
  const formattedCedula = cedula.length > 1 ? cedula.charAt(0) + '-' + cedula.slice(1) : cedula;
  
  // Construir nombre completo con cédula
  const nombreCompleto = row.primer_nombre ? `${row.primer_nombre} ${row.primer_apellido || ''}`.trim() : 'Empleado';
  const nombreConCedula = `${nombreCompleto} (${formattedCedula})`;

  // Formatear fecha y hora correctamente
  const fechaFormateada = formatearFecha(row.fecha);
  const horaFormateada = formatearHora(row.hora);

  $('#observacionEmpleado').text(nombreConCedula);
  $('#observacionFechaHora').text(`${fechaFormateada} ${horaFormateada}`);
  $('#observacionTipo').text(tipo.label);
  $('#observacionEstado').html(`<span class="badge rounded-pill ${estado.style}">${estado.label}</span>`);
  
  // Pasar el array completo de observaciones
  renderObservacionesPrevias(observaciones);
  $('#observacionInput').closest('.row').toggleClass('d-none', !puedeAgregar);
  $('#btnAgregarObservacion').toggleClass('d-none', !puedeAgregar);
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
    // Actualizar el row con las nuevas observaciones
    if (json.datos && Array.isArray(json.datos.observaciones)) {
      currentAsistenciaRow.observacion = JSON.stringify(json.datos.observaciones);
    }
    renderObservacionesPrevias(json.datos.observaciones);
    $('#observacionInput').val('').focus();
    mensajes('success', 4000, json.mensaje || 'Observación agregada correctamente');
    actualizarFilaActual();
  } else {
    mensajes('error', 5000, (json && json.mensaje) ? json.mensaje : 'No se pudo agregar la observación');
  }

  return json;
}

async function eliminarObservacion(idObservacion) {
  if (!currentAsistenciaRow || !currentAsistenciaRow.id_asistencia) {
    return mensajes('error', 5000, 'No se encontró la asistencia seleccionada.');
  }

  const peticion = new FormData();
  peticion.append('peticion', 'eliminar_observacion');
  peticion.append('id_asistencia', currentAsistenciaRow.id_asistencia);
  peticion.append('id_observacion', idObservacion);

  const json = await AjaxHelper.enviaAjax(peticion, ENDPOINT);

  if (json && json.resultado === 200) {
    if (json.datos && Array.isArray(json.datos.observaciones)) {
      currentAsistenciaRow.observacion = JSON.stringify(json.datos.observaciones);
    }
    renderObservacionesPrevias(json.datos.observaciones);
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
  
  // Filtrar observaciones no eliminadas
  const activas = Array.isArray(observaciones) 
    ? observaciones.filter(obs => !obs.eliminada) 
    : [];

  if (activas.length === 0) {
    $container.html('<div class="text-muted">Sin observaciones previas.</div>');
    return;
  }

  const $list = $('<ul>').addClass('list-group list-group-flush mb-0');

  activas.forEach((obs) => {
    const $item = $('<li>').addClass('list-group-item d-flex justify-content-between align-items-start py-2 px-3');
    
    const $content = $('<div>').addClass('flex-grow-1 me-2');
    
    // Mostrar el texto con el guion y en negrita
    const $text = $('<div>').addClass('text-body fw-semibold').text(`- ${obs.texto || ''}`);
    
    // Obtener el nombre del autor desde el empleado si es posible
    let autorNombre = obs.autor || 'Sistema';
    // Si el autor es una cédula, intentar obtener el nombre (esto podría mejorarse con una consulta)
    // Por ahora mostramos la cédula formateada
    if (autorNombre !== 'Sistema') {
      const autorFormateado = autorNombre.length > 1 ? autorNombre.charAt(0) + '-' + autorNombre.slice(1) : autorNombre;
      autorNombre = autorFormateado;
    }
    
    const $meta = $('<small>').addClass('text-muted d-block').html(
      `Autor: ${autorNombre} - Fecha: ${formatDateTime(obs.fecha)}`
    );
    $content.append($text, $meta);

    if ($('#modalObservacion').attr('data-puede-eliminar') === '1') {
      const $button = $('<button>')
        .attr('type', 'button')
        .addClass('btn btn-sm btn-outline-danger btn-eliminar-observacion flex-shrink-0')
        .attr('data-id', obs.id)
        .html('<i class="fas fa-trash-alt"></i>');

      $item.append($content, $button);
    } else {
      $item.append($content);
    }
    $list.append($item);
  });

  $container.empty().append($list);
}

function formatDateTime(dateTimeStr) {
  if (!dateTimeStr) return '';
  try {
    const date = new Date(dateTimeStr);
    if (isNaN(date.getTime())) return dateTimeStr;
    return date.toLocaleString('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      hour12: true
    });
  } catch (e) {
    return dateTimeStr;
  }
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