import * as proveedor from "../Handlers/ProveedorHandler.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"

//MODULO DE INGREDIENTES

//-------INICIALIZACIÖN-------

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
  iniciarValidaciones();
});

//EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$("#btnIngredienteForm").on("click", async function () {
  proveedor.EnviarFormulario($(this).text());
});

$("#btnNuevoProveedor").on("click", function () {
  proveedor.LimpiarFormulario();
  proveedor.EditarModal("registrar");
});

//CAPA DE VALIDACIÓN

function iniciarValidaciones() {
  proveedor.CapaValidar();
  categoriaIngrediente.KeyPressCategoria();
  categoriaIngrediente.KeyUpCategoria();
}

async function crearDataTable(controlador = "") {
  const peticion = new FormData();
  let json = null;
  let arreglo = [];
  peticion.append("peticion", "consultar");

  try {
    json = await AjaxHelper.enviaAjax(peticion);
    arreglo = json.datos;
  } catch (error) {
    arreglo = [];
  }

  if (Array.isArray(arreglo)) {

    proveedor.DataTablePrincipal(arreglo);

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

$(document).on('click', '.btn-editar', function () {
  console.log($(this));
  console.log($(this).attr("data-modulo"));
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})

$(document).on('click', '.btn-eliminar', function () {
  console.log($(this));
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})