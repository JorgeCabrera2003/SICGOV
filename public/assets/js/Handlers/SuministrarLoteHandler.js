import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js"
import * as SelectHelper from "../Helpers/SelectHelper.js"
import * as PermisoHelper from "../Helpers/PermisoHelper.js"

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputSuministrarLote = {
    insumo: $('#entrada-nombreInsumo')
  }

  const spanSuministrarLote = {
    insumo: $('#sentrada-nombreInsumo')
  }

  if (etiquetas === "input") {
    referencia = inputSuministrarLote;
  }

  if (etiquetas === "span") {
    referencia = spanSuministrarLote;
  }

  return referencia;
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalSuministrarLote = {
    modal: $('#modalSuministrarLote'),
    titulo: $('#modalTitleTextmodalSuministrarLote'),
    boton: $('#btnmodalSuministrarLoteForm')
  }

  if (etiqueta === "SuministrarLote") {
    referencia = modalSuministrarLote;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("SuministrarLote");

  if (operacion == 'suministrar') {
    titulo = "Suministrar Insumos";
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
  let modal = EtiquetasModal("SuministrarLote");
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
  let modal = EtiquetasModal("SuministrarLote");

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

    if (ValidarTablaVacia()) {
      if (validarDuplicados()) {
        confirmacion = await MensajeriaHelper.MostrarConfirmacion(`¿Suministrar este Lote?`, mensajeConfirmacion, "question");

        if (confirmacion) {
          let datos = CrearArregloLote();
          peticion.append('peticion', "suministrar_lote");
          peticion.append('lote_insumos', JSON.stringify(datos));
          btn_formulario = true;
          console.log(datos);
        }
      } else {
        btn_formulario = false;
        MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.")
      }
    } else {
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error", "No hay nada en la lista de insumos")
    }
  } //Fin del Suministrar Lote

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await AjaxHelper.enviaAjax(peticion, endpoint);
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
    'SuministrarLote': 'asociar',
    'Eliminar': 'eliminar'
  }
  const DEFAULT = null

  accion = MANEJADOR[btn_string] || DEFAULT
  accion = "suministrar";
  if (accion != null) {
    respuesta = await EnviarDatos(accion)
  } else {
    respuesta = { resultado: 0 }
    MensajeriaHelper.GenerarMensaje("error", 10000, "Error, acción no válida", "")
  }
  return respuesta;
};

//CAPA DE VALIDACIÓN
export function CapaValidar() {
  KeyPressSuministrarLote();
  KeyUpSuministrarLote();
}

function KeyPressSuministrarLote() {
  $('#tablaSuministrarLote').on("keypress", '.input-cantidad', function (e) { ValidadorHelper.ValidarTecla("NumeroDecimal", e); });
}

function KeyUpSuministrarLote() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  $('#tablaSuministrarLote').on('change', '.select-proveedor_lote', function () {
    let $select = $(this);
    let $fila = $select.closest('tr'); // Obtener la fila

    // Buscar el span dentro de la misma fila
    let $span = $fila.find('.span-proveedor_lote');

    if ($select.val() === "default" || $select.val() === "") {
      SelectHelper.FeedbackSelect($select, $span, "Debe seleccionar a un Proveedor", 0);
    } else {
      SelectHelper.FeedbackSelect($select, $span, "", 1);
    }
  });

  $('#tablaSuministrarLote').on('change', '.select-insumo', function () {
    let $select = $(this);
    let $fila = $select.closest('tr'); // Obtener la fila

    // Buscar el span dentro de la misma fila
    let $span = $fila.find('.span-insumo');

    if ($select.val() === "default" || $select.val() === "") {
      SelectHelper.FeedbackSelect($select, $span, "Debe seleccionar a un Insumo", 0);
    } else {
      SelectHelper.FeedbackSelect($select, $span, "", 1);
    }
  });

  $('#tablaSuministrarLote').on('change', '.select-unidad_medida', function () {
    let $select = $(this);
    let $fila = $select.closest('tr'); // Obtener la fila

    // Buscar el span dentro de la misma fila
    let $span = $fila.find('.span-unidad_medida');

    if ($select.val() === "default" || $select.val() === "") {
      SelectHelper.FeedbackSelect($select, $span, "Debe seleccionar a una Unidad de Medida", 0);
    } else {
      SelectHelper.FeedbackSelect($select, $span, "", 1);
    }
  });

  $('#tablaSuministrarLote').on('blur', '.input-cantidad', function () {
    let $input = $(this);
    let $fila = $input.closest('tr'); // Obtener la fila
    let $span = $fila.find('.span-cantidad');
    ValidadorHelper.FormatoNumeroDecimal($(this));
  });

}

export function validarDuplicados() {
  let filasValidas = {};
  let todosValidos = true;
  let errores = [];

  // PRIMERO: Recorrer todas las filas
  $('#tablaSuministrarLote tbody tr').each(function () {
    let $fila = $(this);
    let insumo = $fila.find('.select-insumo').val();
    let proveedor = $fila.find('.select-proveedor_lote').val();
    let cantidad = $fila.find('.input-cantidad').val();
    let unidad_medida = $fila.find('.select-unidad_medida').val();
    // Validar campos obligatorios
    let erroresFila = [];

    if (!insumo || insumo === 'default' || insumo === '') {
      erroresFila.push('Insumo no seleccionado');
      $fila.find('.select-insumo').addClass('is-invalid');
      $fila.find('.span-insumo').addClass('invalid-tooltip').text("Debe seleccionar un Insumo");
      todosValidos = false;
    }

    if (!unidad_medida || unidad_medida === 'default' || unidad_medida === '') {
      erroresFila.push('Unidad de Medida no seleccionado');
      $fila.find('.select-unidad_medida').addClass('is-invalid');
      $fila.find('.span-unidad_medida').addClass('invalid-tooltip').text("Debe seleccionar una Unidad de Medida");
      todosValidos = false;
    }

    if (!proveedor || proveedor === 'default' || proveedor === '') {
      erroresFila.push('Proveedor no seleccionado');
      $fila.find('.select-proveedor_lote').addClass('is-invalid');
      $fila.find('.span-select_proveedor_lote').addClass('invalid-tooltip').text("Debe seleccionar un Proveedor");
      todosValidos = false;
    }

    if (!cantidad || cantidad <= 0) {
      erroresFila.push('Cantidad inválida');
      $fila.find('.input-cantidad').addClass('is-invalid');
      $fila.find('.span-cantidad').addClass('invalid-tooltip').text("El campo no puede estar vacío o estar en 0");
      todosValidos = false;
    }

    // Si la fila tiene errores, no la procesamos para duplicados
    if (erroresFila.length > 0) {
      errores.push({
        fila: $fila.index() + 1,
        errores: erroresFila
      });
      return;
    }

    // Crear clave única: insumo + proveedor
    let clave = insumo + '|' + proveedor;

    if (!filasValidas[clave]) {
      filasValidas[clave] = [];
    }
    filasValidas[clave].push({
      fila: $fila,
      insumo: insumo,
      proveedor: proveedor,
      cantidad: cantidad
    });
  });

  // SEGUNDO: Verificar duplicados
  Object.keys(filasValidas).forEach(function (clave) {
    let items = filasValidas[clave];

    if (items.length > 1) {
      // Marcar todas las filas con esta combinación como duplicadas
      items.forEach(function (item, index) {
        let $fila = item.fila;
        $fila.find('.select-insumo').addClass('is-invalid');
        $fila.find('.select-proveedor_lote').addClass('is-invalid');

        let $span = $fila.find('.span-insumo');
        $span.text(`Combinación duplicada (${items.length} filas con mismo insumo y proveedor)`);
        $span.addClass('invalid-tooltip');

        todosValidos = false;
      });
    } else {
      // Marcar como válido
      items.forEach(function (item) {
        let $fila = item.fila;

        $fila.find('.span-insumo').removeClass('invalid-tooltip').text("");
        $fila.find('.span-unidad_medida').removeClass('invalid-tooltip').text("");
        $fila.find('.span-select_proveedor_lote').removeClass('invalid-tooltip').text("");
        $fila.find('.span-cantidad').removeClass('invalid-tooltip').text("");

        $fila.find('.select-insumo').addClass('is-valid').removeClass('is-invalid');
        $fila.find('.select-proveedor_lote').addClass('is-valid').removeClass('is-invalid');
        $fila.find('.select-unidad_medida').addClass('is-valid').removeClass('is-invalid');
        $fila.find('.input-cantidad').addClass('is-valid').removeClass('is-invalid');
      });
    }
  });

  // TERCERO: Actualizar UI
  if (todosValidos) {
    $('#btn-guardar').prop('disabled', false);
    $('#mensaje-error').hide();
    $('#mensaje-exito').show().text('Todos los datos son válidos');
  } else {
    $('#btn-guardar').prop('disabled', true);
    $('#mensaje-error').show();
    $('#mensaje-error').text(`Errores encontrados: ${errores.length} filas con problemas`);
    $('#mensaje-exito').hide();
  }

  return todosValidos;
}

function ValidarTablaVacia() {
  let table = $('#tablaSuministrarLote').DataTable();
  let data = table.data();

  if (data.length === 0) {
    return false;
  }

  return true;
}

function CrearArregloLote() {
  let datos = [];

  // Recorrer cada fila de la tabla
  $('#tablaSuministrarLote tbody tr').each(function () {
    let $fila = $(this);

    // Obtener valores de cada campo
    let insumo = $fila.find('.select-insumo').val();
    let unidad = $fila.find('.select-unidad_medida').val();
    let cantidad = $fila.find('.input-cantidad').val();
    let proveedor = $fila.find('.select-proveedor_lote').val();

    // Solo agregar si tiene datos válidos
    if (insumo && insumo !== 'default' && proveedor && proveedor !== '') {
      datos.push({
        insumo: insumo,
        unidad_medida: unidad,
        cantidad: cantidad,
        proveedor: proveedor,
      });
    }
  });

  return datos;
}

function RenderBotonEliminar(id) {

  const boton = $('<button>').addClass('btn btn-danger btn-eliminar-insumo:lote').html('<i class="fas fa-trash"></i>');
  const div = $('<div>').addClass('d-flex align-items-center ga-2');
  div.append(boton);

  return div.prop('outerHTML');
}

export async function BorrarFila(boton) {
  let inputTexto = EtiquetasFormulario("input");

  const linea = $(boton).closest('tr');
  const tabla = $('#tablaSuministrarLote').DataTable();
  tabla.row(linea).remove().draw(false);

}

export async function BuscarDatos($fila) {
  await LlenarSelectUnidadMedida($fila.find('.select-insumo').val(), $($fila.find('.select-unidad_medida')));
  await LlenarSelectProveedor($fila.find('.select-insumo').val(), $($fila.find('.select-proveedor_lote')));

  $($fila.find('.select-unidad_medida')).prop('disabled', false).removeClass('is-invalid is-valid');
  $($fila.find('.select-proveedor_lote')).prop('disabled', false).removeClass('is-invalid is-valid');
  $($fila.find('.input-cantidad')).prop('disabled', false).val(0).removeClass('is-invalid is-valid');

  $($fila.find('.span-insumo')).removeClass('invalid-tooltip').text("");
  $($fila.find('.span-unidad_medida')).removeClass('invalid-tooltip').text("");
  $($fila.find('.span-select_proveedor_lote')).removeClass('invalid-tooltip').text("");
  $($fila.find('.span-cantidad')).removeClass('invalid-tooltip').text("");
  CapaValidar();
}

async function RenderizarSelectProveedor() {
  let div = $('<div>').addClass('d-flex align-items-center ga-2');
  let input = $('<select>').addClass('form-select select-proveedor_lote').prop('disabled', true);
  let span = $('<div>').addClass('form-label span-select_proveedor_lote');
  const mensaje = "Seleccione un Proveedor";

  try {

    div.append(input, span);
    let objeto = {
      select: $(input),
      div: div
    }
    let referencia = objeto;
    return referencia;
  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}

async function LlenarSelectProveedor(id_insumo, input) {
  let json = null;
  let datos = new FormData();
  const endpoint = "?page=Insumo";
  const modulo = "EntradaInsumo";
  const mensaje = "Seleccione un Proveedor"
  datos.append("id_insumo", id_insumo);
  datos.append("modulo", modulo);
  datos.append("peticion", "filtrar");


  try {
    json = await AjaxHelper.enviaAjax(datos, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {

      const array = json.datos.map(item => ({
        nombre: item.proveedor,
        valor: item.id_entrada
      }));
      SelectHelper.RenderizarSelect(input, array, mensaje);
      ;
    }
  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}

async function CrearSelectInsumos() {
  let json = { resultado: 0 };
  let datos = new FormData();
  let div = $('<div>').addClass('d-flex align-items-center ga-2');
  let input = $('<select>').addClass('form-select select-insumo');
  let span = $('<div>').addClass('form-label span-insumo');

  const endpoint = "?page=Insumo";
  const modulo = "Insumo";
  const mensaje = "Seleccione un Insumo";
  let arreglo = [];
  datos.append("modulo", modulo);
  datos.append("peticion", "consultar");

  try {
    json = await AjaxHelper.enviaAjax(datos, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      const arrayInsumo = json.datos.map(item => ({
        nombre: item.nombre_insumo,
        valor: item.id_insumo
      }));
      SelectHelper.RenderizarSelect(input, arrayInsumo, mensaje);
    };
    div.append(input, span);
    let objeto = {
      select: $(input),
      div: div
    }
    let referencia = objeto;
    return referencia;

  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}

async function CrearSelectUnidadMedida() {
  let div = $('<div>').addClass('d-flex align-items-center ga-2');
  let input = $('<select>').addClass('form-select select-unidad_medida').prop('disabled', true);
  let span = $('<div>').addClass('form-label span-unidad_medida');

  div.append(input, span);
  let objeto = {
    select: $(input),
    div: div
  }
  let referencia = objeto;
  return referencia;

}

async function LlenarSelectUnidadMedida(id, input) {
  let json = { resultado: 0 };
  let datos = new FormData();

  const endpoint = "?page=Insumo";
  const modulo = "UnidadMedida";
  const mensaje = "Seleccione una Unidad de Medida";
  let arreglo = [];
  datos.append("modulo", modulo);
  datos.append("id_insumo", id);
  datos.append("peticion", "buscar_medida_insumo");

  try {
    json = await AjaxHelper.enviaAjax(datos, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      const arrayUnidad = json.datos.map(item => ({
        nombre: item.nombre + " - " + item.abreviatura,
        valor: item.id_unidad
      }));
      SelectHelper.RenderizarSelect(input, arrayUnidad, mensaje);
    };

  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}

async function CrearInputCantidad() {
  let div = $('<div>').addClass('d-flex align-items-center ga-2');
  let input = $('<input>').addClass('form-control input-cantidad').attr("type", "number").prop('disabled', true);
  let span = $('<div>').addClass('form-label span-cantidad');

  div.append(input, span);
  return div;
}

export async function DataTable() {
  let arreglo = [];
  if ($.fn.DataTable.isDataTable('#tablaSuministrarLote')) {
    $('#tablaSuministrarLote').DataTable().destroy();
  }

  $('#tablaSuministrarLote').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      {
        data: 'insumo'
      },
      {
        data: 'unidad_medida'
      },
      {
        data: 'cantidad'
      },
      {
        data: 'proveedor'
      },
      {
        data: 'boton'
      }
    ],
    order: [[1, 'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
  });
}

export async function AgregarFilaInput() {

  let json = { resultado: 0 };
  let divInsumo = { resultado: 0 };
  let divProveedor = { resultado: 0 };
  let divUnidad = { resultado: 0 };
  let divCantidad = { resultado: 0 };
  let bool = false;
  let tabla = $('#tablaSuministrarLote').DataTable();

  bool = true;
  if (bool) {
    divProveedor = await RenderizarSelectProveedor();
    divInsumo = await CrearSelectInsumos();
    divUnidad = await CrearSelectUnidadMedida();
    divCantidad = await CrearInputCantidad();
    let boton = await RenderBotonEliminar("");

    tabla.row.add({
      insumo: divInsumo.div.prop("outerHTML"),
      unidad_medida: divUnidad.div.prop("outerHTML"),
      cantidad: divCantidad.prop("outerHTML"),
      proveedor: divProveedor.div.prop("outerHTML"),
      boton: boton
    }).draw(false);
  }
  json = null;
}

export function LimpiarFormulario() {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal('SuministrarLote');
  input.insumo.val("").prop("readOnly", false);

  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', false);
  input = null;
  span = null;
  modal = null;
}