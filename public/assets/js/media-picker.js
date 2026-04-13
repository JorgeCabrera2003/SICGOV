/**
 * MEDIA-PICKER.JS - Puente para seleccionar imágenes de la galería
 */
const MediaPicker = (function($) {
    'use strict';

    let mediaData = [];
    let onSelectCallback = null;
    let selectedOriginal = null;

    function init() {
        $('#picker-dir, #picker-search').on('change keyup', renderGrid);
    }

    /**
     * Abre el modal del selector
     * @param {Object} options { multiple: false, onSelect: function(data) }
     */
    function open(options = {}) {
        onSelectCallback = options.onSelect || null;
        const modal = new bootstrap.Modal(document.getElementById('mediaPickerModal'));
        
        cargarMedia();
        modal.show();
    }

    function cargarMedia() {
        $('#picker-grid').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>');
        
        $.ajax({
            url: BASE_URL + '/?page=multimedia',
            type: 'POST',
            data: { peticion: 'consultar' },
            dataType: 'json'
        })
        .done(response => {
            if (response.resultado === 200) {
                mediaData = response.datos;
                renderGrid();
            }
        });
    }

    function renderGrid() {
        const dir = $('#picker-dir').val();
        const search = $('#picker-search').val().toLowerCase();

        let filtered = mediaData.filter(item => {
            const matchesDir = !dir || item.directorio === dir;
            const matchesSearch = !search || item.nombre.toLowerCase().includes(search);
            return matchesDir && matchesSearch;
        });

        let html = '';
        filtered.forEach((item, index) => {
            html += `
                <div class="col-4 col-md-3 col-lg-2 picker-item">
                    <div class="card picker-card h-100 shadow-sm border-0" onclick="MediaPicker.select('${item.ruta}')">
                        <img src="${BASE_URL}${item.ruta}" class="card-img-top picker-preview">
                        <div class="card-body p-1 text-center">
                            <span class="small text-truncate d-block" style="font-size: 0.7rem;">${item.nombre}</span>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#picker-grid').html(html || '<div class="col-12 text-center py-5 text-muted">No hay imágenes.</div>');
        $('#picker-selection-info').text(`${filtered.length} imágenes encontradas.`);
    }

    function select(ruta) {
        if (onSelectCallback) {
            onSelectCallback(ruta);
            bootstrap.Modal.getInstance(document.getElementById('mediaPickerModal')).hide();
        }
    }

    return {
        init,
        open,
        select
    };

})(jQuery);

$(document).ready(() => MediaPicker.init());
