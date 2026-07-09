import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as SelectHelper from "../Helpers/SelectHelper.js";
import { confirmarAccion } from "../Helpers/UIHelper.js";
import { GenerarMensaje, FeedbackToltipInput } from "../Helpers/MensajeriaHelper.js";

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
let coloresTurnos = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#00BCD4', '#795548', '#607D8B'];

// ==========================================
// BOTONES DE TURNOS
// ==========================================

function renderizarBotonesTurnos(turnos) {
  let html = '';
  
  turnos.forEach((turno, index) => {
    const color = coloresTurnos[index % coloresTurnos.length];
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
  renderizarLeyenda(turnos);
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

function renderizarLeyenda(turnos) {
  let html = '';
  turnos.forEach((turno, index) => {
    const color = coloresTurnos[index % coloresTurnos.length];
    html += `<span class="leyenda-item">
      <span class="leyenda-color" style="background-color: ${color};"></span>
      ${turno.nombre}
    </span>`;
  });
  $('#leyendaColores').html(html);
}

// ==========================================
// CALENDARIO
// ==========================================

export function inicializarCalendario() {
  fechaActualCalendario = new Date();
  turnoActivo = null;
  asignaciones = {};
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

  let html = '';

  // Días del mes anterior
  for (let i = primerDiaAjustado - 1; i >= 0; i--) {
    const dia = ultimoDiaMesAnterior - i;
    html += `<div class="p-1">
      <div class="dia-calendario otro-mes d-flex align-items-center justify-content-center">${dia}</div>
    </div>`;
  }

  // Días del mes actual
  for (let dia = 1; dia <= ultimoDia; dia++) {
    const fechaStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
    const fecha = new Date(year, month, dia);
    const diaSemana = fecha.getDay();
    const asignado = asignaciones[fechaStr];
    
    let clases = 'dia-calendario d-flex align-items-center justify-content-center';
    let estilo = '';
    
    if (diaSemana === 0 || diaSemana === 6) clases += ' fin-semana';
    if (fecha.getTime() === hoy.getTime()) clases += ' hoy';
    
    if (asignado) {
      estilo = `background-color: ${asignado.color}; color: white; font-weight: bold;`;
    }
    
    html += `<div class="p-1">
      <div class="${clases}" data-fecha="${fechaStr}" 
        style="${estilo}"
        onclick="toggleDia(this, '${fechaStr}')">${dia}</div>
    </div>`;
  }

  // Rellenar última fila
  const totalCeldas = primerDiaAjustado + ultimoDia;
  const diasRestantes = (7 - (totalCeldas % 7)) % 7;
  for (let i = 1; i <= diasRestantes; i++) {
    html += `<div class="p-1">
      <div class="dia-calendario otro-mes d-flex align-items-center justify-content-center">${i}</div>
    </div>`;
  }

  $('#calendarioDias').html(html);
  actualizarContador();
}

window.toggleDia = function(elemento, fecha) {
  // Si ya tiene asignación, quitarla
  if (asignaciones[fecha]) {
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
}

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
      asignaciones[fechaStr] = { ...turnoActivo };
    }
  }
  renderizarCalendario();
  actualizarInputAsignaciones();
});

$('#btnLimpiarSeleccion').on('click', function () {
  asignaciones = {};
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

  if (input.empleado.val() == "default" || input.empleado.val() == null) {
    SelectHelper.FeedbackSelect(input.empleado, span.empleado, "Debe seleccionar un Empleado", 0);
    bool = false;
  }

  if (Object.keys(asignaciones).length === 0) {
    FeedbackToltipInput($('#asignaciones'), $('#sfecha'), "Debe asignar al menos un día con turno", 0);
    bool = false;
  } else {
    FeedbackToltipInput($('#asignaciones'), $('#sfecha'), "", 1);
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
  const MANEJADOR = { 'Asignar': 'registrar', 'Eliminar': 'eliminar' };
  const accion = MANEJADOR[btn_string] || null;
  if (accion) return await EnviarDatos(accion);
  GenerarMensaje("danger", 10000, "Error", "Acción no válida");
  return { resultado: 0 };
}

// ==========================================
// SELECTS
// ==========================================

export async function CrearSelectEmpleados() {
  const datos = new FormData();
  datos.append("modulo", "Empleado");
  datos.append("peticion", "consultar");
  
  try {
    const json = await AjaxHelper.enviaAjax(datos, "?page=Horario");
    if (json.resultado >= 200 && json.resultado <= 299) {
      SelectHelper.RenderizarSelect($('#empleado'), 
        json.datos.map(item => ({ nombre: item.nombre + " " + item.apellido, valor: item.cedula })),
        "Seleccione un Empleado");
    }
  } catch (error) { console.log(error); }
}

export async function CrearSelectTurnos() {
  const datos = new FormData();
  datos.append("modulo", "Turno");
  datos.append("peticion", "consultar");
  
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
  input.id_horario.val("").prop("disabled", true);
  input.empleado.val("default").prop("disabled", false);
  inicializarCalendario();
  EtiquetasModal("Horario").boton.prop('disabled', false);
  input = null;
}

export function CapaValidar() {
  CrearSelectEmpleados();
  CrearSelectTurnos();
  inicializarCalendario();
}

// ==========================================
// DATATABLE
// ==========================================

function RenderBotonesAccion() {
  const dropdown = $('<div>').addClass('dropdown');
  const boton = $('<button>').addClass('btn btn-sm btn-light border dropdown-toggle')
    .attr('type', 'button').attr('data-bs-toggle', 'dropdown')
    .html('<i class="fas fa-ellipsis-v me-3"></i>Acciones');
  const menu = $('<ul>').addClass('dropdown-menu');
  
  menu.append(
    $('<li>').append($('<a>').addClass('dropdown-item btn-eliminar text-danger').attr('href','#').attr('data-accion',1)
      .html('<i class="fas fa-trash me-2"></i>Eliminar'))
  );
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
          if (row.nombre_turno) {
            return `<span class="badge bg-primary"><i class="fas fa-clock me-1"></i>${row.nombre_turno} (${formatearHora12(row.hora_inicio)} - ${formatearHora12(row.hora_fin)})</span>`;
          }
          return '<span class="badge bg-secondary">Sin turno asignado</span>';
        }
      },
      {
        data: null,
        render: function(row) {
          return `<div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="fas fa-ellipsis-v me-3"></i>Acciones
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item btn-ver-horario text-info" href="#" data-cedula="${row.cedula_empleado}" data-nombre="${row.nombre} ${row.apellido}">
                <i class="fas fa-eye me-2"></i>Ver Horario
              </a></li>
            </ul>
          </div>`;
        }
      }
    ],
    order: [[1, 'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
  });
}

// ==========================================
// MODAL DE HORARIO DEL EMPLEADO (FULLCALENDAR)
// ==========================================

let calendarEmpleado = null;

export async function cargarHorarioEmpleado(cedula, nombre) {
  $('#nombreEmpleadoTitulo').text(nombre);
  
  const peticion = new FormData();
  peticion.append("modulo", "Horario");
  peticion.append("peticion", "consultar");
  peticion.append("empleado_cedula", cedula);

  try {
    const json = await AjaxHelper.enviaAjax(peticion, "?page=Horario");
    if (Array.isArray(json?.datos)) {
      const datos = json.datos;
      
      // Turno de hoy
      const hoy = new Date().toISOString().split('T')[0];
      const turnoHoy = datos.find(d => d.fecha === hoy);
      
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

      // Convertir datos a eventos de FullCalendar
      const colores = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#00BCD4', '#795548', '#607D8B'];
      
      const eventos = datos.map(item => {
        const colorIndex = Math.abs(hashCode(item.id_turno)) % colores.length;
        
        return {
          id: item.id_planificador_turno,
          title: item.nombre_turno,
          start: item.fecha + 'T' + item.hora_inicio,
          end: item.fecha + 'T' + item.hora_fin,
          backgroundColor: colores[colorIndex],
          borderColor: colores[colorIndex],
          textColor: '#fff',
          extendedProps: {
            turno: item.nombre_turno,
            hora_inicio: item.hora_inicio,
            hora_fin: item.hora_fin
          }
        };
      });

      // Destruir calendario anterior si existe
      if (calendarEmpleado) {
        calendarEmpleado.destroy();
      }

      // Crear calendario en el modal
      const calendarEl = document.getElementById('calendarEmpleado');
      calendarEmpleado = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        height: 'auto',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'timeGridWeek,dayGridMonth'
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        eventTimeFormat: {
          hour: 'numeric',
          minute: '2-digit',
          meridiem: 'short',
          hour12: true
        },
        buttonText: {
          timeGridWeek: 'Semana',
          dayGridMonth: 'Mes'
        },
        themeSystem: 'bootstrap5',
        events: eventos,
        eventClick: function(info) {
          const props = info.event.extendedProps;
          Swal.fire({
            title: props.turno,
            html: `
              <p><strong>Horario:</strong> ${formatearHora12(props.hora_inicio)} - ${formatearHora12(props.hora_fin)}</p>
              <p><strong>Fecha:</strong> ${info.event.startStr.split('T')[0]}</p>
            `,
            icon: 'info',
            confirmButtonText: 'Cerrar'
          });
        }
      });

      calendarEmpleado.render();
    }
  } catch (error) {
    console.error(error);
  }

  $('#modalHorarioEmpleado').modal('show');
}

// Destruir calendario al cerrar el modal
$('#modalHorarioEmpleado').on('hidden.bs.modal', function () {
  if (calendarEmpleado) {
    calendarEmpleado.destroy();
    calendarEmpleado = null;
  }
});