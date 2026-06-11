// MODULO DE CATEGORÍAS DE MENÚ

// Interfaz de Acceso a Elementos del Formulario
function etiquetasFormulario() {
    return {
        peticion: $('#peticionCategoria'),
        id_categoria: $('#id_categoria'),
        nombre_categoria: $('#nombre_categoria')
    };
}

// Interfaz de Acceso al Modal Principal
function etiquetasModal() {
    return {
        modal: $('#modalCategoria'),
        titulo: $('#tituloModalCategoria'),
        boton: $('#btnGuardarCategoria'),
        formulario: $('#formCategoria')
    };
}

// Editar la configuración visual del Modal dependiendo de la operación
function editarModal(operacion) {
    let titulo = "";
    let boton = "";
    let eti = etiquetasModal();

    if (operacion === 'registrar') {
        titulo = "Nueva Categoría";
        boton = "Guardar Categoría";
    }

    if (operacion === 'modificar') {
        titulo = "Actualizar Categoría";
        boton = "Actualizar Categoría";
    }

    if (operacion === 'eliminar') {
        titulo = "Eliminar Categoría";
        boton = "Confirmar Eliminación";
    }

    eti.titulo.text(titulo);
    eti.boton.html(`<i class="fas ${operacion === 'eliminar' ? 'fa-trash' : 'fa-save'} me-2"></i>${boton}`);
    eti.modal.modal("show");
}

function limpiar() {
    let input = etiquetasFormulario();
    let eti = etiquetasModal();

    eti.formulario[0].reset();
    input.id_categoria.val("");
    input.peticion.val("registrar");

    eti.formulario.removeClass('was-validated');
    input.nombre_categoria.prop("readOnly", false).removeClass('is-valid is-invalid');

    // Limpiar validaciones personalizadas residuales
    input.nombre_categoria[0].setCustomValidity('');

    // Deshabilitar botón al limpiar (formulario vacío = inválido)
    etiquetasModal().boton.prop('disabled', true);

    // Resetear estado de duplicado
    _nombreDuplicado = false;
    $('#nombre_categoria').removeClass('is-invalid is-valid');
    $('#feedback_nombre_categoria').removeClass('invalid-tooltip d-inline-block').text('');
}

/**
 * Evalúa un campo individual y aplica las clases Bootstrap is-valid / is-invalid.
 * Solo aplica estilos si el usuario ya ha escrito algo (campo no vacío o fue tocado).
 *
 * @param {jQuery}  $campo    - Elemento jQuery del input o textarea
 * @param {jQuery}  $feedback - Elemento jQuery del div de feedback
 * @param {boolean} esValido  - Resultado de la validación
 * @param {string}  mensaje   - Mensaje de error a mostrar si no es válido
 * @param {boolean} [forzar]  - Si true, aplica estilos incluso si el campo está vacío
 */
function aplicarEstilosCampo($campo, $feedback, esValido, mensaje, forzar = false) {
    const val = $campo.val().trim();
    // No colorear si el campo está completamente vacío y no se fuerza
    if (!forzar && val === '') {
        $campo.removeClass('is-valid is-invalid');
        $feedback.removeClass('invalid-tooltip d-inline-block').text('');
        return;
    }

    if (esValido) {
        $campo.addClass('is-valid').removeClass('is-invalid');
        $campo[0].setCustomValidity('');
        $feedback.removeClass('invalid-tooltip d-inline-block').text('');
    } else {
        $campo.addClass('is-invalid').removeClass('is-valid');
        $campo[0].setCustomValidity(mensaje);
        $feedback.addClass('invalid-tooltip d-inline-block').text(mensaje);
    }
}

/**
 * Verifica en tiempo real si el formulario es válido,
 * aplica colores Bootstrap a cada campo y habilita/deshabilita el botón.
 */
function verificarEstadoBoton() {
    const input = etiquetasFormulario();
    const boton = etiquetasModal().boton;

    const nombre = input.nombre_categoria.val().trim();

    const regexSoloLetras = /^[A-ZÁÉÍÓÚÑa-záéíóúñ\s]+$/;
    const regexPrimeraMayus = /^[A-ZÁÉÍÓÚÑ]/;

    // ── Evaluar Nombre ───────────────────────────────────────
    let nombreValido = true;
    let mensajeNombre = '';

    if (!nombre) {
        nombreValido = false;
        mensajeNombre = 'El nombre de la categoría es obligatorio.';
    } else if (nombre.length < 2) {
        nombreValido = false;
        mensajeNombre = 'El nombre debe tener al menos 2 caracteres.';
    } else if (!regexPrimeraMayus.test(nombre)) {
        nombreValido = false;
        mensajeNombre = 'El nombre debe comenzar con una letra mayúscula.';
    } else if (!regexSoloLetras.test(nombre)) {
        nombreValido = false;
        mensajeNombre = 'El nombre solo puede contener letras y espacios.';
    } else if (_nombreDuplicado) {
        nombreValido = false;
        mensajeNombre = 'Ya existe una categoría con ese nombre.';
    }

    // Solo aplicar estilos de formato si NO estamos esperando verificación async
    if (!_nombreDuplicado || nombreValido) {
        aplicarEstilosCampo(
            input.nombre_categoria,
            $('#feedback_nombre_categoria'),
            nombreValido,
            mensajeNombre
        );
    }

    boton.prop('disabled', !nombreValido);
}

/**
 * Estado global: indica si el nombre actual ya existe en la BD.
 * Se usa para bloquear el botón mientras haya duplicado.
 */
let _nombreDuplicado = false;
let _debounceVerificar = null;

/**
 * Verificación asíncrona con debounce:
 * Consulta al backend si ya existe una categoría activa con el mismo nombre.
 * En edición excluye el propio registro para permitir guardar sin cambiar nombre.
 */
function verificarNombreDuplicado() {
    clearTimeout(_debounceVerificar);

    const input = etiquetasFormulario();
    const nombre = input.nombre_categoria.val().trim();
    const idActual = input.id_categoria.val().trim(); // Vacío al registrar
    const campo = input.nombre_categoria;
    const feedback = $('#feedback_nombre_categoria');

    const regexSoloLetras = /^[A-ZÁÉÍÓÚÑa-záéíóúñ\s]+$/;
    const regexPrimeraMayus = /^[A-ZÁÉÍÓÚÑ]/;

    // Solo verificar si el nombre ya pasó las validaciones locales básicas
    if (nombre.length < 2 || !regexPrimeraMayus.test(nombre) || !regexSoloLetras.test(nombre)) {
        _nombreDuplicado = false;
        return;
    }

    _debounceVerificar = setTimeout(async () => {
        try {
            let peticion = new FormData();
            peticion.append('peticion', 'verificar');
            peticion.append('nombre_categoria', nombre);
            if (idActual) peticion.append('id_categoria', idActual);

            const json = await enviaAjax(peticion);

            if (json && json.resultado === 200) {
                _nombreDuplicado = json.existe === true;

                if (_nombreDuplicado) {
                    campo.addClass('is-invalid').removeClass('is-valid');
                    campo[0].setCustomValidity('Ya existe una categoría con ese nombre.');
                    feedback.addClass('invalid-tooltip d-inline-block').text('Ya existe una categoría con ese nombre.');
                } else {
                    campo.addClass('is-valid').removeClass('is-invalid');
                    campo[0].setCustomValidity('');
                    feedback.removeClass('invalid-tooltip d-inline-block').text('');
                }
            } else {
                _nombreDuplicado = false;
            }
        } catch (_) {
            _nombreDuplicado = false;
        }

        // Actualizar estado del botón tras recibir respuesta
        verificarEstadoBoton();
    }, 500);
}

/**
 * Registra listeners de input/keypress para:
 *  - Capitalización automática de la primera letra
 *  - Bloqueo de números y caracteres especiales
 *  - Actualización del estado del botón en tiempo real
 */
function inicializarInputListeners() {
    const input = etiquetasFormulario();

    // ── Nombre ──────────────────────────────────────────────
    // Bloquear números y caracteres especiales al teclear
    input.nombre_categoria.on('keypress', function (e) {
        const char = String.fromCharCode(e.which);
        // Permitir solo letras (incluyendo tildes y ñ) y espacios
        if (!/[a-zA-ZÁÉÍÓÚáéíóúÑñ ]/.test(char)) {
            e.preventDefault();
        }
    });

    // Capitalizar primera letra en tiempo real + verificar duplicado async
    input.nombre_categoria.on('input', function () {
        const pos = this.selectionStart;
        const val = $(this).val();
        if (val.length > 0) {
            const capitalizado = val.charAt(0).toUpperCase() + val.slice(1);
            if (val !== capitalizado) {
                $(this).val(capitalizado);
                // Restaurar posición del cursor
                this.setSelectionRange(pos, pos);
            }
        }
        verificarEstadoBoton();
        verificarNombreDuplicado(); // Dispara verificación asíncrona con debounce
    });
}

// Validaciones personalizadas de los campos de categoría
function validarCamposCategoria(operacion) {
    if (operacion === 'eliminar') return true;

    let input = etiquetasFormulario();
    let valido = true;

    // ---- Nombre de la Categoría ----
    const nombre = input.nombre_categoria.val().trim();
    const campoNombre = input.nombre_categoria[0];
    const feedbackNombre = $('#feedback_nombre_categoria');

    // Regex: solo letras (incluyendo tildes/ñ) y espacios, primera letra mayúscula
    const regexNombre = /^[A-ZÁÉÍÓÚÑ][a-záéíóúñA-ZÁÉÍÓÚÑ\s]*$/;

    if (!nombre) {
        campoNombre.setCustomValidity('El nombre de la categoría es obligatorio.');
        feedbackNombre.addClass('invalid-tooltip d-inline-block').text('El nombre de la categoría es obligatorio.');
        valido = false;
    } else if (nombre.length < 2) {
        campoNombre.setCustomValidity('El nombre debe tener al menos 2 caracteres.');
        feedbackNombre.addClass('invalid-tooltip d-inline-block').text('El nombre debe tener al menos 2 caracteres.');
        valido = false;
    } else if (!/^[A-ZÁÉÍÓÚÑ]/.test(nombre)) {
        campoNombre.setCustomValidity('El nombre debe comenzar con una letra mayúscula.');
        feedbackNombre.addClass('invalid-tooltip d-inline-block').text('El nombre debe comenzar con una letra mayúscula.');
        valido = false;
    } else if (!regexNombre.test(nombre)) {
        campoNombre.setCustomValidity('El nombre solo puede contener letras (sin números ni caracteres especiales).');
        feedbackNombre.addClass('invalid-tooltip d-inline-block').text('El nombre solo puede contener letras (sin números ni caracteres especiales).');
        valido = false;
    } else {
        campoNombre.setCustomValidity('');
    }

    return valido;
}

// Enviar datos
async function enviarDatos(operacion) {
    let input = etiquetasFormulario();
    let modal = etiquetasModal();
    let form = modal.formulario[0];

    // Ejecutar validaciones personalizadas primero
    const customValido = validarCamposCategoria(operacion);

    // Validación nativa de Bootstrap
    if (operacion !== 'eliminar' && (!form.checkValidity() || !customValido)) {
        form.classList.add('was-validated');
        mensajes("error", 5000, "Error de Validación", "Por favor completa correctamente los campos obligatorios.");
        return;
    }

    let confirmacion = false;
    let tituloAccion = "";
    if (operacion === "registrar") tituloAccion = "Se registrará una nueva Categoría";
    if (operacion === "modificar") tituloAccion = "Se actualizará la Categoría";

    confirmacion = await confirmarAccion(tituloAccion, "¿Está seguro de realizar la acción?", operacion === 'eliminar' ? 'warning' : 'question');

    if (confirmacion) {
        modal.boton.prop('disabled', true);

        let peticionData = new FormData(form);
        peticionData.set('peticion', operacion);



        try {
            let json = await enviaAjax(peticionData);

            if (json.resultado >= 200 && json.resultado < 300) {
                modal.modal.modal("hide");
                crearDataTable();
                mensajes("success", 3000, "Éxito", json.mensaje);
            } else {
                mensajes("error", 5000, "Error", json.mensaje || "Ocurrió un error inesperado.");
            }
        } catch (error) {
            mensajes("error", 5000, "Error", "Error de comunicación con el servidor.");
        } finally {
            modal.boton.prop('disabled', false);
        }
    }
}

// Rellenar datos
function rellenar(pos, accion) {
    limpiar();

    let input = etiquetasFormulario();
    const linea = $(pos).closest('tr');
    const tabla = $('#tablaCategoria').DataTable();
    const datosFila = tabla.row(linea).data();

    input.id_categoria.val(datosFila.id_categoria);
    input.nombre_categoria.val(datosFila.nombre_categoria);

    if (accion === 0) { // Editar
        input.peticion.val("modificar");
        editarModal("modificar");
    }

    // Los datos pre-cargados ya son válidos: habilitar botón inmediatamente
    verificarEstadoBoton();
}

// Función exclusiva para Eliminar directamente sin Modal
async function eliminar(pos) {
    const linea = $(pos).closest('tr');
    const tabla = $('#tablaCategoria').DataTable();
    const datosFila = tabla.row(linea).data();

    let confirmacion = await confirmarAccion(`Se eliminará la Categoría`, "¿Está seguro de realizar la acción?", "warning");

    if (confirmacion) {
        let peticionData = new FormData();
        peticionData.append('peticion', 'eliminar');
        peticionData.append('id_categoria', datosFila.id_categoria);
        // Enviamos ID en formato que espera el controlador index() o el de API si usamos otro
        peticionData.append('id', datosFila.id_categoria);

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

// Menú de acciones en DataTable
async function vistaPermisoCategoria() {
    const dropdown = $('<div>').addClass('dropdown');
    const boton = $('<button>').addClass('btn btn-sm bg-body text-body border dropdown-toggle')
        .attr('type', 'button')
        .attr('data-bs-toggle', 'dropdown')
        .html('<i class="fas fa-ellipsis-v me-2"></i>Acciones');

    const menu = $('<ul>').addClass('dropdown-menu dropdown-menu-end');

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
        .attr('onclick', 'eliminar(this)')
        .html('<i class="fa-solid fa-trash me-2"></i>Eliminar');
    itemEliminar.append(linkEliminar);

    menu.append(itemEditar, separador, itemEliminar);
    dropdown.append(boton, menu);

    return dropdown.prop('outerHTML');
}

// Crear DataTable
async function crearDataTable() {
    let peticion = new FormData();
    let arreglo = [];
    let botones = await vistaPermisoCategoria();

    try {
        peticion.append('peticion', 'consultar');
        let json = await enviaAjax(peticion);
        arreglo = json || [];
    } catch (error) {
        arreglo = [];
    }

    if ($.fn.DataTable.isDataTable('#tablaCategoria')) {
        $('#tablaCategoria').DataTable().destroy();
    }

    $('#tablaCategoria').DataTable({
        processing: true,
        data: arreglo,
        columns: [
            {
                data: 'nombre_categoria',
                render: function (data) {
                    return `<strong>${data}</strong>`;
                }
            },
            {
                data: null,
                className: 'text-end',
                render: function () {
                    return botones;
                }
            }
        ],
        order: [[0, 'asc']],
        language: { url: idiomaTabla } // Asumiento que idiomaTabla es una config global
    });
}

// Event Listeners
$(document).ready(function () {
    crearDataTable();

    // Registrar listeners de inputs (capitalización + bloqueo + botón en tiempo real)
    inicializarInputListeners();

    $("#btnNuevaCategoria").on("click", function () {
        limpiar();
        editarModal("registrar");
    });

    $("#btnGuardarCategoria").on("click", function (e) {
        e.preventDefault();
        let peticion = $('#peticionCategoria').val();
        enviarDatos(peticion);
    });

    // Asegurar botón deshabilitado al mostrar el modal (para registrar)
    $('#modalCategoria').on('show.bs.modal', function () {
        // Solo deshabilitar si es una operación de registro (formulario vacío)
        const peticion = $('#peticionCategoria').val();
        if (peticion === 'registrar') {
            $('#btnGuardarCategoria').prop('disabled', true);
        }
    });
});
