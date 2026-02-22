/**
 * MÓDULO DE BITACORA - GOOD VIBES
 * 
 * Muestra el historial de actividades del sistema
 * 
 * Dependencias: jQuery, DataTables, SweetAlert2
 * @version 1.0.1
 */

const BitacoraModule = (function() {
    'use strict';

    let dataTable;

    function initDataTable() {
        dataTable = $('#tablaBitacora').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: `${BASE_URL}/?page=bitacora&action=listarJson`,
                type: 'GET',
                dataSrc: function(json) {
                    console.log('Datos recibidos:', json);
                    return json.data || [];
                }
            },
            columns: [
                { 
                    data: 'id',
                    width: '5%'
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
                    width: '8%'
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
            order: [[5, 'asc']],
            pageLength: 25,
            responsive: true,
            autoWidth: false,
            deferRender: true
        });
    }

    function bindEvents() {
        $('#btnRefrescar').on('click', function(e) {
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
            <div class="text-center py-3">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `);

        $.ajax({
            url: `${BASE_URL}/?page=bitacora&action=buscar`,
            type: 'GET',
            data: { id },
            dataType: 'json'
        })
        .done(function(response) {
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
        .fail(function() {
            $modalBody.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error de conexión
                </div>
            `);
        });
    }

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
                <label class="fw-bold">ID:</label>
                <p class="mb-2">${data.id_bitacora || ''}</p>
            </div>
            <div class="mb-3">
                <label class="fw-bold">Usuario:</label>
                <p class="mb-2">${data.nombres ? data.nombres + ' ' + (data.apellidos || '') : (data.username || 'Sistema')}</p>
                ${data.cedula ? `<small class="text-muted">Cédula: ${data.cedula}</small>` : ''}
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Módulo:</label>
                    <p class="mb-2">${data.modulo || ''}</p>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Acción:</label>
                    <p class="mb-2">${data.accion || ''}</p>
                </div>
            </div>
            <div class="mb-3">
                <label class="fw-bold">Detalles:</label>
                <p class="mb-2 bg-light p-2 rounded">${data.detalles || 'Sin detalles adicionales'}</p>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="fw-bold">IP:</label>
                    <p class="mb-2">${data.ip_address || '0.0.0.0'}</p>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Fecha:</label>
                    <p class="mb-2">${fecha}</p>
                </div>
            </div>
        `;
        
        $('#detalleBody').html(html);
    }


    return {

        init: function() {
            console.log('📋 Inicializando módulo de bitácora');
            
            if (typeof BASE_URL === 'undefined') {
                console.error('BASE_URL no está definida');
                return;
            }

            initDataTable();
            bindEvents();
        },
        recargar: function() {
            if (dataTable) dataTable.ajax.reload();
        }
    };
})();

$(document).ready(() => BitacoraModule.init());