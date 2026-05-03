import * as mensajeria from "./Helpers/MensajeriaHelper.js"
import * as AjaxHelper from "./Helpers/AjaxHelper.js"
import * as ValidarHelper from "./Helpers/ValidadorHelper.js"

//MODULO DE INGREDIENTES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputIngrediente = {
    nombre: $('#nombre'),
    costo_unitario: $('#costo_unitario'),
    categoria_id: $('#clave_categoria'),
    unidad_medida: $('#unidad_medida'),
    stock_inicial: $('#stock_inicial'),
    stock_minimo: $('#stock_minimo'),
    stock_maximo: $('#stock_maximo'),
    id_ingrediente: $('#id_ingrediente')
  }

  const spanIngrediente = {
    nombre: $('#snombre'),
    costo_unitario: $('#scosto_unitario'),
    categoria_id: $('#sclave_categoria'),
    unidad_medida: $('#sunidad_medida'),
    stock_inicial: $('#sstock_inicial'),
    stock_minimo: $('#sstock_minimo'),
    stock_maximo: $('#sstock_maximo'),
    id_ingrediente: $('#sid_ingrediente')
  }

  if (etiquetas === "input-Ingrediente") {
    referencia = inputIngrediente
  }

  if (etiquetas === "span-Ingrediente") {
    referencia = spanIngrediente
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalIngrediente = {
    modal: $('#modalIngrediente'),
    titulo: $('#modalTitleTextIngrediente'),
    boton: $('#btnIngredienteForm')
  }

  if (etiqueta === "Ingrediente") {
    referencia = modalIngrediente;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Ingrediente");

  if (operacion == 'registrar') {
    titulo = "Nuevo Ingrediente";
    boton = "Nuevo";
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Ingrediente";
    boton = "Actualizar";
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Ingrediente";
    boton = "Borrar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

//Función para manejar el cambio de estado del formulario
function manejarCambioEstado(formularioValido) {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let modal = EtiquetasModal("Ingrediente");
  const accion = modal.boton.text();

  if (accion === "Eliminar") {
    // Para eliminar solo validamos el ID
    const idValido = validarKeyUp(/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/, input.id_ingrediente.val(), span.id_ingrediente, '');
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

  let input = EtiquetasFormulario('input-Ingrediente');
  let span = EtiquetasFormulario('span-Ingrediente');
  let modal = EtiquetasModal(modulo);

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
      peticion.append('id_ingrediente', input.id_ingrediente.val());
    }

    if (validarenvio()) {
      confirmacion = await confirmarAccion(`Se ${str_acccion} un Ingrediente`, mensajeConfirmacion, "question");

      if (confirmacion) {
        peticion.append('peticion', accion);
        peticion.append('nombre', input.nombre.val());
        peticion.append('unidad_medida', input.unidad_medida.val());
        peticion.append('costo_unitario', input.costo_unitario.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      mensajeria.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.")
    }
  } //Fin del Registrar y Modificar
  //Eliminar
  if (operacion == "eliminar") {

    if (validarKeyUp(/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/, input.id_ingrediente, span.id_ingrediente, '')) {
      confirmacion = await confirmarAccion("Se eliminará un Ingrediente", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_ingrediente', input.id_ingrediente.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      mensajeria.GenerarMensaje("error", 10000, "Error de Validación", "El ID del Ingrediente no es válido.");
    }
  }//Fin del Eliminar

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await enviaAjax(peticion, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      modal.modal.modal("hide");
      DataTablePrincipal();
      mensajeria.GenerarMensaje(json.icon, 10000, json.mensaje, null);
    }
    modal.boton.prop('disabled', false);
  }

  if (!confirmacion) {
    modal.boton.prop('disabled', false);
  }

  input = null;
  modal = null;
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
    enviarDatos(accion)
  } else {
    console.log("Error, acción no válida")
  }
};

//CAPA DE VALIDACIÓN

export function CapaValidar() {
  KeyPressIngrediente();
}

function KeyPressIngrediente() {
  let input = EtiquetasFormulario("input-Ingrediente")
  input.nombre.on("keypress", function (e) {
    validarKeyPress(/^[0-9 a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ -.\b]*$/, e);
  });

  input.costo_unitario.on("keypress", function (e) {
    validarKeyPress(/^[0-9.\b]*$/, e);
  });

  // Aplicar capitalización en tiempo real para nombre y responsable
  input.nombre.on("input", function () {
    // Capitalizar mientras escribe (opcional)
    const valor = $(this).val();
    if (valor.length === 1) {
      $(this).val(valor.toUpperCase());
    }
  });
}

function Validarenvio(modulo = "Ingrediente") {
  return SistemaValidacion.validarFormulario(EtiquetasFormulario('input-' + modulo));
}

async function RenderPermisoBotones(modulo = "Ingrediente") {

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

function RenderConfigStock(stockMinimo, abreviatura, stockMaximo) {

  const textMin = $('<span>').addClass('text-danger me-1').text(stockMinimo + " " + abreviatura);
  const textMax = $('<span>').addClass('me-1');
  const strong = $('<strong>')
  const text = $('<text>').addClass("ms-1 me-1 text-black").text("/");
  const div = $('<div>');
  let abreviaturaMax = null;

  if (stockMaximo != null && !isNaN(parseFloat(valor)) && isFinite(valor)) {
    textMax.text(stockMaximo + " " + abreviatura).addClass('text-success me-1');
    abreviaturaMax = abreviatura;
  } else {
    textMax.text("Ninguno").addClass('text-black me-1');
  }

  strong.append(text);
  div.append(textMin, strong, textMax);
  return div.prop('outerHTML');
}

function RenderColorearStock(stockActual, stockMinimo, stockMaximo = null, abreviatura) {
  const texto = $('<span>');
  const div = $('<div>').addClass('d-flex align-items-center gap-1');
  let color = "";
  const umbralMinimo = stockMinimo * 0.3;
  const umbralRecomendado = stockMinimo * 0.6;

  if (stockActual <= stockMinimo) {
    color = "text-danger";
  }

  if (stockActual <= umbralMinimo) {
    color = "text-warning";
    console.log(umbralMinimo);
  }

  if (stockActual >= umbralRecomendado) {
    color = "text-success";
  }

  if (stockMaximo != null && !isNaN(parseFloat(valor)) && isFinite(valor)) {
    if (stockActual == stockMaximo) {
      color = "text-success";
    }
  }
  texto.addClass(color).text(stockActual + " " + abreviatura);
  div.append(texto);

  return div.prop('outerHTML');
}

export async function DataTablePrincipal(arreglo) {
  let botones = '';
  botones = await RenderPermisoBotones();

  if ($.fn.DataTable.isDataTable('#tablaIngrediente')) {
    $('#tablaIngrediente').DataTable().destroy();
  }

  $('#tablaIngrediente').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'nombre_ingrediente' },
      { data: 'nombre_categoria' },
      {
        data: null,
        render: function (row) {
          const texto = row.precio_unitario + "$";
          return texto;
        }
      },
      {
        data: null,
        render: function (row) {
          return RenderColorearStock(row.stock_actual, row.stock_minimo, row.stock_maximo, row.abreviatura);
        }
      },
      {
        data: null,
        render: function (row) {
          return RenderConfigStock(row.stock_minimo, row.abreviatura, row.stock_maximo);
        }
      },
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
  SistemaValidacion.limpiarValidacion(EtiquetasFormulario('input-Ingrediente'));

  let input = EtiquetasFormulario('input-Ingrediente');
  let span = EtiquetasFormulario('span-Ingrediente');
  let modal = EtiquetasModal('Ingrediente');
  let fila_stock_inicial = $("#fila-stock-inicial");

  input.id_ingrediente.val("").prop("disabled", true);
  input.nombre.val("").prop("disabled", false);
  input.costo_unitario.val("").prop("disabled", false);
  input.unidad_medida.prop("disabled", false);
  input.stock_inicial.val("").prop("disabled", false);
  input.stock_maximo.val("").prop("disabled", false);
  input.stock_minimo.val("").prop("disabled", false);

  fila_stock_inicial.removeClass("d-none");
  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', true);
  input = null;
  span = null;
  modal = null;
}

export async function EditarFormIngrediente(datos, accion) {
  LimpiarFormulario();
  let input = EtiquetasFormulario("input-Ingrediente");
  let bool = false;
  let modal = EtiquetasModal("Ingrediente")
  let fila_stock_inicial = $("#fila-stock-inicial");

  if (accion == "eliminar") { bool = true; }

  input.id_ingrediente.val(datos.id_ingrediente).prop("disabled", true);
  input.nombre.val(datos.nombre_ingrediente).prop("disabled", bool);
  input.costo_unitario.val(datos.precio_unitario).prop("disabled", bool);
  input.unidad_medida.prop("disabled", bool);
  input.stock_inicial.val(datos.stock_actual).prop("disabled", true);
  input.stock_maximo.val(datos.stock_maximo).prop("disabled", bool);
  input.stock_minimo.val(datos.stock_minimo).prop("disabled", bool);

  fila_stock_inicial.addClass("d-none")
  modal.boton.prop('disabled', false);
  EditarModal(accion);
};
