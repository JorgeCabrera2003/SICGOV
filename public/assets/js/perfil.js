import { mensajes, confirmarAccion } from './Helpers/UIHelper.js';
import { SistemaValidacion } from './Helpers/ValidationHelper.js';
import { enviaAjax } from './Helpers/AjaxHelper.js';

$(document).ready(function () {
    // 1. Initial State & Setup
    aplicarMascaraTelefono();
    inicializarValidacionesTiempoReal();

    // 2. Tab Animation Enhancement
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const targetId = $(e.target).attr('data-bs-target');
        $(targetId).addClass('show');
    });

    // 4. Update Username Form & Validation
    const inputUsername = $('#username_input');
    const spanUsername = $('#susername_input');
    const btnUsername = $('#btnGuardarUsername');

    function validarUsername() {
        const val = inputUsername.val().trim();
        const tieneValor = val !== '';
        // Min 3 chars, ONLY letters
        const valido = val.length >= 3 && /^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ]+$/.test(val);
        const msg = 'Debe tener al menos 3 caracteres y solo letras.';

        if (!tieneValor) {
            inputUsername.removeClass('is-valid is-invalid');
            spanUsername.removeClass('invalid-tooltip d-inline-block').text('');
            btnUsername.prop('disabled', true);
            return false;
        }

        if (!valido) {
            inputUsername.addClass('is-invalid').removeClass('is-valid');
            spanUsername.addClass('invalid-tooltip d-inline-block').text(msg);
            btnUsername.prop('disabled', true);
            return false;
        } else {
            inputUsername.addClass('is-valid').removeClass('is-invalid');
            spanUsername.removeClass('invalid-tooltip d-inline-block').text('');
            btnUsername.prop('disabled', false);
            return true;
        }
    }

    if (typeof validarKeyPress === 'function') {
        inputUsername.on('keypress', function (e) { validarKeyPress(/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ]*$/, e); });
    }
    inputUsername.on('input', validarUsername);
    // Initial call to set button state
    validarUsername();

    $('#formActualizarUsername').on('submit', async function (e) {
        e.preventDefault();

        if (!validarUsername()) {
            mensajes("error", 4000, "Campo Inválido", "El nombre de usuario no cumple con los requisitos.");
            return;
        }

        const username = inputUsername.val().trim();
        const form = this;

        const confirm = await confirmarAccion("Actualizar Usuario", "¿Desea cambiar su nombre de usuario?", "question");
        if (confirm) {
            btnUsername.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...');

            try {
                const data = new FormData(form);
                const res = await enviaAjax(data);

                if (res && res.resultado === 200) {
                    mensajes("success", 3000, "Éxito", res.mensaje);
                    // Update username in profile header
                    $('.profile-username span:first-child').text('@' + username);
                    inputUsername.removeClass('is-valid is-invalid');
                } else {
                    mensajes("error", 5000, "Error", res.mensaje || "No se pudo actualizar el nombre de usuario.");
                }
            } catch (err) {
                mensajes("error", 5000, "Error", "Error de conexión con el servidor.");
            } finally {
                btnUsername.prop('disabled', false).html('<i class="bi bi-save me-2"></i>Actualizar');
            }
        }
    });

    // 5. Telephone Input Mask (7 digits)
    function aplicarMascaraTelefono() {
        $('#telefono').on('input', function () {
            let val = $(this).val().replace(/\D/g, ''); // Numbers only
            if (val.length > 7) val = val.substring(0, 7);
            $(this).val(val);
        });
    }

    // 6. Real-time form input styling listeners & Validation System (Similar to Cliente)
    function etiquetasFormularioPerfil(etiquetas) {
        const input = {
            nombre: $('#nombre'),
            apellido: $('#apellido'),
            correo: $('#correo'),
            telefono: $('#telefono'),
            sexo: $('#sexo'),
            fecha_nacimiento: $('#fecha_nacimiento'),
            direccion: $('#direccion')
        };
        const span = {
            snombre: $('#snombre'),
            sapellido: $('#sapellido'),
            scorreo: $('#scorreo'),
            stelefono: $('#stelefono'),
            ssexo: $('#ssexo'),
            sfecha_nacimiento: $('#sfecha_nacimiento'),
            sdireccion: $('#sdireccion')
        };
        return etiquetas === "input" ? input : (etiquetas === "span" ? span : null);
    }

    function validarCamposPerfil() {
        const input = etiquetasFormularioPerfil('input');
        const btnGuardar = $('#btnGuardarPerfil');
        let formularioValido = true;

        function aplicar($campo, $span, valido, msg) {
            const val = typeof $campo.val === 'function' ? $campo.val() : '';
            const tieneValor = val !== '' && val !== 'default' && val !== null;

            if (!tieneValor && !valido) {
                if ($campo.data('touched')) {
                    $campo.addClass('is-invalid').removeClass('is-valid');
                    $span.addClass('invalid-tooltip d-inline-block').text(msg);
                } else {
                    $campo.removeClass('is-valid is-invalid');
                    $span.removeClass('invalid-tooltip d-inline-block').text('');
                }
            } else if (tieneValor && !valido) {
                $campo.addClass('is-invalid').removeClass('is-valid');
                $span.addClass('invalid-tooltip d-inline-block').text(msg);
            } else if (valido) {
                if (tieneValor) {
                    $campo.addClass('is-valid').removeClass('is-invalid');
                } else {
                    $campo.removeClass('is-valid is-invalid');
                }
                $span.removeClass('invalid-tooltip d-inline-block').text('');
            } else {
                $campo.removeClass('is-valid is-invalid');
                $span.removeClass('invalid-tooltip d-inline-block').text('');
            }

            if (!valido) formularioValido = false;
        }

        // Nombre
        const nombre = input.nombre.val().trim();
        const nombreValido = nombre.length >= 2 && /^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ \b]*$/.test(nombre);
        aplicar(input.nombre, $('#snombre'), nombreValido, 'El nombre debe tener al menos 2 caracteres y solo letras.');

        // Apellido
        const apellido = input.apellido.val().trim();
        const apellidoValido = apellido.length >= 2 && /^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ \b]*$/.test(apellido);
        aplicar(input.apellido, $('#sapellido'), apellidoValido, 'El apellido debe tener al menos 2 caracteres y solo letras.');

        // Correo
        const correo = input.correo.val().trim();
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const correoValido = regexEmail.test(correo);
        aplicar(input.correo, $('#scorreo'), correoValido, 'El formato del correo no es válido.');

        // Teléfono
        const prefijo = $('#prefijo_telefono').val() || '';
        const numTelefono = input.telefono.val().trim();
        const telefonoCompleto = prefijo + '-' + numTelefono;
        const regexTelefono = /^\d{4}-\d{7}$/;
        const telefonoValido = regexTelefono.test(telefonoCompleto);
        aplicar(input.telefono, $('#stelefono'), telefonoValido, 'Ingrese 7 dígitos numéricos.');

        // Sexo
        const sexoVal = input.sexo.val();
        const sexoValido = sexoVal && sexoVal !== 'default';
        aplicar(input.sexo, $('#ssexo'), sexoValido, 'El sexo es obligatorio.');

        // Fecha de Nacimiento
        const fechaNac = input.fecha_nacimiento.val();
        const fechaValida = fechaNac !== '' && new Date(fechaNac) < new Date();
        aplicar(input.fecha_nacimiento, $('#sfecha_nacimiento'), fechaValida, 'La fecha de nacimiento es obligatoria y debe ser válida.');

        // Dirección
        const direccion = input.direccion.val().trim();
        const direccionValida = direccion.length >= 3 && direccion.length <= 200;
        aplicar(input.direccion, $('#sdireccion'), direccionValida, 'La dirección debe tener entre 3 y 200 caracteres.');

        btnGuardar.prop('disabled', !formularioValido);
        return formularioValido;
    }

    function manejarCambioEstadoPerfil(valido) {
        validarCamposPerfil();
    }

    function capaValidarPerfil() {
        const input = etiquetasFormularioPerfil('input');

        function marcarYValidar() {
            $(this).data('touched', true);
            validarCamposPerfil();
        }

        if (typeof validarKeyPress === 'function') {
            input.nombre.on('keypress', function (e) { validarKeyPress(/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ \b]*$/, e); });
            input.apellido.on('keypress', function (e) { validarKeyPress(/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ \b]*$/, e); });
        }

        input.nombre.on('input', function () {
            const val = $(this).val();
            if (val.length === 1) $(this).val(val.toUpperCase());
            marcarYValidar.call(this);
        });
        input.apellido.on('input', function () {
            const val = $(this).val();
            if (val.length === 1) $(this).val(val.toUpperCase());
            marcarYValidar.call(this);
        });

        input.correo.on('input', marcarYValidar);
        input.telefono.on('input', marcarYValidar);
        input.sexo.on('change', marcarYValidar);
        input.fecha_nacimiento.on('change', marcarYValidar);
        input.direccion.on('input', marcarYValidar);
    }

    function inicializarValidacionesTiempoReal() {
        // Inicializar Sistema de Validación Global
        if (typeof SistemaValidacion !== 'undefined') {
            SistemaValidacion.inicializar(etiquetasFormularioPerfil('input'), manejarCambioEstadoPerfil);
        }
        capaValidarPerfil();
        validarCamposPerfil();
    }

    // 7. Edit Profile Form Submission
    $('#formEditarPerfil').on('submit', async function (e) {
        e.preventDefault();

        // Forzar marcado "touched" en todos los campos al intentar enviar
        const input = etiquetasFormularioPerfil('input');
        Object.values(input).forEach($el => $el.data('touched', true));

        let esValidoGeneral = typeof SistemaValidacion !== 'undefined' ? SistemaValidacion.validarFormulario(etiquetasFormularioPerfil('input')) : true;

        if (!validarCamposPerfil() || !esValidoGeneral) {
            mensajes("error", 4000, "Error de Validación", "Por favor corrija los errores en el formulario antes de guardar.");
            return;
        }

        const form = this;
        const nombre = input.nombre.val().trim();
        const apellido = input.apellido.val().trim();

        const confirm = await confirmarAccion("Modificar Perfil", "¿Desea guardar los cambios en su perfil?", "question");
        if (confirm) {
            $('#btnGuardarPerfil').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...');

            try {
                const data = new FormData(form);
                // Combinar prefijo y teléfono antes de enviar
                const prefijo = $('#prefijo_telefono').val() || '';
                const numero = input.telefono.val().trim();
                if (prefijo && numero) {
                    data.set('telefono', prefijo + '-' + numero);
                }
                
                const res = await enviaAjax(data);

                if (res && res.resultado === 200) {
                    mensajes("success", 3000, "Éxito", res.mensaje);

                    // Update header name and global displays
                    const fullName = nombre + ' ' + apellido;
                    $('.profile-name').text(fullName);
                    $('.user-name').text(nombre);
                    $('#userDropdown span').text(nombre);

                    // Reset validation styling but keep values
                    Object.values(input).forEach($el => {
                        $el.removeClass('is-valid is-invalid').removeData('touched');
                    });
                    const span = etiquetasFormularioPerfil('span');
                    Object.values(span).forEach($el => {
                        $el.removeClass('invalid-tooltip d-inline-block').text('');
                    });
                    $('#btnGuardarPerfil').prop('disabled', false);
                } else {
                    mensajes("error", 5000, "Error", res.mensaje || "No se pudo actualizar el perfil.");
                }
            } catch (err) {
                mensajes("error", 5000, "Error", "Error de conexión con el servidor.");
            } finally {
                $('#btnGuardarPerfil').html('<i class="bi bi-save me-2"></i>Guardar Cambios');
            }
        }
    });

    // 8. Change Password Form & Validation
    const inputClaveNueva = $('#clave_nueva');
    const inputClaveConfirmar = $('#clave_confirmar');
    const spanClaveNueva = $('#sclave_nueva');
    const spanClaveConfirmar = $('#sclave_confirmar');
    const btnClave = $('#btnGuardarClave');

    function validarClaves() {
        let formValido = true;

        function aplicarC($campo, $span, valido, msg) {
            const val = $campo.val();
            const tieneValor = val !== '';

            if (!tieneValor) {
                $campo.removeClass('is-valid is-invalid');
                $span.removeClass('invalid-tooltip d-inline-block').text('');
                formValido = false;
            } else if (!valido) {
                $campo.addClass('is-invalid').removeClass('is-valid');
                $span.addClass('invalid-tooltip d-inline-block').text(msg);
                formValido = false;
            } else {
                $campo.addClass('is-valid').removeClass('is-invalid');
                $span.removeClass('invalid-tooltip d-inline-block').text('');
            }
        }

        // Nueva
        const nueva = inputClaveNueva.val();
        // Regex: 8 chars, 1 uppercase, 1 number, 1 special character
        const regexClave = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/;
        const nuevaValida = regexClave.test(nueva);
        aplicarC(inputClaveNueva, spanClaveNueva, nuevaValida, 'Mínimo 8 caracteres, 1 mayúscula, 1 número y 1 símbolo.');

        // Confirmar
        const confirmar = inputClaveConfirmar.val();
        const confirmarValida = (confirmar === nueva) && confirmar.length > 0;
        aplicarC(inputClaveConfirmar, spanClaveConfirmar, confirmarValida, 'Las contraseñas no coinciden.');

        btnClave.prop('disabled', !formValido);
        return formValido;
    }

    inputClaveNueva.on('input', validarClaves);
    inputClaveConfirmar.on('input', validarClaves);
    // Initial check to disable button
    validarClaves();

    $('#formCambiarClave').on('submit', async function (e) {
        e.preventDefault();

        if (!validarClaves()) {
            mensajes("error", 4000, "Error de Validación", "Por favor cumpla con los requisitos de la contraseña.");
            return;
        }

        const form = this;

        const confirm = await confirmarAccion("Cambiar Contraseña", "¿Está seguro de que desea cambiar su contraseña de acceso?", "warning");
        if (confirm) {
            btnClave.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cambiando...');

            try {
                const data = new FormData(form);
                const res = await enviaAjax(data);

                if (res && res.resultado === 200) {
                    mensajes("success", 3000, "Éxito", res.mensaje);
                    form.reset();
                    inputClaveNueva.removeClass('is-valid is-invalid');
                    inputClaveConfirmar.removeClass('is-valid is-invalid');
                    validarClaves();
                } else {
                    mensajes("error", 5000, "Error", res.mensaje || "No se pudo cambiar la contraseña.");
                }
            } catch (err) {
                mensajes("error", 5000, "Error", "Error al comunicarse con el servidor.");
            } finally {
                btnClave.html('<i class="bi bi-shield-check me-2"></i>Actualizar Contraseña');
            }
        }
    });

    // Timeline removed
});
