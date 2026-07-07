import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js"
import * as SelectHelper from "../Helpers/SelectHelper.js"
import * as PermisoHelper from "../Helpers/PermisoHelper.js"

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputAsociar = {
    insumo: $('#entrada-nombreInsumo')
  }

  const spanAsociar = {
    insumo: $('#sentrada-nombreInsumo')
  }

  if (etiquetas === "input") {
    referencia = inputAsociar;
  }

  if (etiquetas === "span") {
    referencia = spanAsociar;
  }

  return referencia;
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalAsociar = {
    modal: $('#modalAsociar'),
    titulo: $('#modalTitleTextmodalAsociar'),
    boton: $('#btnmodalAsociarForm')
  }

  if (etiqueta === "Asociar") {
    referencia = modalAsociar;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Asociar");

  if (operacion == 'suministrar') {
    titulo = "Asociar Insumo";
    boton = "Asociar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

//Función para manejar el cambio de estado del formulario
function manejarCambioEstado(formularioValido) {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let modal = EtiquetasModal("Asociar");
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
  let modal = EtiquetasModal("Asociar");

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
  if (operacion == "asociar") {

    console.log(Validarenvio())

    if (Validarenvio()) {
      confirmacion = await confirmarAccion(`¿Guardar Configuración?`, mensajeConfirmacion, "question");

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
  } //Fin del Asociar
  //Eliminar
  if (operacion == "eliminar_asociación") {

    if (ValidadorHelper.ValidarCampo("ID", input.id_insumo, span.id_insumo)) {
      confirmacion = await confirmarAccion("Desea desasociar el proveedor de este insumo?", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_insumo', input.id_insumo.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "El ID del Asociar no es válido.");
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
    'Asociar': 'asociar',
    'Eliminar': 'eliminar'
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

export async function CargarModalTabla(parametros) {
  const endpoint = "?page=Insumo"
  let modal = EtiquetasModal("Asociar");
  let input = EtiquetasFormulario("input");
  let respuesta = { resultado: 0 };
  let datos = new FormData();
  let nombre_insumo = parametros.nombre_insumo;

  datos.append("modulo", "EntradaInsumo");
  datos.append("peticion", "filtrar")
  datos.append("id_insumo", parametros.id_insumo);
  input.insumo.val(null);
  input.insumo.attr("data-insumo", null);

  respuesta = await AjaxHelper.enviaAjax(datos, endpoint);

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    DataTable(respuesta.datos);
    input.insumo.val(nombre_insumo);
    input.insumo.attr("data-insumo", parametros.id_insumo);
    modal.modal.modal("show");
  }
}

export function CapaValidar() {
  KeyPressAsociar();
  KeyUpAsociar();
}

function KeyPressAsociar() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

}

function KeyUpAsociar() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  $(input.proveedor).on("change", function () {

    if ($(this).val() == "default") {
      SelectHelper.FeedbackSelect($(this), span.proveedor, "Debe seleccionar a un Proveedor", 0)
    } else {
      SelectHelper.FeedbackSelect($(this), span.proveedor, "", 1)
    }

  })

}

function RenderBotonEliminar(id) {

  const boton = $('<button>').addClass('btn btn-danger btn-eliminar-proveedor').html('<i class="fas fa-trash"></i>');
  boton.attr("data-proveedor", id);
  const div = $('<div>').addClass('d-flex align-items-center ga-2');
  div.append(boton);

  return div.prop('outerHTML');
}

function asignarIdSelect() {
  $('.select-proveedor').length
}


async function RenderizarSelect(id_insumo) {
  let json = null;
  let datos = new FormData();
  let div = $('<div>').addClass('d-flex align-items-center ga-2');
  let input = $('<select>').addClass('form-select select-proveedor');
  let span = $('<div>').addClass('form-label span-select_proveedor');

  const endpoint = "?page=Proveedor";
  const mensaje = "Seleccione un Proveedor"
  let arreglo = [];
  datos.append("id_insumo", id_insumo);
  datos.append("peticion", "obtener_proveedor");

  try {
    json = await AjaxHelper.enviaAjax(datos, endpoint);


    console.log(json.datos);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      const array = json.datos.map(item => ({
        nombre: item.nombre,
        valor: item.documento_legal
      }));
      SelectHelper.RenderizarSelect(input, array, mensaje);
    };

    div.append(input, span);

    return div.prop('outerHTML');

  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}


export async function DataTable(arreglo) {
  if ($.fn.DataTable.isDataTable('#tablaAsociar')) {
    $('#tablaAsociar').DataTable().destroy();
  }

  $('#tablaAsociar').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'proveedor',
        render: function (data, type, row) {
          if (data) {
            return row.proveedor;
          }
          return null;
        }
        
      },
      
      {
        data: 'boton',
        render: function (data, type, row) {
          if (data) {
            return data;
          }
          return RenderBotonEliminar(row.id_entrada);
        }
      }
    ],
    order: [[1, 'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
  });
}

export async function AgregarFilaInput() {
  let inputTexto = EtiquetasFormulario("input");
  let id_insumo = inputTexto.insumo.attr("data-insumo")
  let tabla = $('#tablaAsociar').DataTable();
  let selectProveedor = await RenderizarSelect(id_insumo);
  let boton = await RenderBotonEliminar(null);

  tabla.row.add({
    proveedor: selectProveedor,
    boton: boton
  }).draw(false);

  capaValidar();
}

export function LimpiarFormulario() {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal('Asociar');
  let fila_stock_inicial = $("#fila-stock-inicial");

  input.insumo.val("").prop("readOnly", false);

  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', false);
  input = null;
  span = null;
  modal = null;
}