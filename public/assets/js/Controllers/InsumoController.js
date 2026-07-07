import * as insumo from "../Handlers/InsumoHandler.js";
import * as categoriaInsumo from "../Handlers/CategoriaInsumoHandler.js";
import * as suministrarInsumo from "../Handlers/SuministrarInsumoHandler.js";
import * as asociarProveedor from "../Handlers/AsociarProveedorHandler.js";
import * as movimientoInsumo from "../Handlers/MovimientoInsumoHandler.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";

//MODULO DE INGREDIENTES

//-------INICIALIZACIÖN-------

$(document).ready(function () {
  crearDataTable();
  AjaxHelper.registrarEntrada();
  iniciarValidaciones();
});

//EVENTOS CLICK DE LOS BOTONES DE LA INTERFAZ
$("#btnInsumoForm").on("click", async function () {
  let respuesta = null;
  respuesta = await insumo.EnviarFormulario($(this).text());

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    await crearDataTable("insumos");
  };
});

$("#btnSuministrarInsumoForm").on("click", async function () {
  let respuesta = null;
  respuesta = await suministrarInsumo.EnviarFormulario($(this).text());
  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    await crearDataTable("insumos");
  };
});

$("#btnNuevoInsumo").on("click", function () {
  insumo.LimpiarFormulario();
  insumo.EditarModal("registrar");
});

$("#btn-ModalCategorias").on("click", async function () {
  await crearDataTable("categoria-insumo");
  categoriaInsumo.MostrarModalTabla();
})

//Iniciar Modal Formulario de Categoría de Insumo
$("#btnNuevaCategoria").on("click", function () {
  categoriaInsumo.FormNuevaCategoria();
})

$("#btn-CategoriaCancel").on("click", function () {
  categoriaInsumo.CancelarFormulario();
})

$("#btn-agregarProveedor").on("click", function () {
  asociarProveedor.AgregarFilaInput();
})

$("#btn-CategoriaForm").on("click", async function () {
  let respuesta = null;
  respuesta = await categoriaInsumo.EnviarFormulario($(this));

  if (typeof respuesta.resultado === 'number' && (respuesta.resultado >= 200 && respuesta.resultado <= 299)) {
    await crearDataTable("categoria-insumo");
    categoriaInsumo.MostrarModalTabla();
  };
})
//CAPA DE VALIDACIÓN

function iniciarValidaciones() {
  insumo.CapaValidar();
  categoriaInsumo.KeyPressCategoria();
  categoriaInsumo.KeyUpCategoria();
  suministrarInsumo.CapaValidar();
}

async function crearDataTable(controlador = "insumos") {
  const MODULOS = {
    'insumos': 'Insumo',
    'categoria-insumo': 'CategoriaInsumo',
  }
  const DEFAULT = null
  let modulo = MODULOS[controlador] || DEFAULT;
  const peticion = new FormData();
  let json = null;
  let arreglo = [];
  let endpoint = "?page=" + modulo;
  peticion.append("peticion", "consultar");
  peticion.append("modulo", modulo)

  try {
    json = await AjaxHelper.enviaAjax(peticion, endpoint);
    arreglo = json.datos;
  } catch (error) {
    arreglo = [];
  }

  if (Array.isArray(arreglo)) {
    console.log("arreglo");
    if (controlador === "insumos") {
      insumo.DataTablePrincipal(arreglo);
    }

    if (controlador === "categoria-insumo") {
      categoriaInsumo.DataTableCategoria(arreglo);
    }
  }
}

async function rellenar(pos, accion, modulo = "Insumo") {
  let str_accion = "";
  const linea = $(pos).closest('tr');
  const tabla = $('#tabla' + modulo).DataTable();
  const datosFila = tabla.row(linea).data();
  let abrirModalInsumo = true;

  if (accion == 0) {
    str_accion = "modificar";
  }

  if (accion == 1) {
    str_accion = "eliminar";
  }

  if (accion == 2) {
    abrirModalInsumo = false;
    suministrarInsumo.EditarFormSuministrar(datosFila);
  }

  if (accion == 3) {
    abrirModalInsumo = false;
    movimientoInsumo.CargarModalTabla(datosFila);
  }

  if (accion == 4) {
    abrirModalInsumo = false;
    asociarProveedor.CargarModalTabla(datosFila);
  }

  if (modulo == "Insumo" && abrirModalInsumo) {
    await insumo.EditarFormInsumo(datosFila, str_accion)
  }

  if (modulo == "Categoria") {
    await categoriaInsumo.EditarFormCategoria(datosFila, str_accion)
  }
  // Habilitar el botón inmediatamente para Modificar/Eliminar ya que los datos vienen pre-validados
}

$(document).on('click', '.btn-editar', function () {
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})

$(document).on('click', '.btn-eliminar', function () {
  rellenar($(this), $(this).attr("data-accion"), $(this).attr("data-modulo"))
})

$(document).on('click', '.btn-suministrar', function () {
  rellenar($(this), $(this).attr("data-accion"), "Insumo")
})

$(document).on('click', '.btn-movimiento', function () {
  rellenar($(this), $(this).attr("data-accion"), "Insumo")
})

$(document).on('click', '.btn-asociar', function () {
  rellenar($(this), $(this).attr("data-accion"), "Insumo")
})