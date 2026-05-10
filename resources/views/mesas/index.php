<!-- ==========================================
    MÓDULO DE MESAS - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado semántico con header -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-chair me-2 text-primary"></i>
            Gestión de Mesas
        </h1>
        <button class="btn btn-primary fw-semibold shadow-sm" id="btnNuevaMesa">
            <i class="fas fa-plus me-2"></i>Nueva Mesa
        </button>
    </header>

    <!-- Tabla de mesas -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaMesas" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID Mesa</th>
                            <th scope="col">N° Mesa</th>
                            <th scope="col">Área</th>
                            <th scope="col">Capacidad</th>
                            <th scope="col">Estado</th>
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

<!-- ==========================================
    MODALES
========================================== -->

<!-- Modal para Registrar/Editar Mesa -->
<div class="modal fade" id="modalMesa" tabindex="-1" aria-labelledby="modalMesaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalMesaLabel">
                    <i class="fas fa-chair me-2"></i>
                    <span id="modalTitle">Registrar Mesa</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formMesa" autocomplete="off">
                    <input type="hidden" id="peticion" name="peticion" value="registrar">
                    <input type="hidden" id="id_mesa" name="id_mesa">

                    <div class="mb-3">
                        <label for="id_area" class="form-label fw-semibold">
                            <i class="fas fa-building me-1"></i> Área
                        </label>
                        <select class="form-select" id="id_area" name="id_area" required>
                            <option value="">Seleccione un área</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione un área</div>
                    </div>

                    <div class="mb-3">
                        <label for="numero_mesa" class="form-label fw-semibold">
                            <i class="fas fa-hashtag me-1"></i> Número de Mesa
                        </label>
                        <input type="number" class="form-control" id="numero_mesa" name="numero_mesa" 
                               min="1" max="999" required>
                        <div class="invalid-feedback">Ingrese un número de mesa válido (1-999)</div>
                    </div>

                    <div class="mb-3">
                        <label for="capacidad" class="form-label fw-semibold">
                            <i class="fas fa-users me-1"></i> Capacidad
                        </label>
                        <input type="number" class="form-control" id="capacidad" name="capacidad" 
                               min="1" max="50" required>
                        <div class="invalid-feedback">Ingrese una capacidad válida (1-50 personas)</div>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label fw-semibold">
                            <i class="fas fa-circle me-1"></i> Estado
                        </label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="DISPONIBLE">🟢 Disponible</option>
                            <option value="LIBRE">🟢 Libre</option>
                            <option value="OCUPADA">🔴 Ocupada</option>
                            <option value="MANTENIMIENTO">🟡 Mantenimiento</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="estatus" class="form-label fw-semibold">
                            <i class="fas fa-toggle-on me-1"></i> Estatus
                        </label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="estatus" name="estatus" value="1" checked>
                            <label class="form-check-label" for="estatus">Activo</label>
                        </div>
                        <small class="text-muted">Desactive la mesa si no está disponible para uso</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnGuardarMesa">
                    <i class="fas fa-save me-1"></i>Guardar Mesa
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Estado -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalCambiarEstadoLabel">
                    <i class="fas fa-exchange-alt me-2"></i>Cambiar Estado de Mesa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>Mesa: <strong id="cambiarEstadoNumeroMesa"></strong></p>
                <div class="mb-3">
                    <label for="nuevoEstado" class="form-label fw-semibold">Nuevo Estado</label>
                    <select class="form-select" id="nuevoEstado">
                        <option value="DISPONIBLE">🟢 Disponible</option>
                        <option value="LIBRE">🟢 Libre</option>
                        <option value="OCUPADA">🔴 Ocupada</option>
                        <option value="MANTENIMIENTO">🟡 Mantenimiento</option>
                    </select>
                </div>
                <input type="hidden" id="cambiarEstadoIdMesa">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarCambioEstado">Cambiar Estado</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Eliminar Mesa -->
<div class="modal fade" id="modalEliminarMesa" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarLabel">
                    <i class="fas fa-trash-alt me-2"></i>Eliminar Mesa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar la mesa <strong id="eliminarNumeroMesa"></strong>?</p>
                <p class="text-danger mb-0"><small>Esta acción no se puede deshacer.</small></p>
                <input type="hidden" id="eliminarIdMesa">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                    <i class="fas fa-trash-alt me-1"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recursos específicos de la página -->
<script src="<?= BASE_URL ?>/assets/js/mesas.js" defer></script>