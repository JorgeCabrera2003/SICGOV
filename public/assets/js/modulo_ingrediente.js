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