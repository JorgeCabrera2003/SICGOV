$(document).ready(function() {
    const $claveNueva = $('#clave_nueva');
    const $claveConfirmar = $('#clave_confirmar');
    const $btnSubmit = $('#btn-submit');
    const $matchError = $('#match-error');

    // Requirements elements
    const $reqLength = $('#req-length');
    const $reqUpper = $('#req-upper');
    const $reqNumber = $('#req-number');
    const $reqSpecial = $('#req-special');

    // Toggle password visibility
    $('[data-password-toggle]').on('click', function() {
        const targetSelector = $(this).data('password-toggle');
        const $target = $(targetSelector);
        const type = $target.attr('type') === 'password' ? 'text' : 'password';
        $target.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    function validatePassword() {
        const val = $claveNueva.val();
        let isValid = true;

        // Length
        if (val.length >= 8) {
            $reqLength.removeClass('req-unmet').addClass('req-met');
        } else {
            $reqLength.removeClass('req-met').addClass('req-unmet');
            isValid = false;
        }

        // Uppercase
        if (/[A-Z]/.test(val)) {
            $reqUpper.removeClass('req-unmet').addClass('req-met');
        } else {
            $reqUpper.removeClass('req-met').addClass('req-unmet');
            isValid = false;
        }

        // Number
        if (/[0-9]/.test(val)) {
            $reqNumber.removeClass('req-unmet').addClass('req-met');
        } else {
            $reqNumber.removeClass('req-met').addClass('req-unmet');
            isValid = false;
        }

        // Special Character
        if (/[\W_]/.test(val)) {
            $reqSpecial.removeClass('req-unmet').addClass('req-met');
        } else {
            $reqSpecial.removeClass('req-met').addClass('req-unmet');
            isValid = false;
        }

        // Confirm Password Match
        const confirmVal = $claveConfirmar.val();
        if (confirmVal.length > 0 && val !== confirmVal) {
            $matchError.removeClass('d-none');
            isValid = false;
        } else {
            $matchError.addClass('d-none');
            if (confirmVal.length === 0) {
                isValid = false; // Must type confirmation
            }
        }

        $btnSubmit.prop('disabled', !isValid);
        return isValid;
    }

    $claveNueva.on('input', validatePassword);
    $claveConfirmar.on('input', validatePassword);

    $('#form-forzar-clave').on('submit', function(e) {
        e.preventDefault();
        
        if (!validatePassword()) return;

        $btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Guardando...');
        
        $.ajax({
            url: BASE_URL + '/?page=forzar-cambiar-clave',
            method: 'POST',
            data: {
                peticion: 'forzar-cambiar-clave',
                clave_nueva: $claveNueva.val(),
                clave_confirmar: $claveConfirmar.val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.resultado === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Contraseña Actualizada!',
                        text: response.mensaje,
                        confirmButtonText: 'Continuar',
                        background: '#1a1a2e',
                        color: '#fff',
                        confirmButtonColor: '#4e54c8'
                    }).then(() => {
                        window.location.href = BASE_URL + '/?page=home';
                    });
                } else {
                    $('#mensaje-error').text(response.mensaje);
                    $('#alerta-error').removeClass('d-none');
                    $btnSubmit.prop('disabled', false).html('Guardar y Continuar <i class="fa-solid fa-arrow-right ms-2"></i>');
                }
            },
            error: function() {
                $('#mensaje-error').text('Ocurrió un error en el servidor. Intenta de nuevo.');
                $('#alerta-error').removeClass('d-none');
                $btnSubmit.prop('disabled', false).html('Guardar y Continuar <i class="fa-solid fa-arrow-right ms-2"></i>');
            }
        });
    });
});
