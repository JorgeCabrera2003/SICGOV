import * as cargo from "../Handlers/CargoHandler.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"

//MODULO DE PROVEEDORES

//-------INICIALIZACIÖN-------

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
  iniciarValidaciones();
});

//EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$("#btn-CargoForm").on("click", async function () {
  let respuesta = null;
  respuesta = await cargo.EnviarFormulario($(this));

  console.log(respuesta);

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    crearDataTable();
    cargo.CancelarFormulario();
  };
});

$("#btnNuevoCargo").on("click", function () {
  cargo.Limpiar();
  cargo.EditarModal("registrar");
});

//CAPA DE VALIDACIÓN

function iniciarValidaciones() {
  cargo.KeyPressCargo();
  cargo.KeyUpCargo();
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

    cargo.DataTableCargo(arreglo);

  } else {
    console.log("falso");
  }
}

async function rellenar(pos, accion, modulo = "Cargo") {
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

  cargo.EditarFormCargo(datosFila, str_accion)
}

$(document).on('click', '.btn-editar', function () {
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})

$(document).on('click', '.btn-eliminar', function () {
  console.log($(this));
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})