import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js";
import * as SelectHelper from "../Helpers/SelectHelper.js";
import { confirmarAccion } from "../Helpers/UIHelper.js";
import { GenerarMensaje, FeedbackToltipInput } from "../Helpers/MensajeriaHelper.js";

// MÓDULO DE TURNOS

function EtiquetasFormulario(etiquetas) {
  let referencia = null;

  const inputTurno = {
    id_turno: $('#id_turno'),
    nombre: $('#nombre'),
    hora_inicio: $('#hora_inicio'),
    hora_fin: $('#hora_fin'),
    minuto_tolerancia: $('#minuto_tolerancia')
  };

  const spanTurno = {
    nombre: $('#snombre'),
    hora_inicio: $('#shora_inicio'),
    hora_fin: $('#shora_fin'),
    minuto_tolerancia: $('#sminuto_tolerancia')
  };

  if (etiquetas === "input") referencia = inputTurno;
  if (etiquetas === "span") referencia = spanTurno;
  return referencia;
}

function EtiquetasModal() {
  return {
    modal: $('#modalTurno'),
    titulo: $('#modalTitleTextTurno'),
    boton: $('#btnTurnoForm')
  };
}

export function EditarModal(operacion) {
  let titulo, boton;
  const e = EtiquetasModal();

  if (operacion == 'registrar') { titulo = "Nuevo Turno"; boton = "Registrar"; }
  if (operacion == 'modificar') { titulo = "Actualizar Turno"; boton = "Actualizar"; }
  if (operacion == 'eliminar') { titulo = "Eliminar Turno"; boton = "Eliminar"; }

  e.titulo.text(titulo);
  e.boton.text(boton);
  e.modal.modal("show");
}

export async function EnviarDatos(operacion) {
  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal();

  let confirmacion = false;
  let str_accion = "";
  let accion = "";
  let btn_formulario = false;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let peticion = new FormData();
  let json = { resultado: 0 };

  peticion.append("modulo", "Turno");

  // Registrar
  if (operacion == "registrar") {
    str_accion = "registrará";
    accion = "registrar";

    if (ValidarEnvio()) {
      confirmacion = await confirmarAccion(`Se ${str_accion} un Turno`, mensajeConfirmacion, "question");

      if (confirmacion) {
        peticion.append('peticion', accion);
        peticion.append('nombre', input.nombre.val().trim());
        peticion.append('hora_inicio', input.hora_inicio.val());
        peticion.append('hora_fin', input.hora_fin.val());
        peticion.append('minuto_tolerancia', input.minuto_tolerancia.val() || 15);
        peticion.append('estatus', 1);
        btn_formulario = true;
      }
    } else {
      GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores antes de enviar.");
    }
  }

  // Modificar
  if (operacion == "modificar") {
    str_accion = "actualizará";
    accion = "modificar";

    if (ValidarEnvio()) {
      confirmacion = await confirmarAccion(`Se ${str_accion} un Turno`, mensajeConfirmacion, "question");

      if (confirmacion) {
        peticion.append('peticion', accion);
        peticion.append('id_turno', input.id_turno.val());
        peticion.append('nombre', input.nombre.val().trim());
        peticion.append('hora_inicio', input.hora_inicio.val());
        peticion.append('hora_fin', input.hora_fin.val());
        peticion.append('minuto_tolerancia', input.minuto_tolerancia.val() || 15);
        peticion.append('estatus', 1);
        btn_formulario = true;
      }
    } else {
      GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores antes de enviar.");
    }
  }

  // Eliminar
  if (operacion == "eliminar") {
    if (input.id_turno.val() !== "") {
      confirmacion = await confirmarAccion("Se eliminará un Turno", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_turno', input.id_turno.val());
        btn_formulario = true;
      }
    } else {
      GenerarMensaje("error", 10000, "Error de Validación", "El ID del Turno no es válido.");
    }
  }

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await AjaxHelper.enviaAjax(peticion, "?page=Turno");

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      modal.modal.modal("hide");
      GenerarMensaje(json.icon, 10000, json.mensaje, null);
    }
    modal.boton.prop('disabled', false);
  }

  if (!confirmacion) {
    modal.boton.prop('disabled', false);
  }

  input = null;
  modal = null;
  return json;
}

export async function EnviarFormulario(btn_string) {
  let accion = null;
  let respuesta = null;
  const MANEJADOR = {
    'Registrar': 'registrar',
    'Actualizar': 'modificar',
    'Eliminar': 'eliminar'
  };
  const DEFAULT = null;

  accion = MANEJADOR[btn_string] || DEFAULT;

  if (accion != null) {
    respuesta = await EnviarDatos(accion);
  } else {
    respuesta = { resultado: 0 };
    GenerarMensaje("danger", 10000, "Error, acción no válida", "");
  }
  return respuesta;
}

// ==========================================
// VALIDACIÓN
// ==========================================

export function CapaValidar() {
  KeyUpTurno();
}

function KeyUpTurno() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  $(input.nombre).on("keyup", function () {
    if ($(this).val().trim() === "") {
      FeedbackToltipInput($(this), span.nombre, "El nombre del Turno es obligatorio", 0);
    } else if ($(this).val().trim().length < 3) {
      FeedbackToltipInput($(this), span.nombre, "El nombre debe tener al menos 3 caracteres", 0);
    } else {
      FeedbackToltipInput($(this), span.nombre, "", 1);
    }
  });

  $(input.hora_inicio).on("change", function () {
    validarHoras();
  });

  $(input.hora_fin).on("change", function () {
    validarHoras();
  });
}

function validarHoras() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  
  const inicio = input.hora_inicio.val();
  const fin = input.hora_fin.val();

  if (inicio && fin && inicio >= fin) {
    FeedbackToltipInput(input.hora_fin, span.hora_fin, "La hora fin debe ser mayor a la hora inicio", 0);
  } else if (inicio && fin) {
    FeedbackToltipInput(input.hora_fin, span.hora_fin, "", 1);
  }
}

function ValidarEnvio() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let bool = true;

  if (input.nombre.val().trim() === "") {
    FeedbackToltipInput(input.nombre, span.nombre, "El nombre del Turno es obligatorio", 0);
    bool = false;
  }

  if (input.hora_inicio.val() === "") {
    FeedbackToltipInput(input.hora_inicio, span.hora_inicio, "La hora de inicio es obligatoria", 0);
    bool = false;
  }

  if (input.hora_fin.val() === "") {
    FeedbackToltipInput(input.hora_fin, span.hora_fin, "La hora de fin es obligatoria", 0);
    bool = false;
  }

  if (input.hora_inicio.val() && input.hora_fin.val() && input.hora_inicio.val() >= input.hora_fin.val()) {
    FeedbackToltipInput(input.hora_fin, span.hora_fin, "La hora fin debe ser mayor a la hora inicio", 0);
    bool = false;
  }

  return bool;
}

// ==========================================
// DATATABLE
// ==========================================

function formatearHora12(hora24) {
  if (!hora24) return '';
  const [h, m] = hora24.split(':');
  let hora = parseInt(h);
  const ampm = hora >= 12 ? 'PM' : 'AM';
  hora = hora % 12 || 12;
  return `${hora}:${m} ${ampm}`;
}

function RenderBotonesAccion() {
  const dropdown = $('<div>').addClass('dropdown');
  const boton = $('<button>').addClass('btn btn-sm btn-light border dropdown-toggle')
    .attr('type', 'button')
    .attr('data-bs-toggle', 'dropdown')
    .html('<i class="fas fa-ellipsis-v me-3"></i>Acciones');

  const menu = $('<ul>').addClass('dropdown-menu');

  const itemEditar = $('<li>');
  const linkEditar = $('<a>')
    .addClass('dropdown-item btn-editar text-primary')
    .attr('href', '#')
    .attr('data-accion', 0)
    .html('<i class="fas fa-edit me-2"></i>Editar');
  itemEditar.append(linkEditar);

  const itemEliminar = $('<li>');
  const linkEliminar = $('<a>')
    .addClass('dropdown-item btn-eliminar text-danger')
    .attr('href', '#')
    .attr('data-accion', 1)
    .html('<i class="fas fa-trash me-2"></i>Eliminar');
  itemEliminar.append(linkEliminar);

  const separador = $('<li>').html('<hr class="dropdown-divider">');

  menu.append(itemEditar, separador, itemEliminar);
  dropdown.append(boton, menu);

  return dropdown.prop('outerHTML');
}

export async function DataTablePrincipal(arreglo) {
  let botones = RenderBotonesAccion();

  if ($.fn.DataTable.isDataTable('#tablaTurno')) {
    $('#tablaTurno').DataTable().destroy();
  }

  $('#tablaTurno').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'nombre' },
      { 
        data: 'hora_inicio',
        render: function(data) {
          return formatearHora12(data);
        }
      },
      { 
        data: 'hora_fin',
        render: function(data) {
          return formatearHora12(data);
        }
      },
      { 
        data: 'minuto_tolerancia',
        render: function(data) {
          return data ? data + ' min.' : '15 min.';
        }
      },
      {
        data: null,
        render: function() {
          return botones;
        }
      }
    ],
    order: [[0, 'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
  });
}

export function LimpiarFormulario() {
  let input = EtiquetasFormulario('input');
  let modal = EtiquetasModal();

  input.id_turno.val("").prop("disabled", true);
  input.nombre.val("").prop("disabled", false);
  input.hora_inicio.val("").prop("disabled", false);
  input.hora_fin.val("").prop("disabled", false);
  input.minuto_tolerancia.val("").prop("disabled", false); // ← vacío, no "15"

  // Limpiar validaciones visuales
  $('#formTurno').find('input').removeClass('is-valid is-invalid');
  $('#snombre, #shora_inicio, #shora_fin, #sminuto_tolerancia').text('');

  modal.boton.prop('disabled', false);

  input = null;
  modal = null;
}

export async function EditarFormTurno(datos, accion) {
  LimpiarFormulario();
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal = EtiquetasModal();

  if (accion == "eliminar") { 
    bool = true; 
  }

  input.id_turno.val(datos.id_turno).prop("disabled", true);
  input.nombre.val(datos.nombre).prop("disabled", bool);
  input.hora_inicio.val(datos.hora_inicio).prop("disabled", bool);
  input.hora_fin.val(datos.hora_fin).prop("disabled", bool);
  input.minuto_tolerancia.val(datos.minuto_tolerancia || 15).prop("disabled", bool);

  modal.boton.prop('disabled', false);
  EditarModal(accion);
}