/**
 * MÓDULO DE PRODUCTOS - GOOD VIBES
 * 
 * Encapsula toda la funcionalidad del CRUD de productos y categorías
 * usando el patrón módulo para evitar contaminar el ámbito global.
 * 
 * Dependencias: jQuery, DataTables, SweetAlert2
 * @version 1.0.0
 */

const ProductosModule = (function() {
    'use strict';

    // ==========================================
    // VARIABLES PRIVADAS
    // ==========================================
    
    /** @type {DataTable} Instancia de DataTable para productos */
    let dataTable;

    // ==========================================
    // MÉTODOS PRIVADOS
    // ==========================================

    /**
     * Inicializa la tabla de productos con DataTables
     * @private
     */
    function initDataTable() {
        dataTable = $('#tablaProductos').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: `${BASE_URL}/?page=productos&action=listarJson`,
                type: 'GET'
            },
            columns: [
                { data: 'id' },
                { 
                    data: 'imagen',
                    orderable: false,
                    searchable: false
                },
                { data: 'nombre' },
                { data: 'categoria' },
                { 
                    data: 'precio',
                    className: 'text-end fw-semibold'
                },
                { 
                    data: 'stock',
                    className: 'text-center'
                },
                { 
                    data: 'stock_minimo',
                    className: 'text-center'
                },
                { 
                    data: 'estatus',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                { 
                    data: 'acciones',
                    orderable: false,
                    searchable: false,
                    className: 'text-end'
                }
            ],
            language: {
                url: `${BASE_URL}/assets/DataTables/espanol.json`
            },
            order: [[0, 'desc']],
            pageLength: 10,
            responsive: true,
            autoWidth: false
        });
    }

    /**
     * Registra todos los event listeners
     * @private
     */
    function bindEvents() {
        // Eventos de productos
        $('#btnNuevoProducto').on('click', handleNuevoProducto);
        $('#tablaProductos').on('click', '.btn-editar', handleEditarProducto);
        $('#tablaProductos').on('click', '.btn-eliminar', handleEliminarProducto);
        $('#formProducto').on('submit', handleGuardarProducto);
        $('#imagen').on('change', handlePreviewImagen);

        // Eventos de categorías
        $('#btnCategorias').on('click', () => $('#modalCategorias').modal('show'));
        $('#btnGuardarCategoria').on('click', handleGuardarCategoria);
        $('#tablaCategorias').on('click', '.btn-eliminar-categoria', handleEliminarCategoria);
    }

    /**
     * Carga las categorías vía AJAX y actualiza la tabla
     * @private
     */
    function cargarCategorias() {
        $.ajax({
            url: `${BASE_URL}/?page=categorias&action=listar`,
            type: 'GET',
            dataType: 'json'
        })
        .done(function(response) {
            if (!Array.isArray(response)) return;

            const $select = $('#id_categoria');
            const $tbody = $('#tablaCategorias tbody');

            // Limpiar contenedores
            $select.empty().append('<option value="" selected disabled>Seleccionar</option>');
            $tbody.empty();

            if (response.length === 0) {
                $tbody.append(`
                    <tr>
                        <td colspan="2" class="text-center text-muted py-3">
                            No hay categorías disponibles
                        </td>
                    </tr>
                `);
                return;
            }

            // Poblar select y tabla
            response.forEach(cat => {
                $select.append(`
                    <option value="${escapeHtml(cat.id_categoria)}">
                        ${escapeHtml(cat.nombre_categoria)}
                    </option>
                `);

                $tbody.append(`
                    <tr data-id="${escapeHtml(cat.id_categoria)}">
                        <td>${escapeHtml(cat.nombre_categoria)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger border-0 btn-eliminar-categoria" 
                                    data-id="${escapeHtml(cat.id_categoria)}"
                                    title="Eliminar categoría">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        })
        .fail(function(jqXHR) {
            console.error('Error al cargar categorías:', jqXHR.responseText);
        });
    }

    /**
     * Escapa caracteres HTML para prevenir XSS
     * @param {string} text - Texto a escapar
     * @returns {string} Texto escapado
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==========================================
    // MANEJADORES DE EVENTOS
    // ==========================================

    /**
     * Muestra el modal para crear un nuevo producto
     * @param {Event} e - Evento click
     */
    function handleNuevoProducto(e) {
        e.preventDefault();
        
        $('#formProducto')[0].reset();
        $('#id_producto').val('');
        $('#modalTitleText').text('Nuevo Producto');
        $('#previewImagenContainer').hide();
        $('#estatus').prop('checked', true);
        $('#modalProducto').modal('show');
    }

    /**
     * Carga los datos de un producto y muestra el modal para editarlo
     * @param {Event} e - Evento click
     */
    function handleEditarProducto(e) {
        e.preventDefault();
        
        const id = $(this).data('id');

        $.ajax({
            url: `${BASE_URL}/?page=productos&action=buscar`,
            type: 'GET',
            data: { id },
            dataType: 'json'
        })
        .done(function(response) {
            if (!response.success) {
                Swal.fire('Error', response.message, 'error');
                return;
            }

            const p = response.data;
            
            $('#id_producto').val(p.id_producto);
            $('#nombre').val(p.nombre_producto);
            $('#descripcion').val(p.descripcion);
            $('#precio').val(p.precio);
            $('#costo').val(p.costo_preparacion);
            $('#stock').val(p.stock);
            $('#stock_minimo').val(p.stock_minimo);
            $('#id_categoria').val(p.id_categoria);
            $('#estatus').prop('checked', p.estatus == 1);

            if (p.imagen && p.imagen !== 'default-product.png') {
                $('#previewImagen').attr('src', `${BASE_URL}/assets/img/productos/${p.imagen}`);
                $('#previewImagenContainer').show();
            } else {
                $('#previewImagenContainer').hide();
            }

            $('#modalTitleText').text('Editar Producto');
            $('#modalProducto').modal('show');
        })
        .fail(function() {
            Swal.fire('Error', 'Error de conexión', 'error');
        });
    }

    /**
     * Elimina un producto (soft delete)
     * @param {Event} e - Evento click
     */
    function handleEliminarProducto(e) {
        e.preventDefault();
        
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede revertir',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FFD600',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `${BASE_URL}/?page=productos&action=eliminar`,
                type: 'POST',
                data: { id },
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire('Eliminado', response.message, 'success');
                    dataTable.ajax.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            })
            .fail(function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        });
    }

    /**
     * Guarda un producto (crear o actualizar)
     * @param {Event} e - Evento submit
     */
    function handleGuardarProducto(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const $btn = $('#btnGuardarProducto');

        $.ajax({
            url: `${BASE_URL}/?page=productos&action=guardar`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $btn.prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Guardando...
                `);
            }
        })
        .done(function(response) {
            if (response.success) {
                Swal.fire('Éxito', response.message, 'success');
                $('#modalProducto').modal('hide');
                dataTable.ajax.reload();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        })
        .fail(function() {
            Swal.fire('Error', 'Error de conexión', 'error');
        })
        .always(function() {
            $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Guardar Producto');
        });
    }

    /**
     * Muestra vista previa de la imagen seleccionada
     * @param {Event} e - Evento change
     */
    function handlePreviewImagen(e) {
        const file = e.target.files[0];
        
        if (!file) {
            $('#previewImagenContainer').hide();
            return;
        }

        const reader = new FileReader();
        reader.onload = function(ev) {
            $('#previewImagen').attr('src', ev.target.result);
            $('#previewImagenContainer').show();
        };
        reader.readAsDataURL(file);
    }

    /**
     * Guarda una nueva categoría
     */
    function handleGuardarCategoria() {
        const nombre = $('#nuevaCategoria').val().trim();

        if (!nombre) {
            Swal.fire('Atención', 'Ingrese un nombre para la categoría', 'warning');
            return;
        }

        $.ajax({
            url: `${BASE_URL}/?page=categorias&action=guardar`,
            type: 'POST',
            data: { nombre },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                $('#nuevaCategoria').val('');
                Swal.fire('Éxito', 'Categoría guardada', 'success');
                cargarCategorias();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        })
        .fail(function() {
            Swal.fire('Error', 'Error de conexión', 'error');
        });
    }

    /**
     * Elimina una categoría
     * @param {Event} e - Evento click
     */
    function handleEliminarCategoria(e) {
        e.preventDefault();
        
        const id = $(this).data('id');
        const $row = $(this).closest('tr');

        Swal.fire({
            title: '¿Eliminar categoría?',
            text: 'Los productos asociados quedarán sin categoría',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FFD600',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `${BASE_URL}/?page=categorias&action=eliminar`,
                type: 'POST',
                data: { id },
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    $row.remove();
                    cargarCategorias();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            })
            .fail(function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        });
    }

    // ==========================================
    // API PÚBLICA
    // ==========================================

    return {
        /**
         * Inicializa el módulo de productos
         * @public
         */
        init: function() {
            console.log('📦 Inicializando módulo de productos');
            
            if (typeof BASE_URL === 'undefined') {
                console.error('BASE_URL no está definida');
                return;
            }

            initDataTable();
            bindEvents();
            cargarCategorias();
        },

        /**
         * Recarga la tabla de productos
         * @public
         */
        recargarTabla: function() {
            if (dataTable) dataTable.ajax.reload();
        }
    };
})();

// Inicialización automática cuando el DOM está listo
$(document).ready(() => ProductosModule.init());