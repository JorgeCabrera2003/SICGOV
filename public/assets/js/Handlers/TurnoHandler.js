import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";

function EtiquetasFormulario(etiquetas) {
  const input = {
    id_turno: $('#id_turno'),
    nombre: $('#nombre'),
    hora_inicio: $('#hora_inicio'),
    hora_fin: $('#hora_fin'),
    minuto_tolerancia: $('#minuto_tolerancia')
  };
  const span = {
    nombre: $('#snombre'),
    hora_inicio: $('#shora_inicio'),
    hora_fin: $('#shora_fin'),
    minuto_tolerancia: $('#sminuto_tolerancia')
  };
  if (etiquetas === 'input') return input;
  if (etiquetas === 'span') return span;
  return null;
}

function EtiquetasModal() {
  return {
    modal: $('#modalTurno'),
    titulo: $('#modalTitleTextTurno'),
    boton: $('#btnTurnoForm')
  };
}

export function EditarModal(operacion) {
  const etiqueta = EtiquetasModal();
  const form = $('#formTurno');
  form.find('input, select, textarea').prop('readonly', false).prop('disabled', false);
  let titulo = '';
  let boton = '';
  if (operacion === 'registrar') { titulo = 'Registrar Turno'; boton = 'Nuevo'; }
  if (operacion === 'modificar') { titulo = 'Modificar Turno'; boton = 'Actualizar'; }
  if (operacion === 'eliminar') { titulo = 'Eliminar Turno'; boton = 'Borrar'; form.find('input, select, textarea').prop('readonly', true).prop('disabled', true); }
  etiqueta.titulo.text(titulo);
  etiqueta.boton.text(boton);
  etiqueta.modal.modal('show');
}

export async function EnviarDatos(operacion) {
  const input = EtiquetasFormulario('input');
  const modal = EtiquetasModal();
  const peticion = new FormData();
  peticion.append('modulo', 'Turno');
  let accion = '';
  if (operacion === 'registrar') accion = 'registrar';
  if (operacion === 'modificar') accion = 'modificar';
  if (operacion === 'eliminar') accion = 'eliminar';

  if (accion === 'modificar' || accion === 'eliminar') {
    peticion.append('id_turno', input.id_turno.val());
  }

  if (accion !== 'eliminar') {
    // Validación simple
    if (!input.nombre.val() || input.nombre.val().trim() === '') {
      MensajeriaHelper.GenerarMensaje('error', 5000, 'El nombre del turno es obligatorio', null);
      return { resultado: 400, mensaje: 'Nombre obligatorio' };
    }
    peticion.append('nombre', input.nombre.val().trim());
    peticion.append('hora_inicio', input.hora_inicio.val());
    peticion.append('hora_fin', input.hora_fin.val());
    peticion.append('minuto_tolerancia', input.minuto_tolerancia.val() || 15);
  }

  peticion.append('peticion', accion);
  modal.boton.prop('disabled', true);
  const json = await AjaxHelper.enviaAjax(peticion, '?page=Turno');
  modal.boton.prop('disabled', false);

  if (typeof json?.resultado === 'number' && json.resultado >= 200 && json.resultado < 300) {
    modal.modal.modal('hide');
    MensajeriaHelper.GenerarMensaje(json.icon || 'success', 5000, json.mensaje || 'Operación exitosa', null);
  } else {
    MensajeriaHelper.GenerarMensaje(json?.icon || 'error', 5000, json?.mensaje || 'Error en la operación', null);
  }
  return json;
}

export async function EnviarFormulario(btn_string) {
  const MANEJADOR = { 'Nuevo': 'registrar', 'Actualizar': 'modificar', 'Borrar': 'eliminar' };
  const accion = MANEJADOR[btn_string] || null;
  if (accion) return await EnviarDatos(accion);
  return { resultado: 400, mensaje: 'Acción no reconocida' };
}

export function CapaValidar() {
  KeyUpTurno();
}

function KeyUpTurno() {
  const input = EtiquetasFormulario('input');
  $(input.nombre).on('keyup input', () => { $('#snombre').text(''); $(input.nombre).removeClass('is-valid is-invalid'); });
}

function formatTime(hora) {
  if (!hora) return '';
  // hora comes as HH:MM:SS or HH:MM -> convert to 12-hour with AM/PM
  const parts = hora.split(':');
  if (parts.length >= 2) {
    let hh = parseInt(parts[0], 10);
    const mm = parts[1];
    const ampm = hh >= 12 ? 'PM' : 'AM';
    hh = hh % 12;
    if (hh === 0) hh = 12;
    return hh + ':' + mm + ' ' + ampm;
  }
  return hora;
}

export function DataTablePrincipal(arreglo) {
  if ($.fn.DataTable.isDataTable('#tablaTurno')) {
    $('#tablaTurno').DataTable().destroy();
  }

  function botonesAccion() {
    return '<div class="dropdown">' +
      '<button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v me-3"></i>Acciones</button>' +
      '<ul class="dropdown-menu"><li><a class="dropdown-item btn-editar text-primary" href="#" data-accion="modificar"><i class="fas fa-edit me-2"></i>Editar</a></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item btn-eliminar text-danger" href="#" data-accion="eliminar"><i class="fas fa-trash me-2"></i>Eliminar</a></li></ul></div>';
  }

  $('#tablaTurno').DataTable({
    processing: true,
    data: arreglo,
    columnDefs: [{ className: 'text-center align-middle', targets: '_all' }],
    columns: [
      { data: 'nombre' },
      { data: 'hora_inicio', render: function (val) { return formatTime(val); } },
      { data: 'hora_fin', render: function (val) { return formatTime(val); } },
      { data: 'minuto_tolerancia', render: function (v) { return (v === null || v === undefined || v === '') ? '' : (v + ' min.'); } },
      { data: null, orderable: false, searchable: false, render: function () { return botonesAccion(); } }
    ],
    order: [[0, 'asc']],
    language: { url: idiomaTabla }
  });
}

export function LimpiarFormulario() {
  const input = EtiquetasFormulario('input');
  input.id_turno.val('');
  input.nombre.val('');
  input.hora_inicio.val('');
  input.hora_fin.val('');
  input.minuto_tolerancia.val('15');
  $('#formTurno').find('input').removeClass('is-valid is-invalid');
}

export function EditarFormTurno(datos, accion) {
  const input = EtiquetasFormulario('input');
  input.id_turno.val(datos.id_turno || '');
  input.nombre.val(datos.nombre || '');
  input.hora_inicio.val(datos.hora_inicio || '');
  input.hora_fin.val(datos.hora_fin || '');
  input.minuto_tolerancia.val(datos.minuto_tolerancia || 15);
  if (accion === 'eliminar') {
    $('#formTurno').find('input, select, textarea').prop('readonly', true).prop('disabled', true);
  }
  EditarModal(accion);
}
