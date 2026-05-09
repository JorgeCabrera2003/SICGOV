import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";

const modalNoticia = document.getElementById('modalNoticia') ? new bootstrap.Modal(document.getElementById('modalNoticia')) : null;
const $formNoticia = $('#formNoticia');
const $previewContainer = $('#previewContainer');

export async function CargarNoticias() {
    const peticion = new FormData();
    peticion.append('peticion', 'consultar');
    
    try {
        const json = await AjaxHelper.enviaAjax(peticion, '?page=noticias-admin');
        if (json && json.resultado === 200) {
            DataTablePrincipal(json.datos);
        } else {
            DataTablePrincipal([]);
        }
    } catch (e) {
        console.error("Error al cargar noticias", e);
        DataTablePrincipal([]);
    }
}

export function DataTablePrincipal(datos) {
    if ($.fn.DataTable.isDataTable('#tablaNoticias')) {
        $('#tablaNoticias').DataTable().destroy();
    }

    $('#tablaNoticias').DataTable({
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
                    return UIActionBtn({
                        items: [
                            {
                                text: 'Ver noticia pública',
                                icon: 'fas fa-external-link-alt text-info',
                                class: 'btn-ver-publico',
                                id: row.id_noticia,
                                'data-id': row.id_noticia
                            },
                            { divider: true },
                            {
                                text: 'Editar',
                                icon: 'fas fa-edit text-primary',
                                class: 'btn-editar',
                                id: row.id_noticia,
                                'data-id': row.id_noticia,
                                'data-modulo': 'Noticia',
                                'data-accion': 0
                            },
                            { divider: true },
                            {
                                text: 'Eliminar',
                                icon: 'fas fa-trash',
                                class: 'btn-eliminar text-danger',
                                id: row.id_noticia,
                                'data-id': row.id_noticia,
                                'data-modulo': 'Noticia',
                                'data-accion': 1
                            }
                        ]
                    });
                }
            }
        ]
    });
}

export function LimpiarFormulario() {
    if ($formNoticia.length) {
        $formNoticia[0].reset();
        $previewContainer.empty();
        $('#currentImagesSection').hide();
        $('#imagenes_galeria').val('');
        $('#imagenes').val('');
        
        if (typeof SistemaValidacion !== 'undefined') {
            $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            $('.invalid-feedback, .valid-feedback').removeClass('invalid-feedback valid-feedback').text('');
            $('#btnGuardarNoticia').prop('disabled', true);
        }

        // Autocompletar la fecha y hora de publicación con el momento actual
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#fecha_publicacion').val(now.toISOString().slice(0, 16));
    }
}

export function EditarModal(accion) {
    if (accion === 'registrar') {
        $('#peticion').val('registrar');
        $('#modalNoticiaLabel').empty()
            .append($('<i>', { class: 'fas fa-newspaper me-2' })).append('Nueva Noticia');
    } else {
        $('#peticion').val('modificar');
        $('#modalNoticiaLabel').empty()
            .append($('<i>', { class: 'fas fa-edit me-2' })).append('Editar Noticia');
    }
    modalNoticia.show();
}

export async function EnviarFormulario() {
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

    const fd = new FormData($formNoticia[0]);
    
    try {
        const res = await AjaxHelper.enviaAjax(fd, '?page=noticias-admin');
        if (res && res.resultado === 200) {
            MensajeriaHelper.GenerarMensaje('success', 3000, 'Éxito', res.mensaje);
            modalNoticia.hide();
            await CargarNoticias();
        } else {
            MensajeriaHelper.GenerarMensaje('error', 5000, 'Error', res?.mensaje || 'Error en respuesta');
        }
    } catch (error) {
        console.error("Error:", error);
    } finally {
        $btnSubmit.prop('disabled', false).html(originalContent);
    }
}

export async function EditarFormNoticia(datosFila) {
    const id = datosFila.id_noticia;
    
    const fd = new FormData();
    fd.append('peticion', 'validar');
    fd.append('id_noticia', id);

    const res = await AjaxHelper.enviaAjax(fd, '?page=noticias-admin');
    if (res && res.resultado === 200) {
        const d = res.registro;
        LimpiarFormulario();
        EditarModal('modificar');
        
        $('#id_noticia').val(d.id_noticia);
        $('#titulo').val(d.titulo);
        $('#subtitulo').val(d.subtitulo);
        $('#contenido').val(d.contenido);
        $('#tipo').val(d.tipo);
        
        if (d.fecha_publicacion) {
            $('#fecha_publicacion').val(d.fecha_publicacion.slice(0, 16));
        }
        
        RenderCurrentImages(d.imagenes);
        
        if (typeof SistemaValidacion !== 'undefined') {
            $('#titulo, #contenido, #tipo').trigger('blur');
        }
    }
}

export async function EliminarNoticia(id) {
    const confirmado = await MensajeriaHelper.MostrarConfirmacion(
        '¿Eliminar noticia?',
        'Esta acción marcará la noticia como inactiva',
        'warning'
    );

    if (confirmado) {
        const fd = new FormData();
        fd.append('peticion', 'eliminar');
        fd.append('id_noticia', id);

        const res = await AjaxHelper.enviaAjax(fd, '?page=noticias-admin');
        if (res && res.resultado === 200) {
            MensajeriaHelper.GenerarMensaje('success', 3000, 'Eliminado', res.mensaje);
            await CargarNoticias();
        } else {
            MensajeriaHelper.GenerarMensaje('error', 5000, 'Error', res?.mensaje || 'Ocurrió un error');
        }
    }
}

export function VerNoticiaPublica(id) {
    window.open(BASE_URL + '?page=noticias-detalle&id=' + id, '_blank');
}

export function RenderCurrentImages(imagenes) {
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
                        .on('click', () => MarcarPrincipal(img.id_imagen))
                );
            }
            $btnContainer.append(
                $('<button>', { type: 'button', class: 'btn btn-xs btn-outline-danger btn-borrar-img', 'data-id': img.id_imagen, title: 'Eliminar imagen' })
                    .append($('<i>', { class: 'fas fa-trash' }))
                    .on('click', () => EliminarImagen(img.id_imagen))
            );

            $div.append($btnContainer);
            $container.append($div);
        });
    } else {
        $section.hide();
    }
}

export async function EliminarImagen(id) {
    const confirmado = await MensajeriaHelper.MostrarConfirmacion(
        '¿Quitar imagen de la noticia?',
        'La imagen se desvinculará de esta publicación. El archivo original permanece disponible en el Gestor Multimedia.',
        'warning'
    );

    if (confirmado) {
        const fd = new FormData();
        fd.append('peticion', 'eliminar_imagen');
        fd.append('id_imagen', id);

        const res = await AjaxHelper.enviaAjax(fd, '?page=noticias-admin');
        if (res && res.resultado === 200) {
            const id_noticia = $('#id_noticia').val();
            await ReloadImages(id_noticia);
            await CargarNoticias();
        }
    }
}

export async function MarcarPrincipal(id) {
    const fd = new FormData();
    fd.append('peticion', 'marcar_principal');
    fd.append('id_imagen', id);

    const res = await AjaxHelper.enviaAjax(fd, '?page=noticias-admin');
    if (res && res.resultado === 200) {
        const id_noticia = $('#id_noticia').val();
        await ReloadImages(id_noticia);
        await CargarNoticias();
    }
}

export async function ReloadImages(id) {
    const fd = new FormData();
    fd.append('peticion', 'validar');
    fd.append('id_noticia', id);

    const res = await AjaxHelper.enviaAjax(fd, '?page=noticias-admin');
    if (res && res.resultado === 200) {
        RenderCurrentImages(res.registro.imagenes);
    }
}

export function AgregarImagenGaleria(ruta) {
    const $inputGaleria = $('#imagenes_galeria');
    let seleccionadas = $inputGaleria.val() ? JSON.parse($inputGaleria.val()) : [];
    
    if (seleccionadas.includes(ruta)) {
        MensajeriaHelper.GenerarMensaje('info', 3000, 'Información', 'Esta imagen ya ha sido seleccionada');
        return;
    }

    seleccionadas.push(ruta);
    $inputGaleria.val(JSON.stringify(seleccionadas));
    RenderPreviewGaleria(ruta);
}

export function RenderPreviewGaleria(ruta) {
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
                RemoverDeGaleria(ruta);
            })
    );
    $col.append(
        $('<img>', { src: BASE_URL + ruta, css: { width: '100%', height: '100%', objectFit: 'cover' } })
    );
    
    $previewContainer.append($col);
}

export function RemoverDeGaleria(ruta) {
    const $inputGaleria = $('#imagenes_galeria');
    let seleccionadas = JSON.parse($inputGaleria.val() || '[]');
    seleccionadas = seleccionadas.filter(r => r !== ruta);
    $inputGaleria.val(JSON.stringify(seleccionadas));
}

export function ManejarCambioImagenes(e) {
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
}

export function CapaValidar() {
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
}
