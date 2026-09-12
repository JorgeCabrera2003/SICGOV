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
                            <small class="text-muted">Seleccione un turno y luego haga clic en los días del calendario</small>
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
                                    <div class="d-grid mb-1" style="grid-template-columns: repeat(7, 1fr);">
                                        <div class="text-center fw-bold small py-1">LUN</div>
                                        <div class="text-center fw-bold small py-1">MAR</div>
                                        <div class="text-center fw-bold small py-1">MIÉ</div>
                                        <div class="text-center fw-bold small py-1">JUE</div>
                                        <div class="text-center fw-bold small py-1">VIE</div>
                                        <div class="text-center fw-bold small py-1 text-danger">SÁB</div>
                                        <div class="text-center fw-bold small py-1 text-danger">DOM</div>
                                    </div>
                                    <!-- Grid de días -->
                                    <div id="calendarioDias" class="d-grid" style="grid-template-columns: repeat(7, 1fr);">
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

                    <!-- LEYENDA DE COLORES -->
                    <div id="leyendaColores" class="d-flex flex-wrap gap-3 mb-2">
                        <!-- JavaScript llena esto -->
                    </div>

                    <input type="hidden" name="asignaciones" id="asignaciones">

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnHorarioForm">
                    </button>
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
    .dia-calendario.hoy {
        border: 2px solid #0d6efd !important;
    }
    .dia-calendario.fin-semana {
        color: #dc3545;
    }
    
    /* Botones de turno */
    .btn-turno {
        border: 2px solid transparent;
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