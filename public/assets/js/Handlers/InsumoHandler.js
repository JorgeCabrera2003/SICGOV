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

  const inputInsumo = {
    nombre: $('#nombre'),
    costo_unitario: $('#costo_unitario'),
    categoria_id: $('#clave_categoria'),
    unidad_medida: $('#unidad_medida'),
    proveedor: $('#id_proveedor'),
    stock_inicial: $('#stock_inicial'),
    stock_minimo: $('#stock_minimo'),
    stock_maximo: $('#stock_maximo'),
    id_insumo: $('#id_insumo')
  }

  const spanInsumo = {
    nombre: $('#snombre'),
    costo_unitario: $('#scosto_unitario'),
    categoria_id: $('#sclave_categoria'),
    unidad_medida: $('#sunidad_medida'),
    proveedor: $('#sid_proveedor'),
    stock_inicial: $('#sstock_inicial'),
    stock_minimo: $('#sstock_minimo'),
    stock_maximo: $('#sstock_maximo'),
    id_insumo: $('#sid_insumo')
  }

  if (etiquetas === "input") {
    referencia = inputInsumo
  }

  if (etiquetas === "span") {
    referencia = spanInsumo
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalInsumo = {
    modal: $('#modalInsumo'),
    titulo: $('#modalTitleTextInsumo'),
    boton: $('#btnInsumoForm')
  }

  if (etiqueta === "Insumo") {
    referencia = modalInsumo;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Insumo");

  if (operacion == 'registrar') {
    titulo = "Nuevo Insumo";
    boton = "Nuevo";
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Insumo";
    boton = "Actualizar";
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Insumo";
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
  let modal = EtiquetasModal("Insumo");
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
  let modal = EtiquetasModal("Insumo");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();
  let json = { resultado: 0 };

  peticion.append("modulo", "Insumo");

  //Registrar y Modificar
  if (operacion == "registrar" || operacion == "modificar") {
    let bool_peticion = true;
    let stock_maximo = input.stock_maximo.val();
    if (operacion == "registrar") {
      str_acccion = "registrará";
      accion = "registrar"
      if (input.stock_inicial.val() == "" || input.stock_inicial.val() == null) {
        MensajeriaHelper.FeedbackToltipInput(input.stock_inicial, span.stock_inicial,
          "El Stock Inicial no puede estar vacío", 0)
        bool_peticion = false;
      }
      peticion.append('stock_inicial', input.stock_inicial.val());
    }

    if (operacion == "modificar") {
      str_acccion = "actualizará";
      accion = "modificar";
      peticion.append('id_insumo', input.id_insumo.val());
    }

    if (input.stock_maximo.val() == "" || input.stock_maximo.val() == null) {
      stock_maximo = 0;
    }

    if (Validarenvio() && bool_peticion) {
      confirmacion = await confirmarAccion(`Se ${str_acccion} un Insumo`, mensajeConfirmacion, "question");

      if (confirmacion) {
        peticion.append('peticion', accion);
        peticion.append('nombre', input.nombre.val());
        peticion.append('unidad_medida', input.unidad_medida.val());
        peticion.append('costo_unitario', input.costo_unitario.val());
        peticion.append('stock_maximo', stock_maximo);
        peticion.append('stock_minimo', input.stock_minimo.val());
        peticion.append('id_categoria', input.categoria_id.val());
        peticion.append('id_proveedor', input.proveedor.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.")
    }
  } //Fin del Registrar y Modificar
  //Eliminar
  if (operacion == "eliminar") {

    if (ValidadorHelper.ValidarCampo("ID", input.id_insumo, span.id_insumo)) {
      confirmacion = await confirmarAccion("Se eliminará un Insumo", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_insumo', input.id_insumo.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "El ID del Insumo no es válido.");
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
    'Nuevo': 'registrar',
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
  KeyPressInsumo();
  KeyUpInsumo();
  CrearSelectProveedores();
  CrearSelectCategoria();
  CrearSelectUnidadMedida();
}

export async function CrearSelectProveedores() {
  let json = null;
  let datos = new FormData();
  let input = EtiquetasFormulario('input');
  const endpoint = "?page=Proveedor";
  const mensaje = "Seleccione un Proveedor"
  let arreglo = [];
  datos.append("peticion", "consultar")

  try {
    json = await AjaxHelper.enviaAjax(datos, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      const arrayCategoria = json.datos.map(item => ({
        nombre: item.nombre,
        valor: item.documento_legal
      }));
      SelectHelper.RenderizarSelect(input.proveedor, arrayCategoria, mensaje);
    };

  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}

export async function CrearSelectUnidadMedida() {
  let json = null;
  let datos = new FormData();
  let input = EtiquetasFormulario('input');
  const endpoint = "?page=Insumo";
  const modulo = "UnidadMedida";
  const mensaje = "Seleccione una Unidad de Medida"
  let arreglo = [];
  datos.append("modulo", modulo);
  datos.append("peticion", "consultar")

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

export async function CrearSelectCategoria() {
  let json = null;
  let datos = new FormData();
  let input = EtiquetasFormulario('input');
  const endpoint = "?page=CategoriaInsumo";
  const mensaje = "Seleccione una Categoría"
  let arreglo = [];
  datos.append("peticion", "consultar")

  try {
    json = await AjaxHelper.enviaAjax(datos, endpoint);


    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      const arrayCategoria = json.datos.map(item => ({
        nombre: item.nombre,
        valor: item.id_categoria
      }));
      SelectHelper.RenderizarSelect(input.categoria_id, arrayCategoria, mensaje);
    };

  } catch (error) {
    console.log(error);
    arreglo = [];
  }
}

function KeyPressInsumo() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  input.nombre.on("keypress", function (e) { ValidadorHelper.ValidarTecla("Objeto", e); });
  input.stock_inicial.on("keypress", function (e) { ValidadorHelper.ValidarTecla("NumeroDecimal", e); });
  input.stock_maximo.on("keypress", function (e) { ValidadorHelper.ValidarTecla("NumeroDecimal", e); });
  input.stock_minimo.on("keypress", function (e) { ValidadorHelper.ValidarTecla("NumeroDecimal", e); });
}

function KeyUpInsumo() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");


  $(input.nombre).on("keyup", function () {
    ValidadorHelper.ValidarCampo("NombreObjeto", $(this), span.nombre);
  })

  $(input.costo_unitario).on("blur", function () {
    ValidadorHelper.FormatoNumeroDecimal($(this));
    ValidadorHelper.ValidarCampo("NumeroDecimal", $(this), span.costo_unitario);
  })

  $(input.stock_inicial).on("blur", function () {
    ValidadorHelper.FormatoNumeroDecimal($(this), "medida");
    ValidadorHelper.ValidarCampo("NumeroDecimal", $(this), span.stock_inicial);
  })

  $(input.stock_minimo).on("blur", function () {
    ValidadorHelper.FormatoNumeroDecimal($(this), "medida");
    ValidadorHelper.ValidarCampo("NumeroDecimal", $(this), span.stock_minimo);
  })

  $(input.stock_maximo).on("blur", function () {
    ValidadorHelper.FormatoNumeroDecimal($(this), "medida");
    ValidadorHelper.ValidarCampo("NumeroDecimal", $(this), span.stock_maximo);
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

  $(input.categoria_id).on("change", function () {
    if ($(this).val() == "default") {
      SelectHelper.FeedbackSelect($(this), span.categoria_id, "Debe seleccionar a una Categoría", 0)
    } else {
      SelectHelper.FeedbackSelect($(this), span.categoria_id, "", 1)
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


  if (!ValidadorHelper.ValidarCampo("NombreObjeto", input.nombre, span.nombre)) {
    MensajeriaHelper.FeedbackToltipInput(input.nombre, span.nombre, "Nombre ingresado, no válido", 0)
    bool = false;
  }

  if (!ValidadorHelper.ValidarCampo("ID", input.unidad_medida, span.unidad_medida)) {
    bool = false;
  }

  if (!ValidadorHelper.ValidarCampo("FormatoDocumentoLegal", input.proveedor, span.proveedor)) {
    bool = false;
  };

  if (!ValidadorHelper.ValidarCampo("ID", input.categoria_id, span.categoria_id)) {
    bool = false;
  };

  if (input.costo_unitario.val() == '' || input.costo_unitario.val() == null) {
    MensajeriaHelper.FeedbackToltipInput(input.costo_unitario, span.costo_unitario, "El Costo Unitario no puede estar vacío", 0)
    bool = false;
  }

  if (input.proveedor.val() == "default") {
    SelectHelper.FeedbackSelect(input.proveedor, span.proveedor, "Debe Seleccionar a un Proveedor", 0);
    bool = false;
  }

  if (input.unidad_medida.val() == "default") {
    SelectHelper.FeedbackSelect(input.unidad_medida, span.unidad_medida, "Debe Seleccionar una Unidad de Medida", 0);
    bool = false;
  }

  if (input.categoria_id.val() == "default") {
    SelectHelper.FeedbackSelect(input.categoria_id, span.categoria_id, "Debe Seleccionar una Categoría", 0);
    bool = false;
  }

  if (input.stock_minimo.val() == '' || input.stock_minimo.val() == null) {
    MensajeriaHelper.FeedbackToltipInput(input.stock_minimo, span.stock_minimo, "El Stock Mínimo no puede estar vacío", 0)
    bool = false;
  }

  if (input.stock_maximo.val() != '') {
    let stockMinimo = parseFloat(input.stock_minimo.val());
    let stockMaximo = parseFloat(input.stock_maximo.val());
    if (isNaN(stockMinimo)) stockMinimo = 0;
    if (isNaN(stockMaximo)) stockMaximo = 0;

    if (stockMinimo >= stockMaximo) {
      MensajeriaHelper.FeedbackToltipInput(input.stock_minimo, span.stock_minimo, "El Stock Mínimo debe ser menor al Stock Máximo", 0);
      MensajeriaHelper.FeedbackToltipInput(input.stock_maximo, span.stock_maximo, "El Stock Máximo no puede ser menor al Stock Mínimo", 0);
      bool = false;
    } else {
      MensajeriaHelper.FeedbackToltipInput(input.stock_minimo, span.stock_minimo, "", 1);
      MensajeriaHelper.FeedbackToltipInput(input.stock_maximo, span.stock_maximo, "", 1);
    }
  }

  return bool
}

async function RenderPermisoBotones(modulo = "Insumo") {

  const permisos = await PermisoHelper.LlamarPermiso("insumo");
  let bool = false;
  let btn_eliminar = "";
  let btn_modificar = "";
  let btn_suministrar = "";
  let separadorHTML = "";

  if (permisos['insumo']['modificar'] != undefined && permisos['insumo']['modificar'] == 1) {
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
  if (permisos['insumo']['suministrar'] != undefined && permisos['insumo']['suministrar'] == 1) {
    const itemSumistrar = $('<li>');
    const linkSumistrar = $('<a>')
      .addClass('dropdown-item btn-suministrar text-success')
      .attr('href', '#')
      .attr('data-accion', 2)
      .attr('data-modulo', "Suministrar")
      .html('<i class="fa-solid fa-down-long me-2"></i>Suministrar');
    itemSumistrar.append(linkSumistrar);
    btn_suministrar = itemSumistrar;
    bool = true;
  }

  if (permisos['insumo']['eliminar'] != undefined && permisos['insumo']['modificar'] == 1) {
    const itemEliminar = $('<li>');
    const linkEliminar = $('<a>')
      .addClass('dropdown-item btn-eliminar text-danger')
      .attr('href', '#')
      .attr('data-accion', 1)
      .attr('data-modulo', modulo)
      .html('<i class="fas fa-trash me-2"></i>Eliminar');
    itemEliminar.append(linkEliminar);
    btn_eliminar = itemEliminar;
    bool = true;
  }

  if ((btn_modificar != "" || btn_suministrar != "") && btn_eliminar != "") {
    const separador = $('<li>').html('<hr class="dropdown-divider">');
    separadorHTML = separador;
  }

  const dropdown = $('<div>').addClass('dropdown');
  const boton = $('<button>').addClass('btn btn-sm btn-light border dropdown-toggle')
    .attr('type', 'button')
    .attr('data-bs-toggle', 'dropdown')
    .html('<i class="fas fa-ellipsis-v me-3"></i>Acciones');

  const menu = $('<ul>').addClass('dropdown-menu');


  menu.append(btn_modificar, btn_suministrar, separadorHTML, btn_eliminar);
  dropdown.append(boton, menu);

  if (!bool) {
    dropdown.empty(); //Destruye la Etiqueta por si no hay botones que renderizar
  }
  return dropdown.prop('outerHTML');
}

function RenderConfigStock(stockMinimo, abreviatura, stockMaximo) {

  const textMin = $('<span>').addClass('text-danger me-1').text(stockMinimo + " " + abreviatura);
  const textMax = $('<span>').addClass('me-1');
  const strong = $('<strong>')
  const text = $('<text>').addClass("ms-1 me-1 text-black").text("/");
  const div = $('<div>');
  let abreviaturaMax = null;

  if (stockMaximo != null && !isNaN(parseFloat(stockMaximo)) && isFinite(stockMaximo)) {
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

  if (stockMaximo != null && !isNaN(parseFloat(stockMaximo)) && isFinite(stockMaximo)) {
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

  if ($.fn.DataTable.isDataTable('#tablaInsumo')) {
    $('#tablaInsumo').DataTable().destroy();
  }

  $('#tablaInsumo').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'nombre_insumo' },
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
  SistemaValidacion.limpiarValidacion(EtiquetasFormulario('input'));

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal('Insumo');
  let fila_stock_inicial = $("#fila-stock-inicial");

  input.id_insumo.val("").prop("disabled", true);
  input.nombre.val("").prop("disabled", false);
  input.costo_unitario.val("").prop("disabled", false);
  input.unidad_medida.val("default").prop("disabled", false);
  input.proveedor.val("default").prop("disabled", false);
  input.categoria_id.val("default").prop("disabled", false);
  input.stock_inicial.val("").prop("disabled", false);
  input.stock_maximo.val("").prop("disabled", false);
  input.stock_minimo.val("").prop("disabled", false);

  fila_stock_inicial.removeClass("d-none");
  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', false);
  input = null;
  span = null;
  modal = null;
}

export async function EditarFormInsumo(datos, accion) {
  LimpiarFormulario();
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal = EtiquetasModal("Insumo")
  let fila_stock_inicial = $("#fila-stock-inicial");

  if (accion == "eliminar") { bool = true; }

  input.id_insumo.val(datos.id_insumo).prop("disabled", true);
  input.nombre.val(datos.nombre_insumo).prop("disabled", bool);
  input.costo_unitario.val(datos.precio_unitario).prop("disabled", bool);
  input.unidad_medida.prop("disabled", bool);
  input.categoria_id.prop("disabled", bool);
  SelectHelper.BuscarValor(input.unidad_medida, datos.id_unidad_medida, "value")
  SelectHelper.BuscarValor(input.categoria_id, datos.id_categoria, "value")
  input.stock_inicial.val(datos.stock_actual).prop("disabled", true);
  input.stock_maximo.val(datos.stock_maximo).prop("disabled", bool);
  input.stock_minimo.val(datos.stock_minimo).prop("disabled", bool);

  fila_stock_inicial.addClass("d-none")
  modal.boton.prop('disabled', false);
  EditarModal(accion);
};
