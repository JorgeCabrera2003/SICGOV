<!-- ==========================================
    MÓDULO DE INGREDIENTES - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-box me-2 text-warning"></i>
            Gestión de Ingredientes
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de ingrediente">
            <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoIngrediente">
                <i class="fas fa-plus me-2"></i>Nuevo Ingrediente
            </button>
            <button class="btn btn-outline-warning" id="btn-ModalCategorias">
                <i class="fas fa-tags me-2"></i>Categorías
            </button>
        </div>
    </header>

    <!-- Tabla de productos (section semántica) -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaIngrediente" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Precio Unitario</th>
                            <th scope="col">Stock Actual</th>
                            <th scope="col">Stock Mínimo / Stock Máximo</th>
                            <th scope="col"></th>
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
<?php
include_once 'partials/_modal_ingrediente.php';
include_once 'partials/_modal_categoria_ingrediente.php';
include_once 'partials/_modal_categoria_ingrediente_form.php';
?>

<!-- Recursos específicos de la página -->
<script src="<?= BASE_URL ?>/assets/js/ingrediente.js" defer></script>