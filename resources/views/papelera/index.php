<!-- ==========================================
    PAPELERA DE RECICLAJE - GOOD VIBES
    Módulo Global para Restauración de Datos
========================================== -->

<main class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-trash-alt me-2 text-danger"></i>
            Papelera de Reciclaje
        </h1>
        <div class="text-muted small">
            Aquí puedes recuperar registros que fueron eliminados de otros módulos.
        </div>
    </header>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <div class="table-responsive mt-4">
                <table class="table table-hover align-middle" id="tablaPapelera" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Módulo</th>
                            <th scope="col">Descripción / Nombre</th>
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <!-- DataTables cargarà los datos aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Scripts del módulo -->
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/PapeleraController.js" defer></script>
