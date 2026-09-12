import * as horario from "../Handlers/HorarioHandler.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";

// MÓDULO DE HORARIOS

let calendarAgenda = null;

$(document).ready(function () {
  inicializarCalendarioAgenda();
  iniciarValidaciones();
});

// ==========================================
// FULLCALENDAR - AGENDA GLOBAL
// ==========================================

function inicializarCalendarioAgenda() {
  const calendarEl = document.getElementById('calendarHorarios');
  if (calendarEl) {
    calendarAgenda = horario.inicializarCalendarioAgenda(calendarEl);
  }
}

// ==========================================
// EVENTOS DE BOTONES
// ==========================================

$("#btnHorarioForm").on("click", async function () {
  let respuesta = await horario.EnviarFormulario($(this).text());
  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    if (calendarAgenda) calendarAgenda.refetchEvents();
    await crearDataTable();
  }
});

$("#btnNuevoHorario").on("click", function () {
  horario.LimpiarFormulario();
  horario.EditarModal("registrar");
});

// Botón Turnos - Abre el modal con la LISTA
$("#btnGestionarTurnos").on("click", function () {
  $('#modalTurnoLista').modal('show');
});

// Al abrir el modal de lista, cargar la tabla
$('#modalTurnoLista').on('show.bs.modal', async function () {
  await cargarTablaTurnosEnHorario();
});

// Botón Nuevo Turno dentro del modal de lista
$(document).on('click', '#btnNuevoTurnoLista', function () {
  import("../Handlers/TurnoHandler.js").then(turno => {
    turno.LimpiarFormulario();
    turno.EditarModal('registrar');
    $('#modalTurnoLista').modal('hide');
  });
});

// Al cerrar el formulario de turno, refrescar botones, calendario y reabrir lista
$('#modalTurno').on('hidden.bs.modal', async function () {
  await horario.CrearSelectTurnos();
  if (calendarAgenda) calendarAgenda.refetchEvents();
  if ($('#modalTurnoLista').length) {
    $('#modalTurnoLista').modal('show');
  }
});

// ==========================================
// SELECTORES: TODOS LOS HORARIOS / HISTÓRICO
// ==========================================

$("#btnAgendaGlobal").on("click", function () {
  $(this).removeClass('btn-outline-warning').addClass('btn-warning text-dark fw-semibold');
  $("#btnHistorico").removeClass('btn-warning text-dark fw-semibold').addClass('btn-outline-warning');
  $("#agendaGlobalContainer").removeClass('d-none');
  $("#historicoHorarioSection").addClass('d-none');
  if (calendarAgenda) calendarAgenda.render();
});

$("#btnHistorico").on("click", function () {
  $(this).removeClass('btn-outline-warning').addClass('btn-warning text-dark fw-semibold');
  $("#btnAgendaGlobal").removeClass('btn-warning text-dark fw-semibold').addClass('btn-outline-warning');
  $("#agendaGlobalContainer").addClass('d-none');
  $("#historicoHorarioSection").removeClass('d-none');
  crearDataTable();
});

// ==========================================
// VALIDACIÓN Y DATATABLE
// ==========================================

function iniciarValidaciones() {
  horario.CapaValidar();
}

async function crearDataTable() {
  const peticion = new FormData();
  peticion.append("modulo", "Horario");
  peticion.append("peticion", "consultar");

  try {
    const json = await AjaxHelper.enviaAjax(peticion, "?page=Horario");
    if (Array.isArray(json.datos)) {
      horario.DataTablePrincipal(json.datos);
    }
  } catch (error) {
    console.log(error);
  }
}

async function rellenar(pos, accion) {
  let str_accion = "";
  const linea = $(pos).closest('tr');
  const tabla = $('#tablaHorario').DataTable();
  const datosFila = tabla.row(linea).data();

  if (accion == 0) str_accion = "modificar";
  if (accion == 1) str_accion = "eliminar";

  await horario.EditarFormHorario(datosFila, str_accion);
}

$(document).on('click', '.btn-editar', function () {
  rellenar($(this), $(this).attr("data-accion"));
});

$(document).on('click', '.btn-eliminar', function () {
  rellenar($(this), $(this).attr("data-accion"));
});

// ==========================================
// TABLA DE TURNOS EN EL MODAL DE LISTA
// ==========================================

function formatearHora12(hora24) {
  if (!hora24) return '';
  const [h, m] = hora24.split(':');
  let hora = parseInt(h);
  const ampm = hora >= 12 ? 'PM' : 'AM';
  hora = hora % 12 || 12;
  return `${hora}:${m} ${ampm}`;
}

async function cargarTablaTurnosEnHorario() {
  const peticion = new FormData();
  peticion.append("modulo", "Turno");
  peticion.append("peticion", "consultar");

  try {
    const json = await AjaxHelper.enviaAjax(peticion, "?page=Turno");
    if (Array.isArray(json?.datos)) {
      if ($.fn.DataTable.isDataTable('#tablaTurnoLista')) {
        $('#tablaTurnoLista').DataTable().destroy();
      }
      
      $('#tablaTurnoLista').DataTable({
        processing: true,
        data: json.datos,
        columns: [
          { data: 'nombre' },
          { 
            data: 'hora_inicio', 
            render: data => formatearHora12(data) 
          },
          { 
            data: 'hora_fin', 
            render: data => formatearHora12(data) 
          },
          { 
            data: 'minuto_tolerancia', 
            render: data => data ? data + ' min.' : '15 min.' 
          },
          {
            data: null,
            render: function () {
              return `<div class="dropdown">
                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                  <i class="fas fa-ellipsis-v me-3"></i>Acciones
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item btn-editar-turno-lista text-primary" href="#"><i class="fas fa-edit me-2"></i>Editar</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item btn-eliminar-turno-lista text-danger" href="#"><i class="fas fa-trash me-2"></i>Eliminar</a></li>
                </ul>
              </div>`;
            }
          }
        ],
        order: [[0, 'asc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
      });
    }
  } catch (error) {
    console.error(error);
  }
}

// Eventos para editar/eliminar desde la lista
$(document).on('click', '.btn-editar-turno-lista', function () {
  const row = $('#tablaTurnoLista').DataTable().row($(this).closest('tr')).data();
  import("../Handlers/TurnoHandler.js").then(turno => {
    turno.EditarFormTurno(row, 'modificar');
    $('#modalTurnoLista').modal('hide');
  });
});

$(document).on('click', '.btn-eliminar-turno-lista', function () {
  const row = $('#tablaTurnoLista').DataTable().row($(this).closest('tr')).data();
  import("../Handlers/TurnoHandler.js").then(turno => {
    turno.EditarFormTurno(row, 'eliminar');
    $('#modalTurnoLista').modal('hide');
  });
});

// ==========================================
// SELECTORES: TODOS LOS HORARIOS / EMPLEADOS
// ==========================================

$("#btnAgendaGlobal").on("click", function () {
  $(this).removeClass('btn-outline-warning').addClass('btn-warning text-dark fw-semibold');
  $("#btnEmpleados").removeClass('btn-warning text-dark fw-semibold').addClass('btn-outline-warning');
  $("#agendaGlobalContainer").removeClass('d-none');
  $("#empleadosSection").addClass('d-none');
  if (calendarAgenda) calendarAgenda.render();
});

$("#btnEmpleados").on("click", function () {
  $(this).removeClass('btn-outline-warning').addClass('btn-warning text-dark fw-semibold');
  $("#btnAgendaGlobal").removeClass('btn-warning text-dark fw-semibold').addClass('btn-outline-warning');
  $("#agendaGlobalContainer").addClass('d-none');
  $("#empleadosSection").removeClass('d-none');
  crearDataTableEmpleados();
});

// ==========================================
// TABLA DE EMPLEADOS
// ==========================================

async function crearDataTableEmpleados() {
  const peticion = new FormData();
  peticion.append("modulo", "Horario");
  peticion.append("peticion", "consultar");

  try {
    const json = await AjaxHelper.enviaAjax(peticion, "?page=Horario");
    if (Array.isArray(json.datos)) {
      // Agrupar por empleado único
      const empleadosUnicos = [];
      const visto = new Set();
      
      json.datos.forEach(item => {
        if (!visto.has(item.cedula_empleado)) {
          visto.add(item.cedula_empleado);
          empleadosUnicos.push(item);
        }
      });
      
      horario.DataTableEmpleados(empleadosUnicos);
    }
  } catch (error) {
    console.log(error);
  }
}

// Evento Ver Horario
$(document).on('click', '.btn-ver-horario', function () {
  const cedula = $(this).data('cedula');
  const nombre = $(this).data('nombre');
  horario.cargarHorarioEmpleado(cedula, nombre);
});