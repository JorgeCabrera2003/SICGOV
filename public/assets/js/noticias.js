document.addEventListener("DOMContentLoaded", function () {
    let tablaNoticias;

    // Inicializar DataTables
    if (document.getElementById('tablaNoticias')) {
        tablaNoticias = $('#tablaNoticias').DataTable({
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            ajax: {
                url: BASE_URL + '?page=noticias-admin',
                type: 'POST',
                data: function (d) {
                    d.peticion = 'consultar';
                },
                dataSrc: function (json) {
                    if (json.resultado === 200) {
                        return json.datos;
                    }
                    return [];
                }
            },
            columns: [
                {
                    data: 'cant_imagenes',
                    render: function (data) {
                        return `<span class="badge bg-secondary"><i class="fas fa-images"></i> ${data}</span>`;
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
                                return '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Programada</span>';
                            }
                            return '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Publicada</span>';
                        }
                        return '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> Eliminada</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item btn-editar" href="#" data-id="${row.id_noticia}"><i class="fas fa-edit text-primary me-2"></i>Editar</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item btn-eliminar text-danger" href="#" data-id="${row.id_noticia}"><i class="fas fa-trash me-2"></i>Eliminar</a></li>
                                </ul>
                            </div>
                        `;
                    }
                }
            ]
        });
    }

    const modalNoticia = new bootstrap.Modal(document.getElementById('modalNoticia'));
    const formNoticia = document.getElementById('formNoticia');
    const inputImagenes = document.getElementById('imagenes');
    const previewContainer = document.getElementById('previewContainer');

    // Manejo de previsualización de imágenes seleccionadas
    if (inputImagenes) {
        inputImagenes.addEventListener('change', function(e) {
            previewContainer.innerHTML = ''; // Limpiar viejas imágenes
            const files = e.target.files;
            
            if (files.length > 0) {
                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const badge = index === 0 ? '<span class="badge bg-primary position-absolute top-0 start-0 m-1">Portada</span>' : '';
                            const col = document.createElement('div');
                            col.className = 'position-relative m-1 rounded border shadow-sm';
                            col.style.width = '100px';
                            col.style.height = '100px';
                            col.style.overflow = 'hidden';
                            
                            col.innerHTML = `
                                ${badge}
                                <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                            `;
                            previewContainer.appendChild(col);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }

    if (document.getElementById('btnNuevaNoticia')) {
        document.getElementById('btnNuevaNoticia').addEventListener('click', function () {
            formNoticia.reset();
            document.getElementById('peticion').value = 'registrar';
            document.getElementById('modalNoticiaLabel').innerHTML = '<i class="fas fa-newspaper me-2"></i>Nueva Noticia';
            previewContainer.innerHTML = '';
            document.getElementById('currentImagesSection').style.display = 'none';
            modalNoticia.show();
        });
    }

    if (formNoticia) {
        formNoticia.addEventListener('submit', function (e) {
            e.preventDefault();
            
            let btnSubmit = document.getElementById('btnGuardarNoticia');
            let originalContent = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            let formData = new FormData(formNoticia);
            
            fetch(BASE_URL + '?page=noticias-admin', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.resultado === 200) {
                    Swal.fire('Éxito', res.mensaje, 'success');
                    modalNoticia.hide();
                    tablaNoticias.ajax.reload();
                } else {
                    Swal.fire('Error', res.mensaje, 'error');
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            })
            .finally(() => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalContent;
            });
        });
    }

    // Acciones editar / eliminar
    if (document.getElementById('tablaNoticias')) {
        $('#tablaNoticias tbody').on('click', '.btn-editar', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            
            let formData = new FormData();
            formData.append('peticion', 'validar');
            formData.append('id_noticia', id);

            fetch(BASE_URL + '?page=noticias-admin', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).then(res => {
                if(res.resultado === 200) {
                    let d = res.registro;
                    document.getElementById('peticion').value = 'modificar';
                    document.getElementById('id_noticia').value = d.id_noticia;
                    document.getElementById('titulo').value = d.titulo;
                    document.getElementById('subtitulo').value = d.subtitulo;
                    document.getElementById('contenido').value = d.contenido;
                    document.getElementById('tipo').value = d.tipo;
                    
                    if (d.fecha_publicacion) {
                        // Formatear date a input datetime-local slice(0,16) elimina segundos
                        document.getElementById('fecha_publicacion').value = d.fecha_publicacion.slice(0, 16);
                    }
                    
                    
                    document.getElementById('modalNoticiaLabel').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Noticia';
                    previewContainer.innerHTML = '';
                    
                    // Cargar Imágenes Actuales
                    renderCurrentImages(d.imagenes);
                    
                    modalNoticia.show();
                }
            });
        });

        $('#tablaNoticias tbody').on('click', '.btn-eliminar', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            
            Swal.fire({
                title: '¿Eliminar noticia?',
                text: "Esta acción marcará la noticia como inactiva",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    let formData = new FormData();
                    formData.append('peticion', 'eliminar');
                    formData.append('id_noticia', id);

                    fetch(BASE_URL + '?page=noticias-admin', {
                        method: 'POST',
                        body: formData
                    }).then(r => r.json()).then(res => {
                        if(res.resultado === 200) {
                            Swal.fire('Eliminado', res.mensaje, 'success');
                            tablaNoticias.ajax.reload();
                        } else {
                            Swal.fire('Error', res.mensaje, 'error');
                        }
                    });
                }
            });
        });
    }

    function renderCurrentImages(imagenes) {
        const container = document.getElementById('currentImagesContainer');
        const section = document.getElementById('currentImagesSection');
        container.innerHTML = '';

        if (imagenes && imagenes.length > 0) {
            section.style.display = 'block';
            imagenes.forEach(img => {
                const div = document.createElement('div');
                div.className = 'position-relative rounded border p-1 shadow-sm bg-body-tertiary';
                div.style.width = '120px';
                
                const isPrincipal = img.es_principal == 1;
                const principalBadge = isPrincipal ? '<span class="badge bg-warning text-dark position-absolute top-0 start-0 m-1 shadow-sm"><i class="fas fa-star"></i> Portada</span>' : '';

                div.innerHTML = `
                    ${principalBadge}
                    <img src="${BASE_URL}${img.direccion}" class="rounded w-100" style="height: 100px; object-fit: cover;">
                    <div class="mt-1 d-flex justify-content-center gap-1">
                        ${!isPrincipal ? `<button type="button" class="btn btn-xs btn-outline-warning btn-principal" data-id="${img.id_imagen}" title="Poner como portada"><i class="fas fa-star"></i></button>` : ''}
                        <button type="button" class="btn btn-xs btn-outline-danger btn-borrar-img" data-id="${img.id_imagen}" title="Eliminar imagen"><i class="fas fa-trash"></i></button>
                    </div>
                `;
                container.appendChild(div);
            });

            // Re-vincular eventos
            container.querySelectorAll('.btn-borrar-img').forEach(btn => {
                btn.onclick = () => eliminarImagen(btn.dataset.id);
            });
            container.querySelectorAll('.btn-principal').forEach(btn => {
                btn.onclick = () => marcarPrincipal(btn.dataset.id);
            });

        } else {
            section.style.display = 'none';
        }
    }

    function eliminarImagen(id) {
        Swal.fire({
            title: '¿Eliminar esta imagen?',
            text: "El archivo se borrará permanentemente",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, borrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('peticion', 'eliminar_imagen');
                fd.append('id_imagen', id);

                fetch(BASE_URL + '?page=noticias-admin', {
                    method: 'POST',
                    body: fd
                }).then(r => r.json()).then(res => {
                    if (res.resultado === 200) {
                        // Recargar modal
                        const id_noticia = document.getElementById('id_noticia').value;
                        reloadImages(id_noticia);
                        tablaNoticias.ajax.reload(null, false);
                    }
                });
            }
        });
    }

    function marcarPrincipal(id) {
        const fd = new FormData();
        fd.append('peticion', 'marcar_principal');
        fd.append('id_imagen', id);

        fetch(BASE_URL + '?page=noticias-admin', {
            method: 'POST',
            body: fd
        }).then(r => r.json()).then(res => {
            if (res.resultado === 200) {
                const id_noticia = document.getElementById('id_noticia').value;
                reloadImages(id_noticia);
                tablaNoticias.ajax.reload(null, false);
            }
        });
    }

    function reloadImages(id) {
        const fd = new FormData();
        fd.append('peticion', 'validar');
        fd.append('id_noticia', id);

        fetch(BASE_URL + '?page=noticias-admin', {
            method: 'POST',
            body: fd
        }).then(r => r.json()).then(res => {
            if (res.resultado === 200) {
                renderCurrentImages(res.registro.imagenes);
            }
        });
    }
});
