/**
 * CLIENT-SIDE MODULE: MY PROFILE (FACEBOOK-STYLE LAYOUT) - SICGOV
 * Handles interactive tabs, dynamic real-time avatar/banner uploading,
 * mask formatting, secure validations, and async activity log retrieval.
 */

$(document).ready(function () {
    // 1. Initial State & Setup
    cargarHistorialActividad();
    aplicarMascaraTelefono();
    inicializarValidacionesTiempoReal();

    // 2. Tab Animation Enhancement
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const targetId = $(e.target).attr('data-bs-target');
        $(targetId).addClass('show');
    });

    // 3. Avatar Upload Trigger
    $('#btnEditarAvatar').on('click', function () {
        $('#inputAvatar').click();
    });

    $('#inputAvatar').on('change', async function () {
        const file = this.files[0];
        if (!file) return;

        // Visual validation before sending
        const allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'jfif'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) {
            mensajes("error", 5000, "Formato no permitido", "Por favor seleccione una imagen válida (JPG, PNG, WEBP, GIF).");
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            mensajes("error", 5000, "Archivo muy grande", "La imagen no debe superar los 5 MB de tamaño.");
            return;
        }

        const formData = new FormData();
        formData.append('peticion', 'subir-avatar');
        formData.append('foto', file);

        // Show loading toast
        mensajes("info", 2000, "Procesando", "Comprimiendo y convirtiendo imagen a WebP...");

        try {
            const res = await enviaAjax(formData);
            if (res && res.resultado === 200) {
                // Update local profile avatar
                $('#imgAvatar').attr('src', res.url);
                
                // Update global sidebar and navbar avatars immediately
                $('.user-avatar img').attr('src', res.url);
                
                mensajes("success", 3000, "Éxito", "Foto de perfil actualizada exitosamente.");
            } else {
                mensajes("error", 5000, "Error", res.mensaje || "No se pudo actualizar la foto de perfil.");
            }
        } catch (err) {
            mensajes("error", 5000, "Error", "Ocurrió un error al cargar la imagen al servidor.");
        }
    });

    // 4. Cover Banner Upload Trigger
    $('#btnEditarPortada').on('click', function () {
        $('#inputPortada').click();
    });

    $('#inputPortada').on('change', async function () {
        const file = this.files[0];
        if (!file) return;

        const allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'jfif'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) {
            mensajes("error", 5000, "Formato no permitido", "Por favor seleccione una imagen válida (JPG, PNG, WEBP, GIF).");
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            mensajes("error", 5000, "Archivo muy grande", "La imagen no debe superar los 5 MB de tamaño.");
            return;
        }

        const formData = new FormData();
        formData.append('peticion', 'subir-portada');
        formData.append('portada', file);

        mensajes("info", 2000, "Procesando", "Comprimiendo y convirtiendo portada a WebP...");

        try {
            const res = await enviaAjax(formData);
            if (res && res.resultado === 200) {
                const imgElem = $('#imgPortada');
                if (imgElem.is('img')) {
                    imgElem.attr('src', res.url);
                } else {
                    // It was a div gradient, replace with actual img
                    const parent = imgElem.parent();
                    imgElem.remove();
                    parent.prepend(`<img src="${res.url}" alt="Foto de portada" class="profile-cover-img" id="imgPortada">`);
                }
                mensajes("success", 3000, "Éxito", "Foto de portada actualizada exitosamente.");
            } else {
                mensajes("error", 5000, "Error", res.mensaje || "No se pudo actualizar la foto de portada.");
            }
        } catch (err) {
            mensajes("error", 5000, "Error", "Ocurrió un error al cargar la imagen de portada.");
        }
    });

    // 5. Telephone Input Mask (0000-0000000)
    function aplicarMascaraTelefono() {
        $('#telefono').on('input', function () {
            let val = $(this).val().replace(/\D/g, ''); // Numbers only
            if (val.length > 11) val = val.substring(0, 11);
            
            if (val.length > 4) {
                $(this).val(val.substring(0, 4) + '-' + val.substring(4));
            } else {
                $(this).val(val);
            }
        });
    }

    // 6. Real-time form input styling listeners
    function inicializarValidacionesTiempoReal() {
        // Name Validation: characters only, >= 3
        $('#nombre, #apellido').on('input', function () {
            const val = $(this).val().trim();
            const regex = /^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ ]{3,65}$/;
            if (regex.test(val)) {
                $(this).addClass('is-valid').removeClass('is-invalid');
            } else {
                $(this).addClass('is-invalid').removeClass('is-valid');
            }
        });

        // Email Validation
        $('#correo').on('input', function () {
            const val = $(this).val().trim();
            const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (regex.test(val)) {
                $(this).addClass('is-valid').removeClass('is-invalid');
            } else {
                $(this).addClass('is-invalid').removeClass('is-valid');
            }
        });

        // Phone Validation
        $('#telefono').on('input', function () {
            const val = $(this).val().trim();
            const regex = /^\d{4}-\d{7}$/;
            if (regex.test(val)) {
                $(this).addClass('is-valid').removeClass('is-invalid');
            } else {
                $(this).addClass('is-invalid').removeClass('is-valid');
            }
        });

        // Address Validation
        $('#direccion').on('input', function () {
            const val = $(this).val().trim();
            if (val.length >= 10 && val.length <= 200) {
                $(this).addClass('is-valid').removeClass('is-invalid');
            } else {
                $(this).addClass('is-invalid').removeClass('is-valid');
            }
        });
    }

    // 7. Edit Profile Form Submission
    $('#formEditarPerfil').on('submit', async function (e) {
        e.preventDefault();
        const form = this;
        const nombre = $('#nombre').val().trim();
        const apellido = $('#apellido').val().trim();
        const correo = $('#correo').val().trim();
        const telefono = $('#telefono').val().trim();
        const sexo = $('#sexo').val();
        const fecha_nacimiento = $('#fecha_nacimiento').val();
        const direccion = $('#direccion').val().trim();

        // Strict Client Validations
        if (!nombre || !/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ ]{3,65}$/.test(nombre)) {
            mensajes("error", 4000, "Campo Inválido", "Nombres debe contener solo letras y espacio (mínimo 3 caracteres).");
            $('#nombre').addClass('is-invalid');
            return;
        }
        if (!apellido || !/^[a-zA-ZÁÉÍÓÚáéíóúüñÑçÇ ]{3,65}$/.test(apellido)) {
            mensajes("error", 4000, "Campo Inválido", "Apellidos debe contener solo letras y espacio (mínimo 3 caracteres).");
            $('#apellido').addClass('is-invalid');
            return;
        }
        if (!correo || !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(correo)) {
            mensajes("error", 4000, "Campo Inválido", "Por favor ingrese un correo electrónico válido.");
            $('#correo').addClass('is-invalid');
            return;
        }
        if (!telefono || !/^\d{4}-\d{7}$/.test(telefono)) {
            mensajes("error", 4000, "Campo Inválido", "El teléfono celular debe tener exactamente 11 dígitos en formato 0000-0000000.");
            $('#telefono').addClass('is-invalid');
            return;
        }
        if (!sexo) {
            mensajes("error", 4000, "Campo Inválido", "Debe seleccionar su sexo.");
            $('#sexo').addClass('is-invalid');
            return;
        }
        if (!fecha_nacimiento || new Date(fecha_nacimiento) >= new Date()) {
            mensajes("error", 4000, "Campo Inválido", "Fecha de nacimiento inválida o futura.");
            $('#fecha_nacimiento').addClass('is-invalid');
            return;
        }
        if (direccion.length < 10 || direccion.length > 200) {
            mensajes("error", 4000, "Campo Inválido", "La dirección de habitación debe tener entre 10 y 200 caracteres.");
            $('#direccion').addClass('is-invalid');
            return;
        }

        const confirm = await confirmarAccion("Modificar Perfil", "¿Desea guardar los cambios en su perfil?", "question");
        if (confirm) {
            $('#btnGuardarPerfil').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...');
            
            try {
                const data = new FormData(form);
                const res = await enviaAjax(data);
                
                if (res && res.resultado === 200) {
                    mensajes("success", 3000, "Éxito", res.mensaje);
                    
                    // Update header name and global displays
                    const fullName = nombre + ' ' + apellido;
                    $('.profile-name').text(fullName);
                    $('.user-name').text(nombre);
                    $('#userDropdown span').text(nombre);
                    
                    // Force input styling classes reload
                    $(form).find('.is-valid').removeClass('is-valid');
                } else {
                    mensajes("error", 5000, "Error", res.mensaje || "No se pudo actualizar el perfil.");
                }
            } catch (err) {
                mensajes("error", 5000, "Error", "Error de conexión con el servidor.");
            } finally {
                $('#btnGuardarPerfil').prop('disabled', false).html('<i class="bi bi-save me-2"></i>Guardar Cambios');
            }
        }
    });

    // 8. Change Password Form Submission
    $('#formCambiarClave').on('submit', async function (e) {
        e.preventDefault();
        const form = this;
        const actual = $('#clave_actual').val();
        const nueva = $('#clave_nueva').val();
        const confirmar = $('#clave_confirmar').val();

        if (!actual) {
            mensajes("error", 4000, "Contraseña Requerida", "Debe ingresar su contraseña actual.");
            return;
        }
        if (!nueva || nueva.length < 4) {
            mensajes("error", 4000, "Contraseña Muy Corta", "La nueva contraseña debe tener al menos 4 caracteres.");
            return;
        }
        if (nueva !== confirmar) {
            mensajes("error", 4000, "Contraseñas no coinciden", "La nueva contraseña y su confirmación deben coincidir.");
            return;
        }

        const confirm = await confirmarAccion("Cambiar Contraseña", "¿Está seguro de que desea cambiar su contraseña de acceso?", "warning");
        if (confirm) {
            $('#btnGuardarClave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cambiando...');
            
            try {
                const data = new FormData(form);
                const res = await enviaAjax(data);
                
                if (res && res.resultado === 200) {
                    mensajes("success", 3000, "Éxito", res.mensaje);
                    form.reset();
                    $(form).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
                } else {
                    mensajes("error", 5000, "Error", res.mensaje || "No se pudo cambiar la contraseña.");
                }
            } catch (err) {
                mensajes("error", 5000, "Error", "Error al comunicarse con el servidor.");
            } finally {
                $('#btnGuardarClave').prop('disabled', false).html('<i class="bi bi-shield-check me-2"></i>Actualizar Contraseña');
            }
        }
    });

    // 9. Load Activity Timeline Log
    async function cargarHistorialActividad() {
        const container = $('#timelineContainer');
        
        // Show loader before async load
        container.html(`
            <div class="text-center py-5 timeline-loader">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mb-0 text-muted">Obteniendo registro de bitácora...</p>
            </div>
        `);

        try {
            const formData = new FormData();
            formData.append('peticion', 'obtener-actividad');
            const res = await enviaAjax(formData);

            if (res && res.resultado === 200 && res.datos && res.datos.length > 0) {
                let html = '';
                
                res.datos.forEach(log => {
                    // Detect and format action categories
                    let actionClass = 'Modificar';
                    const actionName = log.accion.toLowerCase();
                    
                    if (actionName.includes('registr') || actionName.includes('crear') || actionName.includes('insert')) {
                        actionClass = 'Registrar';
                    } else if (actionName.includes('elimin') || actionName.includes('borrar') || actionName.includes('desactiv')) {
                        actionClass = 'Eliminar';
                    } else if (actionName.includes('sesi') || actionName.includes('log') || actionName.includes('acces')) {
                        actionClass = 'Sesion';
                    }

                    // Format date to local Venezuela representation
                    const dateObj = new Date(log.fecha);
                    const formattedDate = dateObj.toLocaleDateString('es-VE', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });

                    html += `
                        <article class="timeline-item ${actionClass}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content shadow-sm">
                                <div class="timeline-header">
                                    <div class="timeline-title-wrap">
                                        <h3 class="timeline-title">${log.modulo}</h3>
                                        <span class="timeline-badge">${log.accion}</span>
                                    </div>
                                    <span class="timeline-date">
                                        <i class="bi bi-clock"></i>${formattedDate}
                                    </span>
                                </div>
                                <p class="timeline-detail text-muted">${log.detalle || 'Acción ejecutada en el sistema.'}</p>
                                <div class="timeline-ip">
                                    <i class="bi bi-laptop"></i> Dirección IP: <strong>${log.ip_address}</strong>
                                </div>
                            </div>
                        </article>
                    `;
                });

                container.html(html);
            } else {
                container.html(`
                    <div class="timeline-empty py-5">
                        <i class="bi bi-journal-x"></i>
                        <h3 class="h6 fw-bold mb-1">Sin Actividad</h3>
                        <p class="mb-0 text-muted">No se encontraron movimientos registrados bajo su Cédula.</p>
                    </div>
                `);
            }
        } catch (err) {
            container.html(`
                <div class="timeline-empty py-5 border border-danger-subtle bg-danger-subtle bg-opacity-25 rounded-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-2 mb-2"></i>
                    <h3 class="h6 fw-bold text-danger mb-1">Error de Carga</h3>
                    <p class="mb-0 text-muted">No se pudo recuperar su historial en este momento.</p>
                </div>
            `);
        }
    }

    // 10. Recargar button trigger
    $('#btnRecargarActividad').on('click', function () {
        cargarHistorialActividad();
    });
});
