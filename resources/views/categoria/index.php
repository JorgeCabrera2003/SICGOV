<!-- ==========================================
    MÓDULO DE CATEGORÍAS DE MENÚ - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-tags me-2 text-primary"></i>
            Gestión de Categorías del Menú
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de categoría">
            <button class="btn btn-primary fw-semibold" id="btnNuevaCategoria">
                <i class="fas fa-plus me-2"></i>Nueva Categoría
            </button>
        </div>
    </header>

    <!-- Tabla de categorías (section semántica) -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaCategoria" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col" class="text-end">Acciones</th>
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

<!-- Modales -->
<?php include 'partials/_modal_categoria.php'; ?>

<!-- Recursos específicos de la página -->

