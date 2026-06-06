<main class="container-fluid py-4">
    <header class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-history me-2 text-warning" aria-hidden="true"></i>
            Bitácora del Sistema
        </h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltros" aria-expanded="false" aria-controls="collapseFiltros">
                <i class="fas fa-filter me-2"></i>Filtros
            </button>
            <button class="btn btn-primary text-white shadow-sm" id="btnActualizar">
                <i class="fas fa-sync-alt me-2"></i>Actualizar
            </button>
        </div>
    </header>

    <!-- Barra de Filtros Colapsable -->
    <div class="collapse mb-4" id="collapseFiltros">
        <section class="card shadow-sm border-0 bg-body-tertiary">
            <div class="card-body">
                <form id="formFiltros" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Rango de Fecha</label>
                        <div class="input-group input-group-sm">
                            <input type="date" id="fecha_desde" class="form-control" title="Desde">
                            <span class="input-group-text bg-transparent border-start-0 border-end-0 text-muted small">al</span>
                            <input type="date" id="fecha_hasta" class="form-control" title="Hasta">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Módulo</label>
                        <select id="filtro_modulo" class="form-select form-select-sm">
                            <option value="">TODOS LOS MÓDULOS</option>
                            <option value="SEGURIDAD">SEGURIDAD</option>
                            <option value="USUARIOS">USUARIOS</option>
                            <option value="PERFIL">PERFIL</option>
                            <option value="NOTICIAS">NOTICIAS</option>
                            <option value="INVENTARIO">INVENTARIO</option>
                            <option value="PERSONAL">PERSONAL</option>
                        </select>
                    </div>
                    <div class="col-md-5 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary text-white flex-grow-1">
                            <i class="fas fa-search me-1"></i>Buscar
                        </button>
                        <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-eraser me-1"></i>Limpiar
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <!-- Tabla -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaBitacora" style="width:100%">
                    <caption class="visually-hidden">Listado de actividades del sistema</caption>
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

<?php include __DIR__ . '/partials/_modal_detalles.php'; ?>

<!-- ===== RECURSOS DE LA PÁGINA ===== -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bitacora.css">
<!-- ✅ AGREGAR EL SCRIPT DE BITÁCORA -->
<script src="<?= BASE_URL ?>/assets/js/bitacora.js"></script>