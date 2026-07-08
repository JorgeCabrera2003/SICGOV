import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js";

function Etiquetas() {
  return {
    modal: $('#modalPermisoLaboral'),
    titulo: $('#modalTitleTextPermiso'),
    boton: $('#btnPermisoForm'),
    input: {
      id: $('#id_permiso'),
      cedula: $('#permiso-cedula'),
      tipo: $('#permiso-tipo'),
      inicio: $('#permiso-fecha-inicio'),
      fin: $('#permiso-fecha-fin')
    }
  }
}

function AplicarEmpleadoSesion() {
  const e = Etiquetas();
  const cedulaSesion = window.PERMISO_SESSION_USER?.cedula;
  if (!cedulaSesion) return;
  const option = e.input.cedula.find(`option[value="${cedulaSesion}"]`);
  if (option.length) {
    e.input.cedula.val(cedulaSesion);
  }
}

export function EditarModal(operacion) {
  const e = Etiquetas();
  let titulo = '';
  let boton = '';
  if (operacion == 'registrar') { titulo = 'Solicitar Permiso'; boton = 'Solicitar'; }
  if (operacion == 'modificar') { titulo = 'Actualizar Permiso'; boton = 'Actualizar'; }
  if (operacion == 'eliminar') { titulo = 'Eliminar Permiso'; boton = 'Eliminar'; }
  e.titulo.text(titulo);
  e.boton.text(boton);
  if (operacion === 'registrar') {
    AplicarEmpleadoSesion();
  }
  e.modal.modal('show');
}

export async function CargarSelects() {
  const e = Etiquetas();
  const pet = new FormData();
  pet.append('peticion','consultar_tipos');
  pet.append('modulo','PermisoLaboral');
  try {
    let json = await AjaxHelper.enviaAjax(pet, '?page=PermisoLaboral');
    if (Array.isArray(json.datos)) {
      e.input.tipo.empty();
      json.datos.forEach(d => e.input.tipo.append(new Option(d.nombre, d.id_tipo_permiso)));
    }
  } catch(e){}

  const pet2 = new FormData();
  pet2.append('peticion','consultar_empleados');
  pet2.append('modulo','PermisoLaboral');
  try {
    let j2 = await AjaxHelper.enviaAjax(pet2, '?page=PermisoLaboral');
    if (Array.isArray(j2.datos)) {
      e.input.cedula.empty();
      j2.datos.forEach(emp => e.input.cedula.append(new Option(emp.nombre + ' ' + emp.apellido, emp.cedula)));
      AplicarEmpleadoSesion();
    }
  } catch(e){}
}

export async function EnviarFormulario(etiqueta) {
  const e = Etiquetas();
  const accionMap = { 'Solicitar': 'registrar', 'Actualizar': 'modificar', 'Eliminar': 'eliminar' };
  const accion = accionMap[etiqueta.text()];
  if (!accion) return { resultado: 0 };

  const pet = new FormData();
  pet.append('modulo','PermisoLaboral');
  if (accion === 'registrar') {
    pet.append('peticion','registrar');
    pet.append('id_tipo_permiso', e.input.tipo.val());
    pet.append('cedula_empleado', e.input.cedula.val());
    pet.append('fecha_inicio', e.input.inicio.val());
    pet.append('fecha_fin', e.input.fin.val());
  }
  if (accion === 'modificar') {
    pet.append('peticion','modificar');
    pet.append('id_permiso', e.input.id.val());
    pet.append('id_tipo_permiso', e.input.tipo.val());
    pet.append('cedula_empleado', e.input.cedula.val());
    pet.append('fecha_inicio', e.input.inicio.val());
    pet.append('fecha_fin', e.input.fin.val());
  }
  if (accion === 'eliminar') {
    pet.append('peticion','eliminar');
    pet.append('id_permiso', e.input.id.val());
  }

  e.boton.prop('disabled', true);
  const json = await AjaxHelper.enviaAjax(pet, '?page=PermisoLaboral');
  e.boton.prop('disabled', false);
  if (json && typeof json.resultado === 'number') MensajeriaHelper.GenerarMensaje(json.icon || 'info', 8000, json.mensaje || json.message, null);
  return json;
}

export function Limpiar() {
  const e = Etiquetas();
  e.input.id.val('').prop('disabled', false);
  e.input.cedula.val('').prop('disabled', false);
  e.input.tipo.val('').prop('disabled', false);
  e.input.inicio.val('').prop('disabled', false);
  e.input.fin.val('').prop('disabled', false);
  e.boton.prop('disabled', false);
}

export async function DataTablePermisos(arreglo) {
  if ($.fn.DataTable.isDataTable('#tablaPermisoLaboral')) $('#tablaPermisoLaboral').DataTable().destroy();
  $('#tablaPermisoLaboral').DataTable({
    processing: true,
    data: arreglo,
    columnDefs: [{ className: 'text-center align-middle', targets: '_all' }],
    columns: [
      { data: 'empleado' },
      { data: 'tipo_nombre' },
      { data: null, render: function(row){
          const inicio = row.fecha_inicio ? new Date(row.fecha_inicio) : null;
          const fin = row.fecha_fin ? new Date(row.fecha_fin) : null;
          if (!inicio || !fin || isNaN(inicio) || isNaN(fin)) return '-';
          const diffMs = fin - inicio;
          const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24)) + 1;
          return diffDays > 0 ? diffDays : 0;
        } },
      { data: 'estado', render: function(data){
          const estado = String(data || '').toUpperCase();
          let estilo = 'bg-secondary';
          if (estado === 'APROBADO') estilo = 'bg-success';
          if (estado === 'RECHAZADO') estilo = 'bg-danger';
          if (estado === 'PENDIENTE') estilo = 'bg-secondary';
          return `<span class="badge rounded-pill ${estilo} text-white">${estado}</span>`;
        } },
      { data: null, render: function(row){
          const formatearFecha = (fecha) => {
            if (!fecha) return '-';
            const fechaObj = new Date(fecha);
            if (isNaN(fechaObj)) return fecha;
            const dia = String(fechaObj.getDate()).padStart(2, '0');
            const mes = String(fechaObj.getMonth() + 1).padStart(2, '0');
            const anio = fechaObj.getFullYear();
            return `${dia}/${mes}/${anio}`;
          };
          const inicio = formatearFecha(row.fecha_inicio);
          const fin = formatearFecha(row.fecha_fin);
          return `${inicio} / ${fin}`;
        } },
      { data: null, render: function(){
          return '<div class="dropdown">' +
            '<button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
              '<i class="fas fa-ellipsis-v me-3"></i>Acciones' +
            '</button>' +
            '<ul class="dropdown-menu">' +
              '<li><a class="dropdown-item btn-editar text-primary" href="#" data-accion="0"><i class="fas fa-edit me-2"></i>Editar</a></li>' +
              '<li><hr class="dropdown-divider"></li>' +
              '<li><a class="dropdown-item btn-aprobar text-success" href="#" data-accion="aprobar"><i class="fas fa-check me-2"></i>Aprobar</a></li>' +
              '<li><a class="dropdown-item btn-rechazar text-danger" href="#" data-accion="rechazar"><i class="fas fa-times me-2"></i>Rechazar</a></li>' +
              '<li><hr class="dropdown-divider"></li>' +
              '<li><a class="dropdown-item btn-eliminar text-danger" href="#" data-accion="1"><i class="fas fa-trash me-2"></i>Eliminar</a></li>' +
            '</ul>' +
          '</div>';
        } }
    ],
    order: [[4,'desc']],
    language: { url: idiomaTabla }
  });
}

export async function ProcesarEstado(datos, accion) {
  const calcularDias = (inicio, fin) => {
    const fechaInicio = inicio ? new Date(inicio) : null;
    const fechaFin = fin ? new Date(fin) : null;
    if (!fechaInicio || !fechaFin || isNaN(fechaInicio) || isNaN(fechaFin)) return 0;
    const diffMs = fechaFin - fechaInicio;
    return Math.floor(diffMs / (1000 * 60 * 60 * 24)) + 1;
  };

  const formatearFecha = (fecha) => {
    if (!fecha) return '-';
    const fechaObj = new Date(fecha);
    if (isNaN(fechaObj)) return fecha;
    const dia = String(fechaObj.getDate()).padStart(2, '0');
    const mes = String(fechaObj.getMonth() + 1).padStart(2, '0');
    const anio = fechaObj.getFullYear();
    return `${dia}/${mes}/${anio}`;
  };

  const dias = calcularDias(datos.fecha_inicio, datos.fecha_fin);
  const rango = `${formatearFecha(datos.fecha_inicio)} / ${formatearFecha(datos.fecha_fin)}`;
  const titulo = accion === 'aprobar' ? 'Aprobar permiso' : 'Rechazar permiso';
  const mensaje = accion === 'aprobar'
    ? `¿Desea aprobar el permiso de ${datos.empleado} por ${dias} día${dias === 1 ? '' : 's'}?\n\nRango: ${rango}`
    : `¿Desea rechazar el permiso de ${datos.empleado} por ${dias} día${dias === 1 ? '' : 's'}?\n\nRango: ${rango}`;

  const confirmado = await MensajeriaHelper.MostrarConfirmacion(titulo, mensaje, accion === 'aprobar' ? 'question' : 'warning');
  if (!confirmado) return { resultado: 0, mensaje: 'Acción cancelada' };

  const pet = new FormData();
  pet.append('modulo', 'PermisoLaboral');
  pet.append('peticion', accion);
  pet.append('id_permiso', datos.id_permiso);

  const json = await AjaxHelper.enviaAjax(pet, '?page=PermisoLaboral');
  if (json && typeof json.resultado === 'number') {
    const tituloResultado = accion === 'aprobar' ? 'Permiso aprobado' : 'Permiso rechazado';
    MensajeriaHelper.GenerarMensaje(json.icon || 'info', 8000, tituloResultado, json.mensaje || 'Operación completada');
  }
  return json;
}

export async function EditarForm(datos, accion) {
  Limpiar();
  const e = Etiquetas();
  const bool = (accion === 'eliminar');
  e.input.id.val(datos.id_permiso).prop('disabled', true);
  e.input.cedula.val(datos.cedula_empleado).prop('disabled', bool);
  e.input.tipo.val(datos.id_tipo_permiso).prop('disabled', bool);
  e.input.inicio.val(datos.fecha_inicio).prop('disabled', bool);
  e.input.fin.val(datos.fecha_fin).prop('disabled', bool);
  EditarModal(accion);
}
