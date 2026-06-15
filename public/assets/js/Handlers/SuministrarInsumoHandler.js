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

  const inputSuministrar = {
    insumo: $('#suministrar-nombre'),
    proveedor: $('#suministrar-entrada'),
    stock: $('#suministrar-stock'),
    unidad_medida: $('#suministrar-unidad')
  }

  const spanSuministrar = {
    insumo: $('#ssuministrar-nombre'),
    proveedor: $('#ssuministrar-entrada'),
    stock: $('#ssuministrar-stock'),
    unidad_medida: $('#ssuministrar-unidad')
  }

  if (etiquetas === "input") {
    referencia = inputSuministrar
  }

  if (etiquetas === "span") {
    referencia = spanSuministrar
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalSuministrar = {
    modal: $('#modalSuministrarInsumo'),
    titulo: $('#modalTitleTextSuministrarInsumo'),
    boton: $('#btnSuministrarInsumoForm')
  }

  if (etiqueta === "Suministrar") {
    referencia = modalSuministrar;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Suministrar");

  if (operacion == 'suministrar') {
    titulo = "Suministrar Insumo";
    boton = "Suministrar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

//Función para manejar el cambio de estado del formulario
function manejarCambioEstado(formularioValido) {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let modal = EtiquetasModal("Suministrar");
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
  let modal = EtiquetasModal("Suministrar");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();
  let json = { resultado: 0 };

  peticion.append("modulo", "Suministrar");

  //Registrar y Modificar
  if (operacion == "suministrar") {
    let bool_peticion = true;
    let stock_maximo = input.stock_maximo.val();

    if (input.stock_maximo.val() == "" || input.stock_maximo.val() == null) {
      stock_maximo = 0;
    }

    if (Validarenvio() && bool_peticion) {
      confirmacion = await confirmarAccion(`Se ${str_acccion} un Suministrar`, mensajeConfirmacion, "question");

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
      confirmacion = await confirmarAccion("Se eliminará un Suministrar", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_insumo', input.id_insumo.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "El ID del Suministrar no es válido.");
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
    'Suministrar': 'suministrar',
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

export function CapaValidar() {
  KeyPressSuministrar();
  KeyUpSuministrar();
  CrearSelectProveedores();
  CrearSelectCategoria();
  CrearSelectUnidadMedida();
}

export async function CrearSelectProveedores(id_insumo) {
  let json = null;
  let datos = new FormData();
  let input = EtiquetasFormulario('input');
  const endpoint = "?page=Insumo";
  const modulo = "EntradaInsumo";
  const mensaje = "Seleccione un Proveedor"
  let arreglo = [];
  datos.append("insumo", id_insumo);
  datos.append("modulo", modulo);
  datos.append("peticion", "filtrar");

  try {
    json = await AjaxHelper.enviaAjax(datos, endpoint);


    console.log(json.datos);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      const arrayCategoria = json.datos.map(item => ({
        nombre: item.proveedor,
        valor: item.id_entrada
      }));
      SelectHelper.RenderizarSelect(input.proveedor, arrayCategoria, mensaje);
    };

  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}

export async function CrearSelectUnidadMedida(id) {
  let json = null;
  let datos = new FormData();
  let input = EtiquetasFormulario('input');
  const endpoint = "?page=Insumo";
  const modulo = "UnidadMedida";
  const mensaje = "Seleccione una Unidad de Medida"
  let arreglo = [];
  datos.append("modulo", modulo);
  datos.append("id_unidad", id);
  datos.append("peticion", "filtrar");

  try {
    json = await AjaxHelper.enviaAjax(datos, endpoint);


    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      const arrayUnidad = json.datos.map(item => ({
        nombre: item.nombre + " - " + item.abreviatura,
        valor: item.id_unidad
      }));
      SelectHelper.RenderizarSelect(input.unidad_medida, arrayUnidad, mensaje);
    };

  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}


function KeyPressSuministrar() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  input.stock.on("keypress", function (e) { ValidadorHelper.ValidarTecla("NumeroDecimal", e); });
}

function KeyUpSuministrar() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  $(input.stock).on("blur", function () {
    ValidadorHelper.FormatoNumeroDecimal($(this));
    ValidadorHelper.ValidarCampo("NumeroDecimal", $(this), span.costo_unitario);
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

function Validarenvio() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let bool = true;

  if (input.proveedor.val() == "default") {
    SelectHelper.FeedbackSelect($(this), span.proveedor, "Debe selccionar un Tipo de Documento", 0);
    bool = false;
  }

    if (input.unidad_medida.val() == "default") {
    SelectHelper.FeedbackSelect(input.unidad_medida, span.unidad_medida, "Debe Seleccionar una Unidad de Medida", 0);
    bool = false;
  }

  if (input.stock == '' || input.stock == null || input.stock == 0) {
    MensajeriaHelper.FeedbackToltipInput(input.stock, span.stock, "El stock a suministrar no puede estar en 0", 0)
    bool = false;
  }

  return bool
}

export function LimpiarFormulario() {
  SistemaValidacion.limpiarValidacion(EtiquetasFormulario('input'));

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal('Suministrar');
  let fila_stock_inicial = $("#fila-stock-inicial");

  input.insumo.val("").prop("readOnly", true);
  input.proveedor.val("default").prop("disabled", false);
  input.stock.val("").prop("disabled", false);
  input.insumo.prop('dataset').insumo = ""
  input.unidad_medida.val("default").prop("disabled", false);

  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', false);
  input = null;
  span = null;
  modal = null;
}

export async function EditarFormSuministrar(datos) {
  LimpiarFormulario();
  console.log(datos);
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal = EtiquetasModal("Suministrar")

  input.insumo.val(datos.nombre_insumo).prop("disabled", true);
  input.insumo.prop('dataset').insumo = datos.id_insumo;
  input.stock.val("").prop("disabled", false);


  await CrearSelectProveedores(datos.id_insumo);
  await CrearSelectUnidadMedida(datos.id_unidad_medida);

  EditarModal("suministrar");
};
