<!-- ==========================================
    MÓDULO DE USUARIOS - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-users-cog me-2 text-primary"></i>
            Gestión de Usuarios
        </h1>
        <div class="btn-group" role="group" aria-label="Acciones de usuario">
            <?php if (isset($permisos['usuario']['registrar']) && $permisos['usuario']['registrar'] == 1) { ?>
                <button class="btn btn-primary fw-semibold" id="btn-nuevo">
                    <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
                </button>
            <?php } ?>
        </div>
    </header>

    <!-- Tabla de usuario (section semántica) -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tabla-usuario" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Usuario</th>
                            <th scope="col">Rol</th>
                            <th scope="col">Estatus</th>
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

<!-- Modales (incluidos como partials) -->
<?php include 'partials/_modal_usuario.php'; ?>

<script>
    const permisosDB = <?= isset($permisos) ? json_encode($permisos) : 'null' ?>;
</script>

<!-- Recursos específicos de la página -->