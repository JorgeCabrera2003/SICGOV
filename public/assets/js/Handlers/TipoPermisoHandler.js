import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js";
import * as PermisoHelper from "../Helpers/PermisoHelper.js";

//SUBMODULO DE CATEGORIA DE INGREDIENTES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputTipoPermiso = {
    nombre: $('#tipo_permiso-nombre'),
    id_tipo_permiso: $('#id_tipo_permiso')
  }

  const spanTipoPermiso = {
    nombre: $('#stipo_permiso-nombre'),
    id_tipo_permiso: $('#sid_tipo_permiso')
  }

  if (etiquetas === "input") {
    referencia = inputTipoPermiso;
  }

  if (etiquetas === "span") {
    referencia = spanTipoPermiso
  }

  return referencia
}

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalTipoPermisoTabla = {
    modal: $('#modalTipoPermiso'),
    titulo: $('#modalTitleTextTipoPermiso'),
    boton: $('#btn-TipoPermisoPCancel')
  }

  const modalTipoPermiso = {
    modal: $('#modal-formtipo_permiso'),
    titulo: $('#modalTitleText-Form-TipoPermiso'),
    boton: $('#btn-TipoPermisoForm')
  }

  if (etiqueta === "TablaTipoPermiso") {
    referencia = modalTipoPermisoTabla;
  }

  if (etiqueta === "TipoPermiso") {
    referencia = modalTipoPermiso;
  }

  return referencia;
}

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("TipoPermiso");

  if (operacion == 'registrar') {
    titulo = "Nueva Tipo de Permiso";
    boton = "Nuevo";
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Tipo de Permiso";
    boton = "Actualizar";
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Tipo de Permiso";
    boton = "Borrar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

//-------LÓGICA DE CAMBIO ENTRE MODALES------
export function FormNuevaTipoPermiso() {
  let modal_tabla = EtiquetasModal("TablaTipoPermiso");
  let modal_form = EtiquetasModal("TipoPermiso");
  modal_tabla.modal.modal("hide");
  Limpiar();
  EditarModal("registrar");

  modal_tabla = null;
  modal_form = null;
}

export function CancelarFormulario() {
  let modal_form = EtiquetasModal("TipoPermiso");
  let modal_tabla = EtiquetasModal("TablaTipoPermiso");
  modal_form.modal.modal("hide");
  modal_tabla.modal.modal("show");

  modal_form = null;
  modal_tabla = null;
}

export function CerrarFormulario() {
  let modal_form = EtiquetasModal("TipoPermiso");

  modal_form.modal.modal("hide");
  modal_form = null;
}

export function MostrarModalTabla() {
  let modal_tabla = EtiquetasModal("TablaTipoPermiso");
  let modal_form = EtiquetasModal("TipoPermiso");
  modal_form.modal.modal("hide");
  modal_tabla.modal.modal("show");

  modal_tabla = null;
}

//-------FUNNCIONES-------

async function EnviarDatos(operacion, modulo = "TipoPermiso") {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal("TipoPermiso");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();
  let json = null;


  if (modulo == "Permiso") {
    endpoint = "Permiso";
    peticion.append("modulo", "TipoPermiso")
  } else {
    endpoint = "TipoPermiso";
  }

  //Registrar y Modificar
  if (operacion == "registrar" || operacion == "modificar") {

    if (operacion == "registrar") {
      str_acccion = "registrará";
      accion = "registrar"
    }

    if (operacion == "modificar") {
      str_acccion = "actualizará";
      accion = "modificar";
      peticion.append('id_tipo_permiso', input.id_tipo_permiso.val());
    }

    if (ValidarEnvio()) {
      confirmacion = await MensajeriaHelper.MostrarConfirmacion(`Se ${str_acccion} un Tipo de Permiso`, mensajeConfirmacion, "question");

      if (confirmacion) {
        peticion.append('peticion', accion);
        peticion.append('nombre', input.nombre.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar");
    }
  } //Fin del Registrar y Modificar
  //Eliminar
  if (operacion == "eliminar") {

    if (ValidadorHelper.ValidarCampo("ID", input.id_tipo_permiso, span.id_tipo_permiso)) {
      confirmacion = await MensajeriaHelper.MostrarConfirmacion("Se eliminará una Tipo de Permiso", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_tipo_permiso', input.id_tipo_permiso.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "El ID de la Tipo de Permiso no es válido.");
    }
  }//Fin del Eliminar

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await AjaxHelper.enviaAjax(peticion, "?page=" + endpoint);
    modal.boton.prop('disabled', false);
    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      MensajeriaHelper.GenerarMensaje(json.icon, 10000, json.mensaje, null);
    }
  }

  if (!confirmacion) {
    modal.boton.prop('disabled', false);
  }

  input = null;
  modal = null;
  return json;
}

export async function EnviarFormulario(etiqueta_boton, modulo = "TipoPermiso") {
  let accion = null;
  let respuesta = null;
  const MANEJADOR = {
    'Nuevo': 'registrar',
    'Actualizar': 'modificar',
    'Borrar': 'eliminar'
  }
  const DEFAULT = null

  accion = MANEJADOR[etiqueta_boton.text()] || DEFAULT

  if (accion != null) {
    respuesta = await EnviarDatos(accion, modulo);
  } else {
    respuesta = { resultado: 0 }
    MensajeriaHelper.GenerarMensaje("danger", 10000, "Error, acción no válida", "")
  }
  return respuesta;
};

export function KeyPressTipoPermiso() {
  let input = EtiquetasFormulario("input");
  $(input.nombre).on("keypress", function (e) { ValidadorHelper.ValidarTecla("NombreObjeto", e); })
}

export function KeyUpTipoPermiso() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  $(input.nombre).on("keyup", function () {
    ValidadorHelper.ValidarCampo("NombreObjeto", $(this), span.nombre);
  })
}

export function ValidarEnvio() {

  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let bool = true;

  if (!ValidadorHelper.ValidarCampo("NombreObjeto", input.nombre, span.nombre)) {
    bool = false;
  }

  return bool;
}

async function VistaPermiso(modulo = "TipoPermiso") {
  const permisos = await PermisoHelper.LlamarPermiso("tipo_permiso");
  let bool = false;
  let btn_eliminar = "";
  let btn_modificar = "";
  let separadorHTML = "";

  if (permisos['tipo_permiso']['modificar'] != undefined && permisos['tipo_permiso']['modificar'] == 1) {
    const itemEditar = $('<li>');
    const linkEditar = $('<a>')
      .addClass('dropdown-item btn-editar text-primary')
      .attr('href', '#')
      .attr('data-accion', 0)
      .attr('data-modulo', modulo)
      .html('<i class="fas fa-edit me-2"></i>Editar');
    itemEditar.append(linkEditar);
    btn_modificar = itemEditar;
    bool = true;
  }

  if (permisos['tipo_permiso']['eliminar'] != undefined && permisos['tipo_permiso']['modificar'] == 1) {
    const itemEliminar = $('<li>');
    const linkEliminar = $('<a>')
      .addClass('dropdown-item btn-eliminar text-danger')
      .attr('href', '#')
      .attr('data-accion', 1)
      .attr('data-modulo', modulo)
      .html('<i class="fas fa-trash me-2" me-2"></i>Eliminar');
    itemEliminar.append(linkEliminar);
    btn_eliminar = itemEliminar;
    bool = true;
  }

  if (btn_modificar != "" && btn_eliminar != "") {
    const separador = $('<li>').html('<hr class="dropdown-divider">');
    separadorHTML = separador;
  }

  const dropdown = $('<div>').addClass('dropdown');
  const boton = $('<button>').addClass('btn btn-sm btn-light border dropdown-toggle')
    .attr('type', 'button')
    .attr('data-bs-toggle', 'dropdown')
    .html('<i class="fas fa-ellipsis-v me-3"></i>Acciones');

  const menu = $('<ul>').addClass('dropdown-menu');


  menu.append(btn_modificar, separadorHTML, btn_eliminar);
  dropdown.append(boton, menu);

  if (!bool) {
    dropdown.empty(); //Destruye la Etiqueta por si no hay botones que renderizar
  }

  return dropdown.prop('outerHTML');
}

function RecargarDataTable() {

  DataTableTipoPermiso(arreglo);
};

export async function DataTableTipoPermiso(arreglo) {
  let botones = '';
  botones = await VistaPermiso("TipoPermiso");

  if ($.fn.DataTable.isDataTable('#tablaTipoPermiso')) {
    $('#tablaTipoPermiso').DataTable().destroy();
  }

  $('#tablaTipoPermiso').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'nombre' },
      {
        data: null,
        render: function () {
          return botones;
        }
      }
    ],
    order: [[1, 'asc']],
    language: { url: idiomaTabla }
  });
  return true;
}

export async function EditarFormTipoPermiso(datos, accion) {
  Limpiar();
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal_tabla = EtiquetasModal("TablaTipoPermiso");
  let modal_formulario = EtiquetasModal("TipoPermiso")

  if (accion == "eliminar") { bool = true; }

  input.id_tipo_permiso.val(datos.id_tipo_permiso).prop("disabled", true);
  input.nombre.val(datos.nombre).prop("disabled", bool);
  modal_tabla.modal.modal("hide");
  modal_formulario.boton.prop('disabled', false);
  EditarModal(accion);
};

export function Limpiar() {
  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal("TipoPermiso");

  modal.boton.prop('disabled', false);
  input.id_tipo_permiso.val("").prop("readOnly", true);
  input.nombre.val("").prop("readOnly", false).prop("disabled", false);
}
