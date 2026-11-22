/**
 * MEDIA.JS - Gestor de Multimedia
 * Script clásico (IIFE) – NO usa ES modules para compatibilidad
 * Depende de: jQuery, Bootstrap, SweetAlert2 (cargados en footer)
 */
(function ($) {
    'use strict';

    let allMedia = [];
    let $grid;

    function init() {
        $grid = $('#media-grid');
        cargarMedia();
        bindEvents();
    }

    function bindEvents() {
        $('#btn-refresh').on('click', cargarMedia);
        $('#filter-dir, #filter-status').on('change', () => renderGrid());
        $('#search-input').on('keyup', () => renderGrid());

        // Subida de imagen
        $('#upload-form').on('submit', function (e) {
            e.preventDefault();
            subirArchivo(this);
        });

        // Preview en tiempo real
        $('input[name="archivo"]').on('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    let $preview = $('#upload-preview-img');
                    if (!$preview.length) {
                        $preview = $('<img>', {
                            id: 'upload-preview-img',
                            class: 'img-fluid rounded-3 mt-3 shadow-sm',
                            style: 'max-height: 200px; object-fit: cover;'
                        });
                        $(this).closest('.mb-3').append($preview);
                    }
                    $preview.attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Drag & drop en el modal de subida
        const dropZone = document.getElementById('upload-drop-zone');
        if (dropZone) {
            dropZone.addEventListener('dragover', e => {
                e.preventDefault();
                dropZone.classList.add('upload-drop-hover');
            });
            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('upload-drop-hover');
            });
            dropZone.addEventListener('drop', e => {
                e.preventDefault();
                dropZone.classList.remove('upload-drop-hover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const fileInput = document.querySelector('#upload-form input[name="archivo"]');
                    if (fileInput) {
                        // Crear un DataTransfer para asignar el archivo
                        const dt = new DataTransfer();
                        dt.items.add(files[0]);
                        fileInput.files = dt.files;
                        $(fileInput).trigger('change');
                    }
                }
            });
        }

        // Copiar ruta
        $('#btn-copy-path').on('click', function () {
            copyToClipboard($('#detail-path').text());
        });

        // Eliminar
        $('#btn-delete-file').on('click', function () {
            confirmarEliminar($('#detail-path').text());
        });
    }

    async function cargarMedia() {
        if ($grid) {
            $grid.html(
                '<div class="col-12 text-center py-5">' +
                    '<div class="spinner-border" style="color: var(--color-acento);" role="status"></div>' +
                    '<p class="mt-3" style="color: var(--color-sidebar); opacity: 0.7;">Escaneando archivos...</p>' +
                '</div>'
            );
        }

        const fd = new FormData();
        fd.append('peticion', 'consultar');

        try {
            const res = await fetch(window.BASE_URL + '?page=Media', {
                method: 'POST',
                body: fd
            });

            // Verificar Content-Type antes de parsear
            const contentType = res.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await res.text();
                console.error('[Media] Respuesta no-JSON:', text.substring(0, 500));
                mostrarError('El servidor devolvió una respuesta inesperada. Revisa los logs de PHP.');
                return;
            }

            const response = await res.json();
            if (response && response.resultado === 200) {
                allMedia = Array.isArray(response.datos) ? response.datos : [];
                renderGrid();
            } else {
                mostrarError('Error al cargar multimedia: ' + (response?.mensaje || 'Respuesta inválida'));
            }
        } catch (e) {
            console.error('[Media] Error al cargar galería:', e);
            mostrarError('Fallo de conexión al cargar la galería.');
        }
    }

    function renderGrid() {
        const dirFilter    = $('#filter-dir').val();
        const statusFilter = $('#filter-status').val();
        const searchFilter = ($('#search-input').val() || '').toLowerCase();

        const filtered = allMedia.filter(item => {
            const matchesDir    = !dirFilter    || item.directorio === dirFilter;
            const matchesStatus = !statusFilter || (statusFilter === 'linked' ? item.en_uso : !item.en_uso);
            const nombre        = item.nombre || '';
            const matchesSearch = !searchFilter || nombre.toLowerCase().includes(searchFilter);
            return matchesDir && matchesStatus && matchesSearch;
        });

        $grid.empty();

        if (filtered.length === 0) {
            $grid.append(
                $('<div>', { class: 'col-12 text-center py-5' })
                    .append($('<i>', { class: 'fas fa-search-minus fs-1 text-muted' }))
                    .append($('<p>', { class: 'mt-3 text-muted', text: 'No se encontraron archivos con esos filtros.' }))
            );
            return;
        }

        filtered.forEach(item => {
            const badgeClass = item.en_uso ? 'bg-success' : 'bg-warning text-dark';
            const badgeText  = item.en_uso ? 'Vinculada' : 'Sin uso';
            const fileSize   = (item.size / 1024).toFixed(1) + ' KB';
            const imgSrc     = (window.BASE_URL || '').replace(/\/$/, '') + item.ruta;

            const $col  = $('<div>', { class: 'col-6 col-md-4 col-lg-3 mb-4', 'data-path': item.ruta });
            const $card = $('<div>', {
                class: 'media-manager__item rounded-4 overflow-hidden position-relative h-100 d-flex flex-column'
            }).on('click', () => mostrarDetalles(item.ruta));

            const $preview = $('<div>', {
                class: 'media-manager__preview position-relative w-100 d-flex align-items-center justify-content-center'
            });
            $preview.append($('<span>', { class: `badge media-manager__badge position-absolute top-0 end-0 m-2 rounded-pill z-2 ${badgeClass}`, text: badgeText }));
            $preview.append($('<img>', { src: imgSrc, loading: 'lazy', class: 'media-manager__image w-100 h-100 object-fit-cover', alt: item.nombre }));

            const $body = $('<div>', { class: 'p-3 d-flex flex-column flex-grow-1' });
            $body.append($('<p>', { class: 'small text-truncate mb-2 fw-bold', title: item.nombre || '', text: item.nombre || 'Sin nombre' }));

            const $info = $('<div>', { class: 'd-flex justify-content-between align-items-center mt-auto' });
            $info.append($('<span>', {
                class: 'badge rounded-pill',
                css: { backgroundColor: 'var(--color-bg-muted, rgba(26,28,32,0.05))', color: 'var(--color-sidebar)', fontSize: '0.65rem' },
                text: (item.directorio || 'General').toUpperCase()
            }));
            $info.append($('<span>', { class: 'text-muted fw-bold', css: { fontSize: '0.7rem' }, text: fileSize }));
            $body.append($info);

            $card.append($preview, $body);
            $col.append($card);
            $grid.append($col);
        });
    }

    function mostrarDetalles(ruta) {
        const item = allMedia.find(m => m.ruta === ruta);
        if (!item) return;

        const imgSrc = (window.BASE_URL || '').replace(/\/$/, '') + item.ruta;
        $('#detail-preview').attr('src', imgSrc);
        $('#detail-name').text(item.nombre);
        $('#detail-path').text(item.ruta);
        $('#detail-size').text((item.size / 1024).toFixed(1) + ' KB');
        $('#detail-type').text(item.tipo ? item.tipo.toUpperCase() : '');

        const $linksContainer = $('#detail-links').empty();

        if (item.vinculos && item.vinculos.length > 0) {
            item.vinculos.forEach(link => {
                const label = link.nombre || link.id;
                const $div = $('<div>', {
                    class: 'badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 p-2 me-1 mb-1 text-start d-block'
                });
                $div.append($('<i>', { class: 'fas fa-link me-1' }))
                    .append(document.createTextNode(` ${link.tipo}: `))
                    .append($('<strong>', { text: label }));
                $linksContainer.append($div);
            });
            $('#btn-delete-file').prop('disabled', true)
                .addClass('opacity-50')
                .attr('title', 'No se puede eliminar una imagen vinculada');
        } else {
            $linksContainer.append(
                $('<span>', { class: 'text-warning small fst-italic' })
                    .append($('<i>', { class: 'fas fa-exclamation-triangle me-1' }))
                    .append(' Imagen huérfana (puede eliminarse)')
            );
            $('#btn-delete-file').prop('disabled', false)
                .removeClass('opacity-50')
                .removeAttr('title');
        }

        const modal = new bootstrap.Modal(document.getElementById('imageDetailModal'));
        modal.show();
    }

    async function subirArchivo(form) {
        const fd    = new FormData(form);
        const $btn  = $(form).find('button[type="submit"]');
        const archivo = form.querySelector('input[name="archivo"]').files[0];

        // Validaciones del lado cliente
        if (!archivo) {
            Swal.fire('Atención', 'Selecciona un archivo antes de subir.', 'warning');
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(archivo.type)) {
            Swal.fire('Formato inválido', 'Solo se aceptan imágenes JPG, PNG, GIF o WebP.', 'error');
            return;
        }

        const MAX_MB = 10;
        if (archivo.size > MAX_MB * 1024 * 1024) {
            Swal.fire('Archivo muy grande', `El límite es ${MAX_MB} MB.`, 'error');
            return;
        }

        $btn.prop('disabled', true)
            .empty()
            .append($('<span>', { class: 'spinner-border spinner-border-sm me-2' }))
            .append('Subiendo...');

        fd.append('peticion', 'registrar');

        try {
            const res = await fetch(window.BASE_URL + '?page=Media', {
                method: 'POST',
                body: fd
            });

            const contentType = res.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await res.text();
                console.error('[Media] Respuesta no-JSON al subir:', text.substring(0, 500));
                Swal.fire('Error del servidor', 'El servidor no devolvió JSON. Revisa los logs de PHP.', 'error');
                return;
            }

            const response = await res.json();

            if (response && response.resultado === 200) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Imagen subida!',
                    text: 'La imagen se subió correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
                if (modalInstance) modalInstance.hide();
                form.reset();
                $('#upload-preview-img').remove();
                await cargarMedia();
            } else {
                Swal.fire('Error al subir', response.mensaje || 'Error desconocido del servidor.', 'error');
            }
        } catch (error) {
            console.error('[Media] Error fetch al subir:', error);
            Swal.fire('Error de conexión', 'No se pudo comunicar con el servidor. Verifica tu red.', 'error');
        } finally {
            $btn.prop('disabled', false).text('Subir Ahora');
        }
    }

    function confirmarEliminar(ruta) {
        Swal.fire({
            title: '¿Eliminar archivo?',
            text: 'Esta acción borrará el archivo físico del servidor y no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async result => {
            if (!result.isConfirmed) return;

            const fd = new FormData();
            fd.append('peticion', 'eliminar');
            fd.append('ruta', ruta);

            try {
                const res      = await fetch(window.BASE_URL + '?page=Media', { method: 'POST', body: fd });
                const response = await res.json();

                if (response && response.resultado === 200) {
                    Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1500, showConfirmButton: false });
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('imageDetailModal'));
                    if (modalInstance) modalInstance.hide();
                    allMedia = allMedia.filter(m => m.ruta !== ruta);
                    renderGrid();
                } else {
                    Swal.fire('Error', response.mensaje || 'No se pudo eliminar.', 'error');
                }
            } catch (e) {
                Swal.fire('Error de red', 'No se pudo comunicar con el servidor.', 'error');
            }
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const $btn = $('#btn-copy-path');
            const original = $btn.html();
            $btn.empty().append($('<i>', { class: 'fas fa-check me-2' })).append('¡Copiado!');
            setTimeout(() => $btn.html(original), 1500);
        }).catch(() => {
            // Fallback para navegadores sin clipboard API
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        });
    }

    function mostrarError(msg) {
        if ($grid) {
            $grid.html(
                '<div class="col-12 text-center text-danger py-5">' +
                    '<i class="fas fa-times-circle fs-1"></i>' +
                    '<p class="mt-3">' + msg + '</p>' +
                    '<button class="btn btn-outline-secondary btn-sm mt-2" onclick="MediaManager.reload()"><i class="fas fa-sync me-1"></i> Reintentar</button>' +
                '</div>'
            );
        }
    }

    // API pública
    window.MediaManager = { init, mostrarDetalles, reload: cargarMedia };

    $(document).ready(() => window.MediaManager.init());

})(jQuery);
