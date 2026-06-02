import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js"

//SUBMODULO DE CATEGORIA DE INGREDIENTES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputCategoria = {
    nombre: $('#categoria-nombre'),
    id_categoria: $('#id_categoria')
  }

  const spanCategoria = {
    nombre: $('#scategoria-nombre'),
    id_categoria: $('#sid_categoria')
  }

  if (etiquetas === "input") {
    referencia = inputCategoria;
  }

  if (etiquetas === "span") {
    referencia = spanCategoria
  }

  return referencia
}

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalCategoriaTabla = {
    modal: $('#modalCategoria'),
    titulo: $('#modalTitleTextCategoria'),
    boton: $('#btn-CategoriaPCancel')
  }

  const modalCategoria = {
    modal: $('#modal-formcategoria'),
    titulo: $('#modalTitleText-Form-Categoria'),
    boton: $('#btn-CategoriaForm')
  }

  if (etiqueta === "TablaCategoria") {
    referencia = modalCategoriaTabla;
  }

  if (etiqueta === "Categoria") {
    referencia = modalCategoria;
  }

  return referencia;
}

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Categoria");

  if (operacion == 'registrar') {
    titulo = "Nueva Categoría";
    boton = "Nuevo";
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Categoría";
    boton = "Actualizar";
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Categoría";
    boton = "Borrar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

//-------LÓGICA DE CAMBIO ENTRE MODALES------
export function FormNuevaCategoria() {
  let modal_tabla = EtiquetasModal("TablaCategoria");
  let modal_form = EtiquetasModal("Categoria");
  modal_tabla.modal.modal("hide");
  Limpiar();
  EditarModal("registrar");

  modal_tabla = null;
  modal_form = null;
}

export function CancelarFormulario() {
  let modal_form = EtiquetasModal("Categoria");
  let modal_tabla = EtiquetasModal("TablaCategoria");
  modal_form.modal.modal("hide");
  modal_tabla.modal.modal("show");

  modal_form = null;
  modal_tabla = null;
}

export function CerrarFormulario() {
  let modal_form = EtiquetasModal("Categoria");

  modal_form.modal.modal("hide");
  modal_form = null;
}

export function MostrarModalTabla() {
  let modal_tabla = EtiquetasModal("TablaCategoria");
  let modal_form = EtiquetasModal("Categoria");
  modal_form.modal.modal("hide");
  modal_tabla.modal.modal("show");

  modal_tabla = null;
}

//-------FUNNCIONES-------

async function EnviarDatos(operacion) {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal("Categoria");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();
  let json = null;
  //Registrar y Modificar
  if (operacion == "registrar" || operacion == "modificar") {

    if (operacion == "registrar") {
      str_acccion = "registrará";
      accion = "registrar"
    }

    if (operacion == "modificar") {
      str_acccion = "actualizará";
      accion = "modificar";
      peticion.append('id_categoria', input.id_categoria.val());
    }

    if (ValidarEnvio()) {
      confirmacion = await MensajeriaHelper.MostrarConfirmacion(`Se ${str_acccion} una Categoria`, mensajeConfirmacion, "question");

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

    if (ValidadorHelper.ValidarCampo("ID", input.id_categoria, span.id_categoria)) {
      confirmacion = await MensajeriaHelper.MostrarConfirmacion("Se eliminará una Categoría", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_categoria', input.id_categoria.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "El ID de la Categoría no es válido.");
    }
  }//Fin del Eliminar

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await AjaxHelper.enviaAjax(peticion, "?page=categoria-insumo");
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

export async function EnviarFormulario(etiqueta_boton) {
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
    respuesta = await EnviarDatos(accion)
  } else {
    respuesta = { resultado: 0 }
    MensajeriaHelper.GenerarMensaje("danger", 10000, "Error, acción no válida", "")
  }
  return respuesta;
};

export function KeyPressCategoria() {
  let input = EtiquetasFormulario("input");
  $(input.nombre).on("keypress", function (e) { ValidadorHelper.ValidarTecla("NombreObjeto", e); })
}

export function KeyUpCategoria() {
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

async function VistaPermiso(modulo = "Categoria") {

  const dropdown = $('<div>').addClass('dropdown');
  const boton = $('<button>').addClass('btn btn-sm btn-light border dropdown-toggle')
    .attr('type', 'button')
    .attr('data-bs-toggle', 'dropdown')
    .html('<i class="fas fa-ellipsis-v me-3"></i>Acciones');

  const menu = $('<ul>').addClass('dropdown-menu');
  const separador = $('<li>').html('<hr class="dropdown-divider">');

  const itemEditar = $('<li>');
  const linkEditar = $('<a>')
    .addClass('dropdown-item btn-editar text-primary')
    .attr('href', '#')
    .attr('data-accion', 0)
    .attr('data-modulo', modulo)
    .html('<i class="fas fa-edit me-2"></i>Editar');
  itemEditar.append(linkEditar);

  const itemEliminar = $('<li>');
  const linkEliminar = $('<a>')
    .addClass('dropdown-item btn-eliminar text-danger')
    .attr('href', '#')
    .attr('data-accion', 1)
    .attr('data-modulo', modulo)
    .html('<i class="fas fa-trash me-2" me-2"></i>Eliminar');
  itemEliminar.append(linkEliminar);

  menu.append(itemEditar, separador, itemEliminar);
  dropdown.append(boton, menu);

  console.log(dropdown)
  return dropdown.prop('outerHTML');
}

function RecargarDataTable() {

  DataTableCategoria(arreglo);
};

export async function DataTableCategoria(arreglo) {
  let botones = '';
  botones = await VistaPermiso("Categoria");

  if ($.fn.DataTable.isDataTable('#tablaCategoria')) {
    $('#tablaCategoria').DataTable().destroy();
  }

  $('#tablaCategoria').DataTable({
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

export async function EditarFormCategoria(datos, accion) {
  Limpiar();
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal_tabla = EtiquetasModal("TablaCategoria");
  let modal_formulario = EtiquetasModal("Categoria")

  if (accion == "eliminar") { bool = true; }

  input.id_categoria.val(datos.id_categoria).prop("disabled", true);
  input.nombre.val(datos.nombre).prop("disabled", bool);
  modal_tabla.modal.modal("hide");
  modal_formulario.boton.prop('disabled', false);
  EditarModal(accion);
};

export function Limpiar() {
  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal("Categoria");

  modal.boton.prop('disabled', false);
  input.id_categoria.val("").prop("readOnly", true);
  input.nombre.val("").prop("readOnly", false)
}
