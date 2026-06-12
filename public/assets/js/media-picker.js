/**
 * MEDIA-PICKER.JS - Puente para seleccionar imágenes de la galería
 */
const MediaPicker = (function($) {
    'use strict';

    let mediaData = [];
    let onSelectCallback = null;

    function init() {
        $('#picker-dir, #picker-search').on('change keyup', renderGrid);
    }

    /**
     * Abre el modal del selector
     * @param {Object} options { multiple: false, onSelect: function(data) }
     */
    function open(options = {}) {
        onSelectCallback = options.onSelect || null;
        
        let modalEl = document.getElementById('mediaPickerModal');
        if (!modalEl) {
            console.error("No se encontró el elemento #mediaPickerModal en el DOM");
            return;
        }
        
        const modal = new bootstrap.Modal(modalEl);
        cargarMedia();
        modal.show();
    }

    async function cargarMedia() {
        const $grid = $('#picker-grid');
        $grid.empty().append(
            $('<div>', { class: 'col-12 text-center py-5' }).append(
                $('<div>', { class: 'spinner-border text-primary' })
            )
        );
        
        const fd = new FormData();
        fd.append('peticion', 'consultar');

        try {
            const response = await enviaAjax(fd, BASE_URL + '?page=Media');
            if (response && response.resultado === 200) {
                mediaData = response.datos;
                renderGrid();
            }
        } catch (e) {
            $grid.empty().append($('<div>', { class: 'col-12 text-center text-danger', text: 'Error al cargar galería.' }));
        }
    }

    function renderGrid() {
        const dir = $('#picker-dir').val();
        const search = $('#picker-search').val().toLowerCase();

        let filtered = mediaData.filter(item => {
            const matchesDir = !dir || item.directorio === dir;
            const matchesSearch = !search || item.nombre.toLowerCase().includes(search);
            return matchesDir && matchesSearch;
        });

        const $grid = $('#picker-grid');
        $grid.empty();

        if (filtered.length === 0) {
            $grid.append($('<div>', { class: 'col-12 text-center py-5 text-muted', text: 'No hay imágenes.' }));
        } else {
            filtered.forEach(item => {
                const $col = $('<div>', { class: 'col-4 col-md-3 col-lg-2 picker-item' });
                const $card = $('<div>', { class: 'card picker-card h-100 shadow-sm border-0' })
                    .on('click', () => select(item.ruta));
                
                $card.append($('<img>', { src: BASE_URL + item.ruta, class: 'card-img-top picker-preview', loading: 'lazy' }));
                
                const $body = $('<div>', { class: 'card-body p-1 text-center' });
                $body.append($('<span>', { class: 'small text-truncate d-block', css: { fontSize: '0.7rem' }, text: item.nombre }));
                
                $card.append($body);
                $col.append($card);
                $grid.append($col);
            });
        }

        $('#picker-selection-info').text(`${filtered.length} imágenes encontradas.`);
    }

    function select(ruta) {
        if (onSelectCallback) {
            onSelectCallback(ruta);
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('mediaPickerModal'));
            if (modalInstance) modalInstance.hide();
        }
    }

    return {
        init,
        open,
        select
    };

})(jQuery);

$(document).ready(() => MediaPicker.init());
