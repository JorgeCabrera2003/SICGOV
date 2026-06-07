<!-- ==========================================
    MÓDULO DE ÁREAS - GOOD VIBES
    HTML Semántico + Bootstrap 5.3
========================================== -->

<main class="container-fluid py-4">
    <!-- Encabezado -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-building me-2 text-primary"></i>
            Gestión de Áreas
        </h1>
        <button class="btn btn-primary fw-semibold shadow-sm" id="btnNuevaArea">
            <i class="fas fa-plus me-2"></i>Nueva Área
        </button>
    </header>

    <!-- Tabla de áreas -->
    <section class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaAreas" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Descripción</th>
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

<!-- Modal para Registrar/Editar Área -->
<div class="modal fade" id="modalArea" tabindex="-1" aria-labelledby="modalAreaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAreaLabel">
                    <i class="fas fa-building me-2"></i>
                    <span id="modalTitle">Registrar Área</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formArea" autocomplete="off">
                    <input type="hidden" id="peticion" name="peticion" value="registrar">
                    <input type="hidden" id="id_area" name="id_area">

                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold">
                            <i class="fas fa-tag me-1"></i> Nombre del Área
                        </label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               minlength="3" maxlength="60" required>
                        <div class="invalid-feedback">Ingrese un nombre válido (3-60 caracteres)</div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-semibold">
                            <i class="fas fa-align-left me-1"></i> Descripción
                        </label>
                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                  rows="3" maxlength="200"></textarea>
                        <div class="invalid-feedback">La descripción no puede exceder los 200 caracteres</div>
                        <small class="text-muted">Máximo 200 caracteres (opcional)</small>
                    </div>

                    <div class="mb-3">
                        <label for="estatus" class="form-label fw-semibold">
                            <i class="fas fa-toggle-on me-1"></i> Estatus
                        </label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="estatus" name="estatus" value="1" checked>
                            <label class="form-check-label" for="estatus">Activo</label>
                        </div>
                        <small class="text-muted">Desactive el área si no está disponible</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnGuardarArea">
                    <i class="fas fa-save me-1"></i>Guardar Área
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Eliminar Área -->
<div class="modal fade" id="modalEliminarArea" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarLabel">
                    <i class="fas fa-trash-alt me-2"></i>Eliminar Área
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el área <strong id="eliminarNombreArea"></strong>?</p>
                <p class="text-warning mb-2"><small>Nota: Las mesas asociadas a esta área no se eliminarán, pero quedarán sin área asignada.</small></p>
                <p class="text-danger mb-0"><small>Esta acción no se puede deshacer.</small></p>
                <input type="hidden" id="eliminarIdArea">
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