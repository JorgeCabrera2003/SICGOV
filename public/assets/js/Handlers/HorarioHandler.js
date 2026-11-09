import * as AjaxHelper from "../Helpers/AjaxHelper.js?v=20260917-3";
import * as SelectHelper from "../Helpers/SelectHelper.js?v=20260917-3";
import { confirmarAccion } from "../Helpers/UIHelper.js?v=20260917-3";
import { GenerarMensaje, FeedbackToltipInput } from "../Helpers/MensajeriaHelper.js?v=20260917-3";

// MÓDULO DE HORARIOS

function formatearHora12(hora24) {
  if (!hora24) return '';
  const [h, m] = hora24.split(':');
  let hora = parseInt(h);
  const ampm = hora >= 12 ? 'PM' : 'AM';
  hora = hora % 12 || 12;
  return `${hora}:${m} ${ampm}`;
}
// ==========================================
// ETIQUETAS
// ==========================================

function EtiquetasFormulario(etiquetas) {
  let referencia = null;
  const inputHorario = {
    empleado: $('#empleado'),
    id_horario: $('#id_horario')
  };
  const spanHorario = {
    empleado: $('#sempleado'),
    sturno: $('#sturno'),
    sfecha: $('#sfecha'),
    id_horario: $('#sid_horario')
  };
  if (etiquetas === "input") referencia = inputHorario;
  if (etiquetas === "span") referencia = spanHorario;
  return referencia;
}

function EtiquetasModal(etiqueta) {
  let referencia = null;
  const modalHorario = {
    modal: $('#modalHorario'),
    titulo: $('#modalTitleTextHorario'),
    boton: $('#btnHorarioForm')
  };
  if (etiqueta === "Horario") referencia = modalHorario;
  return referencia;
}

export function EditarModal(operacion) {
  let titulo, boton;
  const etiqueta_modal = EtiquetasModal("Horario");
  if (operacion == 'registrar') { titulo = "Asignar Turno"; boton = "Asignar"; }
  if (operacion == 'modificar') { titulo = "Cambiar Turno"; boton = "Actualizar"; }
  if (operacion == 'modificar_lote') { titulo = "Editar Horario"; boton = "Guardar cambios"; }
  if (operacion == 'eliminar') { titulo = "Eliminar Asignación"; boton = "Eliminar"; }
  etiqueta_modal.titulo.text(titulo);
  etiqueta_modal.boton.text(boton);
  etiqueta_modal.modal.modal("show");
}

// ==========================================
// DATOS GLOBALES
// ==========================================

let fechaActualCalendario = new Date();
let turnoActivo = null; // { id_turno, nombre, color }
let asignaciones = {}; // { '2025-03-03': { id_turno, nombre, color }, ... }
let asignacionesOriginales = {}; // Asignaciones existentes al abrir el editor
let coloresTurnos = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#00BCD4', '#795548', '#607D8B'];
let coloresTurnosPorId = new Map();

function esFechaPasada(fecha) {
  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);
  return new Date(`${fecha}T00:00:00`) < hoy;
}

// ==========================================
// BOTONES DE TURNOS
// ==========================================

function renderizarBotonesTurnos(turnos) {
  let html = '';
  
  turnos.forEach((turno, index) => {
    const color = coloresTurnos[index % coloresTurnos.length];
    coloresTurnosPorId.set(String(turno.id_turno), color);
    const activo = turnoActivo && turnoActivo.id_turno === turno.id_turno ? 'activo' : '';
    html += `<button type="button" class="btn btn-sm btn-turno ${activo}" 
      style="background-color: ${color}; color: white;"
      data-turno-id="${turno.id_turno}" 
      data-turno-nombre="${turno.nombre}" 
      data-turno-color="${color}"
      onclick="seleccionarTurno(this, '${turno.id_turno}', '${turno.nombre.replace(/'/g, "\\'")}', '${color}')">
      <i class="fas fa-clock me-1"></i>${turno.nombre}
    </button>`;
  });
  
  $('#botonesTurnos').html(html);
}

window.seleccionarTurno = function(elemento, id, nombre, color) {
  $('.btn-turno').removeClass('activo');
  $(elemento).addClass('activo');
  
  if (id === '') {
    turnoActivo = null;
  } else {
    turnoActivo = { id_turno: id, nombre: nombre, color: color };
  }
  
  console.log('🖌️ Turno activo:', turnoActivo);
  renderizarCalendario();
};

// ==========================================
// CALENDARIO
// ==========================================

export function inicializarCalendario() {
  fechaActualCalendario = new Date();
  turnoActivo = null;
  $('#botonesTurnos .btn-turno').removeClass('activo');
  asignaciones = {};
  asignacionesOriginales = {};
  renderizarCalendario();
}

function renderizarCalendario() {
  const year = fechaActualCalendario.getFullYear();
  const month = fechaActualCalendario.getMonth();

  const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
  $('#tituloMes').text(`${nombresMeses[month]} ${year}`);

  const primerDia = new Date(year, month, 1).getDay();
  const primerDiaAjustado = primerDia === 0 ? 6 : primerDia - 1;
  const ultimoDia = new Date(year, month + 1, 0).getDate();
  const ultimoDiaMesAnterior = new Date(year, month, 0).getDate();

  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);

  let html = '<div class="selector-esquina"></div>';
  const nombresDias = ['LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB', 'DOM'];
  nombresDias.forEach((nombre, indice) => {
    const claseFinSemana = indice >= 5 ? ' text-danger' : '';
    html += `<button type="button" class="selector-calendario selector-columna${claseFinSemana}" onclick="seleccionarColumna(${indice})" title="Seleccionar ${nombre}">${nombre}</button>`;
  });

  const totalCeldas = Math.ceil((primerDiaAjustado + ultimoDia) / 7) * 7;
  for (let posicion = 0; posicion < totalCeldas; posicion++) {
    if (posicion % 7 === 0) {
      const fila = Math.floor(posicion / 7);
      html += `<button type="button" class="selector-calendario selector-fila" onclick="seleccionarFila(${fila})" title="Seleccionar semana ${fila + 1}">S${fila + 1}</button>`;
    }

    const dia = posicion - primerDiaAjustado + 1;
    if (dia < 1 || dia > ultimoDia) {
      const diaFueraDeMes = dia < 1 ? ultimoDiaMesAnterior + dia : dia - ultimoDia;
      html += `<div class="p-1"><div class="dia-calendario otro-mes d-flex align-items-center justify-content-center">${diaFueraDeMes}</div></div>`;
      continue;
    }

    const fechaStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    const fecha = new Date(year, month, dia);
    const diaSemana = fecha.getDay();
    const asignado = asignaciones[fechaStr];
    let clases = 'dia-calendario d-flex align-items-center justify-content-center';
    let estilo = '';

    if (diaSemana === 0 || diaSemana === 6) clases += ' fin-semana';
    if (fecha.getTime() === hoy.getTime()) clases += ' hoy';
    if (fecha < hoy) clases += ' fecha-pasada';
    if (asignado) estilo = `background-color: ${asignado.color}; color: white; font-weight: bold;`;

    const atributoFechaPasada = fecha < hoy ? ' aria-disabled="true" title="No se puede modificar una fecha pasada"' : '';
    const eventoClick = fecha < hoy ? '' : ` onclick="toggleDia(this, '${fechaStr}')"`;
    html += `<div class="p-1"><div class="${clases}" data-fecha="${fechaStr}"
      style="${estilo}"${atributoFechaPasada}${eventoClick}>${dia}</div></div>`;
  }

  $('#calendarioDias').html(html);
  actualizarContador();
}

function obtenerFechasDelMes() {
  const year = fechaActualCalendario.getFullYear();
  const month = fechaActualCalendario.getMonth();
  const ultimoDia = new Date(year, month + 1, 0).getDate();

  return Array.from({ length: ultimoDia }, (_, indice) =>
    `${year}-${String(month + 1).padStart(2, '0')}-${String(indice + 1).padStart(2, '0')}`
  );
}

function alternarSeleccionMasiva(fechas) {
  if (!turnoActivo) {
    GenerarMensaje("warning", 3000, "Seleccione un turno", "Debe seleccionar un turno primero");
    return;
  }

  fechas = fechas.filter(fecha => !esFechaPasada(fecha));
  if (fechas.length === 0) return;

  const todasAsignadasAlTurno = fechas.length > 0 && fechas.every(fecha =>
    asignaciones[fecha]?.id_turno === turnoActivo.id_turno
  );

  fechas.forEach(fecha => {
    if (todasAsignadasAlTurno) {
      delete asignaciones[fecha];
    } else {
      asignaciones[fecha] = { ...turnoActivo };
    }
  });

  renderizarCalendario();
  actualizarInputAsignaciones();
}

window.seleccionarColumna = function(indiceColumna) {
  const fechas = obtenerFechasDelMes().filter(fecha => {
    const diaSemana = new Date(`${fecha}T00:00:00`).getDay();
    const columna = diaSemana === 0 ? 6 : diaSemana - 1;
    return columna === indiceColumna;
  });
  alternarSeleccionMasiva(fechas);
};

window.seleccionarFila = function(indiceFila) {
  const year = fechaActualCalendario.getFullYear();
  const month = fechaActualCalendario.getMonth();
  const primerDia = new Date(year, month, 1).getDay();
  const primerDiaAjustado = primerDia === 0 ? 6 : primerDia - 1;
  const fechas = obtenerFechasDelMes().filter(fecha => {
    const dia = Number(fecha.slice(-2));
    return Math.floor((primerDiaAjustado + dia - 1) / 7) === indiceFila;
  });
  alternarSeleccionMasiva(fechas);
};

window.toggleDia = function(elemento, fecha) {
  if (esFechaPasada(fecha)) {
    GenerarMensaje("warning", 3000, "Fecha no permitida", "No puede asignar ni modificar turnos de días anteriores al actual");
    return;
  }

  // Si se seleccionó otro turno, reemplazarlo directamente.
  if (asignaciones[fecha] && turnoActivo && asignaciones[fecha].id_turno !== turnoActivo.id_turno) {
    asignaciones[fecha] = { ...turnoActivo, id_planificador_turno: asignaciones[fecha].id_planificador_turno };
    console.log(`🔄 Actualizado: ${fecha} -> ${turnoActivo.nombre}`);
  }
  // Si ya tiene asignación y no se seleccionó otro turno, quitarla.
  else if (asignaciones[fecha]) {
    delete asignaciones[fecha];
    console.log(`❌ Quitado: ${fecha}`);
  } 
  // Si no tiene y hay turno activo, asignar
  else if (turnoActivo) {
    asignaciones[fecha] = { ...turnoActivo };
    console.log(`✅ Asignado: ${fecha} -> ${turnoActivo.nombre}`);
  }
  // Si no hay turno activo, no hacer nada
  else {
    console.log(`⚠️ Sin turno seleccionado`);
  }
  
  renderizarCalendario();
  actualizarInputAsignaciones();
};

function actualizarContador() {
  const total = Object.keys(asignaciones).length;
  $('#contadorDias').text(`${total} días asignados`);
}

function actualizarInputAsignaciones() {
  const data = Object.entries(asignaciones).map(([fecha, info]) => ({
    fecha: fecha,
    id_turno: info.id_turno
  }));
  $('#asignaciones').val(JSON.stringify(data));

  if (data.length > 0) {
    $('#asignaciones').removeClass('is-valid is-invalid');
    $('#sfecha')
      .removeClass('valid-feedback invalid-feedback valid-tooltip invalid-tooltip d-inline-block')
      .text('')
      .hide();
  }
}

function actualizarFeedbackHorario($campo, $feedback, esValido, mensaje = '') {
  $campo.removeClass('is-valid is-invalid');
  $feedback
    .removeClass('valid-feedback invalid-feedback valid-tooltip invalid-tooltip d-inline-block')
    .text('')
    .hide();

  if (esValido) {
    $campo.addClass('is-valid');
  } else {
    $campo.addClass('is-invalid');
    $feedback.addClass('invalid-tooltip d-inline-block').text(mensaje).show();
  }
}

$('#empleado').on('change', function () {
  const seleccionado = $(this).val() !== 'default' && $(this).val() !== null && $(this).val() !== '';
  actualizarFeedbackHorario($(this), $('#sempleado'), seleccionado, 'Debe seleccionar un Empleado');
});

// ==========================================
// BOTONES DEL CALENDARIO
// ==========================================

$('#btnMesAnterior').on('click', function () {
  fechaActualCalendario.setMonth(fechaActualCalendario.getMonth() - 1);
  renderizarCalendario();
});

$('#btnMesSiguiente').on('click', function () {
  fechaActualCalendario.setMonth(fechaActualCalendario.getMonth() + 1);
  renderizarCalendario();
});

$('#btnSeleccionarTodos').on('click', function () {
  if (!turnoActivo) {
    GenerarMensaje("warning", 3000, "Seleccione un turno", "Debe seleccionar un turno primero");
    return;
  }
  const year = fechaActualCalendario.getFullYear();
  const month = fechaActualCalendario.getMonth();
  const ultimoDia = new Date(year, month + 1, 0).getDate();
  for (let dia = 1; dia <= ultimoDia; dia++) {
    const fechaStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    if (esFechaPasada(fechaStr)) continue;
    asignaciones[fechaStr] = { ...turnoActivo };
  }
  renderizarCalendario();
  actualizarInputAsignaciones();
});

$('#btnDiasHabiles').on('click', function () {
  if (!turnoActivo) {
    GenerarMensaje("warning", 3000, "Seleccione un turno", "Debe seleccionar un turno primero");
    return;
  }
  const year = fechaActualCalendario.getFullYear();
  const month = fechaActualCalendario.getMonth();
  const ultimoDia = new Date(year, month + 1, 0).getDate();
  for (let dia = 1; dia <= ultimoDia; dia++) {
    const fecha = new Date(year, month, dia);
    const diaSemana = fecha.getDay();
    if (diaSemana >= 1 && diaSemana <= 5) {
      const fechaStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
      if (esFechaPasada(fechaStr)) continue;
      asignaciones[fechaStr] = { ...turnoActivo };
    }
  }
  renderizarCalendario();
  actualizarInputAsignaciones();
});

$('#btnLimpiarSeleccion').on('click', function () {
  asignaciones = Object.fromEntries(
    Object.entries(asignaciones).filter(([fecha]) => esFechaPasada(fecha))
  );
  renderizarCalendario();
  actualizarInputAsignaciones();
});

// ==========================================
// VALIDACIÓN
// ==========================================

function ValidarEnvio() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let bool = true;
  actualizarInputAsignaciones();

  if (input.empleado.val() == "default" || input.empleado.val() == null) {
    actualizarFeedbackHorario(input.empleado, span.empleado, false, "Debe seleccionar un Empleado");
    bool = false;
  } else {
    actualizarFeedbackHorario(input.empleado, span.empleado, true);
  }

  if (Object.keys(asignaciones).length === 0) {
    actualizarFeedbackHorario($('#asignaciones'), $('#sfecha'), false, "Debe asignar al menos un día con turno");
    bool = false;
  } else {
    actualizarFeedbackHorario($('#asignaciones'), $('#sfecha'), true);
  }

  return bool;
}

// ==========================================
// ENVÍO DE DATOS
// ==========================================

export async function EnviarDatos(operacion) {
  let input = EtiquetasFormulario('input');
  let modal = EtiquetasModal("Horario");
  let peticion = new FormData();
  let json = { resultado: 0 };

  peticion.append("modulo", "Horario");

  // REGISTRAR
  if (operacion == "registrar") {
    if (ValidarEnvio()) {
      const datos = JSON.parse($('#asignaciones').val() || '[]');
      
      if (datos.length === 0) {
        GenerarMensaje("error", 3000, "Error", "No hay asignaciones para enviar");
        return { resultado: 0 };
      }

      const confirmacion = await confirmarAccion(
        `Se asignarán ${datos.length} turno(s)`,
        "¿Está seguro de realizar esta acción?",
        "question"
      );

      if (confirmacion) {
        peticion.append('peticion', 'registrar_lote');
        peticion.append('cedula_empleado', input.empleado.val());
        peticion.append('asignaciones', $('#asignaciones').val());
        
        modal.boton.prop('disabled', true);
        json = await AjaxHelper.enviaAjax(peticion, "");

        if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
          modal.modal.modal("hide");
          GenerarMensaje(json.icon, 10000, json.mensaje, null);
        }
        modal.boton.prop('disabled', false);
      }
    } else {
      GenerarMensaje("error", 10000, "Error de Validación", "Corrija los errores antes de enviar.");
    }
  }

  // EDITAR HORARIO COMPLETO: registrar, modificar y eliminar solo las diferencias.
  if (operacion == "modificar_lote") {
    const actual = Object.entries(asignaciones).map(([fecha, info]) => ({
      fecha,
      id_turno: info.id_turno,
      id_planificador_turno: info.id_planificador_turno || null
    }));
    const originalPorFecha = asignacionesOriginales;
    const actualPorFecha = Object.fromEntries(actual.map(item => [item.fecha, item]));
    const altas = actual.filter(item => !originalPorFecha[item.fecha]);
    const cambios = actual.filter(item => originalPorFecha[item.fecha]
      && originalPorFecha[item.fecha].id_turno !== item.id_turno);
    const bajas = Object.values(originalPorFecha).filter(item => !actualPorFecha[item.fecha]);

    if (altas.length === 0 && cambios.length === 0 && bajas.length === 0) {
      GenerarMensaje("info", 5000, "Sin cambios", "No se modificó el horario");
      return { resultado: 200 };
    }

    const resumenCambios = [];
    if (altas.length > 0) resumenCambios.push(`${altas.length} día${altas.length === 1 ? '' : 's'} nuevo${altas.length === 1 ? '' : 's'}`);
    if (cambios.length > 0) resumenCambios.push(`${cambios.length} turno${cambios.length === 1 ? '' : 's'} cambiado${cambios.length === 1 ? '' : 's'}`);
    if (bajas.length > 0) resumenCambios.push(`${bajas.length} día${bajas.length === 1 ? '' : 's'} eliminado${bajas.length === 1 ? '' : 's'}`);

    const confirmacion = await confirmarAccion(
      `Se actualizará el horario:<br>${resumenCambios.join(', ')}.`,
      "¿Desea guardar estos cambios?",
      "question"
    );
    if (!confirmacion) return { resultado: 0 };

    modal.boton.prop('disabled', true);
    let operaciones = 0;
    let errores = 0;

    if (altas.length > 0) {
      const registro = new FormData();
      registro.append('modulo', 'Horario');
      registro.append('peticion', 'registrar_lote');
      registro.append('cedula_empleado', input.empleado.val());
      registro.append('asignaciones', JSON.stringify(altas.map(item => ({
        fecha: item.fecha,
        id_turno: item.id_turno
      }))));
      const respuesta = await AjaxHelper.enviaAjax(registro, '');
      if (respuesta.resultado >= 200 && respuesta.resultado <= 299) operaciones += altas.length;
      else errores++;
    }

    for (const cambio of cambios) {
      const modificar = new FormData();
      modificar.append('modulo', 'Horario');
      modificar.append('peticion', 'modificar');
      modificar.append('id_planificador_turno', originalPorFecha[cambio.fecha].id_planificador_turno);
      modificar.append('id_turno', cambio.id_turno);
      const respuesta = await AjaxHelper.enviaAjax(modificar, '');
      if (respuesta.resultado >= 200 && respuesta.resultado <= 299) operaciones++;
      else errores++;
    }

    for (const baja of bajas) {
      const eliminar = new FormData();
      eliminar.append('modulo', 'Horario');
      eliminar.append('peticion', 'eliminar');
      eliminar.append('id_planificador_turno', baja.id_planificador_turno);
      const respuesta = await AjaxHelper.enviaAjax(eliminar, '');
      if (respuesta.resultado >= 200 && respuesta.resultado <= 299) operaciones++;
      else errores++;
    }

    modal.boton.prop('disabled', false);
    if (errores === 0) {
      modal.modal.modal('hide');
      GenerarMensaje('success', 10000, 'Horario actualizado', `${operaciones} operación(es) aplicada(s)`);
      return { resultado: 200 };
    }

    GenerarMensaje('warning', 10000, 'Actualización parcial', `${operaciones} operación(es) aplicada(s), ${errores} con error`);
    return { resultado: 207 };
  }

  // ELIMINAR
  if (operacion == "eliminar") {
    if (input.id_horario.val() !== "") {
      const confirmacion = await confirmarAccion("Se eliminará la asignación", "¿Está seguro?", "warning");
      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_planificador_turno', input.id_horario.val());
        
        modal.boton.prop('disabled', true);
        json = await AjaxHelper.enviaAjax(peticion, "");
        if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
          modal.modal.modal("hide");
          GenerarMensaje(json.icon, 10000, json.mensaje, null);
        }
        modal.boton.prop('disabled', false);
      }
    }
  }

  input = null;
  modal = null;
  return json;
}

export async function EnviarFormulario(btn_string) {
  const MANEJADOR = { 'Asignar': 'registrar', 'Eliminar': 'eliminar', 'Guardar cambios': 'modificar_lote' };
  const accion = MANEJADOR[btn_string] || null;
  if (accion) return await EnviarDatos(accion);
  GenerarMensaje("danger", 10000, "Error", "Acción no válida");
  return { resultado: 0 };
}

// ==========================================
// SELECTS
// ==========================================

export async function CrearSelectEmpleados(soloSinHorario = false) {
  const datos = new FormData();
  datos.append("modulo", "Empleado");
  datos.append("peticion", "consultar");
  if (soloSinHorario) datos.append("solo_sin_horario", "1");
  
  try {
    const json = await AjaxHelper.enviaAjax(datos, "?page=Horario");
    if (json.resultado >= 200 && json.resultado <= 299) {
      const empleados = Array.isArray(json.datos)
        ? json.datos.map(item => ({ nombre: item.nombre + " " + item.apellido, valor: item.cedula }))
        : [];

      if (empleados.length === 0) {
        $('#empleado').empty();
      } else {
        SelectHelper.RenderizarSelect($('#empleado'), empleados, "Seleccione un Empleado");
      }
    }
  } catch (error) { console.log(error); }
}

export async function CrearSelectTurnos() {
  const datos = new FormData();
  datos.append("modulo", "Turno");
  datos.append("peticion", "consultar");
  datos.append("origen", "Horario");
  
  try {
    const json = await AjaxHelper.enviaAjax(datos, "?page=Horario");
    if (json.resultado >= 200 && json.resultado <= 299) {
      const turnos = json.datos.map(item => ({
        id_turno: item.id_turno,
        nombre: item.nombre + " (" + formatearHora12(item.hora_inicio) + " - " + formatearHora12(item.hora_fin) + ")"
      }));
      renderizarBotonesTurnos(turnos);
    }
  } catch (error) { console.log(error); }
}

// ==========================================
// INICIALIZACIÓN
// ==========================================

export function LimpiarFormulario() {
  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  input.id_horario.val("").prop("disabled", true);
  input.empleado.val("default").prop("disabled", false);
  input.empleado.removeClass("is-valid is-invalid").removeData('touched');
  span.empleado.removeClass("valid-feedback invalid-feedback invalid-tooltip d-inline-block").text("").hide();
  $('#asignaciones').removeClass("is-valid is-invalid").removeData('touched').val("");
  span.sfecha.removeClass("valid-feedback invalid-feedback invalid-tooltip d-inline-block").text("").hide();
  CrearSelectEmpleados(true);
  asignacionesOriginales = {};
  inicializarCalendario();
  EtiquetasModal("Horario").boton.prop('disabled', false);
  input = null;
  span = null;
}

export function CapaValidar() {
  CrearSelectEmpleados();
  const puedeGestionarHorarios = (typeof permisosHorarioDB !== 'undefined')
    && (permisosHorarioDB.horario?.registrar == 1 || permisosHorarioDB.horario?.modificar == 1);
  const puedeVerTurnos = (typeof permisosTurnoDB !== 'undefined')
    && permisosTurnoDB.turno?.ver == 1;
  if (puedeGestionarHorarios || puedeVerTurnos) CrearSelectTurnos();
  inicializarCalendario();
}

// ==========================================
// DATATABLE
// ==========================================

function RenderBotonesAccion() {
  const puedeModificar = (typeof permisosHorarioDB !== 'undefined')
    && permisosHorarioDB.horario
    && permisosHorarioDB.horario.modificar == 1;
  if (!puedeModificar) return '';

  const dropdown = $('<div>').addClass('dropdown');
  const boton = $('<button>').addClass('btn btn-sm btn-light border dropdown-toggle')
    .attr('type', 'button').attr('data-bs-toggle', 'dropdown')
    .html('<i class="fas fa-ellipsis-v me-3"></i>Acciones');
  const menu = $('<ul>').addClass('dropdown-menu');
  
  if (puedeModificar) {
    menu.append(
      $('<li>').append($('<a>').addClass('dropdown-item btn-editar text-primary').attr('href','#').attr('data-accion',0)
        .html('<i class="fas fa-edit me-2"></i>Editar'))
    );
  }
  dropdown.append(boton, menu);
  return dropdown.prop('outerHTML');
}

export async function DataTablePrincipal(arreglo) {
  if ($.fn.DataTable.isDataTable('#tablaHorario')) $('#tablaHorario').DataTable().destroy();
  
  $('#tablaHorario').DataTable({
    processing: true, data: arreglo,
    columns: [
      { data: 'fecha', render: data => data ? new Date(data + 'T00:00:00').toLocaleDateString('es-ES', { day:'2-digit', month:'2-digit', year:'numeric' }) : '' },
      { data: null, render: row => row.nombre + " " + row.apellido },
      { data: null, render: row => `<span class="badge bg-primary"><i class="fas fa-clock me-1"></i>${row.nombre_turno} ${row.hora_inicio} - ${row.hora_fin}</span>` },
      { data: null, render: () => RenderBotonesAccion() }
    ],
    order: [[0, 'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
  });
}

export async function EditarFormHorario(datos, accion) {
  LimpiarFormulario();
  let input = EtiquetasFormulario("input");
  input.id_horario.val(datos.id_planificador_turno).prop("disabled", true);
  input.empleado.prop("disabled", true);
  SelectHelper.BuscarValor(input.empleado, datos.cedula_empleado, "value");
  $('#contadorDias').text(`Fecha: ${datos.fecha}`);
  EditarModal(accion);
}

// ==========================================
// ESCUCHAR CAMBIOS EN TURNOS
// ==========================================

$(document).on('turnosActualizados', async function() {
  console.log('🔄 Actualizando botones de turnos...');
  await CrearSelectTurnos();
});

// ==========================================
// FULLCALENDAR - AGENDA GLOBAL
// ==========================================

export function inicializarCalendarioAgenda(calendarEl) {
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short',
            hour12: true
        },
        themeSystem: 'bootstrap5',

        events: async function (fetchInfo, successCallback, failureCallback) {
            const formData = new FormData();
            formData.append('modulo', 'Horario');
            formData.append('peticion', 'consultar');
            formData.append('fecha_inicio', fetchInfo.startStr.split('T')[0]);
            formData.append('fecha_fin', fetchInfo.endStr.split('T')[0]);

            try {
                const res = await AjaxHelper.enviaAjax(formData, '?page=Horario');
                if (res && res.resultado == 200 && Array.isArray(res.datos)) {
                    const eventos = res.datos.map(item => {
                        // Colores por turno
                        const colores = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#00BCD4', '#795548', '#607D8B'];
                        const colorIndex = Math.abs(hashCode(item.id_turno)) % colores.length;
                        
                        return {
                            id: item.id_planificador_turno,
                            title: `${item.nombre} ${item.apellido} - ${item.nombre_turno}`,
                            start: item.fecha,
                            allDay: true,
                            backgroundColor: colores[colorIndex],
                            borderColor: colores[colorIndex],
                            extendedProps: {
                                cedula: item.cedula_empleado,
                                turno: item.nombre_turno,
                                hora_inicio: item.hora_inicio,
                                hora_fin: item.hora_fin
                            }
                        };
                    });
                    successCallback(eventos);
                } else {
                    failureCallback();
                }
            } catch (e) {
                failureCallback();
            }
        },

        eventClick: function (info) {
            // Mostrar info del turno asignado
            const props = info.event.extendedProps;
            Swal.fire({
                title: info.event.title,
                html: `
                    <p><strong>Turno:</strong> ${props.turno}</p>
                    <p><strong>Horario:</strong> ${formatearHora12(props.hora_inicio)} - ${formatearHora12(props.hora_fin)}</p>
                    <p><strong>Fecha:</strong> ${info.event.startStr}</p>
                `,
                icon: 'info',
                confirmButtonText: 'Cerrar'
            });
        }
    });

    calendar.render();
    return calendar;
}

// Función hash para colores consistentes
function hashCode(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        const char = str.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    return hash;
}

// ==========================================
// TABLA DE EMPLEADOS
// ==========================================

export async function DataTableEmpleados(arreglo) {
  if ($.fn.DataTable.isDataTable('#tablaEmpleados')) {
    $('#tablaEmpleados').DataTable().destroy();
  }

  $('#tablaEmpleados').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'cedula_empleado' },
      { 
        data: null,
        render: function(row) {
          return row.nombre + " " + row.apellido;
        }
      },
      { 
        data: null,
        render: function(row) {
          return (row.turnos || [])
            .map(turno => `<span class="badge bg-primary me-1 mb-1"><i class="fas fa-clock me-1"></i>${turno.nombre}</span>`)
            .join('');
        }
      },
      {
        data: null,
        render: function(row) {
          const puedeVerHorario = (typeof permisosHorarioDB !== 'undefined') && permisosHorarioDB.horario?.ver_horario == 1;
          const puedeModificar = (typeof permisosHorarioDB !== 'undefined') && permisosHorarioDB.horario?.modificar == 1;
          if (!puedeVerHorario && !puedeModificar) return '';
          const acciones = `
              ${puedeVerHorario ? `<li><a class="dropdown-item btn-ver-horario text-info" href="#" data-cedula="${row.cedula_empleado}" data-nombre="${row.nombre} ${row.apellido}">
                <i class="fas fa-eye me-2"></i>Ver Horario
              </a></li>` : ''}
              ${puedeModificar ? `<li><a class="dropdown-item btn-editar-horario text-primary" href="#" data-cedula="${row.cedula_empleado}" data-nombre="${row.nombre} ${row.apellido}">
                <i class="fas fa-edit me-2"></i>Editar Horario
              </a></li>` : ''}`;
          return `<div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="fas fa-ellipsis-v me-3"></i>Acciones
            </button>
            <ul class="dropdown-menu">${acciones}</ul>
          </div>`;
        }
      }
    ],
    order: [[1, 'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
  });
}

async function obtenerHorarioEmpleado(cedula) {
  const peticion = new FormData();
  peticion.append('modulo', 'Horario');
  peticion.append('peticion', 'consultar');
  peticion.append('empleado_cedula', cedula);

  const respuestaHorario = await AjaxHelper.enviaAjax(peticion, '?page=Horario');
  let respuestaTurnos = { datos: [] };
  const puedeConsultarTurnos = (typeof permisosTurnoDB !== 'undefined')
    && permisosTurnoDB.turno?.ver == 1;

  if (puedeConsultarTurnos) {
    const peticionTurnos = new FormData();
    peticionTurnos.append('modulo', 'Turno');
    peticionTurnos.append('peticion', 'consultar');
    peticionTurnos.append('origen', 'Horario');
    respuestaTurnos = await AjaxHelper.enviaAjax(peticionTurnos, '?page=Horario');
  }

  return {
    horarios: Array.isArray(respuestaHorario?.datos) ? respuestaHorario.datos : [],
    turnos: Array.isArray(respuestaTurnos?.datos) ? respuestaTurnos.datos : []
  };
}

export async function editarHorarioEmpleado(cedula, nombre) {
  try {
    const datos = await obtenerHorarioEmpleado(cedula);
    await CrearSelectEmpleados();
    await CrearSelectTurnos();

    const input = EtiquetasFormulario('input');
    SelectHelper.BuscarValor(input.empleado, cedula, 'value');
    input.empleado.prop('disabled', true);
    input.id_horario.val('').prop('disabled', true);

    turnoActivo = null;
    asignaciones = {};
    asignacionesOriginales = {};

    datos.horarios.forEach(item => {
      const fecha = normalizarFechaHorario(item.fecha);
      const asignacion = {
        id_turno: item.id_turno,
        nombre: item.nombre_turno,
        color: coloresTurnosPorId.get(String(item.id_turno)) || coloresTurnos[0],
        id_planificador_turno: item.id_planificador_turno
      };
      asignaciones[fecha] = { ...asignacion };
      asignacionesOriginales[fecha] = { ...asignacion, fecha };
    });

    fechaActualCalendario = datos.horarios.length > 0
      ? new Date(`${normalizarFechaHorario(datos.horarios[0].fecha)}T00:00:00`)
      : new Date();
    renderizarCalendario();
    $('#nombreEmpleadoTitulo').text(nombre);
    EditarModal('modificar_lote');
  } catch (error) {
    console.error(error);
    GenerarMensaje('error', 8000, 'Error', 'No se pudo cargar el horario para editar');
  }
}

// ==========================================
// MODAL DE HORARIO DEL EMPLEADO (FULLCALENDAR)
// ==========================================

let fechaCalendarioEmpleado = new Date();
let datosCalendarioEmpleado = [];
let coloresTurnosEmpleado = new Map();

function colorTurnoEmpleado(idTurno) {
  const colorAsignado = coloresTurnosEmpleado.get(String(idTurno));
  return colorAsignado || coloresTurnos[Math.abs(hashCode(String(idTurno))) % coloresTurnos.length];
}

function normalizarFechaHorario(fecha) {
  return String(fecha ?? '').slice(0, 10);
}

function renderizarCalendarioEmpleado() {
  const year = fechaCalendarioEmpleado.getFullYear();
  const month = fechaCalendarioEmpleado.getMonth();
  const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
  const primerDia = new Date(year, month, 1).getDay();
  const primerDiaAjustado = primerDia === 0 ? 6 : primerDia - 1;
  const ultimoDia = new Date(year, month + 1, 0).getDate();
  const ultimoDiaMesAnterior = new Date(year, month, 0).getDate();
  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);

  $('#tituloMesEmpleado').text(`${nombresMeses[month]} ${year}`);

  let html = '<div class="selector-esquina"></div>';
  ['LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB', 'DOM'].forEach((nombre, indice) => {
    const claseFinSemana = indice >= 5 ? ' text-danger' : '';
    html += `<div class="selector-calendario${claseFinSemana}">${nombre}</div>`;
  });

  const totalCeldas = Math.ceil((primerDiaAjustado + ultimoDia) / 7) * 7;
  for (let posicion = 0; posicion < totalCeldas; posicion++) {
    if (posicion % 7 === 0) {
      const fila = Math.floor(posicion / 7);
      html += `<div class="selector-calendario selector-fila">S${fila + 1}</div>`;
    }

    const dia = posicion - primerDiaAjustado + 1;
    if (dia < 1 || dia > ultimoDia) {
      const diaFueraDeMes = dia < 1 ? ultimoDiaMesAnterior + dia : dia - ultimoDia;
      html += `<div class="p-1"><div class="dia-calendario otro-mes d-flex align-items-center justify-content-center">${diaFueraDeMes}</div></div>`;
      continue;
    }

    const fecha = `${year}-${String(month + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    const fechaDate = new Date(year, month, dia);
    const asignacion = datosCalendarioEmpleado.find(item => normalizarFechaHorario(item.fecha) === fecha);
    let clases = 'dia-calendario d-flex align-items-center justify-content-center';
    let estilo = '';

    if (fechaDate.getDay() === 0 || fechaDate.getDay() === 6) clases += ' fin-semana';
    if (fechaDate.getTime() === hoy.getTime()) clases += ' hoy';
    if (asignacion) {
      clases += ' empleado-asignado';
      estilo = `background-color: ${colorTurnoEmpleado(asignacion.id_turno)}; color: white; font-weight: bold;`;
    }

    html += `<div class="p-1"><div class="${clases}" style="${estilo}" ${asignacion ? `onclick="mostrarDetalleHorarioEmpleado('${fecha}')"` : ''}>${dia}</div></div>`;
  }

  $('#calendarioDiasEmpleado').html(html);

  const turnos = [...new Map(datosCalendarioEmpleado.map(item => [item.id_turno, item])).values()];
  $('#leyendaHorariosEmpleado').html(turnos.map(turno => `
    <span class="leyenda-item">
      <span class="leyenda-color" style="background-color: ${colorTurnoEmpleado(turno.id_turno)}"></span>
      ${turno.nombre_turno}
    </span>`).join(''));
}

window.mostrarDetalleHorarioEmpleado = function(fecha) {
  const asignacion = datosCalendarioEmpleado.find(item => normalizarFechaHorario(item.fecha) === fecha);
  if (!asignacion) return;

  Swal.fire({
    title: asignacion.nombre_turno,
    html: `<p><strong>Fecha:</strong> ${fecha}</p><p><strong>Horario:</strong> ${formatearHora12(asignacion.hora_inicio)} - ${formatearHora12(asignacion.hora_fin)}</p>`,
    icon: 'info',
    confirmButtonText: 'Cerrar'
  });
};

export async function cargarHorarioEmpleado(cedula, nombre) {
  $('#nombreEmpleadoTitulo').text(nombre);
  
  const peticion = new FormData();
  peticion.append("modulo", "Horario");
  peticion.append("peticion", "consultar");
  peticion.append("empleado_cedula", cedula);

  try {
    const peticionTurnos = new FormData();
    peticionTurnos.append("modulo", "Turno");
    peticionTurnos.append("peticion", "consultar");
    peticionTurnos.append("origen", "Horario");

    const [json, jsonTurnos] = await Promise.all([
      AjaxHelper.enviaAjax(peticion, "?page=Horario"),
      AjaxHelper.enviaAjax(peticionTurnos, "?page=Horario")
    ]);

    if (Array.isArray(json?.datos)) {
      const datos = json.datos;
      const turnos = Array.isArray(jsonTurnos?.datos) ? jsonTurnos.datos : [];

      coloresTurnosEmpleado = new Map(
        turnos.map((turno, index) => [
          String(turno.id_turno),
          coloresTurnos[index % coloresTurnos.length]
        ])
      );
      
      // Turno de hoy
      const hoy = new Date().toISOString().split('T')[0];
      const turnoHoy = datos.find(d => normalizarFechaHorario(d.fecha) === hoy);
      
      if (turnoHoy) {
        $('#detalleTurno')
          .removeClass('bg-secondary')
          .addClass('bg-success')
          .html(`<i class="fas fa-clock me-1"></i>Hoy: ${turnoHoy.nombre_turno} (${formatearHora12(turnoHoy.hora_inicio)} - ${formatearHora12(turnoHoy.hora_fin)})`);
      } else {
        $('#detalleTurno')
          .removeClass('bg-success')
          .addClass('bg-secondary')
          .html(`<i class="fas fa-clock me-1"></i>Sin turno hoy`);
      }
      
      $('#detalleDiasAsignados').html(`<i class="fas fa-calendar me-1"></i>${datos.length} día(s) asignado(s)`);

      const modalElement = document.getElementById('modalHorarioEmpleado');
      const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
      modal.show();

      datosCalendarioEmpleado = datos.map(item => ({
        ...item,
        fecha: normalizarFechaHorario(item.fecha)
      }));
      fechaCalendarioEmpleado = new Date();
      renderizarCalendarioEmpleado();
    }
  } catch (error) {
    console.error(error);
  }
}

// Destruir calendario al cerrar el modal
$('#modalHorarioEmpleado').on('hidden.bs.modal', function () {
  datosCalendarioEmpleado = [];
});

$(document).on('click', '#btnMesAnteriorEmpleado', function () {
  fechaCalendarioEmpleado.setMonth(fechaCalendarioEmpleado.getMonth() - 1);
  renderizarCalendarioEmpleado();
});

$(document).on('click', '#btnMesSiguienteEmpleado', function () {
  fechaCalendarioEmpleado.setMonth(fechaCalendarioEmpleado.getMonth() + 1);
  renderizarCalendarioEmpleado();
});