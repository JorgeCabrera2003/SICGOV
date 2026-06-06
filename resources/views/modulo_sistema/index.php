<!-- ==========================================
    MÓDULO DE M{ODULO SISTEMA - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-box me-2 text-warning"></i>
            Módulos del Sistema
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de ingrediente">
            <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoProveedor">
                <i class="fas fa-plus me-2"></i>Nuevo Proveedor
            </button>
        </div>
    </header>

    <!-- Tabla de productos (section semántica) -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaModuloSistema" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nombre del Módulo</th>
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


<!-- Recursos específicos de la página -->
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/ProveedorController.js" defer></script>