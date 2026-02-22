<main class="container-fluid py-4">
    <!-- Encabezado -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-history me-2 text-warning"></i>
            Bitácora del Sistema
        </h1>
        <div>
            <button class="btn btn-outline-secondary" id="btnRefrescar">
                <i class="fas fa-sync-alt me-2"></i>Refrescar
            </button>
        </div>
    </header>

    <!-- Tabla de bitácora -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaBitacora" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Usuario</th>
                            <th scope="col">Módulo</th>
                            <th scope="col">Acción</th>
                            <th scope="col">IP</th>
                            <th scope="col">Fecha</th>
                            <th scope="col" class="text-center">Detalles</th>
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

<?php include 'partials/_modal_detalles.php'; ?>

<!-- Recursos específicos de la página -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bitacora.css">
<script src="<?= BASE_URL ?>/assets/js/bitacora.js" defer></script>