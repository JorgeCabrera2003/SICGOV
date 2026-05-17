import * as proveedor from "../Handlers/ProveedorHandler.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"

//MODULO DE PROVEEDORES

//-------INICIALIZACIÖN-------

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
  iniciarValidaciones();
});

//EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$("#btnProveedorForm").on("click", async function () {
  let respuesta = null;
  respuesta = await proveedor.EnviarFormulario($(this).text());

  console.log(respuesta);

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    crearDataTable();
  };
});

$("#btnNuevoProveedor").on("click", function () {
  proveedor.LimpiarFormulario();
  proveedor.EditarModal("registrar");
});

//CAPA DE VALIDACIÓN

function iniciarValidaciones() {
  proveedor.CapaValidar();
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

async function rellenar(pos, accion, modulo = "Proveedor") {
  const linea = $(pos).closest('tr');
  const tabla = $('#tabla' + modulo).DataTable();
  const datosFila = tabla.row(linea).data();

  proveedor.EditarFormProveedor(datosFila, accion)
}

$(document).on('click', '.btn-editar', function () {
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})

$(document).on('click', '.btn-eliminar', function () {
  console.log($(this));
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})