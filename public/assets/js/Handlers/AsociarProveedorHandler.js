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

export async function EnviarDatos(operacion, datosTabla = null) {

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

    if (validarDuplicados()) {
      confirmacion = await MensajeriaHelper.MostrarConfirmacion(`¿Guardar Configuración?`, mensajeConfirmacion, "question");

      if (confirmacion) {
        let datos = CrearArregloProveedor();
        peticion.append('peticion', "asociar_proveedor");
        peticion.append('proveedores', JSON.stringify(datos));
        peticion.append('id_insumo', input.insumo.attr("data-insumo"));
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.")
    }
  } //Fin del Asociar
  //Eliminar
  if (operacion == "eliminar_asociación") {

    confirmacion = await MensajeriaHelper.MostrarConfirmacion("¿Desea eliminar la asociación del proveedor con este insumo?", mensajeConfirmacion, "warning");

    if (confirmacion) {
      peticion.append('peticion', 'eliminar');
      peticion.append('id_entrada', datosTabla.id_entrada);
      btn_formulario = true;
    }

  }//Fin del Eliminar

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await AjaxHelper.enviaAjax(peticion, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      if (operacion == "asociar" || operacion == "eliminar_asociación") {
        MensajeriaHelper.GenerarMensaje(json.icon, 10000, json.mensaje, null);
        RecargarModalTabla(input.insumo.attr("data-insumo"));
      } else {
        modal.modal.modal("hide");
      }
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

export async function RecargarModalTabla(id_insumo) {
  const endpoint = "?page=Insumo"
  let input = EtiquetasFormulario("input");
  let respuesta = { resultado: 0 };
  let datos = new FormData();

  datos.append("modulo", "EntradaInsumo");
  datos.append("peticion", "filtrar")
  datos.append("id_insumo", id_insumo);
  input.insumo.attr("data-insumo", null);

  respuesta = await AjaxHelper.enviaAjax(datos, endpoint);

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    DataTable(respuesta.datos);
    input.insumo.attr("data-insumo", id_insumo);

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

export function validarDuplicados() {
  let valores = {};
  let todosValidos = true;

  // 1. PRIMERA PASADA: Recolectar todos los valores
  $('#tablaAsociar .select-proveedor').each(function () {
    let $select = $(this);
    let valor = $select.val();

    // Solo considerar valores válidos (no default)
    if (valor && valor !== 'default' && valor !== '') {
      if (!valores[valor]) {
        valores[valor] = [];
      }
      valores[valor].push($select);
    }
  });

  // 2. SEGUNDA PASADA: Marcar duplicados y limpiar validaciones anteriores
  $('#tablaAsociar .select-proveedor').each(function () {
    let $select = $(this);
    let $span = $select.closest('div').find('.span-select_proveedor');
    let valor = $select.val();

    // Limpiar clases y mensajes anteriores
    $select.removeClass('is-valid is-invalid');

    // Si no tiene valor o es default
    if (!valor || valor === 'default' || valor === '') {
      $select.addClass('is-invalid');
      $span.text('Seleccione un proveedor');
      $span.addClass('invalid-tooltip');
      todosValidos = false;
      return;
    }

    // Verificar si es duplicado
    if (valores[valor] && valores[valor].length > 1) {
      // ESTE select es duplicado
      $select.addClass('is-invalid');
      $span.text('Proveedor duplicado');
      $span.addClass('invalid-feedback invalid-tooltip');
      todosValidos = false;
    } else {
      // Es válido
      $select.addClass('is-valid');
      $span.text('');
      $span.removeClass('invalid-feedback invalid-tooltip');
    }
  });

  // 3. Actualizar estado del botón guardar
  if (todosValidos) {
    $('#btn-guardar').prop('disabled', false);
    $('#mensaje-error').hide();
  } else {
    $('#btn-guardar').prop('disabled', true);
    $('#mensaje-error').show();
    $('#mensaje-error').text('Corrige los errores marcados en rojo');
  }

  return todosValidos;
}

function RenderBotonEliminar(id) {

  const boton = $('<button>').addClass('btn btn-danger btn-eliminar-proveedor').html('<i class="fas fa-trash"></i>');
  boton.attr("data-proveedor", id);
  const div = $('<div>').addClass('d-flex align-items-center ga-2');
  div.append(boton);

  return div.prop('outerHTML');
}

export async function BorrarProveedor(boton) {
  let json = { resultado: 0 };
  let inputTexto = EtiquetasFormulario("input");
  let id_insumo = inputTexto.insumo.attr("data-insumo")

  const $boton = $(boton);

  const datos = $boton.data();
  const linea = $(boton).closest('tr');
  const tabla = $('#tablaAsociar').DataTable();

  if (boton.attr("data-proveedor") == null || boton.attr("data-proveedor") == "" || boton.attr("data-proveedor") == undefined) {
  } else {
    let response = { resultado: 0 };
    let datos_tabla = tabla.row(linea).data();
    response = await EnviarDatos('eliminar_asociación', datos_tabla);
    if (typeof response.resultado === 'number' && (response.resultado >= 200 && response.resultado <= 299)) {
      tabla.row(linea).remove().draw(false);
    }
  }

  json = await ConsultarProveedor(id_insumo)
  if (contarSelectsDisponibles() < json.total) {
    $("#btn-agregarProveedor").prop('disabled', false);
  }
}

function contarSelectsDisponibles() {
  var totalSelects = $('.select-proveedor').length
  return totalSelects;
}

async function ConsultarProveedor(id_insumo) {
  let json = { resultado: 0 };
  let datos = new FormData();
  const endpoint = "?page=Proveedor";
  datos.append("id_insumo", id_insumo);
  datos.append("peticion", "obtener_proveedor");
  return json = await AjaxHelper.enviaAjax(datos, endpoint);
}

export async function DesbloquearBotonAgregar() {
  $("#btn-agregarProveedor").prop('disabled', false);
}

async function RenderizarSelect(id_insumo) {
  let json = null;
  let div = $('<div>').addClass('d-flex align-items-center ga-2');
  let input = $('<select>').addClass('form-select select-proveedor');
  let span = $('<div>').addClass('form-label span-select_proveedor');


  const mensaje = "Seleccione un Proveedor";
  let arreglo = [];

  try {
    json = await ConsultarProveedor(id_insumo);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {

      const array = json.datos.map(item => ({
        nombre: item.nombre,
        valor: item.documento_legal
      }));
      SelectHelper.RenderizarSelect(input, array, mensaje);
      ;
      div.append(input, span);
      let objeto = {
        select: $(input),
        div: div
      }
      let referencia = objeto;
      return referencia;
    }
  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}

function CrearArregloProveedor() {

  let datos = [];
  let input = EtiquetasFormulario("input");

  $('.select-proveedor').each(function () {
    let select = $(this);
    let optionSeleccionado = select.find('option:selected');
    let documento = select.val();

    if (documento && documento !== '') {
      datos.push({
        documento: documento,
        id_entrada: null
      });
    }
  });

  return datos;
}

export async function DataTable(arreglo) {
  if ($.fn.DataTable.isDataTable('#tablaAsociar')) {
    $('#tablaAsociar').DataTable().destroy();
  }

  $('#tablaAsociar').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      {
        data: 'proveedor',
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

  let json = { resultado: 0 };
  let bool = false;
  let inputTexto = EtiquetasFormulario("input");
  let id_insumo = inputTexto.insumo.attr("data-insumo")
  let tabla = $('#tablaAsociar').DataTable();
  json = await ConsultarProveedor(id_insumo);

  if (contarSelectsDisponibles() <= json.total) {

    bool = true;
    if (contarSelectsDisponibles() == (json.total - 1)) {
      $("#btn-agregarProveedor").prop('disabled', true);
      MensajeriaHelper.GenerarMensaje("info", 10000, "", "No hay más proveedores disponibles para asociar a este insumo");
      bool = false;
    }
    if (json.total == 0) {
      $("#btn-agregarProveedor").prop('disabled', true);
      bool = false;
      MensajeriaHelper.GenerarMensaje("info", 10000, "", "No hay más proveedores disponibles para asociar a este insumo");
    }
  } else {
    $("#btn-agregarProveedor").prop('disabled', true);
  };

  if (bool) {
    let selectProveedor = await RenderizarSelect(id_insumo);
    let boton = await RenderBotonEliminar("");

    console.log(selectProveedor);

    tabla.row.add({
      proveedor: selectProveedor.div.prop("outerHTML"),
      boton: boton
    }).draw(false);
    selectProveedor.select.val("default");
  }
  json = null;
}

export function LimpiarFormulario() {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal('Asociar');
  input.insumo.val("").prop("readOnly", false);

  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', false);
  input = null;
  span = null;
  modal = null;
}