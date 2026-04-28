import * as ingrediente from "./ingrediente.js"
import * as categoriaIngrediente from "./categoria_ingrediente.js"

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

  return referencia;
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
  ingrediente.EnviarFormulario($(this).text());
});

$("#btnNuevoIngrediente").on("click", function () {
 ingrediente.LimpiarFormulario();
 ingrediente.EditarModal("registrar");
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
  categoriaIngrediente.FormNuevaCategoria();
})

$("#btn-CategoriaCancel").on("click", function () {
  categoriaIngrediente.CancelarFormulario();
})

//CAPA DE VALIDACIÓN

function capaValidar() {
  ingrediente.CapaValidar();
  categoriaIngrediente.KeyPressCategoria();
}

function alidarenvio(modulo = "Ingrediente") {
  return SistemaValidacion.validarFormulario(etiquetasFormulario('input-' + modulo));
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
      ingrediente.DataTablePrincipal(arreglo);
    }

    if (controlador === "categoria-ingrediente") {
      categoriaIngrediente.DataTableCategoria(arreglo);
    }
  } else {
    console.log("falso");
  }
}

async function rellenar(pos, accion, modulo = "Ingrediente") {
  let str_accion = "";
  const linea = $(pos).closest('tr');
  const tabla = $('#tabla' + modulo).DataTable();
  const datosFila = tabla.row(linea).data();

  console.log(datosFila);
  if (accion == 0) {
    str_accion = "modificar";
  }

  if (accion == 1) {
    str_accion = "eliminar";
  }

  if (modulo == "Ingrediente") {
    await ingrediente.EditarFormIngrediente(datosFila, str_accion)
  }

  if (modulo == "Categoria") {
    await categoriaIngrediente.EditarFormCategoria(datosFila, str_accion)
  }
  // Habilitar el botón inmediatamente para Modificar/Eliminar ya que los datos vienen pre-validados
}

$(document).on('click', '.btn-editar', function(){
  console.log($(this));
  console.log($(this).attr("data-modulo"));
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})

$(document).on('click', '.btn-eliminar', function(){
  console.log($(this));
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})