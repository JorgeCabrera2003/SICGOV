import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";
import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as ValidadorHelper from "../Helpers/ValidadorHelper.js";
import * as SelectHelper from "../Helpers/SelectHelper.js";
import * as PermisoHelper from "../Helpers/PermisoHelper.js";

//MODULO DE PROVEEDORES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function EtiquetasFormulario(etiquetas) {
  let referencia = null

  const inputRol = {
    id: $('#id_rol'),
    nombre: $('#nombre')
  }

  const spanRol = {
    id: $('#sid_rol'),
    nombre: $('#snombre')
  }

  if (etiquetas === "input") {
    referencia = inputRol
  }

  if (etiquetas === "span") {
    referencia = spanRol
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

function EtiquetasModal(etiqueta) {
  let referencia = null

  const modalRol = {
    modal: $('#modalRol'),
    titulo: $('#modalTitleTextRol'),
    boton: $('#btnRolForm')
  }

  if (etiqueta === "Rol") {
    referencia = modalRol;
  }

  return referencia;
}
//Fin de Interfaz de Acceso

export function EditarModal(operacion) {
  let titulo;
  let boton;
  let etiqueta_modal = EtiquetasModal("Rol");

  if (operacion == 'registrar') {
    titulo = "Nuevo Rol";
    boton = "Nuevo";
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Rol";
    boton = "Actualizar";
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Rol";
    boton = "Borrar";
  }

  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}

export async function EnviarDatos(operacion) {

  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal("Rol");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
  let mensajeConfirmacion = "¿Está seguro de realizar esta acción?";
  let endpoint = "";
  let peticion = new FormData();
  let permisos = [];
  let json;

  //Registrar y Modificar
  if (operacion == "registrar" || operacion == "modificar") {
    let bool_form = true
    if (operacion == "registrar") {
      str_acccion = "registrará";
      accion = "registrar"
    }

    if (operacion == "modificar") {

      if (!ValidadorHelper.ValidarCampo("ID", input.id, span.id)) {
        bool_form = false;
      }

      str_acccion = "actualizará";
      accion = "modificar";
    }

    if (ValidarEnvio() && bool_form) {
      confirmacion = await confirmarAccion(`Se ${str_acccion} un Rol`, mensajeConfirmacion, "question");

      if (confirmacion) {
        permisos = CrearArregloPermisos();
        peticion.append('peticion', accion);
        peticion.append('nombre', input.nombre.val());
        peticion.append('permisos', JSON.stringify(permisos));
        peticion.append('id', input.id.val());
        btn_formulario = true;
        console.log(permisos)
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.")
    }
  } //Fin del Registrar y Modificar
  //Eliminar
  if (operacion == "eliminar") {
    let bool_eliminar = true;

    if (!ValidadorHelper.ValidarCampo("ID", input.id, span.id)) {
      bool_eliminar = false;
    }

    if (bool_eliminar) {
      confirmacion = await confirmarAccion("Se eliminará un Rol", mensajeConfirmacion, "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id', input.id.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      MensajeriaHelper.GenerarMensaje("error", 10000, "Error de Validación", "ID del Rol no es válido.");
    }
  }//Fin del Eliminar

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    console.log("Formulario enviado");
    json = await AjaxHelper.enviaAjax(peticion, endpoint);

    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      modal.modal.modal("hide");
      MensajeriaHelper.GenerarMensaje(json.icon, 10000, json.mensaje, null);
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
    return await EnviarDatos(accion)
  } else {
    console.log("Error, acción no válida")
  }
};

//CAPA DE VALIDACIÓN

export function CapaValidar() {
  KeyUpRol();
  KeyPressRol();
}

function KeyPressRol() {

  let input = EtiquetasFormulario("input");

  $(input.nombre).on("keypress", function (e) { ValidadorHelper.ValidarTecla("Titulo", e); });
}


function KeyUpRol() {
  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");

  $(input.id).on("keyup", function () {
    ValidadorHelper.ValidarCampo("ID", $(this), span.id);
  })

  $(input.nombre).on("keyup", function () {
    ValidadorHelper.ValidarCampo("Titulo", $(this), span.nombre);
  })

}

function ValidarEnvio() {

  let input = EtiquetasFormulario("input");
  let span = EtiquetasFormulario("span");
  let bool = true;

  if (!ValidadorHelper.ValidarCampo("NombrePersona", input.nombre, span.nombre)) {
    bool = false;
  }

  return bool;
}

export async function CargarFuncionesCheckBox() {
  GruposCheckBox();
  MarcarCheckBox();
}

export async function GruposCheckBox() {
  $('.group-checkbox').each(function () {
    const $checkbox = $(this);

    $checkbox.on('change', function () {
      const modulo = $(this).attr('data-modulo');
      const isChecked = $(this).prop('checked');

      $(`.permission-options[data-modulo-string="${modulo}"] .permission-checkbox`).each(function () {
        $(this).prop('checked', isChecked);
      });
    });
  });
}
export async function MarcarCheckBox() {
  // Script para manejar la selección de grupos completos
  $(document).ready(function () {
    // Seleccionar/deseleccionar todos los permisos de un grupo


    // Actualizar el checkbox del grupo cuando cambian los permisos individuales
    $('.permission-checkbox').each(function () {
      $(this).on('change', function () {
        const $groupContainer = $(this).closest('.permission-options');
        const groupId = $groupContainer.attr('data-modulo-string');
        const $groupCheckbox = $(`.group-checkbox[data-modulo="${groupId}"]`);
        const $allCheckboxes = $(`.permission-options[data-modulo-string="${groupId}"] .permission-checkbox`);

        const allChecked = $allCheckboxes.length === $allCheckboxes.filter(':checked').length;
        const someChecked = $allCheckboxes.filter(':checked').length > 0;

        $groupCheckbox.prop('checked', allChecked);
        $groupCheckbox.prop('indeterminate', someChecked && !allChecked);
      });
    });
  });
}

async function RenderPermisoBotones(modulo = "Rol") {
  const permisos = await PermisoHelper.LlamarPermiso("rol");
  let bool = false;
  let btn_eliminar = "";
  let btn_modificar = "";
  let separadorHTML = "";

  if (permisos['rol']['modificar'] != undefined && permisos['rol']['modificar'] == 1) {
    const itemEditar = $('<li>');
    const linkEditar = $('<a>')
      .addClass('dropdown-item btn-editar text-primary')
      .attr('href', '#')
      .attr('data-accion', 0)
      .attr('data-modulo', modulo)
      .html('<i class="fas fa-edit me-2"></i>Editar');
    itemEditar.append(linkEditar);
    btn_modificar = itemEditar;
    bool = true;
  }

  if (permisos['rol']['eliminar'] != undefined && permisos['rol']['modificar'] == 1) {
    const itemEliminar = $('<li>');
    const linkEliminar = $('<a>')
      .addClass('dropdown-item btn-eliminar text-danger')
      .attr('href', '#')
      .attr('data-accion', 1)
      .attr('data-modulo', modulo)
      .html('<i class="fas fa-trash me-2" me-2"></i>Eliminar');
    itemEliminar.append(linkEliminar);
    btn_eliminar = itemEliminar;
    bool = true;
  }

  if (btn_modificar != "" && btn_eliminar != "") {
    const separador = $('<li>').html('<hr class="dropdown-divider">');
    separadorHTML = separador;
  }

  const dropdown = $('<div>').addClass('dropdown');
  const boton = $('<button>').addClass('btn btn-sm btn-light border dropdown-toggle')
    .attr('type', 'button')
    .attr('data-bs-toggle', 'dropdown')
    .html('<i class="fas fa-ellipsis-v me-3"></i>Acciones');

  const menu = $('<ul>').addClass('dropdown-menu');


  menu.append(btn_modificar, separadorHTML, btn_eliminar);
  dropdown.append(boton, menu);

  if (!bool) {
    dropdown.empty(); //Destruye la Etiqueta por si no hay botones que renderizar
  }

  return dropdown.prop('outerHTML');
}

function LimpiarCheckBox(){
  	$('[data-modulo-string]').each(function () {
		const modulo = $(this);

		modulo.find('.form-check-input').each(function () {
			const checkbox = $(this);
			checkbox.attr('data-id-permiso', '');
			checkbox.prop('checked', false);
			checkbox.prop('disabled', false);
		});
	});
}

async function TraerPermiso(id) {
  var datos = new FormData();
  datos.append("peticion", "filtrar_permiso");
  datos.append("parametro", "modulo_id");
  datos.append("id_rol", id);

  return await AjaxHelper.enviaAjax(datos);
}

function ColocarPermisosCheckBox(Arraypermisos) {

  $('[data-modulo-string]').each(function () {
    const modulo = $(this);
    const moduloString = modulo.data('moduloString');
    const permisos = [];

    const permisosModulo = Arraypermisos[moduloString] || {}
    console.log(permisosModulo);
    modulo.find('.form-check-input').each(function () {
      const checkbox = $(this);
      const accion = checkbox.val();  // Variable intermedia
      console.log(permisosModulo[accion]);

      if (permisosModulo[accion]) {
        if (permisosModulo[accion].estado == 1) {
          checkbox.prop('checked', true);
          checkbox.attr('data-id-permiso', permisosModulo[accion].id);

        } else {
          checkbox.prop('checked', false);
          checkbox.attr('data-id-permiso', permisosModulo[accion].id);
        }
        console.log("Encontrado");
      } else {
        checkbox.prop('checked', false);
        checkbox.attr('data-id-permiso', '');
        console.log("Perdido");
      }
      console.log(checkbox.attr('data-id-permiso'));
    });

    if (permisos.length > 0) {
      permisos_modulos.push({
        modulo: moduloString,
        permisos: permisos
      });
    }
  });
}

function CrearArregloPermisos() {

  const datos = []

  $('[data-modulo-string]').each(function () {
    const modulo = $(this);
    const moduloString = modulo.data('moduloString');
    const permisos = [];

    // Obtener todos los checkboxes dentro del módulo
    modulo.find('.form-check-input').each(function () {
      const checkbox = $(this);
      var bool;
      if (checkbox.prop('checked')) {
        bool = 1;
      } else {
        bool = 0;
      }
      permisos.push({
        id: checkbox.data('idPermiso'),
        accion: checkbox.val(),
        estado: bool
      });
    });

    if (permisos.length > 0) {
      datos.push({
        modulo: moduloString,
        permisos: permisos
      });
    }
  });

  return datos;
}


export async function DataTablePrincipal(arreglo) {
  let botones = '';
  botones = await RenderPermisoBotones();

  if ($.fn.DataTable.isDataTable('#tablaRol')) {
    $('#tablaRol').DataTable().destroy();
  }

  $('#tablaRol').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'nombre_rol' },
      {
        data: null,
        render: function () {
          return botones;
        }
      }
    ],
    order: [[1, 'asc']],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
  });
}

export function LimpiarFormulario() {
  let input = EtiquetasFormulario('input');
  let span = EtiquetasFormulario('span');
  let modal = EtiquetasModal('Rol');


  input.id.val("").removeClass("is-valid is-invalid").prop("disabled", true);
  span.id.text("").removeClass("");

  input.nombre.val("").prop("disabled", false);
  span.nombre.text("").removeClass("valid-tooltip-tooltip invalid-tooltip");

  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  modal.boton.prop('disabled', false);
  input = null;
  span = null;
  modal = null;
  LimpiarCheckBox();
}

export async function EditarFormRol(datos, accion) {
  LimpiarFormulario();
  let input = EtiquetasFormulario("input");
  let bool = false;
  let modal = EtiquetasModal("Rol");
  let responsePermisos = [];

  responsePermisos = await TraerPermiso(datos.id_rol);

  ColocarPermisosCheckBox(responsePermisos.permiso);

  if (accion == "eliminar") { bool = true; }

  input.id.val(datos.id_rol).prop("disabled", bool);
  input.nombre.val(datos.nombre_rol).prop("disabled", bool);

  modal.boton.prop('disabled', false);
  EditarModal(accion);
};
