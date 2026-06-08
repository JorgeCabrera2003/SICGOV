import * as categoriaInsumo from "../Handlers/CategoriaInsumoHandler.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"

//MODULO DE PROVEEDORES

//-------INICIALIZACIÖN-------

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
  iniciarValidaciones();
});

//EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$("#btn-CategoriaForm").on("click", async function () {
  let respuesta = null;
  respuesta = await categoriaInsumo.EnviarFormulario($(this));

  console.log(respuesta);

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    crearDataTable();
    categoriaInsumo.CancelarFormulario();
  };
});

$("#btnNuevaCategoria").on("click", function () {
  categoriaInsumo.Limpiar();
  categoriaInsumo.EditarModal("registrar");
});

//CAPA DE VALIDACIÓN

function iniciarValidaciones() {
  categoriaInsumo.KeyPressCategoria();
  categoriaInsumo.KeyUpCategoria();
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

    categoriaInsumo.DataTableCategoria(arreglo);

  } else {
    console.log("falso");
  }
}

async function rellenar(pos, accion, modulo = "Categoria") {
  const linea = $(pos).closest('tr');
  const tabla = $('#tabla' + modulo).DataTable();
  const datosFila = tabla.row(linea).data();
  let str_accion = null;

  if (accion == 0) {
    str_accion = "modificar";
  }

  if (accion == 1) {
    str_accion = "eliminar";
  }

  categoriaInsumo.EditarFormCategoria(datosFila, str_accion)
}

$(document).on('click', '.btn-editar', function () {
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})

$(document).on('click', '.btn-eliminar', function () {
  console.log($(this));
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})