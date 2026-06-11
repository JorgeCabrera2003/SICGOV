/**
 * MEDIA.JS - Gestor de Multimedia
 */
const MediaManager = (function($) {
    'use strict';

    let allMedia = [];
    const $grid = $('#media-grid');

    function init() {
        cargarMedia();
        bindEvents();
    }

    function bindEvents() {
        $('#btn-refresh').on('click', cargarMedia);
        $('#filter-dir, #filter-status').on('change', () => renderGrid());
        $('#search-input').on('keyup', () => renderGrid());

        // Modal de subida
        $('#upload-form').on('submit', function(e) {
            e.preventDefault();
            subirArchivo(this);
        });

        // Copiar ruta
        $('#btn-copy-path').on('click', function() {
            const path = $('#detail-path').text();
            copyToClipboard(path);
        });

        // Eliminar
        $('#btn-delete-file').on('click', function() {
            const path = $('#detail-path').text();
            confirmarEliminar(path);
        });
    }

    async function cargarMedia() {
        $grid.empty().append(
            $('<div>', { class: 'col-12 text-center py-5' }).append(
                $('<div>', { class: 'spinner-border text-primary' })
            ).append($('<p>', { class: 'mt-3', text: 'Actualizando galería...' }))
        );
        
        const fd = new FormData();
        fd.append('peticion', 'consultar');

        try {
            const response = await enviaAjax(fd, BASE_URL + '?page=multimedia');
            if (response && response.resultado === 200) {
                allMedia = Array.isArray(response.datos) ? response.datos : [];
                renderGrid();
            } else {
                mostrarError('Error al cargar multimedia: ' + (response?.mensaje || 'Respuesta inválida del servidor'));
            }
        } catch (e) {
            mostrarError('Fallo al cargar multimedia');
        }
    }

    function renderGrid() {
        const dirFilter = $('#filter-dir').val();
        const statusFilter = $('#filter-status').val();
        const searchFilter = $('#search-input').val().toLowerCase();

        let filtered = allMedia.filter(item => {
            const matchesDir = !dirFilter || item.directorio === dirFilter;
            const matchesStatus = !statusFilter || (statusFilter === 'linked' ? item.en_uso : !item.en_uso);
            const nombre = item.nombre || '';
            const matchesSearch = !searchFilter || nombre.toLowerCase().includes(searchFilter);
            return matchesDir && matchesStatus && matchesSearch;
        });

        $grid.empty();

        if (filtered.length === 0) {
            $grid.append(
                $('<div>', { class: 'col-12 text-center py-5' }).append(
                    $('<i>', { class: 'fas fa-search-minus fs-1 text-muted' })
                ).append($('<p>', { class: 'mt-3', text: 'No se encontraron archivos con esos filtros.' }))
            );
            return;
        }

        filtered.forEach(item => {
            const badgeClass = item.en_uso ? 'bg-success' : 'bg-warning text-dark';
            const badgeText = item.en_uso ? 'Vinculada' : 'Sin uso';
            const fileSize = (item.size / 1024).toFixed(1) + ' KB';

            const $col = $('<div>', { class: 'col-6 col-md-4 col-lg-3 mb-4', 'data-path': item.ruta });
            const $card = $('<div>', { class: 'media-manager__item rounded-4 overflow-hidden position-relative h-100 d-flex flex-column' })
                .on('click', () => mostrarDetalles(item.ruta));
            
            const $preview = $('<div>', { class: 'media-manager__preview position-relative w-100 d-flex align-items-center justify-content-center' });
            $preview.append($('<span>', { class: `badge media-manager__badge position-absolute top-0 end-0 m-2 rounded-pill z-2 ${badgeClass}`, text: badgeText }));
            $preview.append($('<img>', { src: BASE_URL + item.ruta, loading: 'lazy', class: 'media-manager__image w-100 h-100 object-fit-cover' }));
            
            const $body = $('<div>', { class: 'p-3 d-flex flex-column flex-grow-1' });
            $body.append($('<p>', { class: 'small text-truncate mb-2 fw-bold', title: item.nombre || 'Sin nombre', text: item.nombre || 'Sin nombre' }));
            
            const $info = $('<div>', { class: 'd-flex justify-content-between align-items-center mt-auto' });
            const dirLabel = (item.directorio || 'General').toUpperCase();
            $info.append($('<span>', { class: 'badge rounded-pill', css: { backgroundColor: 'var(--color-bg-muted, rgba(26, 28, 32, 0.05))', color: 'var(--color-sidebar)', fontSize: '0.65rem' }, text: dirLabel }));
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

        $('#detail-preview').attr('src', BASE_URL + item.ruta);
        $('#detail-name').text(item.nombre);
        $('#detail-path').text(item.ruta);
        $('#detail-size').text((item.size / 1024).toFixed(1) + ' KB');
        $('#detail-type').text(item.tipo.toUpperCase());

        const $linksContainer = $('#detail-links');
        $linksContainer.empty();

        if (item.vinculos && item.vinculos.length > 0) {
            item.vinculos.forEach(link => {
                const label = link.nombre || link.id;
                const $div = $('<div>', { class: 'badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 p-2 me-1 mb-1 text-start d-block' });
                $div.append($('<i>', { class: 'fas fa-link me-1' })).append(document.createTextNode(` ${link.tipo}: `)).append($('<strong>', { text: label }));
                $linksContainer.append($div);
            });
            $('#btn-delete-file').prop('disabled', true).addClass('opacity-50').attr('title', 'No se puede eliminar una imagen vinculada');
        } else {
            const $span = $('<span>', { class: 'text-warning small fst-italic' })
                .append($('<i>', { class: 'fas fa-exclamation-triangle me-1' })).append(' Imagen huérfana (puede eliminarse)');
            $linksContainer.append($span);
            $('#btn-delete-file').prop('disabled', false).removeClass('opacity-50').removeAttr('title');
        }

        const modal = new bootstrap.Modal(document.getElementById('imageDetailModal'));
        modal.show();
    }

    async function subirArchivo(form) {
        const fd = new FormData(form);
        const $btn = $(form).find('button[type="submit"]');
        
        $btn.prop('disabled', true).empty().append($('<span>', { class: 'spinner-border spinner-border-sm me-2' })).append('Subiendo...');

        fd.append('peticion', 'registrar');

        try {
            const response = await enviaAjax(fd, BASE_URL + '?page=multimedia');
            if (response && response.resultado === 200) {
                Swal.fire('¡Éxito!', 'Imagen subida correctamente', 'success');
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
                if (modalInstance) modalInstance.hide();
                form.reset();
                cargarMedia();
            } else {
                Swal.fire('Error', response.mensaje || 'Error desconocido', 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Fallo al procesar.', 'error');
        } finally {
            $btn.prop('disabled', false).text('Subir Ahora');
        }
    }

    function confirmarEliminar(ruta) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción borrará el archivo físico del servidor.",
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
                fd.append('ruta', ruta);

                try {
                    const response = await enviaAjax(fd, BASE_URL + '?page=multimedia');
                    if (response && response.resultado === 200) {
                        Swal.fire('Eliminado', response.mensaje || 'Archivo eliminado', 'success');
                        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('imageDetailModal'));
                        if (modalInstance) modalInstance.hide();
                        allMedia = allMedia.filter(m => m.ruta !== ruta);
                        renderGrid();
                    } else {
                        Swal.fire('Ocurrió un error', response.mensaje, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Error de red', 'error');
                }
            }
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const $btn = $('#btn-copy-path');
            const originalHtml = $btn.html();
            $btn.empty().append($('<i>', { class: 'fas fa-check me-2' })).append('¡Copiado!');
            setTimeout(() => $btn.html(originalHtml), 1500);
        });
    }

    function mostrarError(msg) {
        $grid.empty().append(
            $('<div>', { class: 'col-12 text-center text-danger py-5' }).append(
                $('<i>', { class: 'fas fa-times-circle fs-1' })
            ).append($('<p>', { class: 'mt-3', text: msg }))
        );
    }

    return {
        init,
        mostrarDetalles
    };

})(jQuery);

$(document).ready(() => MediaManager.init());
