import * as promocion from "../Handlers/PromocionHandler.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";

$(document).ready(function () {
  crearDataTable();
  iniciarValidaciones();
});

$("#btnPromocionForm").on("click", async function () {
  const respuesta = await promocion.EnviarFormulario($(this).text());
  if (typeof respuesta?.resultado === 'number' && respuesta.resultado >= 200 && respuesta.resultado < 300) {
    crearDataTable();
  }
});

$("#btnNuevaPromocion").on("click", function () {
  promocion.LimpiarFormulario();
  promocion.EditarModal("registrar");
});

function iniciarValidaciones() {
  promocion.CapaValidar();
}

async function crearDataTable() {
  const peticion = new FormData();
  peticion.append("peticion", "consultar");

  let json = null;
  try {
    json = await AjaxHelper.enviaAjax(peticion);
  } catch (error) {
    json = { datos: [] };
  }

  const arreglo = Array.isArray(json?.datos) ? json.datos : [];
  promocion.DataTablePrincipal(arreglo);
}

async function rellenar(pos, accion) {
  const linea = $(pos).closest('tr');
  const tabla = $('#tablaPromocion').DataTable();
  const datosFila = tabla.row(linea).data();
  promocion.EditarFormPromocion(datosFila, accion);
}

$(document).on('click', '.btn-editar', function () {
  rellenar($(this), $(this).attr('data-accion'));
});

$(document).on('click', '.btn-eliminar', function () {
  rellenar($(this), $(this).attr('data-accion'));
});
