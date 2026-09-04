import * as turno from "../Handlers/TurnoHandler.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";

$(document).ready(function () {
  crearDataTable();
  iniciarValidaciones();
});

$(document).on('click', '#btnTurnoForm', async function () {
  const respuesta = await turno.EnviarFormulario($(this).text());
  if (typeof respuesta?.resultado === 'number' && respuesta.resultado >= 200 && respuesta.resultado < 300) {
    crearDataTable();
  }
});

$(document).on('click', '#btnNuevoTurno', function () {
  turno.LimpiarFormulario();
  turno.EditarModal('registrar');
});

function iniciarValidaciones() {
  turno.CapaValidar();
}

async function crearDataTable() {
  const peticion = new FormData();
  peticion.append('modulo', 'Turno');
  peticion.append('peticion', 'consultar');
  let json = null;
  try {
    json = await AjaxHelper.enviaAjax(peticion, '?page=Turno');
  } catch (e) {
    json = { datos: [] };
  }
  const arreglo = Array.isArray(json?.datos) ? json.datos : [];
  turno.DataTablePrincipal(arreglo);
}

async function rellenar(pos, accion) {
  const linea = $(pos).closest('tr');
  const tabla = $('#tablaTurno').DataTable();
  const datosFila = tabla.row(linea).data();
  turno.EditarFormTurno(datosFila, accion);
}

$(document).on('click', '.btn-editar', function () { rellenar($(this), $(this).attr('data-accion')); });
$(document).on('click', '.btn-eliminar', function () { rellenar($(this), $(this).attr('data-accion')); });