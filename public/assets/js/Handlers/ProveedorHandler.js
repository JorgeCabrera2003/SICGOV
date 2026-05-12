import * as mensajeria from "../Helpers/MensajeriaHelper.js"
import * as AjaxHelper from "../Helpers/AjaxHelper.js"
import * as ValidarHelper from "../Helpers/ValidadorHelper.js"

//MODULO DE PROVEEDORES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputProveedor = {
    tipo_documento: $('#tipo_doc'),
    documento_legal: $('#documento_legal'),
    nombre: $('#nombre'),
    prefijo_telefono: $('#prefijo_telefono'),
    telefono: $('#telefono'),
    correo: $('#correo'),
    direccion: $('#direccion')
  }

  const spanproveedor = {
    tipo_documento: $('#stipo_doc'),
    documento_legal: $('#sdocumento_legal'),
    nombre: $('#snombre'),
    prefijo_telefono: $('#sprefijo_telefono'),
    telefono: $('#stelefono'),
    correo: $('#scorreo'),
    direccion: $('#sdireccion')
  }

  if (etiquetas === "input") {
    referencia = inputProveedor
  }

  if (etiquetas === "span") {
    referencia = spanproveedor
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalProveedor = {
    modal: $('#modalProveedor'),
    titulo: $('#modalTitleTextProveedor'),
    boton: $('#btnProveedorForm')
  }

  if (etiqueta === "Proveedor") {
    referencia = modalProveedor;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Proveedor");

  if (operacion == 'registrar') {
    titulo = "Nuevo Proveedor";
    boton = "Nuevo";
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Proveedor";
    boton = "Actualizar";
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Proveedor";
    boton = "Borrar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

export async function EnviarDatos(operacion) {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal(modulo);

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();

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
      confirmacion = await confirmarAccion(`Se ${str_acccion} un Proveedor`, mensajeConfirmacion, "question");

      if (confirmacion) {
        peticion.append('peticion', accion);
        peticion.append('nombre', input.nombre.val());
        peticion.append('unidad_medida', input.unidad_medida.val());
        peticion.append('costo_unitario', input.costo_unitario.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      mensajeria.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.")
    }
  } //Fin del Registrar y Modificar
  //Eliminar
  if (operacion == "eliminar") {

    if (validarKeyUp(/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/, input.id_ingrediente, span.id_ingrediente, '')) {
      confirmacion = await confirmarAccion("Se eliminará un Proveedor", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_ingrediente', input.id_ingrediente.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      mensajeria.GenerarMensaje("error", 10000, "Error de Validación", "El ID del Proveedor no es válido.");
    }
  }//Fin del Eliminar

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await enviaAjax(peticion, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      modal.modal.modal("hide");
      DataTablePrincipal();
      mensajeria.GenerarMensaje(json.icon, 10000, json.mensaje, null);
    }
    modal.boton.prop('disabled', false);
  }

  if (!confirmacion) {
    modal.boton.prop('disabled', false);
  }

  input = null;
  modal = null;

  return json;
}

//Manejo de envio de datos desde el modal
export async function EnviarFormulario(btn_string) {
  let accion = null;
  const MANEJADOR = {
    'Nuevo': 'registrar',
    'Actualizar': 'modificar',
    'Borrar': 'eliminar'
  }
  const DEFAULT = null

  accion = MANEJADOR[btn_string] || DEFAULT

  if (accion != null) {
    return await enviarDatos(accion)
  } else {
    console.log("Error, acción no válida")
  }
};

//CAPA DE VALIDACIÓN

export function CapaValidar() {
  KeyPressProveedor();
}

function KeyPressProveedor() {
  let input = EtiquetasFormulario("input")
  input.nombre.on("keypress", function (e) {
    validarKeyPress(/^[0-9 a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ -.\b]*$/, e);
  });

  input.costo_unitario.on("keypress", function (e) {
    validarKeyPress(/^[0-9.\b]*$/, e);
  });

  // Aplicar capitalización en tiempo real para nombre y responsable
  input.nombre.on("input", function () {
    // Capitalizar mientras escribe (opcional)
    const valor = $(this).val();
    if (valor.length === 1) {
      $(this).val(valor.toUpperCase());
    }
  });
}

function Validarenvio(modulo = "Proveedor") {

}

async function RenderPermisoBotones(modulo = "Proveedor") {

  const dropdown = $('<div>').addClass('dropdown');
  const boton = $('<button>').addClass('btn btn-sm btn-light border dropdown-toggle')
    .attr('type', 'button')
    .attr('data-bs-toggle', 'dropdown')
    .html('<i class="fas fa-ellipsis-v me-3"></i>Acciones');

  const menu = $('<ul>').addClass('dropdown-menu');
  const separador = $('<li>').html('<hr class="dropdown-divider">');

  const itemEditar = $('<li>');
  const linkEditar = $('<a>')
    .addClass('dropdown-item btn-editar text-primary')
    .attr('href', '#')
    .attr('data-accion', 0)
    .attr('data-modulo', modulo)
    .html('<i class="fas fa-edit me-2"></i>Editar');
  itemEditar.append(linkEditar);

  const itemEliminar = $('<li>');
  const linkEliminar = $('<a>')
    .addClass('dropdown-item btn-eliminar text-danger')
    .attr('href', '#')
    .attr('data-accion', 1)
    .attr('data-modulo', modulo)
    .html('<i class="fas fa-trash me-2" me-2"></i>Eliminar');
  itemEliminar.append(linkEliminar);

  menu.append(itemEditar, separador, itemEliminar);
  dropdown.append(boton, menu);

  console.log(dropdown)
  return dropdown.prop('outerHTML');
}


export async function DataTablePrincipal(arreglo) {
  let botones = '';
  botones = await RenderPermisoBotones();

  if ($.fn.DataTable.isDataTable('#tablaProveedor')) {
    $('#tablaProveedor').DataTable().destroy();
  }

  $('#tablaProveedor').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'documento_legal' },
      { data: 'nombre' },
      { data: 'telefono' },
      { data: 'correo' },
      { data: 'direccion' },
      {
        data: null,
        render: function () {
          return botones;
        }
      }
    ],
    order: [[1, 'asc']],
    language: { url: idiomaTabla }
  });
}

export function LimpiarFormulario() {
  SistemaValidacion.limpiarValidacion(EtiquetasFormulario('input'));

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal('Proveedor');

  input.tipo_documento.val("default").prop("disabled", false);
  input.documento_legal.val("").prop("disabled", true);
  input.nombre.val("").prop("disabled", false);
  input.prefijo_telefono.val("default").prop("disabled", false);
  input.correo.val("").prop("disabled", false);
  input.direccion.val("").prop("disabled", false);
  
  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', true);
  input = null;
  span = null;
  modal = null;
}

export async function EditarFormProveedor(datos, accion) {
  LimpiarFormulario();
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal = EtiquetasModal("Proveedor");

  if (accion == "eliminar") { bool = true; }

  input.id_ingrediente.val(datos.id_ingrediente).prop("disabled", true);
  input.nombre.val(datos.nombre_ingrediente).prop("disabled", bool);
  input.costo_unitario.val(datos.precio_unitario).prop("disabled", bool);
  input.unidad_medida.prop("disabled", bool);
  input.stock_inicial.val(datos.stock_actual).prop("disabled", true);
  input.stock_maximo.val(datos.stock_maximo).prop("disabled", bool);
  input.stock_minimo.val(datos.stock_minimo).prop("disabled", bool);

  fila_stock_inicial.addClass("d-none")
  modal.boton.prop('disabled', false);
  EditarModal(accion);
};
