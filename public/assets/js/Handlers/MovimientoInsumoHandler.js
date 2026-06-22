import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js"
import * as SelectHelper from "../Helpers/SelectHelper.js"
import * as PermisoHelper from "../Helpers/PermisoHelper.js"

//MODULO DE INGREDIENTES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputMovimiento = {
    insumo: $('#m-nombreInsumo'),
    stock: $('#m-stockInsumo'),
    unidad_medida: $('#m-unidadmedida')
  }

  const spanMovimiento = {
    insumo: $('#sm-nombreInsumo'),
    stock: $('#sm-stockInsumo'),
    unidad_medida: $('#sm-unidadmedida')
  }

  if (etiquetas === "input") {
    referencia = inputMovimiento
  }

  if (etiquetas === "span") {
    referencia = spanMovimiento
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalMovimiento = {
    modal: $('#modalMovimientoInsumo'),
    titulo: $('#modalTitleTextMovimientoInsumo'),
    boton: $('#btnMovimientoInsumoForm')
  }

  if (etiqueta === "Movimiento") {
    referencia = modalMovimiento;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Movimiento");

  if (operacion == 'suministrar') {
    titulo = "Movimiento Insumo";
    boton = "Movimiento";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

//Función para manejar el cambio de estado del formulario
function manejarCambioEstado(formularioValido) {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let modal = EtiquetasModal("Movimiento");
  const accion = modal.boton.text();

  if (accion === "Eliminar") {
    // Para eliminar solo validamos el ID
    const idValido = validarKeyUp(/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/, input.id_insumo.val(), span.id_insumo, '');
    modal.boton.prop('disabled', !idValido);
  } else {
    // Para registrar y modificar validamos todos los campos
    modal.boton.prop('disabled', !formularioValido);
  }
  modal = null;
  input = null;
  span = null;
}

export async function EnviarDatos(operacion) {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal("Movimiento");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();
  let json = { resultado: 0 };

  peticion.append("modulo", "EntradaInsumo");

  //Registrar y Modificar
  if (operacion == "suministrar") {

    console.log(Validarenvio())

    if (Validarenvio()) {
      confirmacion = await confirmarAccion(`Se va a suministrar un insumo`, mensajeConfirmacion, "question");

      if (confirmacion) {
        peticion.append('peticion', "suministrar");
        peticion.append('id_entrada', input.proveedor.val());
        peticion.append('stock', input.stock.val());
        peticion.append('id_unidad', input.unidad_medida.val());
        peticion.append('id_insumo', input.insumo.prop('dataset').insumo);
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.")
    }
  } //Fin del Registrar y Modificar
  //Eliminar
  if (operacion == "suministrar_lote") {

    if (ValidadorHelper.ValidarCampo("ID", input.id_insumo, span.id_insumo)) {
      confirmacion = await confirmarAccion("Se eliminará un Movimiento", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_insumo', input.id_insumo.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "El ID del Movimiento no es válido.");
    }
  }//Fin del Eliminar

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
  let respuesta = null;
  const MANEJADOR = {
    'Movimiento': 'suministrar',
    'Actualizar': 'modificar',
    'Borrar': 'eliminar'
  }
  const DEFAULT = null

  accion = MANEJADOR[btn_string] || DEFAULT

  if (accion != null) {
    respuesta = await EnviarDatos(accion)
  } else {
    respuesta = { resultado: 0 }
    MensajeriaHelper.GenerarMensaje("danger", 10000, "Error, acción no válida", "")
  }
  return respuesta;
};

//CAPA DE VALIDACIÓN

export async  function CargarModalTabla(parametros){
  const endpoint = "?page=Insumo"
  let modal = EtiquetasModal("Movimiento");
  let input = EtiquetasFormulario("input");
  let respuesta = {resultado: 0};
  let datos = new FormData();

  datos.append("modulo", "Movimiento");
  datos.append("peticion", "entrada")
  datos.append("id_insumo", parametros.id_insumo);

  respuesta = await AjaxHelper.enviaAjax(datos, endpoint);

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    DataTable(respuesta.datos);
    input.insumo.val(respuesta.datos_insumo.nombre_insumo);
    input.stock.val(respuesta.datos_insumo.stock_actual);
    input.unidad_medida.val(respuesta.datos_insumo.abreviatura);
    modal.modal.modal("show");
  }
}

export function CapaValidar() {
  KeyPressMovimiento();
  KeyUpMovimiento();
}

function KeyPressMovimiento() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  input.stock.on("keypress", function (e) { ValidadorHelper.ValidarTecla("NumeroDecimal", e); });
}

function KeyUpMovimiento() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  $(input.stock).on("blur", function () {
    ValidadorHelper.FormatoNumeroDecimal($(this));
    ValidadorHelper.ValidarCampo("NumeroDecimal", $(this), span.stock);
  })

  $(input.unidad_medida).on("change", function () {

    if ($(this).val() == "default") {
      SelectHelper.FeedbackSelect($(this), span.unidad_medida, "Debe seleccionar a una Unidad de Medida", 0)
    } else {
      SelectHelper.FeedbackSelect($(this), span.unidad_medida, "", 1)
    }
  })


  $(input.proveedor).on("change", function () {

    if ($(this).val() == "default") {
      SelectHelper.FeedbackSelect($(this), span.proveedor, "Debe seleccionar a un Proveedor", 0)
    } else {
      SelectHelper.FeedbackSelect($(this), span.proveedor, "", 1)
    }

  })

}

export async function DataTable(arreglo) {
  if ($.fn.DataTable.isDataTable('#tablaEntrada')) {
    $('#tablaEntrada').DataTable().destroy();
  }

  $('#tablaEntrada').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'fecha' },
      { data: 'proveedor' },
      { data: 'descripcion' },
    ],
    order: [[1, 'asc']],
    language: { url: idiomaTabla }
  });
}

export function LimpiarFormulario() {
  SistemaValidacion.limpiarValidacion(EtiquetasFormulario('input'));

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal('Movimiento');
  let fila_stock_inicial = $("#fila-stock-inicial");

  input.insumo.val("").prop("readOnly", false);
  input.proveedor.val("default").prop("disabled", false);
  input.stock.val("").prop("disabled", false);
  input.insumo.prop('dataset').insumo = "";
  input.unidad_medida.val("default").prop("disabled", false);

  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', false);
  input = null;
  span = null;
  modal = null;
}

export async function EditarFormMovimiento(datos) {
  LimpiarFormulario();
  console.log(datos);
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal = EtiquetasModal("Movimiento")

  input.insumo.val(datos.nombre_insumo).prop("disabled", true);
  input.insumo.prop('dataset').insumo = datos.id_insumo;
  input.stock.val("").prop("disabled", false);


  await CrearSelectProveedores(datos.id_insumo);
  await CrearSelectUnidadMedida(datos.id_unidad_medida);

  EditarModal("suministrar");
};
