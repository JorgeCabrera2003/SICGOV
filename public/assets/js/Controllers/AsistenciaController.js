import * as asistencia from "../Handlers/AsistenciaHandler.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";

const ENDPOINT = BASE_URL + '?page=asistencia';

$(document).ready(function () {
  asistencia.init();
  cargarTablaAsistencia();
});

$('#btnMarcarAsistencia').on('click', function () {
  asistencia.openRegisterModal();
});

$('#btnMiAsistencia').on('click', function (event) {
  event.preventDefault();
  asistencia.pending();
});

$('#btnAsistenciaForm').on('click', async function () {
  const json = await asistencia.submitAsistenciaForm();

  if (json && json.resultado === 200) {
    mensajes('success', 5000, json.mensaje || 'Asistencia registrada correctamente');
    $('#modalAsistencia').modal('hide');
    cargarTablaAsistencia();
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
