<!-- ==========================================
    MODAL DE HORARIO - ASIGNACIÓN VISUAL POR COLORES
    ========================================== -->

<div class="modal fade" id="modalHorario" tabindex="-1" aria-labelledby="modalHorarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalHorarioLabel">
                    <i class="fas fa-calendar-check text-warning me-2"></i>
                    <span id="modalTitleTextHorario"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formHorario">
                <div class="modal-body">

                    <input type="hidden" name="id_horario" id="id_horario">

                    <!-- Empleado -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-12 position-relative">
                            <label for="empleado" class="form-label fw-semibold">
                                Empleado <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="empleado" name="empleado">
                                <!-- Contenido Dinámico -->
                            </select>
                            <div class="form-label" id="sempleado"></div>
                        </div>
                    </div>

                    <!-- SELECTOR DE TURNOS COMO BOTONES DE COLORES -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">
                                Seleccionar Turno <span class="text-danger">*</span>
                            </label>
                            <div id="botonesTurnos" class="d-flex flex-wrap gap-2">
                                <!-- JavaScript llena esto -->
                            </div>
                            <small class="text-muted">Seleccione un turno y haga clic en un día</small>
                            <div class="form-label" id="sturno"></div>
                        </div>
                    </div>

                    <!-- CALENDARIO -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <div class="card border">
                                <!-- Navegación -->
                                <div class="card-header bg-light py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnMesAnterior">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <span class="fw-bold fs-6" id="tituloMes"></span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnMesSiguiente">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="card-body p-2">
                                    <!-- Días de la semana -->
                                    <!-- Grid de días -->
                                    <div id="calendarioDias" class="d-grid calendario-grid" style="grid-template-columns: 2.5rem repeat(7, minmax(0, 1fr));">
                                        <!-- JavaScript llena esto -->
                                    </div>
                                </div>
                                
                                <!-- Footer con botones y contador -->
                                <div class="card-footer bg-light py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnSeleccionarTodos">
                                                <i class="fas fa-check-double me-1"></i>Todos
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnDiasHabiles">
                                                <i class="fas fa-calendar-week me-1"></i>Lun-Vie
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnLimpiarSeleccion">
                                                <i class="fas fa-eraser me-1"></i>Limpiar
                                            </button>
                                        </div>
                                        <span class="badge bg-primary fs-6 px-3 py-2" id="contadorDias">0 días</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-label" id="sfecha"></div>
                        </div>
                    </div>

                    <div id="panelExtenderHorario" class="card border mt-3 d-none">
                        <div class="card-header bg-light py-2 fw-semibold">Elegir tramo</div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                Seleccione el rango de fechas que desea renovarlo.
                            </p>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label for="extensionOrigenInicio" class="form-label">Desde</label>
                                    <input type="date" class="form-control" id="extensionOrigenInicio">
                                </div>
                                <div class="col-md-4">
                                    <label for="extensionOrigenFin" class="form-label">Hasta</label>
                                    <input type="date" class="form-control" id="extensionOrigenFin">
                                </div>
                                <div class="col-md-4">
                                    <label for="extensionDestinoInicio" class="form-label">Renovar desde</label>
                                    <input type="date" class="form-control" id="extensionDestinoInicio">
                                </div>
                            </div>
                            <div id="resumenExtension" class="small text-muted mt-3 mb-2 d-none" role="status"></div>
                            <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                                <span>Vista previa:</span>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="extension-legend-sample extension-legend-new" aria-hidden="true"></span>
                                    <span>borde azul punteado = nuevo turno;</span>
                                </span>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="extension-legend-sample extension-legend-conflict" aria-hidden="true"></span>
                                    <span>rojo = conflicto.</span>
                                </span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnVistaPreviaExtension">
                                    <i class="fas fa-eye me-1"></i>Vista previa
                                </button>
                                <button type="button" class="btn btn-sm btn-warning text-dark" id="btnAplicarExtension" disabled>
                                    <i class="fas fa-plus me-1"></i>Agregar al horario
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCerrarExtension">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="asignaciones" id="asignaciones">

                </div>

                <div class="modal-footer border-top-0 justify-content-between">
                    <button type="button" class="btn btn-outline-warning text-dark d-none" id="btnExtenderHorario">
                        <i class="fas fa-copy me-1"></i>Extender horario
                    </button>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnHorarioForm">
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .dia-calendario {
        aspect-ratio: 1;
        cursor: pointer;
        border-radius: 0.375rem;
        transition: all 0.15s;
        user-select: none;
        font-size: 0.85rem;
        border: 2px solid transparent;
    }
    .dia-calendario:hover {
        transform: scale(1.08);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .dia-calendario.otro-mes {
        opacity: 0.2;
        pointer-events: none;
    }
    .dia-calendario.fecha-pasada {
        cursor: not-allowed;
        opacity: 0.45;
        pointer-events: none;
    }
    .dia-calendario.hoy {
        border: 2px solid #0d6efd !important;
    }
    .dia-calendario.extension-preview {
        outline: 3px dotted #0d6efd;
        outline-offset: -3px;
    }
    .dia-calendario.extension-conflicto {
        outline: 3px solid #dc3545;
        outline-offset: -3px;
    }
    .extension-legend-sample {
        display: inline-block;
        width: 1.15rem;
        height: 1.15rem;
        border-radius: 0.25rem;
        background-color: #4caf50;
    }
    .extension-legend-new {
        border: 2px dotted #0d6efd;
    }
    .extension-legend-conflict {
        border: 2px solid #dc3545;
    }
    .dia-calendario.fin-semana {
        color: #dc3545;
    }

    .selector-calendario {
        border: 0;
        background: transparent;
        color: #6c757d;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem;
    }
    .selector-calendario:hover {
        background: #fff3cd;
        color: #664d03;
    }
    .selector-fila {
        align-self: center;
        font-size: 0.65rem;
    }
    
    /* Botones de turno */
    .btn-turno {
        border: 2px solid transparent;
        margin: 2px;
        transition: all 0.2s;
        font-weight: 500;
    }
    .btn-turno:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .btn-turno.activo {
        border: 3px solid #000 !important;
        transform: scale(1.08);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    
    /* Leyenda */
    .leyenda-item {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.8rem;
    }
    .leyenda-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        display: inline-block;
    }
</style>