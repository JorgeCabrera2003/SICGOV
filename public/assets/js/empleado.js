//MODULO DE EmpleadoS

/** Estado global: true si la cédula ya existe en la BD */
let _cedulaDuplicada = false;

/** Lista de IDs de cargo válidos al cargar la página */
let _validCargos = [];

//-------INICIALIZACIÖN-------

//Interfaz de Acceso a los Elementos(inputs y span del formulario)
function etiquetasFormulario(etiquetas) {
  let referencia = null

  const inputEmpleado = {
    tipo_doc: $('#tipo_doc'),
    cedula: $('#cedula'),
    nombre: $('#nombre'),
    apellido: $('#apellido'),
    fecha_nacimiento: $('#fecha_nacimiento'),
    prefijo_telefono: $('#prefijo_telefono'),
    telefono: $('#telefono'),
    correo: $('#correo'),
    direccion: $('#direccion'),
    sexo: $('#sexo'),
    id_cargo: $('#id_cargo')
  }

  const spanEmpleado = {
    scedula: $('#scedula'),
    snombre: $('#snombre'),
    sapellido: $('#sapellido'),
    sfecha_nacimiento: $('#sfecha_nacimiento'),
    stelefono: $('#stelefono'),
    scorreo: $('#scorreo'),
    sdireccion: $('#sdireccion'),
    ssexo: $('#ssexo'),
    sid_cargo: $('#sid_cargo')
  }

  if (etiquetas === "input") {
    referencia = inputEmpleado
  }

  if (etiquetas === "span") {
    referencia = spanEmpleado
  }

  return referencia
}
//Fin de Interfaz de Acceso a los Elementos(inputs y span del formulario)

//Interfaz de Acceso a los Elementos(modal)
function etiquetasModal(etiquetas) {
  let referencia = null

  const modalPrincipal = {
    modal: $('#modalEmpleado'),
    titulo: $('#modalTitleTextEmpleado'),
    boton: $('#btnEmpleadoForm')
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
    titulo = "Nuevo Empleado"
    boton = "Nuevo"
    etiqueta_modal = etiquetasModal("principal");
  }

  if (operacion == 'modificar') {
    titulo = "Actualizar Empleado"
    boton = "Actualizar"
    etiqueta_modal = etiquetasModal("principal");
  }

  if (operacion == 'eliminar') {
    titulo = "Borrar Empleado"
    boton = "Borrar"
    etiqueta_modal = etiquetasModal("principal");
  }
  etiqueta_modal.titulo.text(titulo)
  etiqueta_modal.boton.text(boton)
  etiqueta_modal.modal.modal("show")
}
//Fin de la Función de editarModal

/**
 * Verifica de forma asíncrona (con debounce) si la cédula ya existe en la BD.
 * Solo se activa en modo 'registrar'; en 'modificar' la cédula no puede cambiar.
 */
const verificarCedulaDuplicada = debounce(async function (tipoCedula, numCedula) {
    const input    = etiquetasFormulario('input');
    const $span    = $('#scedula');
    const accion   = etiquetasModal('principal').boton.text();

    // Solo verificar en modo registrar y si la cédula es formalmente válida
    if (accion !== 'Nuevo') return;
    if (!tipoCedula || tipoCedula === 'default') return;
    if (!numCedula || numCedula.length < 7 || numCedula.length > 9) return;

    const cedulaCompleta = tipoCedula + '-' + numCedula;

    try {
        const fd = new FormData();
        fd.append('peticion', 'verificar_cedula');
        fd.append('cedula', cedulaCompleta);

        const json = await enviaAjax(fd);

        if (json && json.existe) {
            _cedulaDuplicada = true;
            input.cedula.addClass('is-invalid').removeClass('is-valid');
            $span.text(json.mensaje || 'Ya existe un Empleado con esta cédula.');
        } else {
            _cedulaDuplicada = false;
        }
    } catch (e) {
        _cedulaDuplicada = false;
    }

    validarCamposEmpleado();
}, 500);

/**
 * Valida todos los campos del formulario de Empleado en tiempo real.
 * Aplica estilos is-valid / is-invalid a cada campo y
 * habilita o deshabilita el botón según el resultado global.
 */
function validarCamposEmpleado() {
    const input = etiquetasFormulario('input');
    const modal = etiquetasModal('principal');
    const accion = modal.boton.text();
    let formularioValido = true;

    // Helper local para aplicar estilos y mensaje en un span
    function aplicar($campo, $span, valido, msg) {
        const val = typeof $campo.val === 'function' ? $campo.val() : '';
        const tieneValor = val !== '' && val !== 'default' && val !== null;

        if (!tieneValor && !valido) {
            // Campo obligatorio vacío: mostrar error sólo si fue tocado
            if ($campo.data('touched')) {
                $campo.addClass('is-invalid').removeClass('is-valid');
                $span.addClass('invalid-tooltip d-inline-block');
                $span.text(msg);
            } else {
                $campo.removeClass('is-valid is-invalid');
                $span.removeClass('invalid-tooltip d-inline-block');
                $span.text('');
            }
        } else if (tieneValor && !valido) {
            $campo.addClass('is-invalid').removeClass('is-valid');
            $span.addClass('invalid-tooltip d-inline-block');
            $span.text(msg);
        } else if (valido) {
            // Solo colorear verde si el campo tiene contenido; vacío = sin color
            if (tieneValor) {
                $campo.addClass('is-valid').removeClass('is-invalid');
            } else {
                $campo.removeClass('is-valid is-invalid');
            }
            $span.removeClass('invalid-tooltip d-inline-block');
            $span.text('');
        } else {
            $campo.removeClass('is-valid is-invalid');
            $span.removeClass('invalid-tooltip d-inline-block');
            $span.text('');
        }

        if (!valido) formularioValido = false;
    }

    // ── Cédula ───────────────────────────────────────────
    const tipoCedula = input.tipo_doc.val();
    const numCedula  = input.cedula.val().trim();
    const $spanCedula = $('#scedula');

    let cedulaValida = false;
    let msgCedula = '';

    if (!tipoCedula || tipoCedula === 'default') {
        msgCedula = 'Selecciona el tipo de documento.';
    } else if (numCedula.length < 7) {
        msgCedula = 'La cédula debe tener al menos 7 dígitos.';
    } else if (numCedula.length > 9) {
        msgCedula = 'La cédula no puede tener más de 9 dígitos.';
    } else if (!/^\d+$/.test(numCedula)) {
        msgCedula = 'La cédula solo puede contener números.';
    } else if (_cedulaDuplicada) {
        msgCedula = 'Esta cédula ya está registrada.';
    } else {
        cedulaValida = true;
    }

    // Para cédula aplicamos sobre el input numérico
    // (si async ya marcó is-invalid, no sobreescribir con is-valid prematuro)
    if (!_cedulaDuplicada || !cedulaValida) {
        if (numCedula !== '' || input.cedula.data('touched')) {
            input.cedula.addClass(cedulaValida ? 'is-valid' : 'is-invalid')
                        .removeClass(cedulaValida ? 'is-invalid' : 'is-valid');
            if (cedulaValida) {
                $spanCedula.removeClass('invalid-tooltip d-inline-block');
                $spanCedula.text('');
            } else {
                $spanCedula.addClass('invalid-tooltip d-inline-block');
                $spanCedula.text(msgCedula);
            }
        } else {
            input.cedula.removeClass('is-valid is-invalid');
            $spanCedula.removeClass('invalid-tooltip d-inline-block');
            $spanCedula.text('');
        }
    }
    if (!cedulaValida) formularioValido = false;

    // ── Nombre ───────────────────────────────────────────
    const nombre = input.nombre.val().trim();
    const nombreValido = nombre.length >= 3;
    aplicar(input.nombre, $('#snombre'), nombreValido, 'El nombre debe tener al menos 3 caracteres.');

    // ── Apellido ─────────────────────────────────────────
    const apellido = input.apellido.val().trim();
    const apellidoValido = apellido.length >= 3;
    aplicar(input.apellido, $('#sapellido'), apellidoValido, 'El apellido debe tener al menos 3 caracteres.');

    // ── Fecha de Nacimiento ─────────────────────────────
    const fechaNac = input.fecha_nacimiento.val();
    const fechaValida = fechaNac !== '';
    aplicar(input.fecha_nacimiento, $('#sfecha_nacimiento'), fechaValida, 'La fecha de nacimiento es obligatoria.');

    // ── Teléfono (opcional) ─────────────────────────────
    const prefijo   = input.prefijo_telefono.val();
    const numTel    = input.telefono.val().trim();
    const $spanTel  = $('#stelefono');
    let telValido   = true;
    let msgTel      = '';

    if (numTel !== '') {
        // Si escribió número → prefijo obligatorio
        if (!prefijo || prefijo === 'default') {
            telValido = false;
            msgTel = 'Selecciona el prefijo del teléfono.';
        } else if (numTel.length !== 7) {
            telValido = false;
            msgTel = 'El número de teléfono debe tener exactamente 7 dígitos.';
        } else if (!/^\d{7}$/.test(numTel)) {
            telValido = false;
            msgTel = 'El teléfono solo puede contener números.';
        }
    } else if (prefijo && prefijo !== 'default') {
        // Si seleccionó prefijo pero no escribió número
        telValido = false;
        msgTel = 'Ingresa el número de teléfono o retira el prefijo.';
    }

    if (numTel !== '' || (prefijo && prefijo !== 'default')) {
        input.telefono.addClass(telValido ? 'is-valid' : 'is-invalid')
                      .removeClass(telValido ? 'is-invalid' : 'is-valid');
        if (telValido) {
            $spanTel.removeClass('invalid-tooltip d-inline-block');
            $spanTel.text('');
        } else {
            $spanTel.addClass('invalid-tooltip d-inline-block');
            $spanTel.text(msgTel);
        }
    } else {
        input.telefono.removeClass('is-valid is-invalid');
        $spanTel.removeClass('invalid-tooltip d-inline-block');
        $spanTel.text('');
    }
    if (!telValido) formularioValido = false;

    // ── Sexo (obligatorio) ──────────────────────────────
    const sexoVal   = input.sexo.val();
    const sexoValido = sexoVal && sexoVal !== 'default';
    aplicar(input.sexo, $('#ssexo'), sexoValido, 'El sexo es obligatorio.');

    // ── Cargo (obligatorio y verificado) ──────────────────────────────
    const cargoVal   = input.id_cargo.val();
    let cargoValido = false;
    let msgCargo = 'El cargo es obligatorio.';
    if (cargoVal && cargoVal !== 'default') {
        if (_validCargos.length > 0 && !_validCargos.includes(cargoVal)) {
            msgCargo = 'El cargo seleccionado no es válido.';
        } else {
            cargoValido = true;
        }
    }
    aplicar(input.id_cargo, $('#sid_cargo'), cargoValido, msgCargo);

    // ── Correo (opcional) ──────────────────────────────
    const correoVal = input.correo.val().trim();
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const correoValido = correoVal === '' || regexEmail.test(correoVal);
    aplicar(input.correo, $('#scorreo'), correoValido, 'El formato del correo no es válido.');

    // ── Dirección (obligatoria) ──────────────────────────
    const dirVal   = input.direccion.val().trim();
    const dirValida = dirVal.length >= 3;
    aplicar(input.direccion, $('#sdireccion'), dirValida, 'La dirección debe tener al menos 3 caracteres.');

    // ── Resultado final ──────────────────────────────────
    if (accion === 'Borrar') {
        modal.boton.prop('disabled', !cedulaValida);
    } else {
        modal.boton.prop('disabled', !formularioValido);
    }
}

//Función para manejar el cambio de estado del formulario (mantener compatibilidad con SistemaValidacion)
function manejarCambioEstado(formularioValido) {
    validarCamposEmpleado();
}

$(document).ready(function () {
  // Inicializar _validCargos
  $('#id_cargo option').each(function() {
      if ($(this).val() !== 'default') {
          _validCargos.push($(this).val());
      }
  });

  // Observador de mutaciones para validar si manipulan el select de cargo desde Inspect Element
  const domObserver = new MutationObserver((mutationsList) => {
      let shouldValidate = false;
      for (let mutation of mutationsList) {
          if (mutation.type === 'attributes' && (mutation.attributeName === 'class' || mutation.attributeName === 'style')) {
              continue;
          }
          shouldValidate = true;
          break;
      }
      if (shouldValidate) {
          validarCamposEmpleado();
          domObserver.takeRecords();
      }
  });

  const catSelectObserver = document.getElementById('id_cargo');
  if (catSelectObserver) {
      domObserver.observe(catSelectObserver, { attributes: true, childList: true, subtree: true, characterData: true });
  }

  crearDataTable();
  registrarEntrada();
  capaValidar();

  // Inicializar sistema de validación (para capitalizar y eventos base)
  SistemaValidacion.inicializar(etiquetasFormulario('input'), manejarCambioEstado);

  // Estado inicial: botón deshabilitado
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
      confirmacion = await confirmarAccion(`Se ${str_acccion} un Empleado`, "¿Está seguro de realizar la acción?", "question");

      if (confirmacion) {
        peticion.append('peticion', accion);
        peticion.append('cedula', input.tipo_doc.val() + '-' + input.cedula.val());
        peticion.append('nombre', input.nombre.val());
        peticion.append('apellido', input.apellido.val());
        peticion.append('fecha_nacimiento', input.fecha_nacimiento.val());
        let telefonoFull = "";
        if (input.prefijo_telefono.val() && input.prefijo_telefono.val() !== 'default' && input.telefono.val()) {
           telefonoFull = input.prefijo_telefono.val() + '-' + input.telefono.val();
        } else {
           telefonoFull = input.telefono.val() || "";
        }
        peticion.append('telefono', telefonoFull);
        peticion.append('correo', input.correo.val());
        peticion.append('direccion', input.direccion.val());
        peticion.append('sexo', input.sexo.val());
        peticion.append('id_cargo', input.id_cargo.val());
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
      confirmacion = await confirmarAccion("Se eliminará un Empleado", "¿Está seguro de realizar la acción?", "warning");

      if (confirmacion) {
        peticion.append('peticion', 'eliminar');
        peticion.append('cedula', input.tipo_doc.val() + '-' + input.cedula.val());
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
$("#btnEmpleadoForm").on("click", async function () {
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

$("#btnNuevoEmpleado").on("click", function () {
  limpia();
  editarModal("registrar")
  // El botón se habilita automáticamente mediante el callback cuando los campos sean válidos
});

// Aplicar capitalización automática cuando el modal se muestra
$('#modalEmpleado').on('shown.bs.modal', function () {
  // Forzar validación inicial cuando se abre el modal
  setTimeout(() => {
    SistemaValidacion.validarFormulario(etiquetasFormulario('input'));
  }, 100);
});

async function vistaPermiso() {
    const dropdown = $('<div>').addClass('dropdown');
    const boton = $('<button>').addClass('btn btn-sm bg-body text-body border dropdown-toggle')
        .attr('type', 'button')
        .attr('data-bs-toggle', 'dropdown')
        .html('<i class="fas fa-ellipsis-v me-2"></i>Acciones');

    const menu = $('<ul>').addClass('dropdown-menu dropdown-menu-end');

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

    const itemEstatus = $('<li>');
    const linkEstatus = $('<a>')
        .addClass('dropdown-item text-danger')
        .attr('href', '#')
        .attr('onclick', 'cambiarEstatus(this)')
        .html('<i class="fa-solid fa-power-off me-2"></i>Cambiar Estatus');
    itemEstatus.append(linkEstatus);

    menu.append(itemConsultar, itemEditar, separador, itemEstatus);
    dropdown.append(boton, menu);

    return dropdown.prop('outerHTML');
}

function capaValidar() {
  let input = etiquetasFormulario('input');

  // Marcar como tocado y validar al interactuar
  function marcarYValidar() {
      $(this).data('touched', true);
      validarCamposEmpleado();
  }

  // Bloqueo de teclas
  input.cedula.on('keypress', function (e) {
      validarKeyPress(/^[0-9]*$/, e);
  });
  input.nombre.on('keypress', function (e) {
      validarKeyPress(/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ \b]*$/, e);
  });
  input.apellido.on('keypress', function (e) {
      validarKeyPress(/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ \b]*$/, e);
  });
  input.telefono.on('keypress', function (e) {
      validarKeyPress(/^[0-9\b]*$/, e);
  });

  // Capitalización automática de primera letra
  input.nombre.on('input', function () {
      const valor = $(this).val();
      if (valor.length === 1) $(this).val(valor.toUpperCase());
      marcarYValidar.call(this);
  });
  input.apellido.on('input', function () {
      const valor = $(this).val();
      if (valor.length === 1) $(this).val(valor.toUpperCase());
      marcarYValidar.call(this);
  });

  // Inputs que solo disparan validación
  input.cedula.on('input', function () {
      $(this).data('touched', true);
      // Resetear estado duplicado al cambiar el número
      _cedulaDuplicada = false;
      validarCamposEmpleado();
      // Disparar verificación async
      const tipo = input.tipo_doc.val();
      verificarCedulaDuplicada(tipo, $(this).val().trim());
  });
  input.fecha_nacimiento.on('change', marcarYValidar);
  input.telefono.on('input', function() {
      if ($(this).val().trim() === '') {
          input.prefijo_telefono.val('default');
          input.prefijo_telefono.removeClass('is-valid is-invalid');
      }
      marcarYValidar.call(this);
  });
  input.correo.on('input', marcarYValidar);
  input.direccion.on('input', marcarYValidar);

  // Selects: marcar tocados y revalidar al cambiar
  input.tipo_doc.on('change', function () {
      $(this).data('touched', true);
      input.cedula.data('touched', true);
      // Resetear duplicado al cambiar el tipo
      _cedulaDuplicada = false;
      validarCamposEmpleado();
      // Disparar verificación async con el nuevo tipo
      const num = input.cedula.val().trim();
      verificarCedulaDuplicada($(this).val(), num);
  });
  input.prefijo_telefono.on('change', function () {
      $(this).data('touched', true);
      input.telefono.data('touched', true);
      validarCamposEmpleado();
  });
  input.sexo.on('change', function () {
      $(this).data('touched', true);
      validarCamposEmpleado();
  });
  input.id_cargo.on('change', function () {
      $(this).data('touched', true);
      validarCamposEmpleado();
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

  if ($.fn.DataTable.isDataTable('#tablaEmpleado')) {
    $('#tablaEmpleado').DataTable().destroy();
  }

  $('#tablaEmpleado').DataTable({
    processing: true,
    data: arreglo,
    columns: [
      { 
        data: 'cedula',
        render: function (data, type) {
          if (!data) return data;
          let formatted = data;
          if (data.indexOf('-') === -1 && data.length > 1) {
              formatted = data.charAt(0) + '-' + data.slice(1);
          }
          if (type === 'display') return formatted;
          if (type === 'filter') return data + ' ' + formatted;
          return data;
        }
      },
      { data: 'nombre' },
      { data: 'apellido' },
      { data: 'cargo', defaultContent: 'No asignado' },
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
        data: 'estatus',
        render: function(data) {
            return data == 1 
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>';
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

  let input = etiquetasFormulario('input');

  input.tipo_doc.val("default").prop("disabled", false);
  input.cedula.val("").prop("readOnly", false);
  input.nombre.val("").prop("readOnly", false);
  input.apellido.val("").prop("readOnly", false);
  input.fecha_nacimiento.val("").prop("readOnly", false);
  input.prefijo_telefono.val("default").prop("disabled", false);
  input.telefono.val("").prop("readOnly", false);
  input.correo.val("").prop("readOnly", false);
  input.direccion.val("").prop("readOnly", false);
  input.sexo.val("default").prop("disabled", false);
  input.id_cargo.val("default").prop("disabled", false);

  // Resetear estado visual de validación y flags 'touched'
  Object.values(input).forEach(function ($el) {
      if ($el && typeof $el.removeClass === 'function') {
          $el.removeClass('is-valid is-invalid').removeData('touched');
      }
  });
  // Limpiar solo los spans de feedback del formulario de Empleados
  $('#scedula, #snombre, #sapellido, #sfecha_nacimiento, #stelefono, #ssexo, #scorreo, #sdireccion, #sid_cargo')
      .removeClass('invalid-tooltip d-inline-block')
      .text('');

  // Deshabilitar el botón al limpiar
  $('#btnEmpleadoForm').prop('disabled', true);
  input = null;
}

function rellenar(pos, accion) {
  limpia();
  let input = etiquetasFormulario('input')
  const linea = $(pos).closest('tr');
  const tabla = $('#tablaEmpleado').DataTable();
  const datosFila = tabla.row(linea).data();

  // Usar los datos directamente de DataTable
  let cedulaFull = datosFila.cedula;
  let tipo = cedulaFull.charAt(0);
  let numero = cedulaFull.includes('-') ? cedulaFull.split('-')[1] : cedulaFull.slice(1);
  buscarSelect(input.tipo_doc, tipo, "value");
  input.cedula.val(numero);
  input.nombre.val(capitalizarTexto(datosFila.nombre));
  input.apellido.val(capitalizarTexto(datosFila.apellido));
  input.fecha_nacimiento.val(datosFila.fecha_nacimiento);
  if (datosFila.telefono) {
     let telLimpio = datosFila.telefono.replace('-', '');
     if (telLimpio.length === 11) {
         buscarSelect(input.prefijo_telefono, telLimpio.substring(0, 4), "value");
         input.telefono.val(telLimpio.substring(4));
     } else {
         input.prefijo_telefono.val("default");
         input.telefono.val(datosFila.telefono);
     }
  } else {
     input.prefijo_telefono.val("default");
     input.telefono.val("");
  }
  input.correo.val(datosFila.correo);
  input.direccion.val(datosFila.direccion);
  buscarSelect(input.sexo, datosFila.sexo, "value");
  buscarSelect(input.id_cargo, datosFila.id_cargo, "value");

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
    input.id_cargo.prop("disabled", true);
    editarModal("eliminar")
  }

  // Habilitar el botón inmediatamente para Modificar/Eliminar ya que los datos vienen pre-validados
  $('#btnEmpleadoForm').prop('disabled', false);
}

// Función exclusiva para Cambiar Estatus directamente sin Modal
async function cambiarEstatus(pos) {
    const linea = $(pos).closest('tr');
    const tabla = $('#tablaEmpleado').DataTable();
    const datosFila = tabla.row(linea).data();
    
    let nuevoEstatus = datosFila.estatus == 1 ? 0 : 1;
    let textoAccion = datosFila.estatus == 1 ? "desactivará" : "reactivará";
    
    let confirmacion = await confirmarAccion(`Se ${textoAccion} al Empleado`, "¿Está seguro de realizar la acción?", "warning");
    
    if (confirmacion) {
        let peticionData = new FormData();
        peticionData.append('peticion', 'cambiar_estatus');
        // cédula is expected by backend instead of id_categoria
        peticionData.append('cedula', datosFila.cedula);
        peticionData.append('estatus', nuevoEstatus);
        
        try {
            let json = await enviaAjax(peticionData);

            if (json.resultado >= 200 && json.resultado < 300) {
                crearDataTable();
                mensajes("success", 3000, "Éxito", json.mensaje);
            } else {
                mensajes("error", 5000, "Error", json.mensaje || "Ocurrió un error inesperado.");
            }
        } catch (error) {
            mensajes("error", 5000, "Error", "Error de comunicación con el servidor.");
        }
    }
}

function consultarFila(pos) {
  const linea = $(pos).closest('tr');
  const tabla = $('#tablaEmpleado').DataTable();
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
  if(cedulaFormateada && cedulaFormateada.indexOf('-') === -1 && cedulaFormateada.length > 1) {
     cedulaFormateada = cedulaFormateada.charAt(0) + '-' + cedulaFormateada.slice(1);
  }
  
  let telefonoFormateado = datosFila.telefono;
  if(telefonoFormateado && telefonoFormateado.indexOf('-') === -1 && telefonoFormateado.length >= 5) {
     telefonoFormateado = telefonoFormateado.substring(0, 4) + '-' + telefonoFormateado.substring(4);
  }

  let sexoTxt = datosFila.sexo === 'M' ? 'Masculino' : (datosFila.sexo === 'F' ? 'Femenino' : 'No especificado');

  let fechaIngresoTxt = 'N/A';
  if (datosFila.fecha_ingreso) {
    let parts = datosFila.fecha_ingreso.split(/[- :]/);
    if (parts.length >= 3) {
      const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
      const dia = parseInt(parts[2], 10);
      const mes = meses[parseInt(parts[1], 10) - 1];
      const anio = parts[0];
      fechaIngresoTxt = `${dia} de ${mes} del ${anio}`;
    } else {
      fechaIngresoTxt = datosFila.fecha_ingreso;
    }
  }

  $('#c_cedula').text(cedulaFormateada || 'N/A');
  $('#c_fecha_nacimiento').text(datosFila.fecha_nacimiento ? formatearFecha(datosFila.fecha_nacimiento) : 'N/A');
  $('#c_nombre_apellido').text(capitalizarTexto(datosFila.nombre) + ' ' + capitalizarTexto(datosFila.apellido));
  $('#c_edad').text(edadTexto);
  $('#c_cargo').text(datosFila.cargo || 'No asignado');
  $('#c_telefono').text(telefonoFormateado || 'N/A');
  $('#c_sexo').text(sexoTxt);
  $('#c_correo').text(datosFila.correo || 'N/A');
  $('#c_direccion').text(datosFila.direccion || 'N/A');
  $('#c_fecha_ingreso').text(fechaIngresoTxt);

  $('#modalConsultarEmpleado').modal('show');
}
