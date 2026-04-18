document.addEventListener("DOMContentLoaded", function () {
    let tablaNoticias;
    const modalNoticia = new bootstrap.Modal(document.getElementById('modalNoticia'));
    const $formNoticia = $('#formNoticia');
    const $inputImagenes = $('#imagenes');
    const $previewContainer = $('#previewContainer');

    // Inicialización del motor Library-First en utils.js
    if (typeof SistemaValidacion !== 'undefined') {
        const elementos = {
            titulo: $('#titulo'),
            subtitulo: $('#subtitulo'),
            contenido: $('#contenido'),
            tipo: $('#tipo')
        };
        SistemaValidacion.inicializar(elementos, (valido) => {
            $('#btnGuardarNoticia').prop('disabled', !valido);
        });
    }

    cargarNoticias();

    async function cargarNoticias() {
        const peticion = new FormData();
        peticion.append('peticion', 'consultar');
        
        try {
            const json = await enviaAjax(peticion, BASE_URL + '?page=noticias-admin');
            let arreglo = [];
            if (json && json.resultado === 200) {
                arreglo = json.datos;
            }
            renderTablaNoticias(arreglo);
        } catch (e) {
            console.error("Error al cargar noticias", e);
            renderTablaNoticias([]);
        }
    }

    function renderTablaNoticias(datos) {
        if ($.fn.DataTable.isDataTable('#tablaNoticias')) {
            $('#tablaNoticias').DataTable().destroy();
        }

        tablaNoticias = $('#tablaNoticias').DataTable({
            responsive: true,
            data: datos,
            order: [[3, 'desc']],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            columns: [
                {
                    data: 'cant_imagenes',
                    render: function (data) {
                        const $span = $('<span>', { class: 'badge bg-secondary' })
                            .append($('<i>', { class: 'fas fa-images me-1' }))
                            .append(document.createTextNode(' ' + data));
                        return $span.prop('outerHTML');
                    }
                },
                { data: 'titulo' },
                { data: 'autor' },
                {
                    data: 'fecha_publicacion',
                    render: function (data) {
                        const date = new Date(data);
                        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
                    }
                },
                {
                    data: 'estatus',
                    render: function (data, type, row) {
                        const date = new Date(row.fecha_publicacion);
                        const now = new Date();
                        if (data == 1) {
                            if (date > now) {
                                return $('<span>', { class: 'badge bg-warning text-dark' })
                                    .append($('<i>', { class: 'fas fa-clock me-1' })).append(' Programada').prop('outerHTML');
                            }
                            return $('<span>', { class: 'badge bg-success' })
                                .append($('<i>', { class: 'fas fa-check-circle me-1' })).append(' Publicada').prop('outerHTML');
                        }
                        return $('<span>', { class: 'badge bg-danger' })
                            .append($('<i>', { class: 'fas fa-times-circle me-1' })).append(' Eliminada').prop('outerHTML');
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        const $dropdown = $('<div>', { class: 'dropdown' });
                        $dropdown.append(
                            $('<button>', { class: 'btn btn-sm btn-light border dropdown-toggle', type: 'button', 'data-bs-toggle': 'dropdown' })
                                .append($('<i>', { class: 'fas fa-ellipsis-v' }))
                        );
                        
                        const $menu = $('<ul>', { class: 'dropdown-menu' });
                        $menu.append(
                            $('<li>').append($('<a>', { class: 'dropdown-item btn-editar', href: '#', 'data-id': row.id_noticia })
                                .append($('<i>', { class: 'fas fa-edit text-primary me-2' })).append('Editar'))
                        );
                        $menu.append($('<li>').append($('<hr>', { class: 'dropdown-divider' })));
                        $menu.append(
                            $('<li>').append($('<a>', { class: 'dropdown-item btn-eliminar text-danger', href: '#', 'data-id': row.id_noticia })
                                .append($('<i>', { class: 'fas fa-trash me-2' })).append('Eliminar'))
                        );
                        $dropdown.append($menu);
                        return $dropdown.prop('outerHTML');
                    }
                }
            ]
        });
    }

    // Manejo de previsualización de imágenes seleccionadas
    if ($inputImagenes.length) {
        $inputImagenes.on('change', function(e) {
            const $galeriaItems = $previewContainer.find('.border-warning');
            $previewContainer.empty();
            $previewContainer.append($galeriaItems);

            const files = e.target.files;
            if (files.length > 0) {
                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const $col = $('<div>', { class: 'position-relative m-1 rounded border shadow-sm', css: { width: '100px', height: '100px', overflow: 'hidden' } });
                            if (index === 0 && $galeriaItems.length === 0) {
                                $col.append($('<span>', { class: 'badge bg-primary position-absolute top-0 start-0 m-1', text: 'Portada' }));
                            }
                            $col.append($('<img>', { src: e.target.result, css: { width: '100%', height: '100%', objectFit: 'cover' } }));
                            $previewContainer.append($col);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }

    const $btnAbrirGaleria = $('#btnAbrirGaleria');
    if ($btnAbrirGaleria.length) {
        $btnAbrirGaleria.on('click', function() {
            MediaPicker.open({
                onSelect: function(ruta) {
                    agregarImagenGaleria(ruta);
                }
            });
        });
    }

    function agregarImagenGaleria(ruta) {
        const $inputGaleria = $('#imagenes_galeria');
        let seleccionadas = $inputGaleria.val() ? JSON.parse($inputGaleria.val()) : [];
        
        if (seleccionadas.includes(ruta)) {
            Swal.fire('Información', 'Esta imagen ya ha sido seleccionada', 'info');
            return;
        }

        seleccionadas.push(ruta);
        $inputGaleria.val(JSON.stringify(seleccionadas));
        renderPreviewGaleria(ruta);
    }

    function renderPreviewGaleria(ruta) {
        const $col = $('<div>', {
            class: 'position-relative m-1 rounded border shadow-sm border-warning',
            css: { width: '100px', height: '100px', overflow: 'hidden' }
        });
        
        $col.append(
            $('<span>', { class: 'badge bg-warning text-dark position-absolute top-0 start-0 m-1', css: { fontSize: '0.6rem' }, text: 'Galería' })
        );
        $col.append(
            $('<button>', { type: 'button', class: 'btn-close btn-close-white position-absolute top-0 end-0 m-1 bg-danger p-1', css: { fontSize: '0.5rem' } })
                .on('click', function() {
                    $(this).parent().remove();
                    removerDeGaleria(ruta);
                })
        );
        $col.append(
            $('<img>', { src: BASE_URL + ruta, css: { width: '100%', height: '100%', objectFit: 'cover' } })
        );
        
        $previewContainer.append($col);
    }

    window.removerDeGaleria = function(ruta) {
        const $inputGaleria = $('#imagenes_galeria');
        let seleccionadas = JSON.parse($inputGaleria.val() || '[]');
        seleccionadas = seleccionadas.filter(r => r !== ruta);
        $inputGaleria.val(JSON.stringify(seleccionadas));
    };

    $('#btnNuevaNoticia').on('click', function () {
        $formNoticia[0].reset();
        $('#peticion').val('registrar');
        
        $('#modalNoticiaLabel').empty()
            .append($('<i>', { class: 'fas fa-newspaper me-2' })).append('Nueva Noticia');
            
        $previewContainer.empty();
        $('#currentImagesSection').hide();
        
        // Reset validaciones previas
        if (typeof SistemaValidacion !== 'undefined') {
            $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            $('.invalid-feedback, .valid-feedback').removeClass('invalid-feedback valid-feedback').text('');
            $('#btnGuardarNoticia').prop('disabled', true);
        }
        
        modalNoticia.show();
    });

    $formNoticia.on('submit', async function (e) {
        e.preventDefault();
        
        // Si hay error en validación, frenar
        if (typeof SistemaValidacion !== 'undefined' && !SistemaValidacion.validarFormularioSilencioso({
            titulo: $('#titulo'), contenido: $('#contenido'), tipo: $('#tipo')
        })) {
            return;
        }
        
        const $btnSubmit = $('#btnGuardarNoticia');
        const originalContent = $btnSubmit.html();
        $btnSubmit.prop('disabled', true);
        $btnSubmit.empty().append(
            $('<span>', { class: 'spinner-border spinner-border-sm me-2' })
        ).append('Guardando...');

        const fd = new FormData(this);
        
        try {
            const res = await enviaAjax(fd, BASE_URL + '?page=noticias-admin');
            if (res && res.resultado === 200) {
                Swal.fire('Éxito', res.mensaje, 'success');
                modalNoticia.hide();
                cargarNoticias();
            } else {
                Swal.fire('Error', res?.mensaje || 'Error en respuesta', 'error');
            }
        } catch (error) {
            console.error("Error:", error);
        } finally {
            $btnSubmit.prop('disabled', false).html(originalContent);
        }
    });

    $(document).on('click', '#tablaNoticias tbody .btn-editar', async function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        const fd = new FormData();
        fd.append('peticion', 'validar');
        fd.append('id_noticia', id);

        const res = await enviaAjax(fd, BASE_URL + '?page=noticias-admin');
        if (res && res.resultado === 200) {
            const d = res.registro;
            $('#peticion').val('modificar');
            $('#id_noticia').val(d.id_noticia);
            $('#titulo').val(d.titulo);
            $('#subtitulo').val(d.subtitulo);
            $('#contenido').val(d.contenido);
            $('#tipo').val(d.tipo);
            
            if (d.fecha_publicacion) {
                $('#fecha_publicacion').val(d.fecha_publicacion.slice(0, 16));
            } else {
                $('#fecha_publicacion').val('');
            }
            
            $('#modalNoticiaLabel').empty()
                .append($('<i>', { class: 'fas fa-edit me-2' })).append('Editar Noticia');
                
            $previewContainer.empty();
            $('#imagenes_galeria').val('');
            $('#imagenes').val('');
            
            renderCurrentImages(d.imagenes);
            
            // Re-checar el estado
            if (typeof SistemaValidacion !== 'undefined') {
                $('#titulo, #contenido, #tipo').trigger('blur');
            }
            
            modalNoticia.show();
        }
    });

    $(document).on('click', '#tablaNoticias tbody .btn-eliminar', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        Swal.fire({
            title: '¿Eliminar noticia?',
            text: "Esta acción marcará la noticia como inactiva",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('peticion', 'eliminar');
                fd.append('id_noticia', id);

                const res = await enviaAjax(fd, BASE_URL + '?page=noticias-admin');
                if (res && res.resultado === 200) {
                    Swal.fire('Eliminado', res.mensaje, 'success');
                    cargarNoticias();
                } else {
                    Swal.fire('Error', res?.mensaje || 'Ocurrió un error', 'error');
                }
            }
        });
    });

    function renderCurrentImages(imagenes) {
        const $container = $('#currentImagesContainer');
        const $section = $('#currentImagesSection');
        $container.empty();

        if (imagenes && imagenes.length > 0) {
            $section.show();
            imagenes.forEach(img => {
                const isPrincipal = img.es_principal == 1;
                const $div = $('<div>', { class: 'position-relative rounded border p-1 shadow-sm bg-body-tertiary', css: { width: '120px' } });
                
                if (isPrincipal) {
                    $div.append(
                        $('<span>', { class: 'badge bg-warning text-dark position-absolute top-0 start-0 m-1 shadow-sm' })
                            .append($('<i>', { class: 'fas fa-star' }), ' Portada')
                    );
                }

                $div.append(
                    $('<img>', { class: 'rounded w-100', src: BASE_URL + img.direccion, css: { height: '100px', objectFit: 'cover' } })
                );

                const $btnContainer = $('<div>', { class: 'mt-1 d-flex justify-content-center gap-1' });
                if (!isPrincipal) {
                    $btnContainer.append(
                        $('<button>', { type: 'button', class: 'btn btn-xs btn-outline-warning btn-principal', 'data-id': img.id_imagen, title: 'Poner como portada' })
                            .append($('<i>', { class: 'fas fa-star' }))
                            .on('click', () => marcarPrincipal(img.id_imagen))
                    );
                }
                $btnContainer.append(
                    $('<button>', { type: 'button', class: 'btn btn-xs btn-outline-danger btn-borrar-img', 'data-id': img.id_imagen, title: 'Eliminar imagen' })
                        .append($('<i>', { class: 'fas fa-trash' }))
                        .on('click', () => eliminarImagen(img.id_imagen))
                );

                $div.append($btnContainer);
                $container.append($div);
            });
        } else {
            $section.hide();
        }
    }

    function eliminarImagen(id) {
        Swal.fire({
            title: '¿Quitar imagen de la noticia?',
            text: "La imagen se desvinculará de esta publicación. El archivo original permanece disponible en el Gestor Multimedia.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('peticion', 'eliminar_imagen');
                fd.append('id_imagen', id);

                const res = await enviaAjax(fd, BASE_URL + '?page=noticias-admin');
                if (res && res.resultado === 200) {
                    const id_noticia = $('#id_noticia').val();
                    reloadImages(id_noticia);
                    cargarNoticias();
                }
            }
        });
    }

    async function marcarPrincipal(id) {
        const fd = new FormData();
        fd.append('peticion', 'marcar_principal');
        fd.append('id_imagen', id);

        const res = await enviaAjax(fd, BASE_URL + '?page=noticias-admin');
        if (res && res.resultado === 200) {
            const id_noticia = $('#id_noticia').val();
            reloadImages(id_noticia);
            cargarNoticias();
        }
    }

    async function reloadImages(id) {
        const fd = new FormData();
        fd.append('peticion', 'validar');
        fd.append('id_noticia', id);

        const res = await enviaAjax(fd, BASE_URL + '?page=noticias-admin');
        if (res && res.resultado === 200) {
            renderCurrentImages(res.registro.imagenes);
        }
    }
});
