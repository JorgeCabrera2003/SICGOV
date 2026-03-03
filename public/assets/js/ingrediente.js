//MODULO DE INGREDIENTES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function etiquetasFormulario(etiquetas) {
  let referencia = null

  const inputIngrediente = {
    nombre: $('#nombre'),
    costo_unitario: $('#costo_unitario'),
    unidad_medida: $('#unidad_medida'),
    id_ingrediente: $('#id_ingrediente')
  }

  const spanIngrediente = {
    snombre: $('s#nombre'),
    costo_unitario: $('#scosto_unitario'),
    unidad_medida: $('#sunidad_medida'),
    id_ingrediente: $('#sid_ingrediente')
  }

  if (etiquetas === "input") {
    referencia = inputIngrediente
  }

  if (etiquetas === "span") {
    referencia = spanIngrediente
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

//Interfaz de Acceso a los Elementos(modal)
function etiquetasModal(etiquetas) {
  let referencia = null

  const modalPrincipal = {
    modal: $('#modalIngrediente'),
    titulo: $('#modalTitleTextIngrediente'),
    boton: $('#btnIngredienteForm')
  }

  if (etiquetas === "principal") {
    referencia = modalPrincipal
  }

  return referencia
}
//Fin de Interfaz de Acceso

//Función para editar textos visuales del modal
function editarModal(operacion) {
  let titulo
  let boton
  let etiqueta_modal = null

  if (operacion == 'registrar') {
    titulo = "Nuevo Ingrediente"
    boton = "Nuevo"
    etiqueta_modal = etiquetasModal("principal");
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Ingrediente"
    boton = "Actualizar"
    etiqueta_modal = etiquetasModal("principal");
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Ingrediente"
    boton = "Borrar"
    etiqueta_modal = etiquetasModal("principal");
  }
  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}
//Fin de la Función de editarModal

//Función para manejar el cambio de estado del formulario
function manejarCambioEstado(formularioValido) {
  let input = etiquetasFormulario("input");
  let span = etiquetasFormulario("span");
  let modal = etiquetasModal("principal");
  const accion = modal.boton.text();

  if (accion === "Eliminar") {
    // Para eliminar solo validamos el ID
    const idValido = validarKeyUp(/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/, input.id_ingrediente.val(), span.id_ingrediente, '');
    modal.boton.prop('disabled', !idValido);
  } else {
    // Para registrar y modificar validamos todos los campos
    modal.boton.prop('disabled', !formularioValido);
  }
  modal = null;
  input = null;
  span = null;
}

$(document).ready(function () {
  crearDataTable();
  registrarEntrada();
  capaValidar();

  // Inicializar sistema de validación con callback
  SistemaValidacion.inicializar(etiquetasFormulario('input'), manejarCambioEstado);

  // Validar estado inicial del formulario
  manejarCambioEstado(false);
});

async function enviarDatos(operacion) {

  let input = etiquetasFormulario('input');
  let span = etiquetasFormulario('span');
  let modal = etiquetasModal("principal");

  let confirmacion = false;
  let str_acccion = "";
  let accion = "";
  let btn_formulario = false;
  let estado_peticion = null;
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
      confirmacion = await confirmarAccion(`Se ${str_acccion} un Ingrediente`, "¿Está seguro de realizar la acción?", "question");

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
      confirmacion = await confirmarAccion("Se eliminará un Ingrediente", "¿Está seguro de realizar la acción?", "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('id_ingrediente', input.id_ingrediente.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      mensajes("error", 10000, "Error de Validación", "El ID del ente no es válido.");
    }
  }//Fin del Eliminar

  if (btn_formulario) {
    modal.boton.prop('disabled', true);
    json = await enviaAjax(peticion);

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
  let accion = null;
  const MANEJADOR = {
    'Nuevo': 'registrar',
    'Actualizar': 'modificar',
    'Borrar': 'eliminar'
  }
  const DEFAULT = null

  accion = MANEJADOR[$(this).text()] || DEFAULT

  if (accion != null) {
    enviarDatos(accion)
  } else {
    console.log("Error, acción no válida")
  }
});

$("#btnNuevoIngrediente").on("click", function () {
  limpia();
  editarModal("registrar")
  // El botón se habilita automáticamente mediante el callback cuando los campos sean válidos
});

//Iniciar Tabla de Eliminadas (Papelera) usando evento click del botón
$("#btn-consultar-eliminados").on("click", function () {
  iniciarTablaEliminadas();
});

// Aplicar capitalización automática cuando el modal se muestra
$('#modalIngrediente').on('shown.bs.modal', function () {
  // Forzar validación inicial cuando se abre el modal
  setTimeout(() => {
    SistemaValidacion.validarFormulario(etiquetasFormulario('input'));
  }, 100);
});

async function vistaPermiso() {
  let botones = "";
  let btn_modificar = "";
  let btn_eliminar = "";
  let permisos = [];
  /* 
    try {
      peticion = new FormData();
      peticion.append('permisos', 'permisos');
      json = await enviaAjax(peticion);
      json = JSON.parse(json);
      permisos = json.permisos;
    } catch (error) {
      botones = "";
      console.log(error);
      return botones;
    }
  
    if (Array.isArray(permisos) || Object.keys(permisos).length == 0 || permisos == null) {
      btn_modificar = "";
      btn_eliminar = "";
    } else {
      if (permisos['ente']['modificar']['estado'] == '1') {
        btn_modificar = `<button onclick="rellenar(this, 0)" class="btn btn-update modificar">
                          <i class="fa-solid fa-pen-to-square"></i>
                        </button>`;
      }
  
      if (permisos['ente']['eliminar']['estado'] == '1') {
        btn_eliminar = `<button onclick="rellenar(this, 1)" class="btn btn-danger eliminar">
                          <i class="fa-solid fa-trash"></i>
                        </button>`;
      }
    }*/

  btn_modificar = `<button onclick="rellenar(this, 0)" class="btn btn-primary modificar">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>`;

  btn_eliminar = `<button onclick="rellenar(this, 1)" class="btn btn-danger eliminar">
                        <i class="fa-solid fa-trash"></i>
                      </button>`;
  botones = btn_modificar + btn_eliminar;
  console.log(botones)
  return botones;
}

/* 
async function botonReactivar() {
  let botones = "";
  let permisos = [];

  try {
    peticion = new FormData();
    peticion.append('permisos', 'permisos');
    json = await enviaAjax(peticion);
    json = JSON.parse(json);
    permisos = json.permisos;
  } catch (error) {
    botones = "";
    console.log(error);
    return botones;
  }

  if (Array.isArray(permisos) || Object.keys(permisos).length == 0 || permisos == null) {
    btn_reactivar = "";
  } else {
    if (permisos['ente']['reactivar']['estado'] == '1') {
      btn_reactivar = `<button onclick="reactivarEnte(this)" class="btn btn-success reactivar">
                  <i class="fa-solid fa-recycle"></i>
                  </button>`;
    }
  }
  console.log(btn_reactivar)
  return btn_reactivar;
}
*/
function capaValidar() {
  let input = etiquetasFormulario("input")
  // Validación con formato en tiempo real
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

function validarenvio() {
  return SistemaValidacion.validarFormulario(etiquetasFormulario('input'));
}

async function crearDataTable() {
  let peticion = new FormData();
  let json = null;
  let arreglo = [];
  let botones = '';
  botones = await vistaPermiso();

  try {
    peticion.append('peticion', 'consultar');
    json = await enviaAjax(peticion);
    arreglo = json.datos;
  } catch (error) {
    arreglo = [];
  }

  if ($.fn.DataTable.isDataTable('#tablaIngrediente')) {
    $('#tablaIngrediente').DataTable().destroy();
  }

  $('#tablaIngrediente').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      {
        data: 'id_ingrediente',
        visible: false
      },
      { data: 'nombre_ingrediente' },
      { data: 'unidad_medida' },
      { data: 'precio_unitario' },
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

async function iniciarTablaEliminadas() {
  let peticion = new FormData();
  let json = null;
  let arreglo = [];
  let boton = '';

  try {
    peticion.append('peticion', 'consultar_eliminadas');
    json = await enviaAjax(peticion);
    console.log(json);
    json = JSON.parse(json);
    arreglo = json.datos;
    boton = await botonReactivar();
    $("#modalEliminadas").modal("show");
  } catch (error) {
    arreglo = [];
    console.log(error);
  }


  if ($.fn.DataTable.isDataTable('#tablaEliminadas')) {
    $('#tablaEliminadas').DataTable().destroy();
  }

  $('#tablaEliminadas').DataTable({
    data: arreglo,
    columns: [
      {
        data: 'id',
        visible: false
      },
      { data: 'nombre' },
      { data: 'nombre_responsable' },
      { data: 'telefono' },
      { data: 'direccion' },
      { data: 'tipo_ente' },
      {
        data: null,
        render: function () {
          return boton;
        }
      }
    ],
    order: [[1, 'asc']],
    language: { url: idiomaTabla }
  });
}

function limpia() {
  SistemaValidacion.limpiarValidacion(etiquetasFormulario('input'));

  let input = etiquetasFormulario('input')
  let span = etiquetasFormulario('span')
  
  input.id_ingrediente.val("").prop("readOnly", true)
  input.nombre.val("").prop("readOnly", false)
  input.costo_unitario.val("").prop("readOnly", false)
  input.unidad_medida.val("default").prop("disabled", false)

  // Deshabilitar el botón al limpiar (se habilitará automáticamente cuando los campos sean válidos)
  $('#enviar').prop('disabled', true);
  input = null;
}

function rellenar(pos, accion) {
  limpia();
  let input = etiquetasFormulario('input')
  const linea = $(pos).closest('tr');
  const tabla = $('#tablaIngrediente').DataTable();
  const datosFila = tabla.row(linea).data();

  // Usar los datos directamente de DataTable (más confiable)
  input.id_ingrediente.val(datosFila.id_ingrediente);
  input.nombre.val(capitalizarTexto(datosFila.nombre_ingrediente));
  input.costo_unitario.val((datosFila.precio_unitario));
  buscarSelect(input.unidad_medida, datosFila.unidad_medida, "value");

  if (accion == 0) {
    editarModal("modificar")
  } else {
    input.id_ingrediente.prop("readOnly", true);
    input.nombre.prop("readOnly", true);
    input.costo_unitario.prop("readOnly", true);
    input.unidad_medida.prop("disabled", true);
    editarModal("eliminar")
  }

  // Habilitar el botón inmediatamente para Modificar/Eliminar ya que los datos vienen pre-validados
  $('#btnIngredienteForm').prop('disabled', false);
}
//Fin Manejo de Envío de Datos

async function reactivarEnte(boton) {
  const linea = $(boton).closest('tr');
  const tabla = $('#tablaEliminadas').DataTable();
  const datosFila = tabla.row(linea).data();
  const id = datosFila.id;
  const regex = new RegExp(/^[A-Z0-9]{3,5}[A-Z0-9]{3}[0-9]{8}[0-9]{0,6}[0-9]{0,2}$/)
  let peticion = new FormData();
  let json = null;


  confirmacion = await confirmarAccion(`Se reactivará un Ente`, "¿Está seguro de realizar la acción?", "question");

  if (confirmacion) {

    if (!regex.test(id)) {
      console.log('Error');
      return;
    }
    peticion.append('peticion', 'reactivar');
    peticion.append('id_ente', id);
    json = await enviaAjax(peticion);
    json = JSON.parse(json)
  }

  if (json != null) {
    if (typeof json.resultado === 'number' && (json.resultado >= 200 && json.resultado <= 299)) {
      mensajes(json.icon, 10000, json.mensaje, null);
      crearDataTable();
      iniciarTablaEliminadas();
    }
  }
}