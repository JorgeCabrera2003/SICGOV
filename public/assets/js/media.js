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
        $('#search-media').on('keyup', () => renderGrid());

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

    function cargarMedia() {
        $grid.html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3">Actualizando galería...</p></div>');
        
        $.ajax({
            url: BASE_URL + '/?page=multimedia',
            type: 'POST',
            data: { peticion: 'consultar' },
            dataType: 'json'
        })
        .done(response => {
            if (response.resultado === 200) {
                allMedia = response.datos;
                renderGrid();
            } else {
                mostrarError('Error al cargar multimedia: ' + (response.mensaje || 'Error desconocido'));
            }
        });
    }

    function renderGrid() {
        const dirFilter = $('#filter-dir').val();
        const statusFilter = $('#filter-status').val();
        const searchFilter = $('#search-media').val().toLowerCase();

        let filtered = allMedia.filter(item => {
            const matchesDir = !dirFilter || item.directorio === dirFilter;
            const matchesStatus = !statusFilter || (statusFilter === 'linked' ? item.en_uso : !item.en_uso);
            const matchesSearch = !searchFilter || item.nombre.toLowerCase().includes(searchFilter);
            return matchesDir && matchesStatus && matchesSearch;
        });

        if (filtered.length === 0) {
            $grid.html('<div class="col-12 text-center py-5"><i class="fas fa-search-minus fs-1 text-muted"></i><p class="mt-3">No se encontraron archivos con esos filtros.</p></div>');
            return;
        }

        let html = '';
        filtered.forEach(item => {
            const badgeClass = item.en_uso ? 'bg-success' : 'bg-warning text-dark';
            const badgeText = item.en_uso ? 'Vinculada' : 'Sin uso';
            const fileSize = (item.size / 1024).toFixed(1) + ' KB';

            html += `
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 media-item" data-path="${item.ruta}">
                    <div class="card media-card border-0 shadow-sm overflow-hidden" onclick="MediaManager.mostrarDetalles('${item.ruta}')">
                        <div class="media-preview-container">
                            <img src="${BASE_URL}${item.ruta}" loading="lazy">
                            <span class="badge ${badgeClass} media-badge">${badgeText}</span>
                        </div>
                        <div class="card-body p-2">
                            <p class="small text-truncate mb-0 fw-bold" title="${item.nombre}">${item.nombre}</p>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="badge directory-badge fw-normal" style="font-size:0.7rem">${item.directorio.toUpperCase()}</span>
                                <span class="text-muted" style="font-size:0.7rem">${fileSize}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $grid.html(html);
    }

    function mostrarDetalles(ruta) {
        const item = allMedia.find(m => m.ruta === ruta);
        if (!item) return;

        $('#detail-preview').attr('src', BASE_URL + item.ruta);
        $('#detail-name').text(item.nombre);
        $('#detail-path').text(item.ruta);
        $('#detail-size').text((item.size / 1024).toFixed(1) + ' KB');
        $('#detail-type').text(item.tipo.toUpperCase());

        // Renderizar vinculaciones
        let linksHtml = '';
        if (item.vinculos && item.vinculos.length > 0) {
            item.vinculos.forEach(link => {
                const label = link.nombre || link.id;
                linksHtml += `<div class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 p-2 me-1 mb-1 text-start d-block">
                    <i class="fas fa-link me-1"></i> ${link.tipo}: <strong>${label}</strong>
                </div>`;
            });
            $('#btn-delete-file').prop('disabled', true).addClass('opacity-50').attr('title', 'No se puede eliminar una imagen vinculada');
        } else {
            linksHtml = '<span class="text-warning small italic"><i class="fas fa-exclamation-triangle me-1"></i> Imagen huérfana (puede eliminarse)</span>';
            $('#btn-delete-file').prop('disabled', false).removeClass('opacity-50').removeAttr('title');
        }
        $('#detail-links').html(linksHtml);

        const modal = new bootstrap.Modal(document.getElementById('imageDetailModal'));
        modal.show();
    }

    function subirArchivo(form) {
        const formData = new FormData(form);
        const $btn = $(form).find('button[type="submit"]');
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Subiendo...');

        formData.append('peticion', 'registrar');

        $.ajax({
            url: BASE_URL + '/?page=multimedia',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false
        })
        .done(response => {
            if (response.resultado === 200) {
                Swal.fire('¡Éxito!', 'Imagen subida correctamente', 'success');
                bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                form.reset();
                cargarMedia();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        })
        .always(() => {
            $btn.prop('disabled', false).text('Subir Ahora');
        });
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
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/?page=multimedia',
                    type: 'POST',
                    data: { 
                        peticion: 'eliminar',
                        ruta: ruta 
                    }
                })
                .done(response => {
                    if (response.resultado === 200) {
                        Swal.fire('Eliminado', response.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('imageDetailModal')).hide();
                        allMedia = allMedia.filter(m => m.ruta !== ruta);
                        renderGrid();
                    } else {
                        Swal.fire('Ocurrió un error', response.message, 'error');
                    }
                });
            }
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const $btn = $('#btn-copy-path');
            const originalHtml = $btn.html();
            $btn.html('<i class="fas fa-check me-2"></i>¡Copiado!');
            setTimeout(() => $btn.html(originalHtml), 1500);
        });
    }

    function mostrarError(msg) {
        $grid.html(`<div class="col-12 text-center text-danger py-5"><i class="fas fa-times-circle fs-1"></i><p class="mt-3">${msg}</p></div>`);
    }

    // Public API
    return {
        init,
        mostrarDetalles
    };

})(jQuery);

$(document).ready(() => MediaManager.init());
