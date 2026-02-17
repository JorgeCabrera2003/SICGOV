<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-box me-2"></i>
                        Gestión de Productos
                    </h6>
                    <div>
                        <button class="btn btn-primary btn-sm" id="btnNuevoProducto">
                            <i class="fas fa-plus me-2"></i>Nuevo Producto
                        </button>
                        <button class="btn btn-outline-primary btn-sm" id="btnCategorias">
                            <i class="fas fa-tags me-2"></i>Categorías
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaProductos" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Imagen</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Stock Mín.</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables carga los datos aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductoLabel">
                    <i class="fas fa-box me-2"></i>
                    <span id="modalTitleText">Nuevo Producto</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formProducto" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_producto" id="id_producto">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="id_categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="id_categoria" name="id_categoria">
                                    <option value="" hidden>Seleccionar categoría</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id_categoria'] ?>"><?= $cat['nombre_categoria'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" maxlength="255"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio Venta ($) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="costo" class="form-label">Costo ($)</label>
                                <input type="number" class="form-control" id="costo" name="costo" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="stock_minimo" class="form-label">Stock Mín.</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" min="0" value="5">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="imagen" class="form-label">Imagen del Producto</label>
                                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                                <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Max 2MB</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="previewImagenContainer" style="display: none;">
                                <label class="form-label">Vista Previa:</label>
                                <div>
                                    <img id="previewImagen" src="#" alt="Vista previa" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="estatus" name="estatus" checked value="1">
                                <label class="form-check-label" for="estatus">Producto Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarProducto">
                        <i class="fas fa-save me-2"></i>Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Categorías -->
<div class="modal fade" id="modalCategorias" tabindex="-1" aria-labelledby="modalCategoriasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCategoriasLabel">
                    <i class="fas fa-tags me-2"></i>
                    Categorías de Productos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="nuevaCategoria" placeholder="Nueva categoría">
                        <button class="btn btn-primary" type="button" id="btnGuardarCategoria">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm" id="tablaCategorias">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th width="100">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                                <tr data-id="<?= $cat['id_categoria'] ?>">
                                    <td><?= $cat['nombre_categoria'] ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-danger btn-eliminar-categoria" data-id="<?= $cat['id_categoria'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#tablaProductos').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '<?php echo BASE_URL; ?>/?page=productos&action=listarJson',
                type: 'GET'
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'imagen'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'categoria'
                },
                {
                    data: 'precio'
                },
                {
                    data: 'stock'
                },
                {
                    data: 'stock_minimo'
                },
                {
                    data: 'estatus'
                },
                {
                    data: 'acciones',
                    orderable: false
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            responsive: true
        });

        $('#btnNuevoProducto').click(function() {
            $('#formProducto')[0].reset();
            $('#id_producto').val('');
            $('#modalTitleText').text('Nuevo Producto');
            $('#previewImagenContainer').hide();
            $('#estatus').prop('checked', true);
            $('#modalProducto').modal('show');
        });

        $('#tablaProductos').on('click', '.btn-editar', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '<?php echo BASE_URL; ?>/?page=productos&action=buscar',
                type: 'GET',
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var p = response.data;
                        $('#id_producto').val(p.id_producto);
                        $('#nombre').val(p.nombre);
                        $('#descripcion').val(p.descripcion);
                        $('#precio').val(p.precio);
                        $('#costo').val(p.costo);
                        $('#stock').val(p.stock);
                        $('#stock_minimo').val(p.stock_minimo);
                        $('#id_categoria').val(p.id_categoria);
                        $('#estatus').prop('checked', p.estatus == 1);

                        if (p.imagen && p.imagen != 'default-product.png') {
                            $('#previewImagen').attr('src', '<?php echo BASE_URL; ?>/assets/img/productos/' + p.imagen);
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
        });

        $('#tablaProductos').on('click', '.btn-eliminar', function() {
            var id = $(this).data('id');
            var row = $(this).closest('tr');

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
                        url: '<?php echo BASE_URL; ?>/?page=productos&action=eliminar',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
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
        });

        // Guardar producto (nuevo/editar)
        $('#formProducto').submit(function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: '<?php echo BASE_URL; ?>/?page=productos&action=guardar',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#btnGuardarProducto').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success');
                        $('#modalProducto').modal('hide');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error en el servidor', 'error');
                },
                complete: function() {
                    $('#btnGuardarProducto').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Guardar Producto');
                }
            });
        });

        // Vista previa de imagen
        $('#imagen').change(function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImagen').attr('src', e.target.result);
                    $('#previewImagenContainer').show();
                };
                reader.readAsDataURL(file);
            }
        });

        // Modal categorías
        $('#btnCategorias').click(function() {
            $('#modalCategorias').modal('show');
        });

        // Guardar nueva categoría
        $('#btnGuardarCategoria').click(function() {
            var nombre = $('#nuevaCategoria').val().trim();
            if (nombre === '') {
                Swal.fire('Atención', 'Ingrese un nombre para la categoría', 'warning');
                return;
            }

            $.ajax({
                url: '<?php echo BASE_URL; ?>/?page=categorias&action=guardar',
                type: 'POST',
                data: {
                    nombre: nombre
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#nuevaCategoria').val('');
                        Swal.fire('Éxito', 'Categoría guardada', 'success');
                        cargarCategorias();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        });

        // Eliminar categoría
        $('#tablaCategorias').on('click', '.btn-eliminar-categoria', function() {
            var id = $(this).data('id');
            var row = $(this).closest('tr');

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
                        url: '<?php echo BASE_URL; ?>/?page=categorias&action=eliminar',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                row.remove();
                                cargarCategorias();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        // Función para recargar categorías en el select
        function cargarCategorias() {
            $.ajax({
                url: '<?php echo BASE_URL; ?>/?page=categorias&action=listar',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var select = $('#id_categoria');
                    select.empty().append('<option value="">Seleccionar categoría</option>');

                    response.forEach(function(cat) {
                        select.append('<option value="' + cat.id_categoria + '">' + cat.nombre + '</option>');
                    });

                    var tbody = $('#tablaCategorias tbody');
                    tbody.empty();
                    response.forEach(function(cat) {
                        tbody.append('<tr data-id="' + cat.id_categoria + '">' +
                            '<td>' + cat.nombre + '</td>' +
                            '<td><button class="btn btn-sm btn-danger btn-eliminar-categoria" data-id="' + cat.id_categoria + '"><i class="fas fa-trash"></i></button></td>' +
                            '</tr>');
                    });
                }
            });
        }

        // Inicializar
        cargarCategorias();
    });
</script>

<style>
    .btn-primary {
        background-color: #FFD600;
        border-color: #FFD600;
        color: #1A1C20;
        font-weight: 600;
    }

    .btn-primary:hover {
        background-color: #E6C000;
        border-color: #E6C000;
        color: #1A1C20;
    }

    .btn-outline-primary {
        color: #1A1C20;
        border-color: #FFD600;
    }

    .btn-outline-primary:hover {
        background-color: #FFD600;
        color: #1A1C20;
    }

    .table th {
        background-color: #F4F7F6;
        color: #1A1C20;
        font-weight: 600;
        border-bottom: 2px solid #FFD600;
    }

    .badge.bg-success {
        background-color: #28a745 !important;
    }

    .badge.bg-danger {
        background-color: #dc3545 !important;
    }

    .modal-header {
        background-color: #F4F7F6;
        border-bottom: 1px solid #FFD600;
    }

    .modal-title i {
        color: #FFD600;
    }

    .form-check-input:checked {
        background-color: #FFD600;
        border-color: #FFD600;
    }
</style>