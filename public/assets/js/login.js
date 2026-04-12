$(document).ready(function() {
    console.log('Login JS cargado correctamente');
    
    // Aquí puedes agregar validaciones adicionales
    $('#login-form').on('submit', function(e) {
        const cedula = $('input[name="CI"]').val().trim();
        const password = $('#password').val().trim();
        
        if (cedula === '' || password === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Campos vacíos',
                text: 'Por favor completa todos los campos'
            });
            return false;
        }
    });
});