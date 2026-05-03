import * as ingrediente from "./ingrediente.js"
import * as categoriaIngrediente from "./categoria_ingrediente.js"
import * as AjaxHelper from "./Helpers/AjaxHelper.js"

//MODULO DE INGREDIENTES

//-------INICIALIZACIÖN-------

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
  iniciarValidaciones();
});

//EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$("#btnIngredienteForm").on("click", async function () {
  ingrediente.EnviarFormulario($(this).text());
});

$("#btnNuevoIngrediente").on("click", function () {
 ingrediente.LimpiarFormulario();
 ingrediente.EditarModal("registrar");
});

$("#btn-ModalCategorias").on("click", async function () {
  await crearDataTable("categoria-ingrediente");
  categoriaIngrediente.MostrarModalTabla();
})

//Iniciar Modal Formulario de Categoría de Ingrediente
$("#btnNuevaCategoria").on("click", function () {
  categoriaIngrediente.FormNuevaCategoria();
})

$("#btn-CategoriaCancel").on("click", function () {
  categoriaIngrediente.CancelarFormulario();
})

$("#btn-CategoriaForm").on("click", function () {
  categoriaIngrediente.EnviarFormulario($(this));
})
//CAPA DE VALIDACIÓN

function iniciarValidaciones() {
  ingrediente.CapaValidar();
  categoriaIngrediente.KeyPressCategoria();
  categoriaIngrediente.KeyUpCategoria();
}

async function crearDataTable(controlador = "ingredientes") {
  const peticion = new FormData();
  let json = null;
  let arreglo = [];
  let endpoint = "?page=" + controlador;
  peticion.append("peticion", "consultar");

  try {
    json = await AjaxHelper.enviaAjax(peticion, endpoint);
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