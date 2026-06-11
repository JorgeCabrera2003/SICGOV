import * as asistencia from "../Handlers/AsistenciaHandler.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";

const ENDPOINT = BASE_URL + '?page=asistencia';
let activeAsistenciaView = 'historico';

$(document).ready(function () {
  asistencia.init();
  cargarTablaAsistencia();
});

$('#btnMarcarAsistencia').on('click', function () {
  asistencia.openRegisterModal();
});

$('#btnAsistenciaHoy').on('click', function (event) {
  event.preventDefault();
  activeAsistenciaView = 'hoy';
  toggleAsistenciaView('hoy');
});

$('#btnHistorial').on('click', function (event) {
  event.preventDefault();
  activeAsistenciaView = 'historico';
  toggleAsistenciaView('historico');
});

$('#btnAsistenciaForm').on('click', async function () {
  const json = await asistencia.submitAsistenciaForm();

  if (json && json.resultado === 200) {
    mensajes('success', 5000, json.mensaje || 'Asistencia registrada correctamente');
    $('#modalAsistencia').modal('hide');
    if (activeAsistenciaView === 'hoy') {
      cargarAsistenciaHoy();
    } else {
      cargarTablaAsistencia();
    }
  } else {
    mensajes('error', 5000, (json && json.mensaje) ? json.mensaje : 'No se pudo registrar la asistencia');
  }
});

async function cargarTablaAsistencia() {
  const peticion = new FormData();
  let json = null;
  let arreglo = [];

  peticion.append('peticion', 'consultar');

  try {
    json = await AjaxHelper.enviaAjax(peticion, ENDPOINT);
    arreglo = Array.isArray(json?.datos) ? json.datos : [];
  } catch (error) {
    arreglo = [];
  }

  asistencia.renderDataTable(arreglo);
}

function toggleAsistenciaView(view) {
  const isHoy = view === 'hoy';
  $('#asistenciaHoyContainer').toggleClass('d-none', !isHoy);
  $('#historicoAsistenciaSection').toggleClass('d-none', isHoy);

  if (isHoy) {
    $('#btnAsistenciaHoy').removeClass('btn-outline-warning').addClass('btn-warning text-dark');
    $('#btnHistorial').removeClass('btn-warning text-dark').addClass('btn-outline-warning');
    cargarAsistenciaHoy();
  } else {
    $('#btnHistorial').removeClass('btn-outline-warning').addClass('btn-warning text-dark');
    $('#btnAsistenciaHoy').removeClass('btn-warning text-dark').addClass('btn-outline-warning');
    $('#asistenciaHoyEmpty').addClass('d-none');
    cargarTablaAsistencia();
  }
}

async function cargarAsistenciaHoy() {
  const peticion = new FormData();
  peticion.append('peticion', 'consultar_hoy');

  let json = null;
  try {
    json = await AjaxHelper.enviaAjax(peticion, ENDPOINT);
  } catch (error) {
    json = null;
  }

  $('#asistenciaHoyContainer').removeClass('d-none');

  if (json && json.resultado === 200 && Array.isArray(json.datos)) {
    renderAsistenciaHoyTable(json.datos);
    $('#asistenciaHoyEmpty').addClass('d-none');
  } else {
    if ($.fn.DataTable.isDataTable('#tablaAsistenciaHoy')) {
      $('#tablaAsistenciaHoy').DataTable().destroy();
    }
    $('#tablaAsistenciaHoy tbody').empty();
    $('#asistenciaHoyEmpty').removeClass('d-none').text(json?.mensaje || 'No hay registros para hoy.');
  }
}

function renderAsistenciaHoyTable(datos) {
  if ($.fn.DataTable.isDataTable('#tablaAsistenciaHoy')) {
    $('#tablaAsistenciaHoy').DataTable().destroy();
  }

  const filas = datos.map((item) => ({
    empleado: item.nombre_empleado && item.nombre_empleado.trim() ? item.nombre_empleado : item.cedula_empleado,
    entrada: item.hora_entrada ? formatearHora(item.hora_entrada) : '-',
    descanso_in: item.hora_descanso_in ? formatearHora(item.hora_descanso_in) : '-',
    descanso_out: item.hora_descanso_out ? formatearHora(item.hora_descanso_out) : '-',
    salida: item.hora_salida ? formatearHora(item.hora_salida) : '-'
  }));

  $('#tablaAsistenciaHoy').DataTable({
    processing: true,
    data: filas,
    columns: [
      { data: 'empleado', className: 'text-start' },
      { data: 'entrada', className: 'text-center' },
      { data: 'descanso_in', className: 'text-center' },
      { data: 'descanso_out', className: 'text-center' },
      { data: 'salida', className: 'text-center' }
    ],
    responsive: true,
    autoWidth: false,
    searching: true,
    paging: true,
    info: true,
    lengthChange: true,
    pageLength: 10,
    order: [[0, 'asc']],
    language: { url: idiomaTabla }
  });
}

function formatearHora(hora) {
  if (!hora) return '-';

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
