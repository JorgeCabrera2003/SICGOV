import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js";
import * as SelectHelper from "../Helpers/SelectHelper.js";

//MODULO DE PROVEEDORES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputRol = {
    id: $('#id_rol'),
    nombre: $('#nombre')
  }

  const spanRol = {
    id: $('#sid_rol'),
    nombre: $('#snombre')
  }

  if (etiquetas === "input") {
    referencia = inputRol
  }

  if (etiquetas === "span") {
    referencia = spanRol
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalRol = {
    modal: $('#modalRol'),
    titulo: $('#modalTitleTextRol'),
    boton: $('#btnRolForm')
  }

  if (etiqueta === "Rol") {
    referencia = modalRol;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Rol");

  if (operacion == 'registrar') {
    titulo = "Nuevo Rol";
    boton = "Nuevo";
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Rol";
    boton = "Actualizar";
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Rol";
    boton = "Borrar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

export async function EnviarDatos(operacion) {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal("Rol");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();
  let permisos = [];
  let json;

  //Registrar y Modificar
  if (operacion == "comprobar") {
    let bool_form = true
    if (operacion == "comprobar") {
      str_acccion = "comprobará";
      accion = "comprobar"
    }
    mensajeConfirmacion = "¿Desea realizar comprobación?";
    confirmacion = await MensajeriaHelper.MostrarConfirmacion(`Se ${str_acccion} los Módulos del Sistema`, mensajeConfirmacion, "question");

    if (confirmacion) {
      peticion.append('peticion', accion);
      btn_formulario = true;
    }
  } //Fin del Comprobar
  //Eliminar
  if (operacion == "reestablecer") {

    peticion.append('peticion', 'reestablecer');
    btn_formulario = true;

  }//Fin del Reestablecer

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
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
    'Comprobar': 'comprobar',
    'Reestablecer': 'reestablecer'
  }
  const DEFAULT = null

  accion = MANEJADOR[btn_string] || DEFAULT

  if (accion != null) {
    return await EnviarDatos(accion)
  } else {
    console.log("Error, acción no válida")
  }
};

export async function ReestablecerModulos(resultado) {

  accion = MANEJADOR[btn_string] || DEFAULT

  if (accion != null) {
    return await EnviarFormulario('Reestablecer')
  } else {
    console.log("Error, acción no válida")
  }
  
};

export async function DataTablePrincipal(arreglo) {

  if ($.fn.DataTable.isDataTable('#tablaModuloSistema')) {
    $('#tablaModuloSistema').DataTable().destroy();
  }

  $('#tablaModuloSistema').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'nombre' }
    ],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
  });
}
