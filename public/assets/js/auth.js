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

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            const password = registerForm.querySelector('[name="clave"]').value.trim();
            const confirmPassword = registerForm.querySelector('[name="rclave"]').value.trim();
            const correo = registerForm.querySelector('[name="correo"]').value.trim();
            const nombre = registerForm.querySelector('[name="nombre"]').value.trim();
            const apellido = registerForm.querySelector('[name="apellido"]').value.trim();
            const cedula = registerForm.querySelector('[name="cedula"]').value.trim();
            const nacionalidad = registerForm.querySelector('[name="nacionalidad"]').value;

            if (!nombre || !apellido || !cedula || !nacionalidad || !correo || !password || !confirmPassword) {
                event.preventDefault();
                registerForm.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    registerForm.style.animation = '';
                }, 500);
                Swal.fire({
                    icon: 'warning',
                    title: 'Formulario incompleto',
                    text: 'Debe completar todos los campos obligatorios.'
                });
                return;
            }

            if (password !== confirmPassword) {
                event.preventDefault();
                registerForm.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    registerForm.style.animation = '';
                }, 500);
                Swal.fire({
                    icon: 'error',
                    title: 'Contraseñas diferentes',
                    text: 'Las contraseñas no coinciden. Verifica por favor.'
                });
            }
        });
    }

    // Validación en tiempo real para recuperación de contraseña
    const claveInput = document.getElementById('clave');
    const rclaveInput = document.getElementById('rclave');
    const btnSubmit = document.getElementById('btn-submit');
    const matchError = document.getElementById('match-error');

    const reqLength = document.getElementById('req-length');
    const reqUpper = document.getElementById('req-upper');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    if (claveInput && rclaveInput && reqLength) {
        function validatePassword() {
            const val = claveInput.value;
            let isValid = true;

            // Longitud
            if (val.length >= 8) {
                reqLength.classList.remove('req-unmet');
                reqLength.classList.add('req-met');
            } else {
                reqLength.classList.remove('req-met');
                reqLength.classList.add('req-unmet');
                isValid = false;
            }

            // Mayúscula
            if (/[A-Z]/.test(val)) {
                reqUpper.classList.remove('req-unmet');
                reqUpper.classList.add('req-met');
            } else {
                reqUpper.classList.remove('req-met');
                reqUpper.classList.add('req-unmet');
                isValid = false;
            }

            // Número
            if (/[0-9]/.test(val)) {
                reqNumber.classList.remove('req-unmet');
                reqNumber.classList.add('req-met');
            } else {
                reqNumber.classList.remove('req-met');
                reqNumber.classList.add('req-unmet');
                isValid = false;
            }

            // Carácter Especial
            if (/[\W_]/.test(val)) {
                reqSpecial.classList.remove('req-unmet');
                reqSpecial.classList.add('req-met');
            } else {
                reqSpecial.classList.remove('req-met');
                reqSpecial.classList.add('req-unmet');
                isValid = false;
            }

            // Confirmar coincidencia
            const confirmVal = rclaveInput.value;
            if (confirmVal.length > 0 && val !== confirmVal) {
                if (matchError) matchError.classList.remove('d-none');
                isValid = false;
            } else {
                if (matchError) matchError.classList.add('d-none');
                if (confirmVal.length === 0) {
                    isValid = false;
                }
            }

            if (btnSubmit) {
                btnSubmit.disabled = !isValid;
            }
            return isValid;
        }

        claveInput.addEventListener('input', validatePassword);
        rclaveInput.addEventListener('input', validatePassword);
    }
});
