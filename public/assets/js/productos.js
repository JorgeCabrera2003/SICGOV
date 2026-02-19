/**
 * Módulo de Productos - Good Vibes
 * Dependencias: jQuery, DataTables, SweetAlert2
 */

var ProductosModule = (function () {
    // Variables privadas
    var table;

    // Inicialización
    function init() {
        console.log('Inicializando módulo de productos');
        initDataTable();
        bindEvents();
        cargarCategorias();
    }

    // Inicializar DataTable
    function initDataTable() {
        table = $('#tablaProductos').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: BASE_URL + '/?page=productos&action=listarJson',
                type: 'GET'
            },
            columns: [
                {data: 'id'},
                {data: 'imagen'},
                {data: 'nombre'},
                {data: 'categoria'},
                {data: 'precio'},
                {data: 'stock'},
                {data: 'stock_minimo'},
                {data: 'estatus'},
                {data: 'acciones', orderable: false}
            ],
            language: {
                url: BASE_URL + '/assets/DataTables/espanol.json'
            },
            order: [[0, 'desc']],
            pageLength: 10,
            responsive: true
        });
    }

    // Bind de eventos
    function bindEvents() {
        // Nuevo producto
        $('#btnNuevoProducto').on('click', nuevoProducto);

        // Editar producto (delegación)
        $('#tablaProductos').on('click', '.btn-editar', editarProducto);

        // Eliminar producto (delegación)
        $('#tablaProductos').on('click', '.btn-eliminar', eliminarProducto);

        // Guardar producto
        $('#formProducto').on('submit', guardarProducto);

        // Vista previa de imagen
        $('#imagen').on('change', previewImagen);

        // Modal categorías
        $('#btnCategorias').on('click', function () {
            $('#modalCategorias').modal('show');
        });

        // Guardar categoría
        $('#btnGuardarCategoria').on('click', guardarCategoria);

        // Eliminar categoría (delegación)
        $('#tablaCategorias').on('click', '.btn-eliminar-categoria', eliminarCategoria);
    }

    // ===== FUNCIONES DE PRODUCTOS =====

    function nuevoProducto() {
        $('#formProducto')[0].reset();
        $('#id_producto').val('');
        $('#modalTitleText').text('Nuevo Producto');
        $('#previewImagenContainer').hide();
        $('#estatus').prop('checked', true);
        $('#modalProducto').modal('show');
    }

    function editarProducto() {
        var id = $(this).data('id');

        $.ajax({
            url: BASE_URL + '/?page=productos&action=buscar',
            type: 'GET',
            data: {id: id},
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    var p = response.data;
                    $('#id_producto').val(p.id_producto);
                    $('#nombre').val(p.nombre_producto);
                    $('#descripcion').val(p.descripcion);
                    $('#precio').val(p.precio);
                    $('#costo').val(p.costo_preparacion);
                    $('#stock').val(p.stock);
                    $('#stock_minimo').val(p.stock_minimo);
                    $('#id_categoria').val(p.id_categoria);
                    $('#estatus').prop('checked', p.estatus == 1);

                    if (p.imagen && p.imagen != 'default-product.png') {
                        $('#previewImagen').attr('src', BASE_URL + '/assets/img/productos/' + p.imagen);
                        $('#previewImagenContainer').show();
                    } else {
                        $('#previewImagenContainer').hide();
                    }

                    $('#modalTitleText').text('Editar Producto');
                    $('#modalProducto').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    }

    function eliminarProducto() {
        var id = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FFD600',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/?page=productos&action=eliminar',
                    type: 'POST',
                    data: {id: id},
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Eliminado', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    function guardarProducto(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: BASE_URL + '/?page=productos&action=guardar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                $('#btnGuardarProducto').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success');
                    $('#modalProducto').modal('hide');
                    table.ajax.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Error en el servidor', 'error');
            },
            complete: function () {
                $('#btnGuardarProducto').prop('disabled', false)
                    .html('<i class="fas fa-save me-2"></i>Guardar Producto');
            }
        });
    }

    function previewImagen() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#previewImagen').attr('src', e.target.result);
                $('#previewImagenContainer').show();
            };
            reader.readAsDataURL(file);
        }
    }

    // ===== FUNCIONES DE CATEGORÍAS =====

    function guardarCategoria() {
        var nombre = $('#nuevaCategoria').val().trim();
        if (nombre === '') {
            Swal.fire('Atención', 'Ingrese un nombre para la categoría', 'warning');
            return;
        }

        $.ajax({
            url: BASE_URL + '/?page=categorias&action=guardar',
            type: 'POST',
            data: {nombre: nombre},
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#nuevaCategoria').val('');
                    Swal.fire('Éxito', 'Categoría guardada', 'success');
                    cargarCategorias();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    }

    function eliminarCategoria() {
        var id = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar categoría?',
            text: "Los productos asociados quedarán sin categoría",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FFD600',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/?page=categorias&action=eliminar',
                    type: 'POST',
                    data: {id: id},
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $(this).closest('tr').remove();
                            cargarCategorias();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    }

    // ===== FUNCIONES COMUNES =====

    function cargarCategorias() {
        $.ajax({
            url: BASE_URL + '/?page=categorias&action=listar',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                // Actualizar select
                var select = $('#id_categoria');
                select.empty().append('<option value="" hidden>Seleccionar categoría</option>');

                response.forEach(function (cat) {
                    select.append('<option value="' + cat.id_categoria + '">' + cat.nombre + '</option>');
                });

                // Actualizar tabla de categorías
                var tbody = $('#tablaCategorias tbody');
                tbody.empty();
                response.forEach(function (cat) {
                    tbody.append('<tr data-id="' + cat.id_categoria + '">' +
                        '<td>' + cat.nombre + '</td>' +
                        '<td><button class="btn btn-sm btn-danger btn-eliminar-categoria" data-id="' + cat.id_categoria + '">' +
                        '<i class="fas fa-trash"></i></button></td>' +
                        '</tr>');
                });
            }
        });
    }

    // API pública
    return {
        init: init,
        recargarTabla: function () {if (table) table.ajax.reload();}
    };
})();

// Inicializar cuando el DOM esté listo
$(document).ready(function () {
    ProductosModule.init();
});