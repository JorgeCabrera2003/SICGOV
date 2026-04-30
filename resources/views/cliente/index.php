<!-- ==========================================
    MÓDULO DE CLIENTES - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-users me-2 text-primary"></i>
            Gestión de Clientes
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de cliente">
            <button class="btn btn-primary fw-semibold" id="btnNuevoCliente">
                <i class="fas fa-plus me-2"></i>Nuevo Cliente
            </button>
        </div>
    </header>

    <!-- Tabla de clientes (section semántica) -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaCliente" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Cédula</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Apellido</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Edad</th>
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
<?php include 'partials/_modal_cliente.php'; ?>
<?php include 'partials/_modal_consultar_cliente.php'; ?>

<!-- Recursos específicos de la página -->
<script src="<?= BASE_URL ?>/assets/js/cliente.js" defer></script>
