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
                        width: '15%'
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
            $('#btnRefrescar').on('click', function (e) {
                e.preventDefault();
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
         * Renderiza los detalles en el modal
         */
        function mostrarDetalles(data) {
            const fecha = new Date(data.fecha).toLocaleString('es-VE', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            const html = `
            <div class="mb-3">
                <label class="fw-bold small text-uppercase text-muted">ID:</label>
                <p class="mb-2 p-2 bg-light rounded">${data.id_bitacora || ''}</p>
            </div>
            <div class="mb-3">
                <label class="fw-bold small text-uppercase text-muted">Usuario:</label>
                <p class="mb-2 p-2 bg-light rounded">${data.nombres ? data.nombres + ' ' + (data.apellidos || '') : (data.username || 'Sistema')}</p>
                ${data.cedula ? `<small class="text-muted d-block">Cédula: ${data.cedula}</small>` : ''}
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold small text-uppercase text-muted">Módulo:</label>
                    <p class="mb-2 p-2 bg-light rounded">${data.modulo || ''}</p>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold small text-uppercase text-muted">Acción:</label>
                    <p class="mb-2 p-2 bg-light rounded">${data.accion || ''}</p>
                </div>
            </div>
            <div class="mb-3">
                <label class="fw-bold small text-uppercase text-muted">Detalles:</label>
                <p class="mb-2 p-3 bg-light rounded">${data.detalles || 'Sin detalles adicionales'}</p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="fw-bold small text-uppercase text-muted">IP:</label>
                    <p class="mb-2 p-2 bg-light rounded font-monospace">${data.ip_address || '0.0.0.0'}</p>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold small text-uppercase text-muted">Fecha:</label>
                    <p class="mb-2 p-2 bg-light rounded">${fecha}</p>
                </div>
            </div>
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