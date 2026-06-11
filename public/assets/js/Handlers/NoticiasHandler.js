import * as AjaxHelper from "../Helpers/AjaxHelper.js";
import * as MensajeriaHelper from "../Helpers/MensajeriaHelper.js";

//-------INICIALIZACIÓN-------

function GetEtiquetas(tipo) {
    const inputs = {
        id_noticia: $('#id_noticia'),
        peticion: $('#peticion'),
        titulo: $('#titulo'),
        subtitulo: $('#subtitulo'),
        contenido: $('#contenido'),
        tipo: $('#tipo'),
        fecha_publicacion: $('#fecha_publicacion'),
        imagenes: $('#imagenes'),
        imagenes_galeria: $('#imagenes_galeria')
    };

    const modales = {
        noticia: $('#modalNoticia'),
        titulo: $('#modalNoticiaLabel'),
        botonGuardar: $('#btnGuardarNoticia')
    };

    const contenedores = {
        preview: $('#previewContainer'),
        currentImages: $('#currentImagesContainer'),
        currentSection: $('#currentImagesSection')
    };

    if (tipo === "inputs") return inputs;
    if (tipo === "modales") return modales;
    if (tipo === "contenedores") return contenedores;
    return null;
}

export async function CargarNoticias() {
    const peticion = new FormData();
    peticion.append('peticion', 'consultar');
    
    try {
        const json = await AjaxHelper.enviaAjax(peticion, '?page=Noticia');
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
                            const $span = $('<span>', { class: 'badge bg-warning text-dark' })
                                .append($('<i>', { class: 'fas fa-clock me-1' })).append(' Programada');
                            return $span.prop('outerHTML');
                        }
                        const $span = $('<span>', { class: 'badge bg-success' })
                            .append($('<i>', { class: 'fas fa-check-circle me-1' })).append(' Publicada');
                        return $span.prop('outerHTML');
                    }
                    const $span = $('<span>', { class: 'badge bg-danger' })
                        .append($('<i>', { class: 'fas fa-times-circle me-1' })).append(' Eliminada');
                    return $span.prop('outerHTML');
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return RenderAcciones(row);
                }
            }
        ]
    });
}

function RenderAcciones(row) {
    const $dropdown = $('<div>', { class: 'dropdown d-inline-block' });
    const $btn = $('<button>', {
        class: 'btn btn-sm btn-light border dropdown-toggle',
        type: 'button',
        'data-bs-toggle': 'dropdown',
        'aria-expanded': 'false'
    }).append($('<i>', { class: 'fas fa-ellipsis-v me-2' }), 'Acciones');

    const $menu = $('<ul>', { class: 'dropdown-menu dropdown-menu-end shadow-sm' });
    
    // Ver Público
    const $itemVer = $('<li>').append(
        $('<a>', {
            class: 'dropdown-item btn-ver-publico',
            href: 'javascript:void(0)',
            'data-id': row.id_noticia
        }).append($('<i>', { class: 'fas fa-external-link-alt me-2 text-info' }), 'Ver noticia pública')
    );

    // Editar
    const $itemEditar = $('<li>').append(
        $('<a>', {
            class: 'dropdown-item btn-editar text-primary',
            href: 'javascript:void(0)',
            'data-id': row.id_noticia,
            'data-modulo': 'Noticia',
            'data-accion': 0
        }).append($('<i>', { class: 'fas fa-edit me-2' }), 'Editar')
    );

    // Eliminar
    const $itemEliminar = $('<li>').append(
        $('<a>', {
            class: 'dropdown-item btn-eliminar text-danger',
            href: 'javascript:void(0)',
            'data-id': row.id_noticia,
            'data-modulo': 'Noticia',
            'data-accion': 1
        }).append($('<i>', { class: 'fas fa-trash me-2' }), 'Eliminar')
    );

    $menu.append($itemVer, $('<li>').append($('<hr>', { class: 'dropdown-divider' })), $itemEditar, $itemEliminar);
    return $dropdown.append($btn, $menu).prop('outerHTML');
}


export function LimpiarFormulario() {
    const inputs = GetEtiquetas("inputs");
    const containers = GetEtiquetas("contenedores");
    const modales = GetEtiquetas("modales");

    inputs.id_noticia.val('');
    inputs.titulo.val('');
    inputs.subtitulo.val('');
    inputs.contenido.val('');
    inputs.tipo.val('INFO');
    inputs.imagenes.val('');
    inputs.imagenes_galeria.val('');
    
    containers.preview.empty();
    containers.currentSection.hide();
    
    if (typeof SistemaValidacion !== 'undefined') {
        $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
        $('.invalid-feedback, .valid-feedback').removeClass('invalid-feedback valid-feedback').text('');
        modales.botonGuardar.prop('disabled', true);
    }

    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    inputs.fecha_publicacion.val(now.toISOString().slice(0, 16));
}

export function EditarModal(accion) {
    const modales = GetEtiquetas("modales");
    const inputs = GetEtiquetas("inputs");

    if (accion === 'registrar') {
        inputs.peticion.val('registrar');
        modales.titulo.empty().append($('<i>', { class: 'fas fa-newspaper me-2' })).append('Nueva Noticia');
    } else {
        inputs.peticion.val('modificar');
        modales.titulo.empty().append($('<i>', { class: 'fas fa-edit me-2' })).append('Editar Noticia');
    }
    modales.noticia.modal('show');
}

export async function EnviarFormulario() {
    const inputs = GetEtiquetas("inputs");
    const modales = GetEtiquetas("modales");

    if (typeof SistemaValidacion !== 'undefined' && !SistemaValidacion.validarFormularioSilencioso({
        titulo: inputs.titulo, contenido: inputs.contenido, tipo: inputs.tipo
    })) {
        return;
    }

    const $btnSubmit = modales.botonGuardar;
    const originalContent = $btnSubmit.html();
    $btnSubmit.prop('disabled', true);
    $btnSubmit.empty().append($('<span>', { class: 'spinner-border spinner-border-sm me-2' })).append('Guardando...');

    const fd = new FormData($('#formNoticia')[0]);
    
    try {
        const res = await AjaxHelper.enviaAjax(fd, '?page=Noticia');
        if (res && res.resultado === 200) {
            MensajeriaHelper.GenerarMensaje('success', 3000, 'Éxito', res.mensaje);
            modales.noticia.modal('hide');
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
    const inputs = GetEtiquetas("inputs");
    
    const fd = new FormData();
    fd.append('peticion', 'validar');
    fd.append('id_noticia', id);

    const res = await AjaxHelper.enviaAjax(fd, '?page=Noticia');
    if (res && res.resultado === 200) {
        const d = res.registro;
        LimpiarFormulario();
        EditarModal('modificar');
        
        inputs.id_noticia.val(d.id_noticia);
        inputs.titulo.val(d.titulo);
        inputs.subtitulo.val(d.subtitulo);
        inputs.contenido.val(d.contenido);
        inputs.tipo.val(d.tipo);
        
        if (d.fecha_publicacion) {
            inputs.fecha_publicacion.val(d.fecha_publicacion.slice(0, 16));
        }
        
        RenderCurrentImages(d.imagenes);
        
        if (typeof SistemaValidacion !== 'undefined') {
            inputs.titulo.trigger('blur');
            inputs.contenido.trigger('blur');
            inputs.tipo.trigger('blur');
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

        const res = await AjaxHelper.enviaAjax(fd, '?page=Noticia');
        if (res && res.resultado === 200) {
            MensajeriaHelper.GenerarMensaje('success', 3000, 'Eliminado', res.mensaje);
            await CargarNoticias();
        } else {
            MensajeriaHelper.GenerarMensaje('error', 5000, 'Error', res?.mensaje || 'Ocurrió un error');
        }
    }
}

export function VerNoticiaPublica(id) {
    window.open(BASE_URL + '?page=Noticia&type=detalle&id=' + id, '_blank');
}

export function RenderCurrentImages(imagenes) {
    const containers = GetEtiquetas("contenedores");
    const $container = containers.currentImages;
    const $section = containers.currentSection;
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

            $div.append($('<img>', { class: 'rounded w-100', src: BASE_URL + img.direccion, css: { height: '100px', objectFit: 'cover' } }));

            const $btnContainer = $('<div>', { class: 'mt-1 d-flex justify-content-center gap-1' });
            if (!isPrincipal) {
                $btnContainer.append(
                    $('<button>', { type: 'button', class: 'btn btn-xs btn-outline-warning', title: 'Poner como portada' })
                        .append($('<i>', { class: 'fas fa-star' }))
                        .on('click', () => MarcarPrincipal(img.id_imagen))
                );
            }
            $btnContainer.append(
                $('<button>', { type: 'button', class: 'btn btn-xs btn-outline-danger', title: 'Eliminar imagen' })
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

        const res = await AjaxHelper.enviaAjax(fd, '?page=Noticia');
        if (res && res.resultado === 200) {
            const inputs = GetEtiquetas("inputs");
            await ReloadImages(inputs.id_noticia.val());
            await CargarNoticias();
        }
    }
}

export async function MarcarPrincipal(id) {
    const fd = new FormData();
    fd.append('peticion', 'marcar_principal');
    fd.append('id_imagen', id);

    const res = await AjaxHelper.enviaAjax(fd, '?page=Noticia');
    if (res && res.resultado === 200) {
        const inputs = GetEtiquetas("inputs");
        await ReloadImages(inputs.id_noticia.val());
        await CargarNoticias();
    }
}

export async function ReloadImages(id) {
    const fd = new FormData();
    fd.append('peticion', 'validar');
    fd.append('id_noticia', id);

    const res = await AjaxHelper.enviaAjax(fd, '?page=Noticia');
    if (res && res.resultado === 200) {
        RenderCurrentImages(res.registro.imagenes);
    }
}

export function AgregarImagenGaleria(ruta) {
    const inputs = GetEtiquetas("inputs");
    let seleccionadas = inputs.imagenes_galeria.val() ? JSON.parse(inputs.imagenes_galeria.val()) : [];
    
    if (seleccionadas.includes(ruta)) {
        MensajeriaHelper.GenerarMensaje('info', 3000, 'Información', 'Esta imagen ya ha sido seleccionada');
        return;
    }

    seleccionadas.push(ruta);
    inputs.imagenes_galeria.val(JSON.stringify(seleccionadas));
    RenderPreviewGaleria(ruta);
}

export function RenderPreviewGaleria(ruta) {
    const containers = GetEtiquetas("contenedores");
    const $col = $('<div>', { class: 'position-relative m-1 rounded border shadow-sm border-warning', css: { width: '100px', height: '100px', overflow: 'hidden' } });
    
    $col.append($('<span>', { class: 'badge bg-warning text-dark position-absolute top-0 start-0 m-1', css: { fontSize: '0.6rem' }, text: 'Galería' }));
    $col.append(
        $('<button>', { type: 'button', class: 'btn-close btn-close-white position-absolute top-0 end-0 m-1 bg-danger p-1', css: { fontSize: '0.5rem' } })
            .on('click', function() {
                $(this).parent().remove();
                RemoverDeGaleria(ruta);
            })
    );
    $col.append($('<img>', { src: BASE_URL + ruta, css: { width: '100%', height: '100%', objectFit: 'cover' } }));
    
    containers.preview.append($col);
}

export function RemoverDeGaleria(ruta) {
    const inputs = GetEtiquetas("inputs");
    let seleccionadas = JSON.parse(inputs.imagenes_galeria.val() || '[]');
    seleccionadas = seleccionadas.filter(r => r !== ruta);
    inputs.imagenes_galeria.val(JSON.stringify(seleccionadas));
}

export function ManejarCambioImagenes(e) {
    const containers = GetEtiquetas("contenedores");
    const $galeriaItems = containers.preview.find('.border-warning');
    containers.preview.empty().append($galeriaItems);

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
                    containers.preview.append($col);
                }
                reader.readAsDataURL(file);
            }
        });
    }
}

export function CapaValidar() {
    if (typeof SistemaValidacion !== 'undefined') {
        const inputs = GetEtiquetas("inputs");
        const modales = GetEtiquetas("modales");
        const elementos = {
            titulo: inputs.titulo,
            subtitulo: inputs.subtitulo,
            contenido: inputs.contenido,
            tipo: inputs.tipo
        };
        SistemaValidacion.inicializar(elementos, (valido) => {
            modales.botonGuardar.prop('disabled', !valido);
        });
    }
}

