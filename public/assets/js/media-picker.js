/**
 * MEDIA-PICKER.JS - Selector de imágenes de la galería + subida rápida
 * Script clásico (IIFE) compatible con carga sin type="module"
 */
(function($) {
    'use strict';

    let mediaData = [];
    let onSelectCallback = null;

    function init() {
        $('#picker-dir, #picker-search').on('change keyup', renderGrid);
        bindUploadEvents();
    }

    /**
     * Abre el modal del selector
     * @param {Object} options { onSelect: function(ruta) {} }
     */
    function open(options = {}) {
        onSelectCallback = options.onSelect || null;

        const modalEl = document.getElementById('mediaPickerModal');
        if (!modalEl) {
            console.error('[MediaPicker] No se encontró #mediaPickerModal en el DOM');
            return;
        }

        cargarMedia();
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    async function cargarMedia() {
        const $grid = $('#picker-grid');
        $grid.html(
            '<div class="col-12 text-center py-5">' +
                '<div class="spinner-border text-warning"></div>' +
                '<p class="mt-2 text-muted">Cargando galería...</p>' +
            '</div>'
        );

        const fd = new FormData();
        fd.append('peticion', 'consultar');

        try {
            const res = await fetch(window.BASE_URL + '?page=Media', {
                method: 'POST',
                body: fd
            });
            const response = await res.json();

            if (response && response.resultado === 200) {
                mediaData = response.datos || [];
                renderGrid();
            } else {
                $grid.html('<div class="col-12 text-center text-danger py-4">Error al cargar la galería.</div>');
            }
        } catch (e) {
            console.error('[MediaPicker] Error fetch:', e);
            $grid.html('<div class="col-12 text-center text-danger py-4">Error de conexión con el servidor.</div>');
        }
    }

    function renderGrid() {
        const dir = $('#picker-dir').val();
        const search = ($('#picker-search').val() || '').toLowerCase();

        const filtered = mediaData.filter(item => {
            const matchesDir = !dir || item.directorio === dir;
            const matchesSearch = !search || (item.nombre || '').toLowerCase().includes(search);
            return matchesDir && matchesSearch;
        });

        const $grid = $('#picker-grid');
        $grid.empty();

        if (filtered.length === 0) {
            $grid.html('<div class="col-12 text-center py-5 text-muted"><i class="fas fa-images fa-2x mb-2"></i><p>No hay imágenes en esta sección.</p></div>');
        } else {
            filtered.forEach(item => {
                const imgSrc = window.BASE_URL.replace(/\/$/, '') + item.ruta;
                const $col = $('<div>', { class: 'col-4 col-md-3 col-lg-2 picker-item' });
                const $card = $('<div>', { class: 'card picker-card h-100 shadow-sm border-0' })
                    .on('click', () => select(item.ruta));

                $card.append($('<img>', {
                    src: imgSrc,
                    class: 'card-img-top picker-preview',
                    loading: 'lazy',
                    alt: item.nombre
                }));

                const $body = $('<div>', { class: 'card-body p-1 text-center' });
                $body.append($('<span>', {
                    class: 'small text-truncate d-block',
                    css: { fontSize: '0.65rem' },
                    text: item.nombre
                }));

                $card.append($body);
                $col.append($card);
                $grid.append($col);
            });
        }

        $('#picker-selection-info').text(filtered.length + ' imagen(es) encontrada(s).');
    }

    function select(ruta) {
        if (onSelectCallback) {
            onSelectCallback(ruta);
        }
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('mediaPickerModal'));
        if (modalInstance) modalInstance.hide();
    }

    // ── Subida rápida desde el picker ────────────────────────────
    function bindUploadEvents() {
        $(document).on('submit', '#picker-upload-form', async function(e) {
            e.preventDefault();
            await subirDesdePickerForm(this);
        });

        // Drag & drop zone
        $(document).on('dragover', '#picker-drop-zone', function(e) {
            e.preventDefault();
            $(this).addClass('picker-drop-hover');
        }).on('dragleave drop', '#picker-drop-zone', function(e) {
            e.preventDefault();
            $(this).removeClass('picker-drop-hover');
            if (e.type === 'drop') {
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    $('#picker-upload-input').prop('files', files);
                    subirArchivoDirecto(files[0], $('#picker-upload-dir').val() || 'uploads');
                }
            }
        });
    }

    async function subirDesdePickerForm(form) {
        const $btn = $(form).find('button[type="submit"]');
        const fileInput = $(form).find('input[type="file"]')[0];
        const directorio = $(form).find('select[name="directorio"]').val() || 'uploads';

        if (!fileInput || !fileInput.files.length) {
            alert('Selecciona un archivo primero.');
            return;
        }

        $btn.prop('disabled', true).text('Subiendo...');

        const fd = new FormData();
        fd.append('peticion', 'registrar');
        fd.append('archivo', fileInput.files[0]);
        fd.append('directorio', directorio);

        try {
            const res = await fetch(window.BASE_URL + '?page=Media', {
                method: 'POST',
                body: fd
            });
            const response = await res.json();

            if (response && response.resultado === 200) {
                form.reset();
                $('#picker-upload-section').slideUp();
                await cargarMedia();
                // Auto-seleccionar la imagen recién subida
                if (response.ruta) {
                    select(response.ruta);
                }
            } else {
                alert('Error: ' + (response.mensaje || 'No se pudo subir la imagen.'));
            }
        } catch (err) {
            console.error('[MediaPicker] Error al subir:', err);
            alert('Error de conexión al subir la imagen.');
        } finally {
            $btn.prop('disabled', false).text('Subir y Seleccionar');
        }
    }

    async function subirArchivoDirecto(file, directorio) {
        const fd = new FormData();
        fd.append('peticion', 'registrar');
        fd.append('archivo', file);
        fd.append('directorio', directorio);

        try {
            const res = await fetch(window.BASE_URL + '?page=Media', { method: 'POST', body: fd });
            const response = await res.json();
            if (response && response.resultado === 200) {
                await cargarMedia();
                if (response.ruta) select(response.ruta);
            } else {
                alert('Error al subir: ' + (response.mensaje || 'Desconocido'));
            }
        } catch(err) {
            alert('Error de conexión.');
        }
    }

    // Exponer globalmente
    window.MediaPicker = { init, open, select, reload: cargarMedia };

    $(document).ready(() => window.MediaPicker.init());

})(jQuery);
