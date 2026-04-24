//MODULO DE INGREDIENTES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function etiquetasFormulario(etiquetas) {
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

  const inputCategoria = {
    nombre: $('#categoria-nombre'),
    descripcion: $('#categoria-descripcion'),
    id_categoria: $('#id_categoria')
  }

  const spanCategoria = {
    nombre: $('#scategoria-nombre'),
    descripcion: $('#scategoria-descripcion'),
    id_categoria: $('#sid_categoria')
  }

  if (etiquetas === "input-Ingrediente") {
    referencia = inputIngrediente
  }

  if (etiquetas === "span-Ingrediente") {
    referencia = spanIngrediente
  }

  if (etiquetas === "input-Categoria") {
    referencia = inputCategoria;
  }

  if (etiquetas === "span-Categoria") {
    referencia = spanCategoria
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

//Interfaz de Acceso a los Elementos(modal)
function etiquetasModal(etiqueta) {
  let referencia = null

  const modalIngrediente = {
    modal: $('#modalIngrediente'),
    titulo: $('#modalTitleTextIngrediente'),
    boton: $('#btnIngredienteForm')
  }

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

  if (etiqueta === "Ingrediente") {
    referencia = modalIngrediente;
  }

  if (etiqueta === "TablaCategoria") {
    referencia = modalCategoriaTabla;
  }

  if (etiqueta === "Categoria") {
    referencia = modalCategoria;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

//Función para editar textos visuales del modal
function editarModal(modal = "Ingrediente", operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = etiquetasModal(modal);
  let vocal = "o";
  let sustantivo = "Ingrediente";

  if (modal == "Categoria") {
    vocal = "a"
    sustantivo = "Categoría";
  }

  if (operacion == 'registrar') {
    titulo = "Nuev" + vocal + " " + sustantivo;
    boton = "Nuevo";
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar " + sustantivo;
    boton = "Actualizar";
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar " + sustantivo;
    boton = "Borrar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}
//Fin de la Función de editarModal

//Función para manejar el cambio de estado del formulario
function manejarCambioEstado(formularioValido) {
  let input = etiquetasFormulario("input");
  let span = etiquetasFormulario("span");
  let modal = etiquetasModal("Ingrediente");
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

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
  capaValidar();

  // Inicializar sistema de validación con callback
  SistemaValidacion.inicializar(etiquetasFormulario('input-Ingrediente'), manejarCambioEstado);
  SistemaValidacion.inicializar(etiquetasFormulario('input-Categoria'), manejarCambioEstado);

  // Validar estado inicial del formulario
  manejarCambioEstado(false);
});

async function enviarDatos(operacion, modulo = "Ingrediente") {

  let input = etiquetasFormulario('input-' + modulo);
  let span = etiquetasFormulario('span-' + modulo);
  let modal = etiquetasModal(modulo);

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();

  if (modulo == "Ingrediente") {
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
        mensajes("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.");
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
        mensajes("error", 10000, "Error de Validación", "El ID del Ingrediente no es válido.");
      }
    }//Fin del Eliminar
  }
  if (modulo == "Categoria") {
    endpoint = "?page=categoria-ingrediente";
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

      if (validarenvio()) {
        confirmacion = await confirmarAccion(`Se ${str_acccion} una Categoría`, mensajeConfirmacion, "question");

        if (confirmacion) {
          peticion.append('peticion', accion);
          peticion.append('nombre', input.nombre.val());
          peticion.append('descripcion', input.descripcion.val());
          btn_formulario = true;
        }
      } else {
        btn_formulario = false;
        mensajes("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.");
      }
    } //Fin del Registrar y Modificar
    //Eliminar
    if (operacion == "eliminar") {

      if (validarKeyUp(/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/, input.id_categoria, span.id_categoria, '')) {
        confirmacion = await confirmarAccion("Se eliminará una Categoría", mensajeConfirmacion, "warning");

        if (confirmacion) {
          peticion.append('peticion', 'eliminar');
          peticion.append('id_categoria', input.id_categoria.val());
          btn_formulario = true;
        }
      } else {
        btn_formulario = false;
        mensajes("error", 10000, "Error de Validación", "El ID de la Categoría no es válido.");
      }
    }//Fin del Eliminar
  }

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await enviaAjax(peticion, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      modal.modal.modal("hide");
      crearDataTable();
      mensajes(json.icon, 10000, json.mensaje, null);
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
$("#btnIngredienteForm").on("click", async function () {
  let accion = null;
  const MANEJADOR = {
    'Nuevo': 'registrar',
    'Actualizar': 'modificar',
    'Borrar': 'eliminar'
  }
  const DEFAULT = null

  accion = MANEJADOR[$(this).text()] || DEFAULT

  if (accion != null) {
    enviarDatos(accion)
  } else {
    console.log("Error, acción no válida")
  }
});

$("#btnNuevoIngrediente").on("click", function () {
  limpia("Ingrediente");
  editarModal("Ingrediente", "registrar")
  // El botón se habilita automáticamente mediante el callback cuando los campos sean válidos
});

//Iniciar Tabla de Categoría de Ingrediente
$("#btn-ModalCategorias").on("click", async function () {
  let etiqueta_modal = etiquetasModal("TablaCategoria");

  await crearDataTable("categoria-ingrediente")
  etiqueta_modal.modal.modal("show");

  etiqueta_modal = null;
})

//Iniciar Modal Formulario de Categoría de Ingrediente
$("#btnNuevaCategoria").on("click", function () {
  let modal_tabla = etiquetasModal("TablaCategoria");
  let modal_form = etiquetasModal("Categoria");
  modal_tabla.modal.modal("hide");
  editarModal("Categoria", "registrar");

  modal_tabla = null;
  modal_form = null;
})

$("#btn-CategoriaCancel").on("click", function () {
  let modal_form = etiquetasModal("Categoria");
  let modal_tabla = etiquetasModal("TablaCategoria");
  modal_form.modal.modal("hide");
  modal_tabla.modal.modal("show");

  console.log(modal_form);
  modal_form = null;
  modal_tabla = null;
})

//Iniciar Tabla de Eliminadas (Papelera) usando evento click del botón
$("#btn-consultar-eliminados").on("click", function () {
  iniciarTablaEliminadas();
})

// Aplicar capitalización automática cuando el modal se muestra
$('#modalIngrediente').on('shown.bs.modal', function () {
  // Forzar validación inicial cuando se abre el modal
  setTimeout(() => {
    SistemaValidacion.validarFormulario(etiquetasFormulario('input'));
  }, 100);
});

//CAPA DE VALIDACIÓN

function capaValidar() {
  KeyPressIngrediente();
  KeyPressCategoria();
}

function KeyPressIngrediente() {
  let input = etiquetasFormulario("input-Ingrediente")
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

function KeyPressCategoria() {
  let input = etiquetasFormulario("input-Categoria");


}

function validarenvio(modulo = "Ingrediente") {
  return SistemaValidacion.validarFormulario(etiquetasFormulario('input-' + modulo));
}

async function vistaPermiso(modulo = "Ingrediente") {

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
    .attr('onclick', `rellenar(this, 0, "` + modulo + `")`)
    .html('<i class="fas fa-edit me-2"></i>Editar');
  itemEditar.append(linkEditar);

  const itemEliminar = $('<li>');
  const linkEliminar = $('<a>')
    .addClass('dropdown-item btn-eliminar text-danger')
    .attr('href', '#')
    .attr('onclick', `rellenar(this, 1, "` + modulo + `")`)
    .html('<i class="fas fa-trash me-2" me-2"></i>Eliminar');
  itemEliminar.append(linkEliminar);

  menu.append(itemEditar, separador, itemEliminar);
  dropdown.append(boton, menu);

  console.log(dropdown)
  return dropdown.prop('outerHTML');
}

function ColorearStock(stockActual, stockMinimo, stockMaximo = null, abreviatura) {
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

async function crearDataTable(controlador = "ingredientes") {
  const peticion = new FormData();
  let json = null;
  let arreglo = [];
  let endpoint = "?page=" + controlador;


  peticion.append("peticion", "consultar");

  try {
    json = await enviaAjax(peticion, endpoint);
    arreglo = json.datos;
  } catch (error) {
    arreglo = [];
  }

  if (Array.isArray(arreglo)) {
    console.log("arreglo");
    if (controlador === "ingredientes") {
      DataTablePrincipal(arreglo);
    }

    if (controlador === "categoria-ingrediente") {
      DataTableCategoria(arreglo);
    }
  } else {
    console.log("falso");
  }
}

async function DataTablePrincipal(arreglo) {
  let botones = '';
  botones = await vistaPermiso("Ingrediente");

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
          return ColorearStock(row.stock_actual, row.stock_minimo, row.stock_maximo, row.abreviatura);
        }
      },
      {
        data: null,
        render: function (row) {
          const textMin = $('<span>').addClass('text-danger me-1').text(row.stock_minimo + " " + row.abreviatura);
          const textMax = $('<span>').addClass('me-1');
          const strong = $('<strong>')
          const text = $('<text>').addClass("ms-1 me-1 text-black").text("/");
          const div = $('<div>');
          let abreviaturaMax = null;

          if (row.stock_maximo != null && !isNaN(parseFloat(valor)) && isFinite(valor)) {
            textMax.text(row.stock_maximo + " " + row.abreviatura).addClass('text-success me-1');
            abreviaturaMax = row.abreviatura;
          } else {
            textMax.text("Ninguno").addClass('text-black me-1');
          }

          strong.append(text);
          div.append(textMin, strong, textMax);
          return div.prop('outerHTML');
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

async function DataTableCategoria(arreglo) {
  let botones = '';
  botones = await vistaPermiso("Categoria");

  if ($.fn.DataTable.isDataTable('#tablaCategoria')) {
    $('#tablaCategoria').DataTable().destroy();
  }

  $('#tablaCategoria').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'nombre' },
      { data: 'descripcion' },
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

function limpia(formulario) {
  SistemaValidacion.limpiarValidacion(etiquetasFormulario('input-' + formulario));

  let input = etiquetasFormulario('input-' + formulario);
  let span = etiquetasFormulario('span-' + formulario);
  let modal = etiquetasModal(formulario);
  let fila_stock_inicial = $("#fila-stock-inicial");


  if (formulario == "Ingrediente") {
  input.id_ingrediente.val("").prop("disabled", true);
  input.nombre.val("").prop("disabled", false);
  input.costo_unitario.val("").prop("disabled", false);
  input.unidad_medida.prop("disabled", false);
  input.stock_inicial.val("").prop("disabled", false);
  input.stock_maximo.val("").prop("disabled", false);
  input.stock_minimo.val("").prop("disabled", false);

  fila_stock_inicial.removeClass("d-none");
}

  if (formulario == "Categoria") {
    input.id_categoria.val("").prop("readOnly", true);
    input.nombre.val("").prop("readOnly", false)
    input.descripcion.val("").prop("readOnly", false)
  }


  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', true);
  input = null;
  span = null;
  modal = null;
}

async function rellenar(pos, accion, modulo = "Ingrediente") {
  limpia(modulo);
  let input = etiquetasFormulario('input-' + modulo);
  let str_accion = "";
  const linea = $(pos).closest('tr');
  const tabla = $('#tabla' + modulo).DataTable();
  const datosFila = tabla.row(linea).data();

  if (accion == 0) {
    str_accion = "modificar";
  }

  if(accion == 1){
    str_accion = "eliminar";
  }

  if (modulo == "Ingrediente") {
    await editarFormIngrediente(input, datosFila, str_accion)
  }

  if (modulo == "Categoria") {
    await editarFormCategoria(input, datosFila, str_accion)
  }


  editarModal(modulo, str_accion)
  // Habilitar el botón inmediatamente para Modificar/Eliminar ya que los datos vienen pre-validados
  $('#btnIngredienteForm').prop('disabled', false);
}

async function editarFormIngrediente(input, datos, accion) {

  let bool = false;
  let fila_stock_inicial = $("#fila-stock-inicial");

  if(accion == "eliminar") {bool = true;}

  input.id_ingrediente.val(datos.id_ingrediente).prop("disabled", true);
  input.nombre.val(datos.nombre_ingrediente).prop("disabled", bool);
  input.costo_unitario.val(datos.precio_unitario).prop("disabled", bool);
  input.unidad_medida.prop("disabled", bool);
  input.stock_inicial.val(datos.stock_actual).prop("disabled", true);
  input.stock_maximo.val(datos.stock_maximo).prop("disabled", bool);
  input.stock_minimo.val(datos.stock_minimo).prop("disabled", bool);

  fila_stock_inicial.addClass("d-none")
};

async function editarFormCategoria(input, datos, accion) {

  let bool = false;
  let modal_tabla = etiquetasModal("TablaCategoria");

  if(accion == "eliminar") {bool = true;}

  input.id_categoria.val(datos.id_categoria).prop("disabled", true);
  input.nombre.val(datos.nombre).prop("disabled", bool);
  input.descripcion.val(datos.descripcion).prop("disabled", bool);
  modal_tabla.modal.modal("hide");
};