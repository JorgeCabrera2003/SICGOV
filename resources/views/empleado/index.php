<!-- ==========================================
    MÓDULO DE EMPLEADOS - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-id-badge me-2 text-primary"></i>
            Gestión de Empleados
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de empleado">
            <?php if (isset($permisos['empleado']['registrar']) && $permisos['empleado']['registrar'] == 1) { ?>
            <button class="btn btn-primary fw-semibold" id="btnNuevoEmpleado">
                <i class="fas fa-plus me-2"></i>Nuevo Empleado
            </button>
            <?php } ?>
        </div>
    </header>

    <!-- Tabla de empleados (section semántica) -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaEmpleado" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Cédula</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Apellido</th>
                            <th scope="col">Cargo</th>
                            <th scope="col">Edad</th>
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
<?php include 'partials/_modal_empleado.php'; ?>
<?php include 'partials/_modal_consultar_empleado.php'; ?>

<!-- Recursos específicos de la página -->
<script>
    const permisosDB = <?= isset($permisos) ? json_encode($permisos) : 'null' ?>;
</script>
<script src="<?= BASE_URL ?>public/assets/js/empleado.js"></script>
