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
});
