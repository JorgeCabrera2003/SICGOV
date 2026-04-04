document.addEventListener('DOMContentLoaded', function () {
    const registerModalElement = document.getElementById('registerModal');
    const registerModal = registerModalElement ? new bootstrap.Modal(registerModalElement) : null;
    const showRegisterModal = window.authOpenRegisterModal === true;

    const passwordToggles = document.querySelectorAll('[data-password-toggle]');
    passwordToggles.forEach(function (button) {
        const targetSelector = button.getAttribute('data-password-toggle');
        const target = document.querySelector(targetSelector);

        if (!target) {
            return;
        }

        button.addEventListener('click', function () {
            const type = target.getAttribute('type');
            const icon = button.querySelector('i');
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

    if (showRegisterModal && registerModal) {
        registerModal.show();
    }

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            const ci = loginForm.querySelector('[name="CI"]').value.trim();
            const password = loginForm.querySelector('[name="password"]').value.trim();
            if (!ci || !password) {
                event.preventDefault();
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
                Swal.fire({
                    icon: 'warning',
                    title: 'Formulario incompleto',
                    text: 'Debe completar todos los campos obligatorios.'
                });
                return;
            }

            if (password !== confirmPassword) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Contraseñas diferentes',
                    text: 'Las contraseñas no coinciden. Verifica por favor.'
                });
            }
        });
    }
});
