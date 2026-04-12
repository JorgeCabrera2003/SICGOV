/**
 * Dependencias: jQuery, DataTables, SweetAlert2
 * @version 2.1.0
 */
document.addEventListener('DOMContentLoaded', function () {
    const BitacoraModule = (function () {
        'use strict';

        let dataTable;
        function initDataTable() {
            dataTable = $('#tablaBitacora').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: `${BASE_URL}/?page=bitacora&action=listarJson`,
                    type: 'GET',
                    data: function (d) {
                        d.modulo = $('#filtro_modulo').val();
                        d.desde = $('#fecha_desde').val();
                        d.hasta = $('#fecha_hasta').val();
                    },
                    dataSrc: function (json) {
                        return json.data || [];
                    }
                },
                columns: [
                    {
                        data: 'id',
                        width: '5%',
                        className: 'text-muted font-monospace small',
                        visible: false
                    },
                    {
                        data: 'usuario',
                        width: '15%'
                    },
                    {
                        data: 'modulo',
                        width: '10%'
                    },
                    {
                        data: 'accion',
                        width: '20%'
                    },
                    {
                        data: 'ip',
                        width: '8%',
                        className: 'font-monospace small'
                    },
                    {
                        data: 'fecha',
                        width: '20%',
                        render: function (data) {
                            return formatearFechaSistema(data);
                        }
                    },
                    {
                        data: 'acciones',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '8%'
                    }
                ],
                language: {
                    url: `${BASE_URL}/assets/DataTables/espanol.json`
                },
                order: [[5, 'desc']],
                pageLength: 25,
                responsive: true,
                autoWidth: false,
                deferRender: true
            });
        }

        function bindEvents() {
            $('#btnActualizar').on('click', function (e) {
                e.preventDefault();
                dataTable.ajax.reload();
            });

            $('#formFiltros').on('submit', function (e) {
                e.preventDefault();
                dataTable.ajax.reload();
            });

            $('#btnLimpiar').on('click', function (e) {
                e.preventDefault();
                $('#formFiltros')[0].reset();
                dataTable.ajax.reload();
            });

            $('#tablaBitacora').on('click', '.btn-ver-detalle', handleVerDetalle);
        }
        function handleVerDetalle(e) {
            e.preventDefault();

            const id = $(this).data('id');
            const $modalBody = $('#detalleBody');

            $modalBody.html(`
            <div class="text-center py-4">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `);

            $.ajax({
                url: `${BASE_URL}/?page=bitacora&action=buscar`,
                type: 'GET',
                data: {id},
                dataType: 'json'
            })
                .done(function (response) {
                    if (response.success && response.data) {
                        mostrarDetalles(response.data);
                    } else {
                        $modalBody.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No se pudieron cargar los detalles
                    </div>
                `);
                    }
                })
                .fail(function () {
                    $modalBody.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error de conexión
                </div>
            `);
                });
        }

        /**
         * Formatea una fecha al estándar del sistema (DD/MM/YYYY, HH:MM:SS AM/PM)
         */
        function formatearFechaSistema(fechaRaw) {
            if (!fechaRaw) return "N/A";
            const date = new Date(fechaRaw);
            if (isNaN(date.getTime())) return fechaRaw; // Fallback si no es fecha
            
            return date.toLocaleDateString('es-VE') + ', ' + 
                   date.toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }

        /**
         * Transforma un objeto JSON en una estructura visual amigable (Lista de campos)
         */
        function renderAuditTable(jsonString, colorClass) {
            try {
                const data = JSON.parse(jsonString);
                if (!data || typeof data !== 'object') return jsonString;

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

                let html = `<div class="p-3 bg-dark bg-opacity-10 border-start border-4 border-${colorClass} rounded shadow-sm">`;
                
                for (const key in data) {
                    const label = labels[key] || key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' ');
                    let value = data[key];

                    // Formatear fechas dentro de los valores
                    if (key.includes('fecha') || (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value))) {
                        value = formatearFechaSistema(value);
                    }

                    if (value === null) value = '<span class="text-muted italic">Vacio</span>';

                    html += `
                        <div class="mb-2 border-bottom border-secondary border-opacity-10 pb-1">
                            <label class="d-block fw-bold small text-uppercase text-secondary">${label}</label>
                            <div class="text-body">${value}</div>
                        </div>
                    `;
                }

                html += `</div>`;
                return html;
            } catch (e) {
                return `<pre class="bg-dark text-warning p-2 small">${jsonString}</pre>`;
            }
        }

        /**
         * Renderiza los detalles en el modal
         */
        function mostrarDetalles(data) {
            const fecha = formatearFechaSistema(data.fecha);

            const html = `
            <div class="mb-3">
                <label class="fw-bold small text-uppercase text-muted">Número de Registro:</label>
                <p class="mb-2 p-2 bg-body-tertiary border rounded small font-monospace text-body">${data.id_bitacora || ''}</p>
            </div>
            <div class="mb-3">
                <label class="fw-bold small text-uppercase text-muted">Realizado por:</label>
                <div class="d-flex align-items-center p-2 bg-body-tertiary border rounded">
                    <i class="fas fa-user-circle fa-2x me-2 text-secondary opacity-50"></i>
                    <div>
                        <p class="mb-0 fw-semibold text-body">
                            ${data.nombres ? data.nombres + ' ' + (data.apellidos || '') : (data.username || 'Sistema')}
                            <span class="text-muted small">(${data.rol || 'N/A'})</span>
                        </p>
                        ${data.cedula ? `<small class="text-muted">Documento: ${data.cedula}</small>` : ''}
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold small text-uppercase text-muted">Área del Sistema:</label>
                    <p class="mb-2 p-2 bg-body-tertiary border rounded text-body">${data.modulo || ''}</p>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold small text-uppercase text-muted">Actividad Realizada:</label>
                    <p class="mb-2 p-2 bg-body-tertiary border rounded text-body">${data.accion || ''}</p>
                </div>
            </div>
            <div class="mb-3">
                <label class="fw-bold small text-uppercase text-muted">Descripción de la Acción:</label>
                <div class="mb-2 p-3 bg-body-tertiary border rounded text-body shadow-sm">${(data.detalle || 'Sin detalles adicionales').replace('(ID:', '(Número:')}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold small text-uppercase text-muted">Dirección IP:</label>
                    <p class="mb-2 p-2 bg-body-tertiary border rounded font-monospace text-body">${data.ip_address || '0.0.0.0'}</p>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold small text-uppercase text-muted">Fecha y Hora:</label>
                    <p class="mb-2 p-2 bg-body-tertiary border rounded text-body">${fecha}</p>
                </div>
            </div>
            
            ${data.valores_anteriores ? `
            <div class="mb-4">
                <label class="fw-bold small text-uppercase text-danger mb-2"><i class="fas fa-history me-1"></i> Estado Anterior (Antes del cambio):</label>
                ${renderAuditTable(data.valores_anteriores, 'danger')}
            </div>` : ''}
            
            ${data.valores_nuevos ? `
            <div class="mb-4">
                <label class="fw-bold small text-uppercase text-success mb-2"><i class="fas fa-check-circle me-1"></i> Estado Nuevo (Después del cambio):</label>
                ${renderAuditTable(data.valores_nuevos, 'success')}
            </div>` : ''}
        `;

            $('#detalleBody').html(html);
        }

        return {
            init: function () {
                console.log('Inicializando módulo de bitácora');

                if (typeof BASE_URL === 'undefined') {
                    console.error('BASE_URL no está definida');
                    return;
                }

                initDataTable();
                bindEvents();
            },

            recargar: function () {
                if (dataTable) dataTable.ajax.reload();
            }
        };
    })();

    // Inicialización automática
    $(document).ready(() => BitacoraModule.init());

});