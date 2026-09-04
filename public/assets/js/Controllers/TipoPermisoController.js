import * as tipoPermiso from "../Handlers/TipoPermisoHandler.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"

//MODULO DE PROVEEDORES

//-------INICIALIZACIÖN-------

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
  iniciarValidaciones();
});

//EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$("#btn-TipoPermisoForm").on("click", async function () {
  let respuesta = null;
  respuesta = await tipoPermiso.EnviarFormulario($(this));

  console.log(respuesta);

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    crearDataTable();
    tipoPermiso.CancelarFormulario();
  };
});

$("#btnNuevoTipo").on("click", function () {
  tipoPermiso.Limpiar();
  tipoPermiso.EditarModal("registrar");
});

//CAPA DE VALIDACIÓN

function iniciarValidaciones() {
  tipoPermiso.KeyPressTipoPermiso();
  tipoPermiso.KeyUpTipoPermiso();
}

async function crearDataTable(controlador = "") {
  const peticion = new FormData();
  let json = null;
  let arreglo = [];
  peticion.append("peticion", "consultar");

  try {
    json = await AjaxHelper.enviaAjax(peticion, '?page=TipoPermiso');
    arreglo = json.datos;
  } catch (error) {
    arreglo = [];
  }

  if (Array.isArray(arreglo)) {

    tipoPermiso.DataTableTipoPermiso(arreglo);

  } else {
    console.log("falso");
  }
}

async function rellenar(pos, accion, modulo = "TipoPermiso") {
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

  tipoPermiso.EditarFormTipoPermiso(datosFila, str_accion)
}

$(document).on('click', '.btn-editar', function () {
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})

$(document).on('click', '.btn-eliminar', function () {
  console.log($(this));
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})