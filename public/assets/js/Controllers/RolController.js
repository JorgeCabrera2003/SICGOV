import * as rol from "../Handlers/RolHandler.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"

//MODULO DE PROVEEDORES

//-------INICIALIZACIÖN-------

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
  iniciarValidaciones();
  rol.CargarFuncionesCheckBox();
});

//EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$("#btnRolForm").on("click", async function () {
  let respuesta = null;
  respuesta = await rol.EnviarFormulario($(this).text());

  console.log(respuesta);

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    crearDataTable();
  };
});

$("#btnNuevoRol").on("click", function () {
  rol.LimpiarFormulario();
  rol.EditarModal("registrar");
});

//CAPA DE VALIDACIÓN

function iniciarValidaciones() {
  rol.CapaValidar();
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

    rol.DataTablePrincipal(arreglo);

  } else {
    console.log("falso");
  }
}

async function rellenar(pos, accion, modulo = "Rol") {
  const linea = $(pos).closest('tr');
  const tabla = $('#tabla' + modulo).DataTable();
  const datosFila = tabla.row(linea).data();
  let str_accion = "";
  if(accion == 0){
    str_accion = "modificar";
  } else {
    str_accion = "eliminar";
  }

  rol.EditarFormRol(datosFila, str_accion)
}

$(document).on('click', '.btn-editar', function () {
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})

$(document).on('click', '.btn-eliminar', function () {
  console.log($(this));
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})