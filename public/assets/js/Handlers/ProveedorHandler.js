import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js";
import * as SelectHelper from "../Helpers/SelectHelper.js";

//MODULO DE PROVEEDORES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputProveedor = {
    tipo_documento: $('#tipo_doc'),
    documento_legal: $('#documento_legal'),
    nombre: $('#nombre'),
    prefijo_telefono: $('#prefijo_telefono'),
    telefono: $('#telefono'),
    correo: $('#correo'),
    direccion: $('#direccion')
  }

  const spanproveedor = {
    tipo_documento: $('#stipo_doc'),
    documento_legal: $('#sdocumento_legal'),
    nombre: $('#snombre'),
    telefono: $('#stelefono'),
    correo: $('#scorreo'),
    direccion: $('#sdireccion')
  }

  if (etiquetas === "input") {
    referencia = inputProveedor
  }

  if (etiquetas === "span") {
    referencia = spanproveedor
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalProveedor = {
    modal: $('#modalProveedor'),
    titulo: $('#modalTitleTextProveedor'),
    boton: $('#btnProveedorForm')
  }

  if (etiqueta === "Proveedor") {
    referencia = modalProveedor;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Proveedor");

  if (operacion == 'registrar') {
    titulo = "Nuevo Proveedor";
    boton = "Nuevo";
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Proveedor";
    boton = "Actualizar";
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Proveedor";
    boton = "Borrar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

export async function EnviarDatos(operacion) {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal("Proveedor");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();

  //Registrar y Modificar
  if (operacion == "registrar" || operacion == "modificar") {

    if (operacion == "registrar") {
      str_acccion = "registrará";
      accion = "registrar"
    }

    if (operacion == "modificar") {
      str_acccion = "actualizará";
      accion = "modificar";
    }

    if (ValidarEnvio()) {
      confirmacion = await confirmarAccion(`Se ${str_acccion} un Proveedor`, mensajeConfirmacion, "question");

      if (confirmacion) {
        peticion.append('peticion', accion);
        peticion.append('nombre', input.nombre.val());
        peticion.append('telefono', input.prefijo_telefono.val() + "-" + input.telefono.val());
        peticion.append('correo', input.correo.val());
        peticion.append('direccion', input.direccion.val());
        peticion.append('documento_legal', input.tipo_documento.val() + "" + input.documento_legal.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.")
    }
  } //Fin del Registrar y Modificar
  //Eliminar
  if (operacion == "eliminar") {

    if (validarKeyUp(/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/, input.id_ingrediente, span.id_ingrediente, '')) {
      confirmacion = await confirmarAccion("Se eliminará un Proveedor", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('documento_legal', input.tipo_documento.val() + "" + input.documento_legal.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "Docmento Legal del Proveedor no es válido.");
    }
  }//Fin del Eliminar

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    console.log("Formulario enviado");
    json = await AjaxHelper.enviaAjax(peticion, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      modal.modal.modal("hide");
      MensajeriaHelper.GenerarMensaje(json.icon, 10000, json.mensaje, null);
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

//Manejo de envio de datos desde el modal
export async function EnviarFormulario(btn_string) {
  let accion = null;
  const MANEJADOR = {
    'Nuevo': 'registrar',
    'Actualizar': 'modificar',
    'Borrar': 'eliminar'
  }
  const DEFAULT = null

  accion = MANEJADOR[btn_string] || DEFAULT

  if (accion != null) {
    return await EnviarDatos(accion)
  } else {
    console.log("Error, acción no válida")
  }
};

//CAPA DE VALIDACIÓN

export function CapaValidar() {
  KeyUpProveedor();
  KeyPressProveedor();
}

function KeyPressProveedor() {

  let input = EtiquetasFormulario("input");

  $(input.documento_legal).on("keypress", function (e) { ValidadorHelper.ValidarTecla("Cedula", e); });
  $(input.nombre).on("keypress", function (e) { ValidadorHelper.ValidarTecla("Titulo", e); });
  $(input.telefono).on("keypress", function (e) { ValidadorHelper.ValidarTecla("Telefono", e); });
  $(input.correo).on("keypress", function (e) { ValidadorHelper.ValidarTecla("Correo", e); });
  $(input.direccion).on("keypress", function (e) { ValidadorHelper.ValidarTecla("Direccion", e); });
}


function KeyUpProveedor() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  $(input.documento_legal).on("keyup", function () {
    ValidadorHelper.ValidarCampo("DocumentoLegal", $(this), span.documento_legal);
  })

  $(input.nombre).on("keyup", function () {
    ValidadorHelper.ValidarCampo("Titulo", $(this), span.nombre);
  })

  $(input.telefono).on("keyup", function () {
    ValidadorHelper.ValidarCampo("Telefono-Segmento", $(this), span.telefono);
  })

  $(input.correo).on("keyup", function () {
    ValidadorHelper.ValidarCampo("Correo", $(this), span.correo);
  })

  $(input.direccion).on("keyup", function () {
    ValidadorHelper.ValidarCampo("Direccion", $(this), span.direccion);
  })

  $(input.prefijo_telefono).on("change", function () {

    if ($(this).val() == "default") {
      SelectHelper.FeedbackSelect($(this), span.telefono, "Debe selccionar un código", 0)
    } else {
      SelectHelper.FeedbackSelect($(this), span.telefono, "", 1)
    }

  })

  $(input.tipo_documento).on("change", function () {
    if ($(this).val() == "default") {
      SelectHelper.FeedbackSelect($(this), span.telefono, "Debe selccionar un Tipo de Documento", 0)
    } else {
      SelectHelper.FeedbackSelect($(this), span.telefono, "", 1)
    }
  })

}

function ValidarEnvio() {

  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let bool = true;

  if (input.tipo_documento.val() == "default") {
    SelectHelper.FeedbackSelect($(this), span.tipo_documento, "Debe selccionar un Tipo de Documento", 0);
    bool = false;
  }

  if (!ValidadorHelper.ValidarCampo("DocumentoLegal", input.documento_legal, span.documento_legal)) {
    bool = false;
  }

  if (!ValidadorHelper.ValidarCampo("Titulo", input.nombre, span.nombre)) {
    bool = false;
  }

  if (!ValidadorHelper.ValidarCampo("Direccion", input.direccion, span.direccion)) {
    bool = false;
  };

  if (input.prefijo_telefono.val() == "default") {
    SelectHelper.FeedbackSelect(input.prefijo_telefono, span.telefono, "Debe selccionar un código", 0);
    bool = false;
  }

  if (!ValidadorHelper.ValidarCodigoTelefono(input.prefijo_telefono, span.telefono)) {
    bool = false;
  }

  if (!ValidadorHelper.ValidarCampo("Telefono-Segmento", input.telefono, span.telefono)) {
    bool = false;
  }

  if (!ValidadorHelper.ValidarCampo("Correo", input.correo, span.correo)) {
    bool = false;
  };

  return bool;
}

async function RenderPermisoBotones(modulo = "Proveedor") {

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
    .attr('data-accion', 'modificar')
    .attr('data-modulo', modulo)
    .html('<i class="fas fa-edit me-2"></i>Editar');
  itemEditar.append(linkEditar);

  const itemEliminar = $('<li>');
  const linkEliminar = $('<a>')
    .addClass('dropdown-item btn-eliminar text-danger')
    .attr('href', '#')
    .attr('data-accion', 'eliminar')
    .attr('data-modulo', modulo)
    .html('<i class="fas fa-trash me-2" me-2"></i>Eliminar');
  itemEliminar.append(linkEliminar);

  menu.append(itemEditar, separador, itemEliminar);
  dropdown.append(boton, menu);

  console.log(dropdown)
  return dropdown.prop('outerHTML');
}


export async function DataTablePrincipal(arreglo) {
  let botones = '';
  botones = await RenderPermisoBotones();

  if ($.fn.DataTable.isDataTable('#tablaProveedor')) {
    $('#tablaProveedor').DataTable().destroy();
  }

  $('#tablaProveedor').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'documento_legal' },
      { data: 'nombre' },
      { data: 'telefono' },
      { data: 'correo' },
      { data: 'direccion' },
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
}

export function LimpiarFormulario() {
  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal('Proveedor');

  input.tipo_documento.val("default").prop("disabled", false);
  input.documento_legal.val("").prop("disabled", false);
  input.nombre.val("").prop("disabled", false);
  input.prefijo_telefono.val("default").prop("disabled", false);
  input.telefono.val("").prop("disabled", false);
  input.correo.val("").prop("disabled", false);
  input.direccion.val("").prop("disabled", false);

  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', false);
  input = null;
  span = null;
  modal = null;
}

export async function EditarFormProveedor(datos, accion) {
  LimpiarFormulario();
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal = EtiquetasModal("Proveedor");
  let tipo_documentoStr = datos.documento_legal.charAt(0)
  let digitos_documentoStr = datos.documento_legal.substring(2)
  let numeroStr = datos.telefono.toString();
  let codigo_telefono = numeroStr.substring(0, 4);
  let numero_telefono = numeroStr.substring(5);
  if (accion == "eliminar") { bool = true; }

  
  SelectHelper.BuscarValor(input.tipo_documento, tipo_documentoStr, "value");
  input.tipo_documento.prop("disabled", bool);
  input.documento_legal.val(digitos_documentoStr).prop("disabled", bool);
  input.nombre.val(datos.nombre).prop("nombre", bool);
  input.prefijo_telefono.prop("disabled", bool);
  SelectHelper.BuscarValor(input.prefijo_telefono, codigo_telefono, "value");
  input.telefono.val(numero_telefono).prop("disabled", bool);
  input.correo.val(datos.correo).prop("disabled", bool);
  input.direccion.val(datos.direccion).prop("disabled", bool);

  modal.boton.prop('disabled', false);
  EditarModal(accion);
};
