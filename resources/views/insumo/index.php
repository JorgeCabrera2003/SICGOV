<!-- ==========================================
    MÓDULO DE INGREDIENTES - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-droplet me-2 text-primary"></i>
            Gestión de Insumos
        </h1>
    </header>

    <ul class="nav nav-tabs nav-justified">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#insumos">Control de Insumos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#movimientos">Movimientos de Entradas y Salidas</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane container active" id="insumos">

            <div class="btn-group mt-4" role="group" aria-label="Acciones de insumo">
                <?php
                if (isset($permisos['insumo']['registrar']) && $permisos['insumo']['registrar'] == 1) {
                    ?>
                    <button class="btn btn-warning text-dark fw-semibold" id="btnNuevoInsumo">
                        <i class="fas fa-plus me-2"></i>Nuevo Insumo
                    </button>
                    <?php
                }
                if (isset($permisos['categoria_insumo']['ver']) && $permisos['categoria_insumo']['ver'] == 1) {
                    ?>
                    <button class="btn btn-outline-warning" id="btn-ModalCategorias">
                        <i class="fas fa-tags me-2"></i>Categorías
                    </button>
                    <?php
                }
                if (isset($permisos['insumo']['suministrar']) && $permisos['insumo']['suministrar'] == 1) {
                    ?>
                    <button class="btn btn-warning text-dark fw-semibold" id="btnSuministrarLote">
                        <i class="fa-solid fa-box me-2"></i>Suministrar Insumos
                    </button>
                    <?php
                }
                ?>
            </div>

            <section class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablaInsumo" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Categoria</th>
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
        </div>
        <div class="tab-pane container fade" id="movimientos">

            <div class="btn-group mt-4" role="group" >
                <button class="btn btn-warning text-dark fw-semibold" id="btnRecargarHistorial">
                    <i class="fa-solid fa-rotate me-2"></i>Recargar Historial
                </button>
            </div>
            <section class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h2 class="h3 mb-0">
                        <i class="fa-solid fa-down-long me-2 text-primary"></i>
                        Entradas de Insumos
                    </h2>
                    <div class="table-responsive mt-2">
                        <table class="table table-hover align-middle" id="tablaEntradas" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Insumo</th>
                                    <th scope="col">Proveedor</th>
                                    <th scope="col">Cantidad Ingresada</th>
                                    <th scope="col">Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables carga los datos aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h2 class="h3 mb-0">
                        <i class="fa-solid fa-up-long text-primary"></i>
                        Salidas de Insumos
                    </h2>
                    <div class="table-responsive mt-2">
                        <table class="table table-hover align-middle" id="tablaSalidas" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Insumo</th>
                                    <th scope="col">Pedido</th>
                                    <th scope="col">Cantidad Egresada</th>
                                    <th scope="col">Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables carga los datos aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Tabla de productos (section semántica) -->

</main>

<!-- Modales (incluidos como partials) -->
<?php
include_once 'partials/_modal_insumo.php';
include_once 'partials/_modal_categoria_insumo.php';
include_once 'partials/_modal_suministrar_insumo.php';
include_once 'partials/_modal_movimientos_insumo.php';
include_once 'partials/_modal_asociar_proveedor.php';
include_once 'partials/_modal_suministrar_lote.php';
include_once $basePath . '/resources/views/categoria_insumo/partials/_modal_categoria_insumo_form.php';
?>

<!-- Recursos específicos de la página -->
<script type="module" src="<?= BASE_URL ?>/assets/js/Controllers/InsumoController.js" defer></script>