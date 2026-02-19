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

<!-- Modales -->
<?php include 'modales/modal_producto.php'; ?>
<?php include 'modales/modal_categorias.php'; ?>

<!-- CSS y JS específicos de la página -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/productos.css">
<script src="<?php echo BASE_URL; ?>/assets/js/productos.js"></script>