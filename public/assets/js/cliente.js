//MODULO DE CLIENTES

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function etiquetasFormulario(etiquetas) {
  let referencia = null

  const inputCliente = {
    tipo_doc: $('#tipo_doc'),
    cedula: $('#cedula'),
    nombre: $('#nombre'),
    apellido: $('#apellido'),
    fecha_nacimiento: $('#fecha_nacimiento'),
    prefijo_telefono: $('#prefijo_telefono'),
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
    const cedulaValida = input.tipo_doc.val() !== null && input.tipo_doc.val() !== "default" && Math.ceil(input.cedula.val().length) >= 5;
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
        peticion.append('cedula', input.tipo_doc.val() + input.cedula.val());
        peticion.append('nombre', input.nombre.val());
        peticion.append('apellido', input.apellido.val());
        peticion.append('fecha_nacimiento', input.fecha_nacimiento.val());
        let telefonoFull = "";
        if (input.prefijo_telefono.val() && input.prefijo_telefono.val() !== 'default' && input.telefono.val()) {
           telefonoFull = input.prefijo_telefono.val() + input.telefono.val();
        } else {
           telefonoFull = input.telefono.val() || "";
        }
        peticion.append('telefono', telefonoFull);
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

    if (input.tipo_doc.val() !== null && input.tipo_doc.val() !== "default" && input.cedula.val().length >= 5) {
      confirmacion = await confirmarAccion("Se eliminará un Cliente", "¿Está seguro de realizar la acción?", "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('cedula', input.tipo_doc.val() + input.cedula.val());
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
  const dropdown = $('<div>').addClass('dropdown');
  const boton = $('<button>').addClass('btn btn-sm btn-dark dropdown-toggle')
    .attr('type', 'button')
    .attr('data-bs-toggle', 'dropdown')
    .html('<i class="fas fa-ellipsis-v me-2"></i>Acciones');

  const menu = $('<ul>').addClass('dropdown-menu');

  const itemConsultar = $('<li>');
  const linkConsultar = $('<a>')
    .addClass('dropdown-item text-info')
    .attr('href', '#')
    .attr('onclick', 'consultarFila(this)')
    .html('<i class="fa-solid fa-eye me-2"></i>Consultar');
  itemConsultar.append(linkConsultar);

  const itemEditar = $('<li>');
  const linkEditar = $('<a>')
    .addClass('dropdown-item text-primary')
    .attr('href', '#')
    .attr('onclick', 'rellenar(this, 0)')
    .html('<i class="fa-solid fa-pen-to-square me-2"></i>Editar');
  itemEditar.append(linkEditar);

  const separador = $('<li>').html('<hr class="dropdown-divider">');

  const itemEliminar = $('<li>');
  const linkEliminar = $('<a>')
    .addClass('dropdown-item text-danger')
    .attr('href', '#')
    .attr('onclick', 'rellenar(this, 1)')
    .html('<i class="fa-solid fa-trash me-2"></i>Eliminar');
  itemEliminar.append(linkEliminar);

  menu.append(itemConsultar, itemEditar, separador, itemEliminar);
  dropdown.append(boton, menu);

  return dropdown.prop('outerHTML');
}

function capaValidar() {
  let input = etiquetasFormulario("input")
  
  input.cedula.on("keypress", function (e) {
    validarKeyPress(/^[0-9]*$/, e);
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
      { 
        data: 'cedula',
        render: function (data, type) {
          if (!data) return data;
          const formatted = (data.length > 1) ? data.charAt(0) + '-' + data.slice(1) : data;
          if (type === 'display') return formatted;
          if (type === 'filter') return data + ' ' + formatted;
          return data;
        }
      },
      { data: 'nombre' },
      { data: 'apellido' },
      { 
        data: 'telefono',
        render: function (data, type) {
          if (!data || data.trim() === '') {
            return type === 'display' ? '<span class="text-muted">N/A</span>' : '';
          }
          const formatted = (data.length >= 5) ? data.substring(0, 4) + '-' + data.substring(4) : data;
          if (type === 'display') return formatted;
          if (type === 'filter') return data + ' ' + formatted;
          return data;
        }
      },
      { 
        data: 'fecha_nacimiento',
        render: function(data) {
          if (!data) return "N/A";
          const partes = data.split("-");
          if(partes.length !== 3) return "N/A";
          const fn = new Date(partes[0], partes[1] - 1, partes[2]);
          const hoy = new Date();
          let edad = hoy.getFullYear() - fn.getFullYear();
          const m = hoy.getMonth() - fn.getMonth();
          if (m < 0 || (m === 0 && hoy.getDate() < fn.getDate())) {
            edad--;
          }
          return edad + " años";
        }
      },
      {
        data: null,
        render: function () {
          return botones;
        }
      }
    ],
    order: [[0, 'desc']],
    language: { url: idiomaTabla }
  });
}

function limpia() {
  SistemaValidacion.limpiarValidacion(etiquetasFormulario('input'));

  let input = etiquetasFormulario('input')
  
  input.tipo_doc.val("default").prop("disabled", false)
  input.cedula.val("").prop("readOnly", false)
  input.nombre.val("").prop("readOnly", false)
  input.apellido.val("").prop("readOnly", false)
  input.fecha_nacimiento.val("").prop("readOnly", false)
  input.prefijo_telefono.val("default").prop("disabled", false)
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
  let cedulaFull = datosFila.cedula;
  let tipo = cedulaFull.charAt(0);
  let numero = cedulaFull.slice(1);
  buscarSelect(input.tipo_doc, tipo, "value");
  input.cedula.val(numero);
  input.nombre.val(capitalizarTexto(datosFila.nombre));
  input.apellido.val(capitalizarTexto(datosFila.apellido));
  input.fecha_nacimiento.val(datosFila.fecha_nacimiento);
  if (datosFila.telefono && datosFila.telefono.length === 11) {
     buscarSelect(input.prefijo_telefono, datosFila.telefono.substring(0, 4), "value");
     input.telefono.val(datosFila.telefono.substring(4));
  } else {
     input.prefijo_telefono.val("default");
     input.telefono.val(datosFila.telefono || "");
  }
  input.correo.val(datosFila.correo);
  input.direccion.val(datosFila.direccion);
  buscarSelect(input.sexo, datosFila.sexo, "value");

  input.tipo_doc.prop("disabled", true);
  input.cedula.prop("readOnly", true); // La cédula no se modifica

  if (accion == 0) {
    editarModal("modificar")
  } else {
    input.nombre.prop("readOnly", true);
    input.apellido.prop("readOnly", true);
    input.fecha_nacimiento.prop("readOnly", true);
    input.prefijo_telefono.prop("disabled", true);
    input.telefono.prop("readOnly", true);
    input.correo.prop("readOnly", true);
    input.direccion.prop("readOnly", true);
    input.sexo.prop("disabled", true);
    editarModal("eliminar")
  }

  // Habilitar el botón inmediatamente para Modificar/Eliminar ya que los datos vienen pre-validados
  $('#btnClienteForm').prop('disabled', false);
}

function consultarFila(pos) {
  const linea = $(pos).closest('tr');
  const tabla = $('#tablaCliente').DataTable();
  const datosFila = tabla.row(linea).data();

  // Calcular edad
  let edadTexto = "N/A";
  if (datosFila.fecha_nacimiento) {
    const partes = datosFila.fecha_nacimiento.split("-");
    if(partes.length === 3) {
      const fn = new Date(partes[0], partes[1] - 1, partes[2]);
      const hoy = new Date();
      let edad = hoy.getFullYear() - fn.getFullYear();
      const m = hoy.getMonth() - fn.getMonth();
      if (m < 0 || (m === 0 && hoy.getDate() < fn.getDate())) {
        edad--;
      }
      edadTexto = edad + " años";
    }
  }

  let cedulaFormateada = datosFila.cedula;
  if(cedulaFormateada && cedulaFormateada.length > 1) {
     cedulaFormateada = cedulaFormateada.charAt(0) + '-' + cedulaFormateada.slice(1);
  }
  
  let telefonoFormateado = datosFila.telefono;
  if(telefonoFormateado && telefonoFormateado.length >= 5) {
     telefonoFormateado = telefonoFormateado.substring(0, 4) + '-' + telefonoFormateado.substring(4);
  }

  let sexoTxt = datosFila.sexo === 'M' ? 'Masculino' : (datosFila.sexo === 'F' ? 'Femenino' : 'No especificado');

  let fechaRegistroTxt = 'N/A';
  if (datosFila.fecha_registro) {
    let parts = datosFila.fecha_registro.split(/[- :]/);
    if (parts.length >= 6) {
      const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
      const dia = parseInt(parts[2], 10);
      const mes = meses[parseInt(parts[1], 10) - 1];
      const anio = parts[0];
      let hora = parseInt(parts[3], 10);
      const minuto = parts[4].padStart(2, '0');
      const ampm = hora >= 12 ? 'PM' : 'AM';
      hora = hora % 12;
      hora = hora ? hora : 12;
      fechaRegistroTxt = `${dia} de ${mes} del ${anio} a las ${hora}:${minuto} ${ampm}`;
    } else {
      fechaRegistroTxt = datosFila.fecha_registro;
    }
  }

  $('#c_cedula').text(cedulaFormateada || 'N/A');
  $('#c_fecha_nacimiento').text(datosFila.fecha_nacimiento ? formatearFecha(datosFila.fecha_nacimiento) : 'N/A');
  $('#c_nombre_apellido').text(capitalizarTexto(datosFila.nombre) + ' ' + capitalizarTexto(datosFila.apellido));
  $('#c_edad').text(edadTexto);
  $('#c_telefono').text(telefonoFormateado || 'N/A');
  $('#c_sexo').text(sexoTxt);
  $('#c_correo').text(datosFila.correo || 'N/A');
  $('#c_direccion').text(datosFila.direccion || 'N/A');
  $('#c_fecha_registro').text(fechaRegistroTxt);

  $('#modalConsultarCliente').modal('show');
}
