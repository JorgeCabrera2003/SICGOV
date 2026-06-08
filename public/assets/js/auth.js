document.addEventListener('DOMContentLoaded', function () {
    const authCarouselElement = document.getElementById('authCarousel');
    let authCarousel = null;

    if (authCarouselElement) {
        authCarousel = new bootstrap.Carousel(authCarouselElement, {
            interval: false,
            touch: true,
            wrap: true
        });

        authCarouselElement.addEventListener('slide.bs.carousel', function (e) {
            const activeItem = document.querySelector('#authCarousel .carousel-item.active');
            if (activeItem) {
                activeItem.style.animation = '';
            }
        });

        authCarouselElement.addEventListener('slid.bs.carousel', function (e) {
            const activeItem = document.querySelector('#authCarousel .carousel-item.active');
            if (activeItem) {
                activeItem.style.animation = '';
                setTimeout(() => {
                    activeItem.style.filter = '';
                    activeItem.style.boxShadow = '';
                }, 100);
            }
        });
    }

    const openRegisterSlide = window.authOpenRegisterSlide === true;
    if (openRegisterSlide && authCarousel) {
        setTimeout(() => {
            authCarousel.to(1);
        }, 200);
    }

    const passwordToggles = document.querySelectorAll('[data-password-toggle]');
    passwordToggles.forEach(function (button) {
        const targetSelector = button.getAttribute('data-password-toggle');
        const target = document.querySelector(targetSelector);
        if (!target) return;

        button.addEventListener('click', function () {
            const type = target.getAttribute('type');
            const icon = button.querySelector('i');
            button.style.transform = 'scale(0.95)';
            setTimeout(() => {
                button.style.transform = '';
            }, 150);

            if (type === 'password') {
                target.setAttribute('type', 'text');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                target.setAttribute('type', 'password');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            const ci = loginForm.querySelector('[name="CI"]').value.trim();
            const password = loginForm.querySelector('[name="password"]').value.trim();
            if (!ci || !password) {
                event.preventDefault();
                loginForm.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    loginForm.style.animation = '';
                }, 500);
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos vacíos',
                    text: 'Por favor completa todos los campos antes de continuar.'
                });
            }
        });
    }

    // Validation functions
    function aplicarValidacion(input, span, isValid, mensaje) {
        if (!span || !input) return;

        input.classList.remove('is-valid', 'is-invalid');
        span.className = '';
        span.innerHTML = '';

        if (input.value.trim() === '') {
            span.style.display = 'none';
            return false;
        }

        if (isValid) {
            input.classList.add('is-valid');
            span.style.display = 'none';
        } else {
            input.classList.add('is-invalid');
            span.style.display = 'inline-block';
            span.className = 'invalid-tooltip px-2 py-1 mt-1 rounded';
            span.innerHTML = '<i class="fa fa-exclamation-triangle me-1"></i>' + mensaje;
        }
        return isValid;
    }

    function soloLetras(e) {
        const char = String.fromCharCode(e.which || e.keyCode);
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(char)) {
            e.preventDefault();
        }
    }

    function formatoNombres(input) {
        let val = input.value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
        input.value = val;
    }

    const regForm = document.getElementById('register-form');
    if (regForm) {
        const btnReg = regForm.querySelector('button[type="submit"]');

        const inputs = {
            nombre: document.getElementById('nombre'),
            apellido: document.getElementById('apellido'),
            nacionalidad: document.getElementById('nacionalidad'),
            cedula: document.getElementById('cedula'),
            fecha_nacimiento: document.getElementById('fecha_nacimiento'),
            sexo: document.getElementById('sexo'),
            correo: document.getElementById('correo'),
            numero_telefono: document.getElementById('numero_telefono'),
            prefijo_telefono: document.getElementById('prefijo_telefono'),
            direccion: document.getElementById('direccion'),
            username: document.getElementById('username'),
            clave: document.getElementById('clave'),
            rclave: document.getElementById('rclave')
        };
        const spans = {
            nombre: document.getElementById('snombre'),
            apellido: document.getElementById('sapellido'),
            cedula: document.getElementById('scedula'),
            fecha_nacimiento: document.getElementById('sfecha_nacimiento'),
            sexo: document.getElementById('ssexo'),
            correo: document.getElementById('scorreo'),
            telefono: document.getElementById('stelefono'),
            direccion: document.getElementById('sdireccion'),
            username: document.getElementById('susername'),
            clave: document.getElementById('sclave'),
            rclave: document.getElementById('srclave')
        };
        const hiddenTelefono = document.getElementById('telefono');

        // Prevent invalid chars on input
        if (inputs.nombre) inputs.nombre.addEventListener('keypress', soloLetras);
        if (inputs.apellido) inputs.apellido.addEventListener('keypress', soloLetras);
        if (inputs.cedula) inputs.cedula.addEventListener('input', function () { this.value = this.value.replace(/\D/g, ''); });
        if (inputs.numero_telefono) inputs.numero_telefono.addEventListener('input', function () { this.value = this.value.replace(/\D/g, ''); });
        if (inputs.username) inputs.username.addEventListener('input', function () { this.value = this.value.replace(/[^a-zA-Z]/g, ''); });

        let cedulaDisponible = true;
        let cedulaEnChequeo = '';

        async function chequearCedula() {
            if (inputs.cedula && inputs.nacionalidad) {
                const ced = inputs.cedula.value.trim();
                const nac = inputs.nacionalidad.value;
                if (ced.length >= 7 && ced.length <= 9 && nac !== '') {
                    const checkStr = nac + '-' + ced;
                    if (cedulaEnChequeo === checkStr) return;

                    cedulaEnChequeo = checkStr;

                    const formData = new FormData();
                    formData.append('peticion', 'verificar_cedula');
                    formData.append('nacionalidad', nac);
                    formData.append('cedula', ced);

                    try {
                        // El form action de login manda a ?page=login (o BASE_URL si es index)
                        const res = await fetch(BASE_URL + '/?page=login', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await res.json();
                        cedulaDisponible = !data.existe;
                        validarFormulario();
                    } catch (e) {
                        console.error("Error validando cédula:", e);
                    }
                } else {
                    cedulaDisponible = true;
                }
            }
        }

        if (inputs.cedula) inputs.cedula.addEventListener('blur', chequearCedula);
        if (inputs.nacionalidad) inputs.nacionalidad.addEventListener('change', chequearCedula);

        function validarFormulario() {
            let isValid = true;

            // Nombre
            if (inputs.nombre) {
                formatoNombres(inputs.nombre);
                let valid = inputs.nombre.value.trim().length >= 3;
                if (!aplicarValidacion(inputs.nombre, spans.nombre, valid, 'Mínimo 3 caracteres, solo letras.')) isValid = false;
            }

            // Apellido
            if (inputs.apellido) {
                formatoNombres(inputs.apellido);
                let valid = inputs.apellido.value.trim().length >= 3;
                if (!aplicarValidacion(inputs.apellido, spans.apellido, valid, 'Mínimo 3 caracteres, solo letras.')) isValid = false;
            }

            // Cedula
            if (inputs.cedula && inputs.nacionalidad) {
                let valid = inputs.cedula.value.trim().length >= 7 && inputs.cedula.value.trim().length <= 9 && inputs.nacionalidad.value !== '';
                if (!valid) {
                    if (!aplicarValidacion(inputs.cedula, spans.cedula, false, 'Seleccione nacionalidad y 7-9 dígitos.')) isValid = false;
                } else if (!cedulaDisponible) {
                    if (!aplicarValidacion(inputs.cedula, spans.cedula, false, 'La cédula ya está registrada.')) isValid = false;
                } else {
                    aplicarValidacion(inputs.cedula, spans.cedula, true, '');
                }
            }

            // Fecha
            if (inputs.fecha_nacimiento) {
                let valid = inputs.fecha_nacimiento.value.trim() !== '';
                if (!aplicarValidacion(inputs.fecha_nacimiento, spans.fecha_nacimiento, valid, 'Selecciona una fecha válida.')) isValid = false;
            }

            // Sexo
            if (inputs.sexo) {
                let valid = inputs.sexo.value !== '';
                if (!aplicarValidacion(inputs.sexo, spans.sexo, valid, 'Selecciona tu sexo.')) isValid = false;
            }

            // Correo
            if (inputs.correo) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                let valid = re.test(inputs.correo.value.trim());
                if (!aplicarValidacion(inputs.correo, spans.correo, valid, 'Formato de correo electrónico inválido.')) isValid = false;
            }

            // Telefono
            if (inputs.numero_telefono && inputs.prefijo_telefono) {
                let numValid = inputs.numero_telefono.value.trim().length === 7;
                let prefValid = inputs.prefijo_telefono.value !== '';
                let valid = numValid && prefValid;

                if (valid && hiddenTelefono) {
                    hiddenTelefono.value = inputs.prefijo_telefono.value + '-' + inputs.numero_telefono.value.trim();
                } else if (hiddenTelefono) {
                    hiddenTelefono.value = '';
                }

                if (!aplicarValidacion(inputs.numero_telefono, spans.telefono, valid, 'Seleccione prefijo y 7 números.')) isValid = false;
            }

            // Direccion
            if (inputs.direccion) {
                let valid = inputs.direccion.value.trim().length >= 4;
                if (!aplicarValidacion(inputs.direccion, spans.direccion, valid, 'Mínimo 4 caracteres requeridos.')) isValid = false;
            }

            // Username
            if (inputs.username) {
                let valid = inputs.username.value.trim().length >= 3;
                if (!aplicarValidacion(inputs.username, spans.username, valid, 'Mínimo 3 caracteres, solo letras.')) isValid = false;
            }

            // Clave
            if (inputs.clave) {
                const val = inputs.clave.value;
                const regexClave = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/;
                let valid = regexClave.test(val);
                if (!aplicarValidacion(inputs.clave, spans.clave, valid, 'Mínimo 8 caracteres, 1 mayúscula, 1 número y 1 símbolo.')) isValid = false;
            }

            // RClave
            if (inputs.rclave && inputs.clave) {
                let valid = inputs.rclave.value === inputs.clave.value && inputs.rclave.value.trim().length > 0;
                if (!aplicarValidacion(inputs.rclave, spans.rclave, valid, 'Las contraseñas no coinciden.')) isValid = false;
            }

            if (btnReg) {
                btnReg.disabled = !isValid;
            }
        }

        Object.values(inputs).forEach(input => {
            if (input) {
                input.addEventListener('input', validarFormulario);
                input.addEventListener('change', validarFormulario);
            }
        });

        // Initialize state
        validarFormulario();

        regForm.addEventListener('submit', function (event) {
            if (btnReg && btnReg.disabled) {
                event.preventDefault();
                regForm.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    regForm.style.animation = '';
                }, 500);
                Swal.fire({
                    icon: 'warning',
                    title: 'Formulario incompleto',
                    text: 'Debe completar todos los campos correctamente antes de registrarse.'
                });
            }
        });
    }

    // Validaciones para la pantalla de restablecer contraseña (nueva_password.php)
    const claveReset = document.getElementById('clave');
    const rclaveReset = document.getElementById('rclave');
    const reqLength = document.getElementById('req-length');
    const reqUpper = document.getElementById('req-upper');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    const matchError = document.getElementById('match-error');
    const btnSubmitReset = document.getElementById('btn-submit');

    if (claveReset && rclaveReset && reqLength && reqUpper && reqNumber && reqSpecial && btnSubmitReset && matchError) {
        function validateResetPassword() {
            const val = claveReset.value;
            let isValid = true;

            // Al menos 8 caracteres
            if (val.length >= 8) {
                reqLength.classList.remove('req-unmet');
                reqLength.classList.add('req-met');
            } else {
                reqLength.classList.remove('req-met');
                reqLength.classList.add('req-unmet');
                isValid = false;
            }

            // Al menos una mayúscula
            if (/[A-Z]/.test(val)) {
                reqUpper.classList.remove('req-unmet');
                reqUpper.classList.add('req-met');
            } else {
                reqUpper.classList.remove('req-met');
                reqUpper.classList.add('req-unmet');
                isValid = false;
            }

            // Al menos un número
            if (/[0-9]/.test(val)) {
                reqNumber.classList.remove('req-unmet');
                reqNumber.classList.add('req-met');
            } else {
                reqNumber.classList.remove('req-met');
                reqNumber.classList.add('req-unmet');
                isValid = false;
            }

            // Al menos un carácter especial
            if (/[\W_]/.test(val)) {
                reqSpecial.classList.remove('req-unmet');
                reqSpecial.classList.add('req-met');
            } else {
                reqSpecial.classList.remove('req-met');
                reqSpecial.classList.add('req-unmet');
                isValid = false;
            }

            // Confirmar contraseña coincide
            const confirmVal = rclaveReset.value;
            if (confirmVal.length > 0 && val !== confirmVal) {
                matchError.classList.remove('d-none');
                isValid = false;
            } else {
                matchError.classList.add('d-none');
                if (confirmVal.length === 0) {
                    isValid = false;
                }
            }

            btnSubmitReset.disabled = !isValid;
        }

        claveReset.addEventListener('input', validateResetPassword);
        rclaveReset.addEventListener('input', validateResetPassword);

        // Estado inicial
        validateResetPassword();
    }
});
