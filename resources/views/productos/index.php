<!-- ==========================================
    MÓDULO DE PRODUCTOS - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-box me-2 text-warning"></i>
            Gestión de Productos
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de productos">
            <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoProducto">
                <i class="fas fa-plus me-2"></i>Nuevo Producto
            </button>
            <button class="btn btn-outline-warning" id="btnCategorias">
                <i class="fas fa-tags me-2"></i>Categorías
            </button>
        </div>
    </header>

    <!-- Tabla de productos (section semántica) -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaProductos" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Imagen</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Categoría</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Stock Mín.</th>
                            <th scope="col">Estatus</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables carga los datos aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<!-- Modales (incluidos como partials) -->
<?php include 'partials/_modal_producto.php'; ?>
<?php include 'partials/_modal_categorias.php'; ?>

<!-- Recursos específicos de la página -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/productos.css">
<script src="<?= BASE_URL ?>/assets/js/productos.js" defer></script>