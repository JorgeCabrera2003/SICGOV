/**
 * Dependencias: jQuery, DataTables, SweetAlert2
 * @version 2.2.0 - Estandarizado con enviaAjax y DOM jQuery puros
 */
document.addEventListener('DOMContentLoaded', function () {
    const BitacoraModule = (function () {
        'use strict';

        let dataTable;

        async function initDataTable() {
            await reloadData();
        }

        async function reloadData() {
            const peticion = new FormData();
            peticion.append('action', 'listarJson');
            peticion.append('modulo', $('#filtro_modulo').val());
            peticion.append('desde', $('#fecha_desde').val());
            peticion.append('hasta', $('#fecha_hasta').val());

            try {
                const json = await enviaAjax(peticion, BASE_URL + '/?page=bitacora');
                let arreglo = [];
                if (json && json.data) {
                    arreglo = json.data;
                }
                renderTable(arreglo);
            } catch (e) {
                console.error("Error al cargar bitacora", e);
                renderTable([]);
            }
        }

        function renderTable(datos) {
            if ($.fn.DataTable.isDataTable('#tablaBitacora')) {
                $('#tablaBitacora').DataTable().destroy();
            }

            dataTable = $('#tablaBitacora').DataTable({
                data: datos,
                columns: [
                    { data: 'id', width: '5%', className: 'text-muted font-monospace small', visible: false },
                    { data: 'usuario', width: '15%' },
                    { data: 'modulo', width: '10%' },
                    { data: 'accion', width: '20%' },
                    { data: 'ip', width: '8%', className: 'font-monospace small' },
                    { 
                        data: 'fecha', width: '20%',
                        render: function (data) {
                            return formatearFechaSistema(data);
                        }
                    },
                    { data: 'acciones', orderable: false, searchable: false, className: 'text-center', width: '8%' }
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                order: [[5, 'desc']],
                pageLength: 25,
                responsive: true,
                autoWidth: false
            });
        }

        function bindEvents() {
            $('#btnActualizar').on('click', function (e) {
                e.preventDefault();
                reloadData();
            });

            $('#formFiltros').on('submit', function (e) {
                e.preventDefault();
                reloadData();
            });

            $('#btnLimpiar').on('click', function (e) {
                e.preventDefault();
                $('#formFiltros')[0].reset();
                reloadData();
            });

            $('#tablaBitacora').on('click', '.btn-ver-detalle', handleVerDetalle);
        }

        async function handleVerDetalle(e) {
            e.preventDefault();

            const id = $(this).data('id');
            const $modalBody = $('#detalleBody');

            $modalBody.empty().append(
                $('<div>', { class: 'text-center py-4' }).append(
                    $('<div>', { class: 'spinner-border text-warning', role: 'status' }).append(
                        $('<span>', { class: 'visually-hidden', text: 'Cargando...' })
                    )
                )
            );

            const fd = new FormData();
            fd.append('action', 'buscar');
            fd.append('id', id);

            try {
                const response = await enviaAjax(fd, BASE_URL + '/?page=bitacora');
                if (response && response.success && response.data) {
                    mostrarDetalles(response.data);
                } else {
                    mostrarErrorDetalle($modalBody, 'No se pudieron cargar los detalles');
                }
            } catch (error) {
                mostrarErrorDetalle($modalBody, 'Error de conexión');
            }
        }

        function mostrarErrorDetalle($modalBody, mensaje) {
            $modalBody.empty().append(
                $('<div>', { class: 'alert alert-danger' })
                    .append($('<i>', { class: 'fas fa-exclamation-triangle me-2' }))
                    .append(document.createTextNode(mensaje))
            );
        }

        function formatearFechaSistema(fechaRaw) {
            if (!fechaRaw) return "N/A";
            
            if (fechaRaw.includes('/')) {
                return fechaRaw; 
            }
            
            const date = new Date(fechaRaw);
            if (isNaN(date.getTime())) return fechaRaw;
            
            return date.toLocaleDateString('es-VE') + ', ' + 
                   date.toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }

        function renderAuditTable(jsonString, colorClass) {
            try {
                const data = JSON.parse(jsonString);
                if (!data || typeof data !== 'object') return document.createTextNode(jsonString);

                const labels = {
                    'id_noticia': 'Número de Noticia',
                    'titulo': 'Título',
                    'subtitulo': 'Subtítulo / Introducción',
                    'contenido': 'Contenido',
                    'tipo': 'Categoría',
                    'fecha_publicacion': 'Fecha de Publicación',
                    'cedula': 'Cédula del Autor',
                    'estatus': 'Estado en Sistema'
                };

                const $container = $('<div>', { class: `p-3 bg-body-tertiary border-start border-4 border-${colorClass} rounded shadow-sm` });
                
                for (const key in data) {
                    let label = labels[key] || key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' ');
                    label = label.replace(/ID/g, 'Número').replace(/id/g, 'Número');
                    
                    let value = data[key];

                    if (key.includes('fecha') || (typeof value === 'string' && /^\\d{4}-\\d{2}-\\d{2}/.test(value))) {
                        value = formatearFechaSistema(value);
                    }

                    const $item = $('<div>', { class: 'mb-2 border-bottom border-secondary border-opacity-10 pb-1' });
                    $item.append($('<label>', { class: 'd-block fw-bold small text-uppercase text-secondary', text: label }));
                    
                    if (value === null) {
                        $item.append($('<div>', { class: 'text-body' }).append($('<span>', { class: 'text-muted fst-italic', text: 'Vacio' })));
                    } else {
                        $item.append($('<div>', { class: 'text-body', text: value }));
                    }
                    
                    $container.append($item);
                }

                return $container;
            } catch (e) {
                return $('<pre>', { class: 'bg-dark text-warning p-2 small', text: jsonString });
            }
        }

        function mostrarDetalles(data) {
            const fecha = formatearFechaSistema(data.fecha);
            const $body = $('#detalleBody');
            $body.empty();

            $body.append(
                $('<div>', { class: 'mb-3' })
                    .append($('<label>', { class: 'fw-bold small text-uppercase text-muted', text: 'Número de Registro:' }))
                    .append($('<p>', { class: 'mb-2 p-2 bg-body-tertiary border rounded small font-monospace text-body', text: data.id_bitacora || '' }))
            );

            const $usuarioDiv = $('<div>', { class: 'd-flex align-items-center p-2 bg-body-tertiary border rounded' });
            $usuarioDiv.append($('<i>', { class: 'fas fa-user-circle fa-2x me-2 text-secondary opacity-50' }));
            const $usuarioInfo = $('<div>');
            
            const nombreMostrar = data.nombres ? data.nombres + ' ' + (data.apellidos || '') : (data.username || 'Sistema');
            $usuarioInfo.append(
                $('<p>', { class: 'mb-0 fw-semibold text-body' })
                    .append(document.createTextNode(nombreMostrar + " "))
                    .append($('<span>', { class: 'text-muted small', text: `(${data.rol || 'N/A'})` }))
            );
            if (data.cedula) {
                $usuarioInfo.append($('<small>', { class: 'text-muted', text: `Documento: ${data.cedula}` }));
            }
            $usuarioDiv.append($usuarioInfo);
            $body.append($('<div>', { class: 'mb-3' }).append($('<label>', { class: 'fw-bold small text-uppercase text-muted', text: 'Realizado por:' })).append($usuarioDiv));

            const $row1 = $('<div>', { class: 'row mb-3' });
            $row1.append($('<div>', { class: 'col-md-6' })
                .append($('<label>', { class: 'fw-bold small text-uppercase text-muted', text: 'Área del Sistema:' }))
                .append($('<p>', { class: 'mb-2 p-2 bg-body-tertiary border rounded text-body', text: data.modulo || '' }))
            );
            $row1.append($('<div>', { class: 'col-md-6' })
                .append($('<label>', { class: 'fw-bold small text-uppercase text-muted', text: 'Actividad Realizada:' }))
                .append($('<p>', { class: 'mb-2 p-2 bg-body-tertiary border rounded text-body', text: data.accion || '' }))
            );
            $body.append($row1);

            $body.append(
                $('<div>', { class: 'mb-3' })
                    .append($('<label>', { class: 'fw-bold small text-uppercase text-muted', text: 'Descripción de la Acción:' }))
                    .append($('<div>', { class: 'mb-2 p-3 bg-body-tertiary border rounded text-body shadow-sm', text: (data.detalle || 'Sin detalles adicionales').replace('(ID:', '(Número:') }))
            );

            const $row2 = $('<div>', { class: 'row mb-3' });
            $row2.append($('<div>', { class: 'col-md-6' })
                .append($('<label>', { class: 'fw-bold small text-uppercase text-muted', text: 'Dirección IP:' }))
                .append($('<p>', { class: 'mb-2 p-2 bg-body-tertiary border rounded font-monospace text-body', text: data.ip_address || '0.0.0.0' }))
            );
            $row2.append($('<div>', { class: 'col-md-6' })
                .append($('<label>', { class: 'fw-bold small text-uppercase text-muted', text: 'Fecha y Hora:' }))
                .append($('<p>', { class: 'mb-2 p-2 bg-body-tertiary border rounded text-body', text: fecha }))
            );
            $body.append($row2);

            if (data.valores_anteriores) {
                const $sectionAnt = $('<div>', { class: 'mb-4' });
                $sectionAnt.append(
                    $('<label>', { class: 'fw-bold small text-uppercase text-danger mb-2' })
                        .append($('<i>', { class: 'fas fa-history me-1' })).append(' Estado Anterior (Antes del cambio):')
                ).append(renderAuditTable(data.valores_anteriores, 'danger'));
                $body.append($sectionAnt);
            }

            if (data.valores_nuevos) {
                const $sectionNue = $('<div>', { class: 'mb-4' });
                $sectionNue.append(
                    $('<label>', { class: 'fw-bold small text-uppercase text-success mb-2' })
                        .append($('<i>', { class: 'fas fa-check-circle me-1' })).append(' Estado Nuevo (Después del cambio):')
                ).append(renderAuditTable(data.valores_nuevos, 'success'));
                $body.append($sectionNue);
            }
        }

        return {
            init: function () {
                if (typeof BASE_URL === 'undefined') {
                    console.error('BASE_URL no está definida');
                    return;
                }
                initDataTable();
                bindEvents();
            },
            recargar: function () {
                reloadData();
            }
        };
    })();

    $(document).ready(() => BitacoraModule.init());
});