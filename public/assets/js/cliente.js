//MODULO DE CLIENTES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function etiquetasFormulario(etiquetas) {
  let referencia = null

  const inputCliente = {
    cedula: $('#cedula'),
    nombre: $('#nombre'),
    apellido: $('#apellido'),
    fecha_nacimiento: $('#fecha_nacimiento'),
    telefono: $('#telefono'),
    correo: $('#correo'),
    direccion: $('#direccion'),
    sexo: $('#sexo')
  }

  const spanCliente = {
    scedula: $('#scedula'),
    snombre: $('#snombre'),
    sapellido: $('#sapellido'),
    sfecha_nacimiento: $('#sfecha_nacimiento'),
    stelefono: $('#stelefono'),
    scorreo: $('#scorreo'),
    sdireccion: $('#sdireccion'),
    ssexo: $('#ssexo')
  }

  if (etiquetas === "input") {
    referencia = inputCliente
  }

  if (etiquetas === "span") {
    referencia = spanCliente
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

//Interfaz de Acceso a los Elementos(modal)
function etiquetasModal(etiquetas) {
  let referencia = null

  const modalPrincipal = {
    modal: $('#modalCliente'),
    titulo: $('#modalTitleTextCliente'),
    boton: $('#btnClienteForm')
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
    titulo = "Nuevo Cliente"
    boton = "Nuevo"
    etiqueta_modal = etiquetasModal("principal");
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Cliente"
    boton = "Actualizar"
    etiqueta_modal = etiquetasModal("principal");
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Cliente"
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

  if (accion === "Borrar") {
    // Para eliminar solo validamos la cédula
    const cedulaValida = validarKeyUp(/^[V|E|J|G|v|e|j|g][0-9]{5,9}$/, input.cedula.val(), span.scedula, '');
    modal.boton.prop('disabled', !cedulaValida);
  } else {
    // Para registrar y modificar validamos todos los campos requeridos
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
    }

    if (validarenvio()) {
      confirmacion = await confirmarAccion(`Se ${str_acccion} un Cliente`, "¿Está seguro de realizar la acción?", "question");

      if (confirmacion) {
        peticion.append('peticion', accion);
        peticion.append('cedula', input.cedula.val());
        peticion.append('nombre', input.nombre.val());
        peticion.append('apellido', input.apellido.val());
        peticion.append('fecha_nacimiento', input.fecha_nacimiento.val());
        peticion.append('telefono', input.telefono.val());
        peticion.append('correo', input.correo.val());
        peticion.append('direccion', input.direccion.val());
        peticion.append('sexo', input.sexo.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      mensajes("error", 10000, "Error de Validación", "Por favor corrija los errores en el formulario antes de enviar.");
    }
  } //Fin del Registrar y Modificar
  
  //Eliminar
  if (operacion == "eliminar") {

    if (validarKeyUp(/^[V|E|J|G|v|e|j|g][0-9]{5,9}$/, input.cedula, span.scedula, '')) {
      confirmacion = await confirmarAccion("Se eliminará un Cliente", "¿Está seguro de realizar la acción?", "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('cedula', input.cedula.val());
        btn_formulario = true;
      }
    } else {
      btn_formulario = false;
      mensajes("error", 10000, "Error de Validación", "La cédula no es válida.");
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
$("#btnClienteForm").on("click", async function () {
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

$("#btnNuevoCliente").on("click", function () {
  limpia();
  editarModal("registrar")
  // El botón se habilita automáticamente mediante el callback cuando los campos sean válidos
});

// Aplicar capitalización automática cuando el modal se muestra
$('#modalCliente').on('shown.bs.modal', function () {
  // Forzar validación inicial cuando se abre el modal
  setTimeout(() => {
    SistemaValidacion.validarFormulario(etiquetasFormulario('input'));
  }, 100);
});

async function vistaPermiso() {
  let botones = "";
  let btn_modificar = "";
  let btn_eliminar = "";

  btn_modificar = `<button onclick="rellenar(this, 0)" class="btn btn-primary modificar">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>`;

  btn_eliminar = `<button onclick="rellenar(this, 1)" class="btn btn-danger eliminar">
                        <i class="fa-solid fa-trash"></i>
                      </button>`;
  botones = btn_modificar + "&nbsp;" + btn_eliminar;
  return botones;
}

function capaValidar() {
  let input = etiquetasFormulario("input")
  
  input.cedula.on("keypress", function (e) {
    validarKeyPress(/^[V|E|J|G|v|e|j|g|0-9]*$/, e);
  });

  input.nombre.on("keypress", function (e) {
    validarKeyPress(/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ \b]*$/, e);
  });

  input.apellido.on("keypress", function (e) {
    validarKeyPress(/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ \b]*$/, e);
  });

  input.telefono.on("keypress", function (e) {
    validarKeyPress(/^[0-9\b]*$/, e);
  });

  // Aplicar capitalización en tiempo real
  input.nombre.on("input", function () {
    const valor = $(this).val();
    if (valor.length === 1) {
      $(this).val(valor.toUpperCase());
    }
  });

  input.apellido.on("input", function () {
    const valor = $(this).val();
    if (valor.length === 1) {
      $(this).val(valor.toUpperCase());
    }
  });

  input.cedula.on("input", function () {
    let valor = $(this).val();
    if (valor.length >= 1) {
       $(this).val(valor.charAt(0).toUpperCase() + valor.slice(1));
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

  if ($.fn.DataTable.isDataTable('#tablaCliente')) {
    $('#tablaCliente').DataTable().destroy();
  }

  $('#tablaCliente').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { data: 'cedula' },
      { data: 'nombre' },
      { data: 'apellido' },
      { data: 'telefono' },
      { data: 'fecha_registro' },
      {
        data: null,
        render: function () {
          return botones;
        }
      }
    ],
    order: [[4, 'desc']],
    language: { url: idiomaTabla }
  });
}

function limpia() {
  SistemaValidacion.limpiarValidacion(etiquetasFormulario('input'));

  let input = etiquetasFormulario('input')
  
  input.cedula.val("").prop("readOnly", false)
  input.nombre.val("").prop("readOnly", false)
  input.apellido.val("").prop("readOnly", false)
  input.fecha_nacimiento.val("").prop("readOnly", false)
  input.telefono.val("").prop("readOnly", false)
  input.correo.val("").prop("readOnly", false)
  input.direccion.val("").prop("readOnly", false)
  input.sexo.val("default").prop("disabled", false)

  // Deshabilitar el botón al limpiar 
  $('#btnClienteForm').prop('disabled', true);
  input = null;
}

function rellenar(pos, accion) {
  limpia();
  let input = etiquetasFormulario('input')
  const linea = $(pos).closest('tr');
  const tabla = $('#tablaCliente').DataTable();
  const datosFila = tabla.row(linea).data();

  // Usar los datos directamente de DataTable
  input.cedula.val(datosFila.cedula);
  input.nombre.val(capitalizarTexto(datosFila.nombre));
  input.apellido.val(capitalizarTexto(datosFila.apellido));
  input.fecha_nacimiento.val(datosFila.fecha_nacimiento);
  input.telefono.val(datosFila.telefono);
  input.correo.val(datosFila.correo);
  input.direccion.val(datosFila.direccion);
  buscarSelect(input.sexo, datosFila.sexo, "value");

  input.cedula.prop("readOnly", true); // La cédula no se modifica

  if (accion == 0) {
    editarModal("modificar")
  } else {
    input.nombre.prop("readOnly", true);
    input.apellido.prop("readOnly", true);
    input.fecha_nacimiento.prop("readOnly", true);
    input.telefono.prop("readOnly", true);
    input.correo.prop("readOnly", true);
    input.direccion.prop("readOnly", true);
    input.sexo.prop("disabled", true);
    editarModal("eliminar")
  }

  // Habilitar el botón inmediatamente para Modificar/Eliminar ya que los datos vienen pre-validados
  $('#btnClienteForm').prop('disabled', false);
}
