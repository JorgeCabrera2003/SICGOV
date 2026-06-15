// ==========================================
// MÓDULO DE USUARIOS - GOOD VIBES
// JS / AJAX & DATATABLE LOGIC
// ==========================================

// Listas de control anti-hackeo (pobladas desde el servidor al cargar)
let rolesValidosList = [];
let empleadosValidosList = [];
let cedulaEdicionOriginal = '';

// Observador del DOM (anti-hackeo via Inspect Element)
let domObserver;

// Interfaz de Acceso a Elementos del Formulario
function etiquetasFormulario() {
    return {
        peticion: $('#peticionUsuario'),
        cedula: $('#cedula'),
        cedula_editar: $('#cedula_editar'),
        username: $('#username'),
        rol: $('#rol'),
        clave: $('#clave'),
        rclave: $('#rclave')
    };
}

// Interfaz de Acceso al Modal Principal
function etiquetasModal() {
    return {
        modal: $('#modalUsuario'),
        titulo: $('#modalTitleTextUsuario'),
        boton: $('#btnUsuarioForm'),
        formulario: $('#formUsuario')
    };
}

// Editar la configuración visual del Modal dependiendo de la operación
function editarModal(operacion) {
    let titulo = "";
    let boton = "";
    let eti = etiquetasModal();

    if (operacion === 'registrar') {
        titulo = "Nuevo Usuario";
        boton = "Guardar Usuario";
    }

    if (operacion === 'modificar') {
        titulo = "Actualizar Usuario";
        boton = "Actualizar Usuario";
    }

    eti.titulo.text(titulo);
    eti.boton.html(`<i class="fas fa-save me-2"></i>${boton}`);
    eti.modal.modal("show");
}

// Limpiar el formulario y reestablecer su estado inicial
function limpiar() {
    let input = etiquetasFormulario();
    let eti = etiquetasModal();

    eti.formulario[0].reset();
    input.peticion.val("registrar");

    eti.formulario.removeClass('was-validated');

    // Resetear estilos de validación
    input.cedula.val("").prop("disabled", false).removeClass('is-valid is-invalid');
    input.username.val("").removeClass('is-valid is-invalid');
    input.rol.val("").prop("disabled", false).removeClass('is-valid is-invalid');
    input.clave.val("").removeClass('is-valid is-invalid');
    input.rclave.val("").removeClass('is-valid is-invalid');

    // Restablecer vistas de selección vs detalles
    $('#grupo-seleccion-empleado').removeClass('d-none');
    $('#grupo-detalle-empleado').addClass('d-none');
    $('#txt-empleado-nombre').text('');
    $('#txt-empleado-cedula').text('');
    input.cedula_editar.val('');

    // Restablecer obligatoriedad de contraseñas
    $('#help-clave').addClass('d-none');
    $('#req-clave').removeClass('d-none');
    $('#req-rclave').removeClass('d-none');
    input.clave.attr('required', true);
    input.rclave.attr('required', true);

    // Limpiar feedbacks flotantes
    $('#feedback_cedula').removeClass('invalid-tooltip d-inline-block').text('');
    $('#feedback_cedula_editar').removeClass('invalid-tooltip d-inline-block').text('');
    $('#feedback_rol').removeClass('invalid-tooltip d-inline-block').text('');
    $('#feedback_username').removeClass('invalid-tooltip d-inline-block').text('');
    $('#feedback_clave').removeClass('invalid-tooltip d-inline-block').text('');
    $('#feedback_rclave').removeClass('invalid-tooltip d-inline-block').text('');

    // Resetear variable de control anti-hackeo para cédula
    cedulaEdicionOriginal = '';

    // Deshabilitar botón (formulario vacío = inválido)
    eti.boton.prop('disabled', true);

    // Cargar listas desplegables dinámicas (roles se cargan una única vez al iniciar)
    cargarEmpleadosSinUsuario();
}

/**
 * Aplica estilos de validación en los campos y muestra mensajes de feedback.
 */
function aplicarEstilosCampo($campo, $feedback, esValido, mensaje, forzar = false) {
    const val = $campo.val() ? $campo.val().trim() : '';
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
 * Valida el estado de todos los inputs en tiempo real y gestiona el botón Guardar.
 */
function verificarEstadoBoton() {
    const input = etiquetasFormulario();
    const boton = etiquetasModal().boton;
    const peticion = input.peticion.val();

    // ── 1. Evaluar Empleado/Cédula ───────────────────────────
    let cedulaValida = true;
    let mensajeCedula = '';
    if (peticion === 'registrar') {
        const ced = input.cedula.val();
        if (!ced || ced === "") {
            // Sin selección: deshabilita el botón sin mostrar tooltip (campo virgen)
            cedulaValida = false;
            aplicarEstilosCampo(input.cedula, $('#feedback_cedula'), false, '', false);
        } else if (empleadosValidosList.length > 0 && !empleadosValidosList.includes(String(ced))) {
            // Valor manipulado con Inspeccionar: mostrar tooltip de inmediato
            cedulaValida = false;
            mensajeCedula = 'El valor del empleado seleccionado no existe.';
            aplicarEstilosCampo(input.cedula, $('#feedback_cedula'), false, mensajeCedula, true);
        } else {
            // Válido
            aplicarEstilosCampo(input.cedula, $('#feedback_cedula'), true, '', false);
        }
    } else {
        // En modo modificar, verificar que cedula_editar no haya sido alterada
        const cedulaActual = input.cedula_editar.val();
        if (cedulaEdicionOriginal && cedulaActual !== cedulaEdicionOriginal) {
            cedulaValida = false;
            mensajeCedula = 'El valor del empleado seleccionado no existe.';
            // Mostrar el mensaje en el área visible del detalle de empleado
            $('#feedback_cedula_editar').addClass('invalid-tooltip d-inline-block').text(mensajeCedula);
        } else {
            $('#feedback_cedula_editar').removeClass('invalid-tooltip d-inline-block').text('');
        }
    }

    // ── 2. Evaluar Nombre de Usuario ─────────────────────────
    const username = input.username.val() ? input.username.val().trim() : '';
    let usernameValido = true;
    let mensajeUsername = '';

    const regexSoloLetras = /^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ]+$/;

    if (!username) {
        usernameValido = false;
        mensajeUsername = 'El nombre de usuario es obligatorio.';
    } else if (username.length < 3) {
        usernameValido = false;
        mensajeUsername = 'Debe tener al menos 3 letras.';
    } else if (!regexSoloLetras.test(username)) {
        usernameValido = false;
        mensajeUsername = 'Debe contener solamente letras.';
    }

    aplicarEstilosCampo(input.username, $('#feedback_username'), usernameValido, mensajeUsername);

    // ── 3. Evaluar Rol del Sistema ───────────────────────────
    const rol = input.rol.val();
    let rolValido = true;
    let mensajeRol = '';

    if (!rol) {
        // Sin selección: deshabilita el botón sin mostrar tooltip (campo virgen)
        rolValido = false;
        aplicarEstilosCampo(input.rol, $('#feedback_rol'), false, '', false);
    } else if (rolesValidosList.length > 0 && !rolesValidosList.includes(String(rol))) {
        // Valor manipulado con Inspeccionar: mostrar tooltip de inmediato
        rolValido = false;
        mensajeRol = 'El rol seleccionado no es válido.';
        aplicarEstilosCampo(input.rol, $('#feedback_rol'), false, mensajeRol, true);
    } else {
        // Válido
        aplicarEstilosCampo(input.rol, $('#feedback_rol'), true, '', false);
    }

    // ── 4. Evaluar Contraseña ────────────────────────────────
    const clave = input.clave.val() || '';
    const rclave = input.rclave.val() || '';

    let claveValida = true;
    let mensajeClave = '';
    let rclaveValida = true;
    let mensajeRclave = '';

    if (peticion === 'registrar') {
        // Obligatorio en registrar
        if (!clave) {
            claveValida = false;
            mensajeClave = 'La contraseña es obligatoria.';
        } else if (clave.length < 4) {
            claveValida = false;
            mensajeClave = 'Debe tener al menos 4 caracteres.';
        }

        if (!rclave) {
            rclaveValida = false;
            mensajeRclave = 'Por favor confirme la contraseña.';
        } else if (clave !== rclave) {
            rclaveValida = false;
            mensajeRclave = 'Las contraseñas no coinciden.';
        }
    } else {
        // Opcional en modificar
        if (clave !== '') {
            if (clave.length < 4) {
                claveValida = false;
                mensajeClave = 'Debe tener al menos 4 caracteres.';
            }

            if (!rclave) {
                rclaveValida = false;
                mensajeRclave = 'Por favor confirme la contraseña.';
            } else if (clave !== rclave) {
                rclaveValida = false;
                mensajeRclave = 'Las contraseñas no coinciden.';
            }
        } else {
            if (rclave !== '') {
                rclaveValida = false;
                mensajeRclave = 'Debe ingresar la contraseña primero.';
            }
        }
    }

    aplicarEstilosCampo(input.clave, $('#feedback_clave'), claveValida, mensajeClave);
    aplicarEstilosCampo(input.rclave, $('#feedback_rclave'), rclaveValida, mensajeRclave);

    const formularioValido = cedulaValida && usernameValido && rolValido && claveValida && rclaveValida;
    boton.prop('disabled', !formularioValido);
}

// Carga asíncrona de los roles activos del sistema
async function cargarRolesActivos() {
    try {
        let peticion = new FormData();
        peticion.append('peticion', 'roles-activos');
        let json = await enviaAjax(peticion);

        let $rolSelect = $('#rol');
        $rolSelect.empty();
        $rolSelect.append('<option value="" selected disabled>Seleccione un rol...</option>');

        // Resetear lista de control anti-hackeo
        rolesValidosList = [];

        if (json && json.datos) {
            json.datos.forEach(rol => {
                $rolSelect.append(`<option value="${rol.id_rol}">${rol.nombre_rol}</option>`);
                rolesValidosList.push(String(rol.id_rol));
            });
        }
    } catch (e) {
        console.error("Error al cargar roles:", e);
    }
}

// Carga asíncrona de empleados que no tienen cuenta de usuario creada
async function cargarEmpleadosSinUsuario() {
    try {
        let peticion = new FormData();
        peticion.append('peticion', 'empleados-sin-usuario');
        let json = await enviaAjax(peticion);

        let $cedulaSelect = $('#cedula');
        $cedulaSelect.empty();
        $cedulaSelect.append('<option value="" selected disabled>Seleccione un empleado...</option>');

        // Resetear lista de control anti-hackeo
        empleadosValidosList = [];

        if (json && json.datos && json.datos.length > 0) {
            json.datos.forEach(emp => {
                $cedulaSelect.append(`<option value="${emp.cedula}">${emp.nombre} ${emp.apellido} (Cédula: ${emp.cedula})</option>`);
                empleadosValidosList.push(String(emp.cedula));
            });
        } else {
            $cedulaSelect.append('<option value="" disabled>No hay empleados disponibles sin usuario</option>');
        }
    } catch (e) {
        console.error("Error al cargar empleados sin usuario:", e);
    }
}

// Inicialización de Listeners de Eventos en Inputs
function inicializarInputListeners() {
    const input = etiquetasFormulario();

    input.cedula.on('change', verificarEstadoBoton);
    input.rol.on('change', verificarEstadoBoton);

    // Detectar si el campo oculto cedula_editar es alterado desde Inspeccionar
    input.cedula_editar.on('input change', verificarEstadoBoton);

    input.username.on('keypress', function (e) {
        const char = String.fromCharCode(e.which);
        if (!/[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ]/.test(char)) {
            e.preventDefault();
        }
    });

    input.username.on('input', verificarEstadoBoton);
    input.clave.on('input', verificarEstadoBoton);
    input.rclave.on('input', verificarEstadoBoton);

    // Alternar visibilidad de contraseñas (Eye Icon Toggle)
    $('.btn-pwd-toggle').off('click').on('click', function () {
        const targetSelector = $(this).attr('data-target');
        const $input = $(targetSelector);
        const $icon = $(this).find('i');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
}

// Enviar datos del formulario al backend mediante AJAX
async function enviarDatos(operacion) {
    let input = etiquetasFormulario();
    let modal = etiquetasModal();
    let form = modal.formulario[0];

    // Verificar validez una última vez
    verificarEstadoBoton();
    if (modal.boton.prop('disabled')) {
        mensajes("error", 5000, "Error de Validación", "Por favor completa correctamente los campos obligatorios.");
        return;
    }

    let confirmacion = false;
    let tituloAccion = "";
    if (operacion === "registrar") tituloAccion = "Se registrará un nuevo Usuario";
    if (operacion === "modificar") tituloAccion = "Se actualizará el Usuario";

    confirmacion = await confirmarAccion(tituloAccion, "¿Está seguro de realizar la acción?", "question");

    if (confirmacion) {
        modal.boton.prop('disabled', true);

        let peticionData = new FormData(form);
        peticionData.set('peticion', operacion);

        if (operacion === 'modificar') {
            peticionData.set('cedula', input.cedula_editar.val());
        }

        try {
            let json = await enviaAjax(peticionData);

            if (json && json.resultado >= 200 && json.resultado < 300) {
                modal.modal.modal("hide");
                crearDataTable();
                mensajes("success", 3000, "Éxito", json.mensaje);
            } else {
                mensajes("error", 5000, "Error", (json && json.mensaje) || "Ocurrió un error inesperado.");
            }
        } catch (error) {
            mensajes("error", 5000, "Error", "Error de comunicación con el servidor.");
        } finally {
            modal.boton.prop('disabled', false);
        }
    }
}

// Rellenar datos en el modal para operación de Modificar (Edición)
function rellenar(pos, accion) {
    limpiar();

    let input = etiquetasFormulario();
    const linea = $(pos).closest('tr');
    const tabla = $('#tabla-usuario').DataTable();
    const datosFila = tabla.row(linea).data();

    // Rellenar datos clave
    input.cedula_editar.val(datosFila.cedula);
    input.username.val(datosFila.username);

    // Guardar la cédula original para detectar manipulaciones en modo editar
    cedulaEdicionOriginal = String(datosFila.cedula);

    // Asignar el rol usando helper del sistema
    buscarSelect(input.rol, datosFila.id_rol, "value");

    // Configurar modo de Modificación
    input.peticion.val("modificar");

    // Intercambiar visibilidades de sección empleado
    $('#grupo-seleccion-empleado').addClass('d-none');
    $('#grupo-detalle-empleado').removeClass('d-none');
    $('#txt-empleado-nombre').text(`${datosFila.nombre} ${datosFila.apellido}`);
    $('#txt-empleado-cedula').text(`Cédula: ${datosFila.cedula}`);

    // Configurar contraseñas como opcionales
    $('#help-clave').removeClass('d-none');
    $('#req-clave').addClass('d-none');
    $('#req-rclave').addClass('d-none');
    input.clave.removeAttr('required');
    input.rclave.removeAttr('required');

    if (accion === 0) {
        editarModal("modificar");
    }

    // Revalidar campos pre-cargados
    verificarEstadoBoton();
}

// Cambiar el estatus del usuario (Activo 1 / Inactivo 0) directamente
async function toggleEstatus(pos, targetEstatus) {
    const linea = $(pos).closest('tr');
    const tabla = $('#tabla-usuario').DataTable();
    const datosFila = tabla.row(linea).data();

    const nombreCompleto = `${datosFila.nombre} ${datosFila.apellido}`;
    const textoConfirmacion = targetEstatus == 1
        ? `¿Está seguro de activar al usuario ${nombreCompleto}?`
        : `¿Está seguro de inactivar al usuario ${nombreCompleto}?`;

    let confirmacion = await confirmarAccion(
        targetEstatus == 1 ? "Activar Usuario" : "Inactivar Usuario",
        textoConfirmacion,
        targetEstatus == 1 ? "question" : "warning"
    );

    if (confirmacion) {
        let peticionData = new FormData();
        peticionData.append('peticion', 'toggle-estatus');
        peticionData.append('cedula', datosFila.cedula);
        peticionData.append('estatus', targetEstatus);

        try {
            let json = await enviaAjax(peticionData);

            if (json && json.resultado >= 200 && json.resultado < 300) {
                crearDataTable();
                mensajes("success", 3000, "Éxito", json.mensaje);
            } else {
                mensajes("error", 5000, "Error", (json && json.mensaje) || "Ocurrió un error inesperado.");
            }
        } catch (error) {
            mensajes("error", 5000, "Error", "Error de comunicación con el servidor.");
        }
    }
}

// Forzar cambio de clave
async function forzarCambioClave(pos) {
    const linea = $(pos).closest('tr');
    const tabla = $('#tabla-usuario').DataTable();
    const datosFila = tabla.row(linea).data();

    const nombreUsuario = datosFila.username;
    const textoConfirmacion = `¿Está seguro de forzar a ${nombreUsuario} a cambiar su contraseña en su próximo inicio de sesión?`;

    let confirmacion = await confirmarAccion(
        "Forzar Cambio de Clave",
        textoConfirmacion,
        "warning"
    );

    if (confirmacion) {
        let peticionData = new FormData();
        peticionData.append('peticion', 'forzar-clave');
        peticionData.append('cedula', datosFila.cedula);

        try {
            let json = await enviaAjax(peticionData);

            if (json && json.resultado >= 200 && json.resultado < 300) {
                mensajes("success", 3000, "Éxito", json.mensaje);
            } else {
                mensajes("error", 5000, "Error", (json && json.mensaje) || "Ocurrió un error inesperado.");
            }
        } catch (error) {
            mensajes("error", 5000, "Error", "Error de comunicación con el servidor.");
        }
    }
}

// Inicialización de la DataTable principal
async function crearDataTable() {
    if (typeof permisosDB === 'undefined' || !permisosDB || !permisosDB.usuario || permisosDB.usuario.ver != 1) {
        $('#tabla-usuario').closest('.card').html('<div class="card-body text-center py-5"><i class="fas fa-lock fs-1 text-danger mb-3"></i><h4 class="text-danger">Acceso Denegado</h4><p>No tienes permiso para ver la lista de usuarios.</p></div>');
        return;
    }

    let peticion = new FormData();
    let arreglo = [];

    try {
        peticion.append('peticion', 'consultar');
        let json = await enviaAjax(peticion);
        arreglo = json.datos || [];
    } catch (error) {
        arreglo = [];
    }

    if ($.fn.DataTable.isDataTable('#tabla-usuario')) {
        $('#tabla-usuario').DataTable().destroy();
    }

    $('#tabla-usuario').DataTable({
        responsive: true,
        processing: true,
        data: arreglo,
        columns: [
            {
                data: 'username',
                render: function (data) {
                    return `<strong>${data}</strong>`;
                }
            },
            { data: 'rol' },
            {
                data: 'estatus',
                render: function (data) {
                    if (data == 1) {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill"><i class="fas fa-circle-check me-1"></i>Activo</span>';
                    } else {
                        return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill"><i class="fas fa-circle-xmark me-1"></i>Inactivo</span>';
                    }
                }
            },
            {
                data: null,
                className: 'text-end',
                render: function (data, type, row) {
                    const dropdown = $('<div>').addClass('dropdown');
                    const boton = $('<button>').addClass('btn btn-sm bg-body text-body border dropdown-toggle')
                        .attr('type', 'button')
                        .attr('data-bs-toggle', 'dropdown')
                        .html('<i class="fas fa-ellipsis-v me-2"></i>Acciones');

                    const menu = $('<ul>').addClass('dropdown-menu dropdown-menu-end');

                    if (typeof permisosDB !== 'undefined' && permisosDB && permisosDB.usuario && permisosDB.usuario.modificar == 1) {
                        const itemEditar = $('<li>');
                        const linkEditar = $('<a>')
                            .addClass('dropdown-item text-primary')
                            .attr('href', '#')
                            .attr('onclick', 'rellenar(this, 0)')
                            .html('<i class="fa-solid fa-pen-to-square me-2"></i>Editar');
                        itemEditar.append(linkEditar);
                        menu.append(itemEditar);
                    }

                    // Cambiar estado (Activar / Inactivar)
                    const esActivo = row.estatus == 1;
                    let addToggle = false;
                    if (typeof permisosDB !== 'undefined' && permisosDB && permisosDB.usuario) {
                        if (esActivo && permisosDB.usuario.eliminar == 1) addToggle = true;
                        if (!esActivo && permisosDB.usuario.modificar == 1) addToggle = true;
                    }

                    if (addToggle) {
                        if (menu.children().length > 0) {
                            menu.append($('<li>').html('<hr class="dropdown-divider">'));
                        }
                        
                        const itemToggle = $('<li>');
                        const claseTexto = esActivo ? 'text-warning' : 'text-success';
                        const icono = esActivo ? 'fa-user-slash' : 'fa-user-check';
                        const textoAccion = esActivo ? 'Inactivar' : 'Activar';

                        const linkToggle = $('<a>')
                            .addClass(`dropdown-item ${claseTexto}`)
                            .attr('href', '#')
                            .attr('onclick', `toggleEstatus(this, ${esActivo ? 0 : 1})`)
                            .html(`<i class="fa-solid ${icono} me-2"></i>${textoAccion}`);
                        itemToggle.append(linkToggle);
                        menu.append(itemToggle);
                    }

                    if (typeof permisosDB !== 'undefined' && permisosDB && permisosDB.usuario && permisosDB.usuario.modificar == 1) {
                        // Forzar cambio de clave
                        const itemForzarClave = $('<li>');
                        const linkForzarClave = $('<a>')
                            .addClass('dropdown-item text-danger')
                            .attr('href', '#')
                            .attr('onclick', 'forzarCambioClave(this)')
                            .html('<i class="fa-solid fa-key me-2"></i>Forzar cambio de clave');
                        itemForzarClave.append(linkForzarClave);
                        menu.append(itemForzarClave);
                    }

                    if (menu.children().length === 0) {
                        return '<span class="text-muted"><i class="fas fa-lock"></i> Sin acciones</span>';
                    }

                    dropdown.append(boton, menu);

                    return dropdown.prop('outerHTML');
                }
            }
        ],
        order: [[0, 'asc']],
        language: { url: idiomaTabla }
    });
}

// DOM Ready
$(document).ready(function () {
    crearDataTable();
    inicializarInputListeners();
    inicializarObservadorDOM(); // Observador de mutaciones anti-hackeo
    cargarRolesActivos(); // Carga de roles de forma segura una única vez al iniciar

    // Evento Click para botón de Registro
    if ($("#btn-nuevo").length) {
        $("#btn-nuevo").on("click", function () {
            limpiar();
            editarModal("registrar");
        });
    }

    // Guardar/Actualizar
    $("#btnUsuarioForm").on("click", function (e) {
        e.preventDefault();
        let peticion = $('#peticionUsuario').val();
        enviarDatos(peticion);
    });

    // Asegurar validación al abrir modal
    $('#modalUsuario').on('show.bs.modal', function () {
        const peticion = $('#peticionUsuario').val();
        if (peticion === 'registrar') {
            $('#btnUsuarioForm').prop('disabled', true);
        }
    });

    // Reconectar el observador al mostrar el modal (los selects pueden haber cambiado)
    $('#modalUsuario').on('shown.bs.modal', function () {
        reconectarObservadorDOM();
    });

    // Desconectar el observador al cerrar el modal (limpieza)
    $('#modalUsuario').on('hidden.bs.modal', function () {
        if (domObserver) {
            domObserver.disconnect();
        }
    });
});

/**
 * Inicializa el MutationObserver para detectar manipulaciones del DOM
 * en los selects de rol y cédula (incluso desde Inspeccionar del navegador).
 * Mismo patrón que el módulo de Menú.
 */
function inicializarObservadorDOM() {
    domObserver = new MutationObserver((mutationsList) => {
        let shouldValidate = false;
        for (let mutation of mutationsList) {
            // Ignorar cambios de class/style para evitar bucle infinito
            // (verificarEstadoBoton agrega/quita is-valid, is-invalid, etc.)
            if (
                mutation.type === 'attributes' &&
                (mutation.attributeName === 'class' || mutation.attributeName === 'style')
            ) {
                continue;
            }
            shouldValidate = true;
            break;
        }
        if (shouldValidate) {
            verificarEstadoBoton();
            // Limpiar la cola de mutaciones generadas por verificarEstadoBoton
            // para evitar un bucle infinito
            domObserver.takeRecords();
        }
    });

    reconectarObservadorDOM();
}

/**
 * Reconecta el observador a los elementos del formulario.
 * Se llama al inicializar y cada vez que se abre el modal.
 */
function reconectarObservadorDOM() {
    if (!domObserver) return;

    domObserver.disconnect();

    const opcionesObserver = {
        attributes: true,
        childList: true,
        subtree: true,
        characterData: true
    };

    // Observar el select de empleado (cedula) — solo visible al registrar
    const selectCedula = document.getElementById('cedula');
    if (selectCedula) {
        domObserver.observe(selectCedula, opcionesObserver);
    }

    // Observar el select de rol
    const selectRol = document.getElementById('rol');
    if (selectRol) {
        domObserver.observe(selectRol, opcionesObserver);
    }

    // Observar el campo oculto cedula_editar — visible en modo modificar
    const inputCedulaEditar = document.getElementById('cedula_editar');
    if (inputCedulaEditar) {
        domObserver.observe(inputCedulaEditar, opcionesObserver);
    }
}
